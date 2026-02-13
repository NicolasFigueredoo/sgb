<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\PedidoProducto;
use App\Models\Producto;
use App\Models\Bejerman\PedidoVenta;
use App\Services\EnvioPedidoBejermanService;
use App\Services\PrecioService;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PedidoController extends Controller
{
    protected $envioPedidoService;
    protected $precioService;

    public function __construct(
        EnvioPedidoBejermanService $envioPedidoService,
        PrecioService $precioService
    ) {
        $this->envioPedidoService = $envioPedidoService;
        $this->precioService = $precioService;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'tipo_entrega' => 'nullable|string',
            'mensaje' => 'sometimes|string',
            'archivo' => 'sometimes|file',
            'subtotal' => 'nullable|numeric',
            'iva' => 'nullable|numeric',
            'iibb' => 'nullable|numeric',
            'total' => 'nullable|numeric',
            'entregado' => 'nullable|boolean',
        ]);

        try {
            DB::beginTransaction();

            // Guardar archivo
            if ($request->hasFile('archivo')) {
                $archivoPath = $request->file('archivo')->store('files', 'public');
                $data["archivo"] = $archivoPath;
            }

            // Calcular descuento total desde el carrito
            $user = auth()->user();
            $cartItems = Cart::content();
            $descuentoTotal = 0;

            foreach ($cartItems as $item) {
                if (isset($item->options['descuento_aplicado'])) {
                    $descuentoTotal += $item->options['descuento_aplicado'] * $item->qty;
                }
            }

            $data['descuento_total'] = $descuentoTotal;
            $data['origen'] = 'web';
            $data['estado'] = 'pendiente';

            // Crear pedido
            $pedido = Pedido::create($data);

            // **ENVIAR AUTOMÁTICAMENTE A BEJERMAN**
            if ($user && $user->bejerman_cliente_cod) {
                $resultado = $this->envioPedidoService->enviarPedido($pedido);

                if ($resultado['success']) {
                    \Log::info("✅ Pedido #{$pedido->id} enviado a Bejerman", [
                        'bejerman_id' => $resultado['bejerman_id']
                    ]);
                } else {
                    \Log::warning("⚠️ No se pudo enviar pedido #{$pedido->id} a Bejerman", [
                        'error' => $resultado['error']
                    ]);
                }
            }

            DB::commit();

            return redirect()->back()->with([
                'pedido_id' => $pedido->id,
                'message' => 'Pedido creado y enviado a Bejerman exitosamente'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('❌ Error creando pedido', ['error' => $e->getMessage()]);

            return back()->with('error', 'Error al crear el pedido: ' . $e->getMessage());
        }
    }

    /**
     * Ver mis pedidos (VISTA UNIFICADA: Web + Bejerman)
     */
    public function misPedidos()
    {
        $user = auth()->user();

        // 1. Pedidos de la web (locales)
        $pedidosWeb = Pedido::where('user_id', $user->id)
            ->with(['productos.producto'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($pedido) {

                // si no tenés subtotal/iva/descuento en la tabla, calculalos:
                $subtotal = $pedido->productos->sum(fn($i) => (float)$i->cantidad * (float)$i->precio_unitario);

                return [
                    'id' => $pedido->id,
                    'origen' => 'web',
                    'created_at' => $pedido->created_at, // o formateado
                    'estado' => $pedido->estado,
                    'tipo_entrega' => $pedido->tipo_entrega ?? null,
                    'mensaje' => $pedido->mensaje ?? null,
                    'fecha' => optional($pedido->created_at)->format('d/m/Y H:i'),

                    'subtotal' => $pedido->subtotal ?? $subtotal,
                    'descuento' => $pedido->descuento ?? ($pedido->descuento_total ?? 0),
                    'iva' => $pedido->iva ?? 0,
                    'total' => $pedido->total,

                    // ✅ ESTO ES LO QUE TU MODAL ESPERA
                    'productos' => $pedido->productos,
                ];
            });


        // 2. Pedidos desde Bejerman (si el cliente está vinculado)
        $pedidosBejerman = collect();

        if ($user->bejerman_cliente_cod) {
            $pedidosBejerman = PedidoVenta::on('bejerman')
                ->where('cve_CodCli', $user->bejerman_cliente_cod)
                ->with('items')
                ->orderBy('cve_FEmision', 'desc')
                ->limit(50)
                ->get()
                ->map(function ($pedido) {
                    return [
                        'id' => $pedido->cve_ID,
                        'origen' => 'bejerman',
                        'created_at' => $pedido->cve_FEmision,
                        'estado' => $pedido->estado_texto,
                        'numero' => $pedido->cve_Nro,
                        'fecha' => optional($pedido->created_at)->format('d/m/Y H:i'),

                        'subtotal' => null,
                        'descuento' => null,
                        'iva' => null,
                        'total' => $pedido->cve_ImpMonLoc,

                        // ✅ misma key
                        'productos' => $pedido->items,
                    ];
                });
        }

        // 3. Unificar y ordenar por fecha
        $pedidos = $pedidosWeb->concat($pedidosBejerman)
            ->sortByDesc('created_at')
            ->values();


        return inertia('privada/mispedidos', [
            'pedidos' => $pedidos,
        ]);
    }

    /**
     * Ver todos los pedidos (Admin) - Vista unificada
     */
    public function misPedidosAdmin(Request $request)
    {
        $perPage = $request->input('per_page', 10);

        $query = Pedido::query()
            ->with(['productos.producto', 'user'])
            ->orderBy('created_at', 'desc');

        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where('id', $searchTerm);
        }

        $pedidos = $query->paginate($perPage);

        return inertia('admin/pedidosAdmin', [
            'pedidos' => $pedidos,
        ]);
    }

    public function cambiarEstado(Request $request)
    {
        $pedido = Pedido::find($request->id);
        $pedido->estado = $request->estado;
        $pedido->save();

        return back()->with('success', 'Estado actualizado');
    }

    public function recomprar(Request $request)
    {
        $productos_pedidos = PedidoProducto::where('pedido_id', $request->pedido_id)->get();

        foreach ($productos_pedidos as $producto_pedido) {
            $producto = Producto::find($producto_pedido->producto_id);
            Cart::add(
                $producto->id,
                $producto->name,
                $producto_pedido->cantidad,
                $producto->precio,
                0,
            );
        }

        return back()->with('success', 'Productos agregados al carrito');
    }

    public function update(Request $request)
    {
        $pedido = Pedido::find($request->id);

        if (!$pedido) {
            return back()->with('error', 'Pedido no encontrado');
        }

        $pedido->entregado = !$pedido->entregado;
        $pedido->save();

        return back()->with('success', 'Pedido actualizado');
    }

    /**
     * Reintentar envío a Bejerman (si falló)
     */
    public function reintentarEnvioBejerman(Pedido $pedido)
    {
        if ($pedido->bejerman_id) {
            return back()->with('error', 'Este pedido ya fue enviado a Bejerman');
        }

        $resultado = $this->envioPedidoService->enviarPedido($pedido);

        if ($resultado['success']) {
            return back()->with('success', "Pedido enviado a Bejerman exitosamente (ID: {$resultado['bejerman_id']})");
        } else {
            return back()->with('error', "Error al enviar pedido: {$resultado['error']}");
        }
    }
}
