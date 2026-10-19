<?php

namespace App\Http\Controllers;

use App\Models\DetalleVenta;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Venta;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    private const STOCK_BAJO = 5;

    public function __invoke(Request $request)
    {
        $usuario = $request->user();
        $esAdmin = $usuario->esAdmin();

        // El administrador ve todas las ventas; el cajero solo las suyas
        $consulta = fn () => Venta::query()
            ->when(! $esAdmin, fn ($q) => $q->where('user_id', $usuario->id));

        // Tarjetas del día
        $ventasHoy = (float) $consulta()->whereDate('created_at', today())->sum('total');
        $notasHoy  = $consulta()->whereDate('created_at', today())->count();
        $ticket    = $notasHoy > 0 ? $ventasHoy / $notasHoy : 0;

        $ventasAyer = (float) $consulta()->whereDate('created_at', today()->subDay())->sum('total');
        $cambio = $ventasAyer > 0
            ? round((($ventasHoy - $ventasAyer) / $ventasAyer) * 100, 1)
            : null;

        // Ventas de los últimos 7 días (incluye hoy)
        $desde  = today()->subDays(6);
        $porDia = $consulta()
            ->where('created_at', '>=', $desde)
            ->selectRaw('DATE(created_at) as dia, SUM(total) as total')
            ->groupBy('dia')
            ->pluck('total', 'dia');

        $nombres = ['dom', 'lun', 'mar', 'mié', 'jue', 'vie', 'sáb'];
        $semana = collect(range(6, 0))->map(function ($i) use ($porDia, $nombres) {
            $fecha = today()->subDays($i);

            return [
                'etiqueta' => $nombres[$fecha->dayOfWeek] . ' ' . $fecha->day,
                'total'    => (float) ($porDia[$fecha->toDateString()] ?? 0),
                'hoy'      => $i === 0,
            ];
        });
        $maxSemana = max(1, $semana->max('total'));

        // Productos más vendidos de la semana
        $masVendidos = DetalleVenta::query()
            ->where('created_at', '>=', $desde)
            ->selectRaw('producto_id, SUM(cantidad) as unidades')
            ->groupBy('producto_id')
            ->orderByDesc('unidades')
            ->limit(5)
            ->with('producto')
            ->get();

        // Inventario y últimas ventas
        $stockBajo = Producto::where('stock', '<=', self::STOCK_BAJO)
            ->orderBy('stock')
            ->orderBy('nombre')
            ->get();

        $ultimas = $consulta()->with(['cliente', 'user'])->latest()->limit(5)->get();

        // Solo administrador
        $porCajero = collect();
        $adeudo = 0;
        if ($esAdmin) {
            $porCajero = Venta::whereDate('created_at', today())
                ->selectRaw('user_id, COUNT(*) as notas, SUM(total) as total')
                ->groupBy('user_id')
                ->with('user')
                ->get();
            $adeudo = (float) Proveedor::sum('adeudo');
        }

        $umbral = self::STOCK_BAJO;
        $fecha  = ucfirst(now()->locale('es')->translatedFormat('l j \d\e F'));

        return view('dashboard', compact(
            'esAdmin', 'fecha', 'ventasHoy', 'notasHoy', 'ticket', 'cambio',
            'semana', 'maxSemana', 'masVendidos', 'stockBajo', 'umbral',
            'ultimas', 'porCajero', 'adeudo'
        ));
    }
}