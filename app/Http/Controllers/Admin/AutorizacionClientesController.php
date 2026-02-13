<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SincronizacionBejermanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AutorizacionClientesController extends Controller
{
    protected $sincronizacionService;

    public function __construct(SincronizacionBejermanService $sincronizacionService)
    {
        $this->sincronizacionService = $sincronizacionService;
    }

    /**
     * Dashboard de clientes pendientes de autorización
     */
    public function index(Request $request)
    {
        $query = User::where('rol', 'cliente')
            ->orderBy('created_at', 'desc');

        // Filtros
        if ($request->has('estado')) {
            if ($request->estado === 'pendientes') {
                $query->where('autorizado', false);
            } elseif ($request->estado === 'autorizados') {
                $query->where('autorizado', true);
            }
        }

        $clientes = $query->paginate(20);

        return inertia('admin/autorizacion-clientes', [
            'clientes' => $clientes,
            'filtros' => $request->only(['estado']),
        ]);
    }

    /**
     * Autorizar cliente y sincronizar con Bejerman
     */
    public function autorizar(Request $request, User $user)
    {
        try {
            // 1. Verificar que el cliente existe en Bejerman
            if (!$this->sincronizacionService->existeClienteEnBejerman($user->email)) {
                return back()->with('error', "Cliente no encontrado en Bejerman con email: {$user->email}");
            }

            // 2. Sincronizar datos desde Bejerman
            $resultado = $this->sincronizacionService->sincronizarCliente($user);

            if (!$resultado['success']) {
                return back()->with('error', "Error al sincronizar: {$resultado['error']}");
            }

            // 3. Autorizar usuario
            $user->update([
                'autorizado' => true,
                'fecha_autorizacion' => now(),
                'autorizado_por' => Auth::id(),
            ]);

            return back()->with('success', "Cliente {$user->name} autorizado y sincronizado correctamente");

        } catch (\Exception $e) {
            return back()->with('error', "Error al autorizar cliente: {$e->getMessage()}");
        }
    }

    /**
     * Rechazar/desautorizar cliente
     */
    public function rechazar(User $user)
    {
        $user->update([
            'autorizado' => false,
            'fecha_autorizacion' => null,
            'autorizado_por' => null,
        ]);

        return back()->with('success', "Cliente {$user->name} desautorizado");
    }

    /**
     * Re-sincronizar cliente (actualizar descuentos y precios)
     */
    public function resincronizar(User $user)
    {
        try {
            $this->sincronizacionService->resincronizarDescuentos($user);

            return back()->with('success', "Cliente {$user->name} re-sincronizado correctamente");

        } catch (\Exception $e) {
            return back()->with('error', "Error al re-sincronizar: {$e->getMessage()}");
        }
    }
}