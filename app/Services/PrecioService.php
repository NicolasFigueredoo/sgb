<?php

namespace App\Services;

use App\Models\Bejerman\ListaPrecio;
use App\Models\Bejerman\BonificacionArticulo;
use App\Models\User;
use App\Models\Producto;
use Illuminate\Support\Facades\Log;

class PrecioService
{
    /**
     * Calcular precio final con descuentos de Bejerman
     */
    public function calcularPrecioFinal(User $user, string $producto_codigo, int $cantidad = 1)
    {
        try {
            // 1. Obtener precio base desde Bejerman (si existe lista de precios)
            $precioBase = $this->obtenerPrecioBase($user, $producto_codigo);

            // 2. Si no hay lista de precios en Bejerman, usar precio local
            if (!$precioBase) {
                $producto = Producto::where('code', $producto_codigo)->first();
                $precioBase = $producto->precio ?? 0;
            }

            // 3. Aplicar descuentos en cascada (descuento_uno, descuento_dos, descuento_tres)
            $precioConDescuentos = $this->aplicarDescuentosCascada(
                $precioBase,
                $user->descuento_uno,
                $user->descuento_dos,
                $user->descuento_tres
            );

            // 4. Verificar si existe un descuento especial por artículo en Bejerman
            $precioEspecial = $this->obtenerPrecioEspecial($user, $producto_codigo);
            
            if ($precioEspecial) {
                $precioConDescuentos = $precioEspecial;
            }

            return [
                'precio_base' => round($precioBase, 2),
                'precio_final' => round($precioConDescuentos, 2),
                'descuento_aplicado' => round($precioBase - $precioConDescuentos, 2),
                'porcentaje_descuento' => $precioBase > 0 ? round((($precioBase - $precioConDescuentos) / $precioBase) * 100, 2) : 0,
            ];

        } catch (\Exception $e) {
            Log::error("❌ Error calculando precio", [
                'user_id' => $user->id,
                'producto' => $producto_codigo,
                'error' => $e->getMessage(),
            ]);

            // Fallback: usar precio local sin descuentos
            $producto = Producto::where('code', $producto_codigo)->first();
            $precio = $producto->precio ?? 0;

            return [
                'precio_base' => $precio,
                'precio_final' => $precio,
                'descuento_aplicado' => 0,
                'porcentaje_descuento' => 0,
            ];
        }
    }

    /**
     * Obtener precio base desde lista de precios de Bejerman
     */
    private function obtenerPrecioBase(User $user, string $producto_codigo)
    {
        if (!$user->bejerman_lista_precio_cod) {
            return null;
        }

        $precio = ListaPrecio::on('bejerman')
            ->where('lprdlp_Cod', $user->bejerman_lista_precio_cod)
            ->where('lprart_CodGen', $producto_codigo)
            ->value('lpr_Precio');

        return $precio ? (float) $precio : null;
    }

    /**
     * Aplicar descuentos en cascada (uno sobre otro)
     */
    private function aplicarDescuentosCascada($precio, $dto1, $dto2, $dto3)
    {
        $precioConDescuento = $precio;

        // Descuento 1
        if ($dto1 > 0) {
            $precioConDescuento -= $precioConDescuento * ($dto1 / 100);
        }

        // Descuento 2 (sobre el precio ya con descuento 1)
        if ($dto2 > 0) {
            $precioConDescuento -= $precioConDescuento * ($dto2 / 100);
        }

        // Descuento 3 (sobre el precio ya con descuentos 1 y 2)
        if ($dto3 > 0) {
            $precioConDescuento -= $precioConDescuento * ($dto3 / 100);
        }

        return $precioConDescuento;
    }

    /**
     * Verificar si existe precio especial por artículo en Bejerman
     */
    private function obtenerPrecioEspecial(User $user, string $producto_codigo)
    {
        if (!$user->bejerman_cliente_cod) {
            return null;
        }

        // Buscar en tabla DtoXClienteXArt (descuentos especiales por cliente y artículo)
        $precioEspecial = \DB::connection('bejerman')
            ->table('DtoXClienteXArt')
            ->where('dtccli_Cod', $user->bejerman_cliente_cod)
            ->where('dtcart_CodGen', $producto_codigo)
            ->value('dtc_Precio');

        return $precioEspecial ? (float) $precioEspecial : null;
    }

    /**
     * Calcular descuento total de un pedido
     */
    public function calcularDescuentoPedido($items, User $user)
    {
        $descuentoTotal = 0;

        foreach ($items as $item) {
            $calculo = $this->calcularPrecioFinal($user, $item->code, $item->qty);
            $descuentoTotal += $calculo['descuento_aplicado'] * $item->qty;
        }

        return round($descuentoTotal, 2);
    }
}