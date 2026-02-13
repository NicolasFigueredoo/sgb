<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CuentaCorrienteController;
use App\Http\Controllers\ListaDePreciosController;
use App\Http\Controllers\MargenesController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\PedidoProductoController;
use App\Http\Controllers\PrivadaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\SendPedidoController;
use App\Models\Categoria;
use App\Models\InformacionDePago;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');

    Route::middleware('privada')->group(function () {
        Route::get('privada/productos', [ProductoController::class, 'indexPrivada'])->name('index.privada.productos');
        Route::get('privada/carrito', [PrivadaController::class, 'carrito']);

        Route::get('privada/informacion-de-pagos', function () {
            $informacion = InformacionDePago::first();
            return inertia('privada/informacion', [
                'informacion' => $informacion,
            ]);
        })->name('informacion.pagos');

        Route::get('privada/pedidos', [PedidoController::class, 'misPedidos']);
        Route::get('privada/lista-de-precios', [ListaDePreciosController::class, 'index']);

        Route::post('seleccionarCliente', [PrivadaController::class, 'seleccionarCliente'])
            ->name('seleccionarCliente');

        Route::post('hacerPedido', [PrivadaController::class, 'hacerPedido'])
            ->name('hacerPedido');
    Route::get('privada/cuenta-corriente', [CuentaCorrienteController::class, 'index'])->name('cuenta.corriente');

        Route::post('recomprar', [PedidoController::class, 'recomprar'])
            ->name('recomprar');

        Route::get('privada/margenes', [MargenesController::class, 'index'])->name('margenes');
    });

    Route::put('/margenes/actualizar', [MargenesController::class, 'actualizarMargen'])->name('margenes.actualizar');
    Route::post('/margenes/guardar', [MargenesController::class, 'guardarMargenes'])->name('margenes.guardar');


    Route::post('sendPedido', [SendPedidoController::class, 'sendReactEmail'])
        ->name('sendPedido');

    Route::post('sendInformacion', [PrivadaController::class, 'sendInformacion'])
        ->name('sendInformacion');

    Route::post('pedido', [PedidoController::class, 'store'])
        ->name('pedido.store');

    Route::post('pedidoProducto', [PedidoProductoController::class, 'store'])
        ->name('pedidoProducto.store');

    Route::post('addtocart', [CartController::class, 'addtocart'])
        ->name('addtocart');
    Route::post('remove', [CartController::class, 'remove'])
        ->name('remove');
    Route::post('update', [CartController::class, 'update'])
        ->name('update');
    Route::post('destroy', [CartController::class, 'destroy'])
        ->name('destroy');

    Route::post('compraRapida', [CartController::class, 'compraRapida'])->name('compraRapida');

    Route::get('borrarCliente', [PrivadaController::class, 'borrarCliente'])
        ->name('borrarCliente');
});
