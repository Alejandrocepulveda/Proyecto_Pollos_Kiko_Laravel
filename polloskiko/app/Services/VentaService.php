<?php

namespace App\Services;

use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VentaService
{
    /**
     * @param  array<int, array{producto_id:int, cantidad:int}>  $items
     */
    public function registrar(int $userId, ?int $clienteId, array $items): Venta
    {
        return DB::transaction(function () use ($userId, $clienteId, $items) {
            // Si el mismo producto viene dos veces, se suman las cantidades
            $cantidades = collect($items)
                ->groupBy('producto_id')
                ->map(fn ($grupo) => (int) $grupo->sum('cantidad'));

            // lockForUpdate evita que dos cajeros vendan la misma existencia a la vez
            $productos = Producto::whereIn('id', $cantidades->keys())
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $total = 0;
            $detalles = [];

            foreach ($cantidades as $id => $cantidad) {
                $producto = $productos[$id];

                if ($producto->stock < $cantidad) {
                    throw ValidationException::withMessages([
                        'items' => "No hay suficiente stock de {$producto->nombre}. Unidades actuales: {$producto->stock}",
                    ]);
                }

                $total += $producto->precio_venta * $cantidad;
                $detalles[] = [
                    'producto_id'     => $id,
                    'cantidad'        => $cantidad,
                    'precio_unitario' => $producto->precio_venta,
                ];
            }

            $venta = Venta::create([
                'user_id'    => $userId,
                'cliente_id' => $clienteId,
                'total'      => $total,
            ]);

            $venta->detalles()->createMany($detalles);

            foreach ($cantidades as $id => $cantidad) {
                $productos[$id]->decrement('stock', $cantidad);
            }

            return $venta;
        });
    }
}
