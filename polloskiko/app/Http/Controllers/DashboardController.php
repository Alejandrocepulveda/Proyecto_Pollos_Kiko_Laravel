<?php

namespace App\Http\Controllers;

use App\Models\AvisoStock;
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
        $fecha   = ucfirst(now()->locale('es')->translatedFormat('l j \d\e F'));

        // ================= PANEL DEL CAJERO =================
        if (! $usuario->esAdmin()) {
            $misVentas = fn () => Venta::where('user_id', $usuario->id);

            $ventasHoy = (float) $misVentas()->whereDate('created_at', today())->sum('total');
            $notasHoy  = $misVentas()->whereDate('created_at', today())->count();

            $bajos = Producto::where('stock', '<=', self::STOCK_BAJO)
                ->orderBy('stock')
                ->orderBy('nombre')
                ->limit(8)
                ->get();

            $totalBajos = Producto::where('stock', '<=', self::STOCK_BAJO)->count();
            $avisados   = AvisoStock::pendientes()->pluck('id', 'producto_id');
            $ultimas    = $misVentas()->with('cliente')->latest()->limit(5)->get();

            return view('dashboard_cajero', compact(
                'fecha', 'ventasHoy', 'notasHoy', 'bajos', 'totalBajos', 'avisados', 'ultimas'
            ));
        }

        // ================= PANEL DEL ADMINISTRADOR =================
        $consulta = fn () => Venta::query();

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
            $dia = today()->subDays($i);

            return [
                'etiqueta' => $nombres[$dia->dayOfWeek] . ' ' . $dia->day,
                'total'    => (float) ($porDia[$dia->toDateString()] ?? 0),
                'hoy'      => $i === 0,
            ];
        });
        $maxSemana = max(1, $semana->max('total'));

        $masVendidos = DetalleVenta::query()
            ->where('created_at', '>=', $desde)
            ->selectRaw('producto_id, SUM(cantidad) as unidades')
            ->groupBy('producto_id')
            ->orderByDesc('unidades')
            ->limit(5)
            ->with('producto')
            ->get();

        $stockBajo = Producto::where('stock', '<=', self::STOCK_BAJO)
            ->orderBy('stock')
            ->orderBy('nombre')
            ->get();

        $ultimas = $consulta()->with(['cliente', 'user'])->latest()->limit(5)->get();

        $porCajero = Venta::whereDate('created_at', today())
            ->selectRaw('user_id, COUNT(*) as notas, SUM(total) as total')
            ->groupBy('user_id')
            ->with('user')
            ->get();

        $adeudo  = (float) Proveedor::sum('adeudo');
        $esAdmin = true;
        $umbral  = self::STOCK_BAJO;

        return view('dashboard', compact(
            'esAdmin', 'fecha', 'ventasHoy', 'notasHoy', 'ticket', 'cambio',
            'semana', 'maxSemana', 'masVendidos', 'stockBajo', 'umbral',
            'ultimas', 'porCajero', 'adeudo'
        ));
    }
}
