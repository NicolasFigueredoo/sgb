<?php

namespace App\Jobs;

use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Producto;
use App\Models\Modelo;
use App\Models\Motor;
use App\Models\ProductoModelo;
use App\Models\ProductoMotor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportarProductosDesdeExcelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $archivoPath;

    public function __construct(string $archivoPath)
    {
        $this->archivoPath = $archivoPath;
    }

    public function handle()
    {
        $disk = config('filesystems.default', 'local');

        Log::info('IMPORT START', [
            'disk' => $disk,
            'archivoPath' => $this->archivoPath,
        ]);

        $filePath = Storage::disk($disk)->path($this->archivoPath);

        Log::info('IMPORT FILE', [
            'filePath' => $filePath,
            'exists' => file_exists($filePath),
            'size' => file_exists($filePath) ? filesize($filePath) : null,
        ]);

        if (!file_exists($filePath)) {
            Log::error('IMPORT FILE NOT FOUND', ['filePath' => $filePath]);
            return;
        }

        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();

        $highestRow = (int) $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        Log::info('IMPORT SHEET', [
            'highestRow' => $highestRow,
            'highestColumn' => $highestColumn,
        ]);

        if ($highestRow < 2) {
            Log::warning('IMPORT EMPTY EXCEL', ['highestRow' => $highestRow]);
            return;
        }

        $procesados = 0;
        $saltados = 0;
        $errores = 0;

        // Normaliza números con coma, etc.
        $toIntSafe = function ($value, $default = 0) {
            if ($value === null) return $default;
            $v = trim((string) $value);
            if ($v === '') return $default;

            // si tiene letras o símbolos raros, devolvemos default
            if (preg_match('/[a-zA-Z]/', $v)) return $default;

            // 1.234 -> 1234 (miles)
            $v = str_replace('.', '', $v);
            // 123,45 -> 123.45
            $v = str_replace(',', '.', $v);

            if (!is_numeric($v)) return $default;

            // precio/unidad_pack en tu tabla parecen enteros
            $n = (float) $v;
            if ($n < 0) return $default;

            return (int) round($n);
        };

        for ($row = 2; $row <= $highestRow; $row++) {
            try {
                // Mapeo EXACTO por letras según tu Excel:
                // A CODIGO SGB
                // B MARCA
                // C PRODUCTO/CATEGORIA
                // D Modelo / Motor (texto, no se guarda en productos)
                // E MODELOS
                // F MOTORES
                // G CILINDRADA
                // H..K Alternativos
                // L MEDIDAS
                // M Unidades por Juego
                // N Precio Unitario
                // O Precio Jgo

                $code = trim((string)($sheet->getCell("A{$row}")->getValue() ?? ''));
                if ($code === '') {
                    $saltados++;
                    continue;
                }

                $marcaNombre = trim((string)($sheet->getCell("B{$row}")->getValue() ?? ''));
                $categoriaNombre = trim((string)($sheet->getCell("C{$row}")->getValue() ?? ''));
                $modelosTxt = trim((string)($sheet->getCell("E{$row}")->getValue() ?? ''));
                $motoresTxt = trim((string)($sheet->getCell("F{$row}")->getValue() ?? ''));
                $medidas = trim((string)($sheet->getCell("L{$row}")->getValue() ?? ''));

                $unidad_pack_raw = $sheet->getCell("M{$row}")->getValue();
                $precio_unit_raw = $sheet->getCell("N{$row}")->getValue();
                $precio_jgo_raw  = $sheet->getCell("O{$row}")->getValue();

                $code_oem = trim((string)($sheet->getCell("H{$row}")->getValue() ?? ''));

                $unidad_pack = $toIntSafe($unidad_pack_raw, 1);
                if ($unidad_pack <= 0) $unidad_pack = 1; // NOT NULL

                // elegimos precio jgo si existe, si no unitario
                $precio_jgo  = $toIntSafe($precio_jgo_raw, 0);
                $precio_unit = $toIntSafe($precio_unit_raw, 0);
                $precio = $precio_unit;

                // guardia: si viene un numero absurdo, lo anulamos y logueamos
                if ($precio > 999999999) { // ajustá el umbral a tu realidad
                    Log::warning('IMPORT PRECIO ABERRANTE', [
                        'row' => $row,
                        'code' => $code,
                        'precio_raw_unit' => $precio_unit_raw,
                        'precio_raw_jgo' => $precio_jgo_raw,
                        'precio_calculado' => $precio,
                    ]);
                    $precio = 0;
                }

                $categoriaId = null;
                if ($categoriaNombre !== '') {
                    $categoria = Categoria::firstOrCreate(['name' => $categoriaNombre]);
                    $categoriaId = $categoria->id;
                }

                $marcaId = null;
                if ($marcaNombre !== '') {
                    $marca = Marca::firstOrCreate(['name' => $marcaNombre]);
                    $marcaId = $marca->id;
                }

                $producto = Producto::updateOrCreate(
                    ['code' => $code],
                    [
                        'name' => $categoriaNombre !== '' ? $categoriaNombre : $code,
                        'code_oem' => $code_oem !== '' ? $code_oem : null,
                        'unidad_pack' => $unidad_pack,         // NOT NULL
                        'medidas' => $medidas !== '' ? $medidas : null,
                        'precio' => $precio,                   // int
                        'categoria_id' => $categoriaId,
                        'marca_id' => $marcaId,
                    ]
                );

                // Modelos (tabla producto_modelos con Modelo)
                if ($modelosTxt !== '') {
                    $modelosArr = array_filter(array_map('trim', preg_split('/,|;|\|/', $modelosTxt)));
                    foreach ($modelosArr as $modeloName) {
                        if ($modeloName === '') continue;

                        $modelo = Modelo::firstOrCreate(['name' => $modeloName]);
                        ProductoModelo::firstOrCreate([
                            'producto_id' => $producto->id,
                            'modelo_id' => $modelo->id,
                        ]);
                    }
                }

                // Motores (tabla producto_motores con Motor)
                if ($motoresTxt !== '') {
                    $motoresArr = array_filter(array_map('trim', preg_split('/,|;|\|/', $motoresTxt)));
                    foreach ($motoresArr as $motorName) {
                        if ($motorName === '') continue;

                        $motor = Motor::firstOrCreate(['name' => $motorName]);
                        ProductoMotor::firstOrCreate([
                            'producto_id' => $producto->id,
                            'motor_id' => $motor->id,
                        ]);
                    }
                }

                $procesados++;

                if ($procesados % 200 === 0) {
                    Log::info('IMPORT PROGRESS', [
                        'procesados' => $procesados,
                        'row' => $row,
                    ]);
                }

            } catch (\Throwable $e) {
                $errores++;
                Log::error('IMPORT ROW ERROR', [
                    'row' => $row,
                    'msg' => $e->getMessage(),
                ]);
                continue;
            }
        }

        Log::info('IMPORT DONE', [
            'procesados' => $procesados,
            'saltados' => $saltados,
            'errores' => $errores,
        ]);
    }
}
