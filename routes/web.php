<?php

use App\Http\Controllers\ContactoController;
use App\Http\Controllers\DescargarArchivo;
use App\Http\Controllers\HomePages;
use App\Http\Controllers\NovedadesController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SendContactInfoController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Admin\AutorizacionClientesController;
use App\Http\Controllers\PedidoController;


# ---------------------- Rutas de zona pública ---------------------- #

Route::get('/', [HomePages::class, 'home'])->name('home');
Route::get('/nosotros', [HomePages::class, 'nosotros'])->name('nosotros');
Route::get('/calidad', [HomePages::class, 'calidad'])->name('calidad');
Route::get('/novedades', [HomePages::class, 'novedades'])->name('novedades');
Route::get('/contacto', [HomePages::class, 'contacto'])->name('contacto');
Route::get('/novedades/{id}', [NovedadesController::class, 'novedadesShow'])->name('novedades');
Route::post('/contacto/sendemail', [ContactoController::class, 'sendContact'])->name('send.contact');
Route::get('/catalogos', [HomePages::class, 'catalogos'])->name('catalogos');
Route::get('/productos', [ProductoController::class, 'indexVistaPrevia'])->name('productos');
Route::get('/productos/categorias', [ProductoController::class, 'indexCategorias'])->name('productos.categorias');
Route::get('/p/{codigo}', [ProductoController::class, 'show'])->name('producto');

Route::get('/busqueda', [ProductoController::class, 'SearchProducts'])->name('searchproducts');


# ------------------------------------------------------------------- #
// Ruta para la API de búsqueda (AJAX)
Route::post('/api/search', [SearchController::class, 'search'])
    ->name('api.search');

// Ruta para la página de resultados completos
Route::get('/buscar', [SearchController::class, 'searchPage'])
    ->name('search.results');

Route::get('/{code}', [ProductoController::class, 'handleQR'])->name(
    'handleQR'
);


Route::get('/bejerman/tablas', function () {
    try {
        $tables = DB::connection('bejerman')
            ->select("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE' ORDER BY TABLE_NAME");

        return response()->json([
            'ok' => true,
            'count' => count($tables),
            'tables' => array_map(fn($t) => $t->TABLE_NAME, $tables),
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'ok' => false,
            'error' => $e->getMessage(),
        ], 500);
    }
});


Route::get('/bejerman/per-estructura', function () {
    $columns = DB::connection('bejerman')
        ->select("SELECT COLUMN_NAME, DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'per'");

    return response()->json($columns);
});









Route::get('/fix-images', [ProductoController::class, 'fixImagePath'])->name('fix.images');

Route::get('/imagenes-prod', [ProductoController::class, 'imagenesProducto']);
Route::get('/agregar-marca', [ProductoController::class, 'agregarMarca']);



// routes/web.php
Route::get('/descargar/archivo/{id}', [DescargarArchivo::class, 'descargarArchivo'])
    ->name('descargar.archivo');

Route::post('/newsletter/store', [App\Http\Controllers\NewsletterController::class, 'store'])->name('newsletter.store');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render(component: 'dashboard');
    })->name('dashboard');
});



Route::get('/bejerman/clientes-estructura', function () {
    $columns = DB::connection('bejerman')
        ->select("SELECT COLUMN_NAME, DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'Clientes'");
    
    return response()->json($columns);
});

Route::get('/bejerman/clientes-sample', function () {
    $clientes = DB::connection('bejerman')
        ->table('Clientes')
        ->limit(3)
        ->get();
    
    return response()->json($clientes);
});

Route::get('/bejerman/articulos-estructura', function () {
    $columns = DB::connection('bejerman')
        ->select("SELECT COLUMN_NAME, DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'Articulos'");
    
    return response()->json($columns);
});

Route::get('/bejerman/cabventa-estructura', function () {
    $columns = DB::connection('bejerman')
        ->select("SELECT COLUMN_NAME, DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'CabVenta'");
    
    return response()->json($columns);
});


// Rutas Admin - Autorización de Clientes
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/autorizacion-clientes', [AutorizacionClientesController::class, 'index'])
        ->name('admin.autorizacion.index');
    Route::post('/autorizacion-clientes/{user}/autorizar', [AutorizacionClientesController::class, 'autorizar'])
        ->name('admin.autorizacion.autorizar');
    Route::post('/autorizacion-clientes/{user}/rechazar', [AutorizacionClientesController::class, 'rechazar'])
        ->name('admin.autorizacion.rechazar');
    Route::post('/autorizacion-clientes/{user}/resincronizar', [AutorizacionClientesController::class, 'resincronizar'])
        ->name('admin.autorizacion.resincronizar');
});

// Rutas de Pedidos
Route::middleware(['auth'])->group(function () {
    Route::post('/pedidos/{pedido}/reintentar-bejerman', [PedidoController::class, 'reintentarEnvioBejerman'])
        ->name('pedidos.reintentar-bejerman');
});


Route::get('/bejerman/buscar-descuentos', function () {
    // Buscar tablas/columnas relacionadas con descuentos
    $columns = DB::connection('bejerman')
        ->select("SELECT TABLE_NAME, COLUMN_NAME 
                  FROM INFORMATION_SCHEMA.COLUMNS 
                  WHERE COLUMN_NAME LIKE '%dto%' 
                  OR COLUMN_NAME LIKE '%desc%'
                  OR COLUMN_NAME LIKE '%bonif%'
                  OR TABLE_NAME LIKE '%Desc%'
                  OR TABLE_NAME LIKE '%Dto%'
                  ORDER BY TABLE_NAME");
    
    return response()->json($columns);
});

Route::get('/bejerman/listaprec-estructura', function () {
    $columns = DB::connection('bejerman')
        ->select("SELECT COLUMN_NAME, DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'ListaPrec'");
    
    return response()->json($columns);
});

Route::get('/bejerman/condvta-estructura', function () {
    $columns = DB::connection('bejerman')
        ->select("SELECT COLUMN_NAME, DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'CondVta'");
    
    return response()->json($columns);
});

Route::get('/bejerman/condvta-sample', function () {
    $data = DB::connection('bejerman')
        ->table('CondVta')
        ->limit(5)
        ->get();
    
    return response()->json($data);
});


Route::post('/admin/productos/vincular-imagenes', [ImportController::class, 'vincularImagenesProductos'])
    ->name('admin.productos.vincular_imagenes');


    
require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
require __DIR__ . '/admin_auth.php';
