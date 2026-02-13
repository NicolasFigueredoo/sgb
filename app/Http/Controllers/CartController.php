<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Services\PrecioService;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    protected $precioService;

    public function __construct(PrecioService $precioService)
    {
        $this->precioService = $precioService;
        
        // Restaurar carrito al inicializar
        if (Auth::check() && !session('cliente_seleccionado')) {
            Cart::restore(Auth::id());
        } elseif (session('cliente_seleccionado')) {
            Cart::restore(session('cliente_seleccionado')->id);
        }
    }

    public function index()
    {
        $cartItems = Cart::content();
        $cartTotal = Cart::total();
        $cartCount = Cart::count();

        return view('cart.index', compact('cartItems', 'cartTotal', 'cartCount'));
    }

    public function addtocart(Request $request)
    {
        $producto = Producto::where('id', $request->id)->first();

        if (!$producto) {
            return response()->json(['ok' => false, 'message' => 'Producto no encontrado'], 404);
        }

        // Obtener usuario actual
        $user = Auth::check() ? Auth::user() : session('cliente_seleccionado');

        // Calcular precio con descuentos de Bejerman
        $precioCalculado = $this->precioService->calcularPrecioFinal(
            $user, 
            $producto->code, 
            $request->qty ?? 1
        );

        Cart::add(
            $request->id,
            $request->name,
            $request->qty ?? 1,
            $precioCalculado['precio_final'], // Precio con descuentos aplicados
            0,
            [
                'precio_base' => $precioCalculado['precio_base'],
                'descuento_aplicado' => $precioCalculado['descuento_aplicado'],
                'porcentaje_descuento' => $precioCalculado['porcentaje_descuento'],
                'code' => $producto->code,
            ]
        );

        // Guardar en base de datos
        if (Auth::check() && !session('cliente_seleccionado')) {
            Cart::store(Auth::id());
        } elseif (session('cliente_seleccionado')) {
            Cart::store(session('cliente_seleccionado')->id);
        }

           return redirect()->back()->with('success', 'Producto agregado al carrito');

    }

    public function update(Request $request)
    {
        $request->validate([
            'qty' => 'required|integer|min:1'
        ]);

        if ($request->rowId) {
            $item = Cart::get($request->rowId);
            $user = Auth::check() ? Auth::user() : session('cliente_seleccionado');

            // Recalcular precio con nueva cantidad
            $precioCalculado = $this->precioService->calcularPrecioFinal(
                $user,
                $item->options->code,
                $request->qty
            );

            Cart::update($request->rowId, [
                'qty' => $request->qty,
                'price' => $precioCalculado['precio_final'],
                'options' => [
                    'precio_base' => $precioCalculado['precio_base'],
                    'descuento_aplicado' => $precioCalculado['descuento_aplicado'],
                    'porcentaje_descuento' => $precioCalculado['porcentaje_descuento'],
                    'code' => $item->options->code,
                ]
            ]);
        }

        // Guardar cambios
        if (Auth::check() && !session('cliente_seleccionado')) {
            Cart::store(Auth::id());
        } elseif (session('cliente_seleccionado')) {
            Cart::store(session('cliente_seleccionado')->id);
        }

        return redirect()->back()->with('success', 'Carrito actualizado correctamente');
    }

    public function remove(Request $request)
    {
        Cart::remove($request->rowId);

        if (Auth::check() && !session('cliente_seleccionado')) {
            Cart::store(Auth::id());
        } elseif (session('cliente_seleccionado')) {
            Cart::store(session('cliente_seleccionado')->id);
        }

        return redirect()->back()->with('success', 'Producto eliminado del carrito');
    }

    public function destroy()
    {
        Cart::destroy();

        if (Auth::check() && !session('cliente_seleccionado')) {
            Cart::erase(Auth::id());
        } elseif (session('cliente_seleccionado')) {
            Cart::erase(session('cliente_seleccionado')->id);
        }

        return redirect()->back()->with('success', 'Carrito vaciado completamente');
    }

    public function saveCart()
    {
        if (Auth::check() && !session('cliente_seleccionado')) {
            Cart::store(Auth::id());
        } elseif (session('cliente_seleccionado')) {
            Cart::store(session('cliente_seleccionado')->id);
        }

        return response()->json(['success' => true, 'message' => 'Carrito guardado']);
    }

    public function compraRapida(Request $request)
    {
        try {
            $producto = Producto::where('code', $request->code)->first();

            if (!$producto) {
                return response()->json(['ok' => false, 'message' => 'Producto no encontrado'], 404);
            }

            $user = Auth::check() ? Auth::user() : session('cliente_seleccionado');

            // Calcular precio con descuentos de Bejerman
            $precioCalculado = $this->precioService->calcularPrecioFinal(
                $user,
                $producto->code,
                $request->qty ?? 1
            );

            Cart::add(
                $producto->id,
                $producto->name,
                $request->qty ?? 1,
                $precioCalculado['precio_final'],
                0,
                [
                    'precio_base' => $precioCalculado['precio_base'],
                    'descuento_aplicado' => $precioCalculado['descuento_aplicado'],
                    'porcentaje_descuento' => $precioCalculado['porcentaje_descuento'],
                    'code' => $producto->code,
                ]
            );

            if (Auth::check()) {
                Cart::store(Auth::id());
            } elseif (session()->has('cliente_seleccionado')) {
                Cart::store(session('cliente_seleccionado')->id);
            }

            return response()->json([
                'ok' => true,
                'message' => 'Producto añadido al carrito correctamente',
                'precio_info' => $precioCalculado,
            ]);

        } catch (\Throwable $e) {
            Log::error('❌ Error en compraRapida', ['error' => $e->getMessage()]);
            return response()->json(['ok' => false, 'message' => 'Error interno del servidor'], 500);
        }
    }
}