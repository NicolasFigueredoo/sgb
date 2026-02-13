<?php

namespace App\Jobs;

use App\Models\Producto;
use App\Models\ImagenProducto;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class VincularImagenesProductosJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $disk = 'public',
        public string $dir = 'productos'
    ) {}

    public function handle(): void
    {
        Log::info('IMG JOB START', ['disk' => $this->disk, 'dir' => $this->dir]);

        if (!Storage::disk($this->disk)->exists($this->dir)) {
            Log::error('IMG DIR NOT FOUND', ['dir' => $this->dir]);
            return;
        }

        $files = Storage::disk($this->disk)->files($this->dir);

        $procesadas = 0;
        $asignadas = 0;
        $sin_match = 0;

        foreach ($files as $path) {
            $procesadas++;

            $filename = pathinfo($path, PATHINFO_FILENAME); // sin extension
            $base = $this->normalizeCode($filename);

            if ($base === '') {
                continue;
            }

            // 1) Buscar por code
            $producto = Producto::where('code', $base)->first();

            // 2) Si no, buscar por code_oem
            if (!$producto) {
                $producto = Producto::where('code_oem', $base)->first();
            }

            if (!$producto) {
                $sin_match++;
                if ($sin_match <= 20) {
                    Log::warning('IMG NO MATCH', ['file' => $path, 'base' => $base]);
                }
                continue;
            }

            // Evitar duplicar (si ya tiene esa imagen)
            $exists = ImagenProducto::where('producto_id', $producto->id)
                ->where('image', $path)
                ->exists();

            if ($exists) {
                continue;
            }

            ImagenProducto::create([
                'producto_id' => $producto->id,
                'image' => $path, // ej: productos/M208-5A-010.jpg
            ]);

            $asignadas++;

            if ($asignadas % 200 === 0) {
                Log::info('IMG PROGRESS', [
                    'procesadas' => $procesadas,
                    'asignadas' => $asignadas,
                    'sin_match' => $sin_match,
                ]);
            }
        }

        Log::info('IMG JOB END', [
            'procesadas' => $procesadas,
            'asignadas' => $asignadas,
            'sin_match' => $sin_match,
        ]);
    }

    private function normalizeCode(string $s): string
    {
        $s = trim($s);
        $s = str_replace([' ', '_'], '-', $s);
        $s = preg_replace('/-+/', '-', $s);
        return strtoupper($s);
    }
}
