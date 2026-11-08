<?php

namespace App\Http\Controllers;

use App\Models\AvisoStock;
use App\Models\Producto;
use Illuminate\Http\Request;

class AvisoStockController extends Controller
{
    /** Administrador: avisos pendientes y últimos atendidos. */
    public function index()
    {
        $pendientes = AvisoStock::pendientes()->with(['producto', 'user'])->latest()->get();

        $atendidos = AvisoStock::whereNotNull('atendido_at')
            ->with(['producto', 'user', 'atendidoPor'])
            ->latest('atendido_at')
            ->limit(15)
            ->get();

        $atendidosHoy = AvisoStock::whereDate('atendido_at', today())->count();

        return view('avisos.index', compact('pendientes', 'atendidos', 'atendidosHoy'));
    }

    /** Cajero y administrador: lista de productos para avisar que se acaban. */
    public function create(Request $request)
    {
        $buscar = trim((string) $request->query('q', ''));

        $productos = Producto::query()
            ->when($buscar !== '', fn ($q) => $q->where('nombre', 'like', "%{$buscar}%"))
            ->orderBy('stock')
            ->orderBy('nombre')
            ->get();

        $pendientes = AvisoStock::pendientes()->with('user')->get()->keyBy('producto_id');

        return view('avisos.create', compact('productos', 'pendientes', 'buscar'));
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'producto_id' => ['required', 'exists:productos,id'],
            'nota'        => ['nullable', 'string', 'max:150'],
        ]);

        $producto = Producto::findOrFail($datos['producto_id']);

        // Evita avisos repetidos mientras el anterior siga pendiente
        if (AvisoStock::pendientes()->where('producto_id', $producto->id)->exists()) {
            return back()->with('error',
                "Ya se avisó que '{$producto->nombre}' se está acabando. El administrador lo tiene pendiente.");
        }

        AvisoStock::create([
            'producto_id' => $producto->id,
            'user_id'     => $request->user()->id,
            'nota'        => $datos['nota'] ?? null,
        ]);

        return back()->with('exito', "Listo: se avisó al administrador sobre '{$producto->nombre}'.");
    }

    /** Administrador: marca el aviso como atendido. */
    public function atender(Request $request, AvisoStock $aviso)
    {
        if ($aviso->atendido_at === null) {
            $aviso->update([
                'atendido_at'  => now(),
                'atendido_por' => $request->user()->id,
            ]);
        }

        return back()->with('exito', "Aviso de '{$aviso->producto->nombre}' marcado como atendido.");
    }
}
