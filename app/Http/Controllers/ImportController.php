<?php

namespace App\Http\Controllers;

use App\Console\Commands\ImportarExcelProductos;
use App\Jobs\ActualizarPreciosJob;
use App\Jobs\ImportarClientesJob;
use App\Jobs\ImportarOfertasJob;
use App\Jobs\ImportarProductosDesdeExcelJob;
use App\Jobs\ImportarVendedoresJob;
use Illuminate\Http\Request;

class ImportController extends Controller
{
    public function importar(Request $request)
    {

        // Guardar archivo en almacenamiento temporal
        $archivoPath = str_replace('/storage/', '', parse_url($request->path, PHP_URL_PATH));

        $lista_id = $request->lista_id;


        // Encolar el Job
        ActualizarPreciosJob::dispatch($archivoPath, $lista_id);
    }

 public function importarProductos(Request $request)
{
    $request->validate([
        'archivo' => ['required', 'file', 'mimes:xlsx,xls', 'max:51200'],
    ]);

    \Log::info('IMPORT HIT', [
        'orig_name' => $request->file('archivo')->getClientOriginalName(),
        'size' => $request->file('archivo')->getSize(),
        'disk' => config('filesystems.default'),
    ]);

    $archivoPath = $request->file('archivo')->store('importaciones');

    \Log::info('IMPORT DISPATCH JOB', [
        'archivoPath' => $archivoPath,
        'resolvedPath' => \Storage::path($archivoPath),
        'exists' => file_exists(\Storage::path($archivoPath)),
    ]);

    \App\Jobs\ImportarProductosDesdeExcelJob::dispatch($archivoPath);

    return back()->with('success', 'Importación encolada. Revisá el log para el progreso.');
}


public function vincularImagenesProductos()
{
    VincularImagenesProductosJob::dispatch('public', 'productos');

    return back()->with('success', 'Job encolado: Vincular imágenes de productos.');
}

    public function importarClientes(Request $request)
    {
        $request->validate([
            'archivo' => 'required|mimes:xlsx,xls'
        ]);
        // Guardar archivo en almacenamiento temporal
        $archivoPath = $request->file('archivo')->store('importaciones');

        // Encolar el Job
        ImportarClientesJob::dispatch($archivoPath);
    }

    public function importarVendedores(Request $request)
    {
        $request->validate([
            'archivo' => 'required|mimes:xlsx,xls'
        ]);
        // Guardar archivo en almacenamiento temporal
        $archivoPath = $request->file('archivo')->store('importaciones');

        // Encolar el Job
        ImportarVendedoresJob::dispatch($archivoPath);
    }

    public function importarOfertas(Request $request)
    {
        $request->validate([
            'archivo' => 'required|mimes:xlsx,xls'
        ]);
        // Guardar archivo en almacenamiento temporal
        $archivoPath = $request->file('archivo')->store('importaciones');

        // Encolar el Job
        ImportarOfertasJob::dispatch($archivoPath);
    }
}
