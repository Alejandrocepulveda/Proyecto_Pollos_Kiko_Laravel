<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductoController extends Controller
{
    private function reglas(?Producto $producto = null): array
    {
        return [
            'nombre'       => ['required', 'string', 'max:80', Rule::unique('productos', 'nombre')->ignore($producto)],
            'stock'        => ['required', 'integer', 'min:0', 'max:1000000'],
            'precio_venta' => ['required', 'numeric', 'gt:0', 'max:99999999'],
        ];
    }

    private function mensajes(): array
    {
        return ['nombre.unique' => 'Ya existe un producto con ese nombre.'];
    }

    public function index(Request $request)
    {
        $buscar   = trim((string) $request->query('q', ''));
        $soloBajo = $request->boolean('bajo');

        $productos = Producto::query()
            ->withSum('detalles', 'cantidad')
            ->when($buscar !== '', fn ($q) => $q->where('nombre', 'like', "%{$buscar}%"))
            ->when($soloBajo, fn ($q) => $q->where('stock', '<=', Producto::STOCK_BAJO))
            ->orderBy('nombre')
            ->get();

        // El resumen siempre cuenta todo el inventario, aunque haya filtros
        $todos = Producto::all(['stock', 'precio_venta']);
        $resumen = [
            'total'    => $todos->count(),
            'valor'    => $todos->sum(fn ($p) => $p->stock * $p->precio_venta),
            'bajo'     => $todos->where('stock', '>', 0)->where('stock', '<=', Producto::STOCK_BAJO)->count(),
            'agotados' => $todos->where('stock', 0)->count(),
        ];

        return view('productos.index', compact('productos', 'buscar', 'soloBajo', 'resumen'));
    }

    public function create()
    {
        return view('productos.create');
    }

    public function store(Request $request)
    {
        $producto = Producto::create($request->validate($this->reglas(), $this->mensajes()));

        return redirect()->route('productos.index')
            ->with('exito', "¡Producto '{$producto->nombre}' agregado al inventario con éxito!");
    }

    public function edit(Producto $producto)
    {
        return view('productos.edit', compact('producto'));
    }

    public function update(Request $request, Producto $producto)
    {
        $producto->update($request->validate($this->reglas($producto), $this->mensajes()));

        return redirect()->route('productos.index')
            ->with('exito', "Producto '{$producto->nombre}' actualizado correctamente.");
    }

    public function reponer(Request $request, Producto $producto)
    {
        $datos = $request->validate([
            'cantidad' => ['required', 'integer', 'min:1', 'max:100000'],
        ]);

        $producto->increment('stock', $datos['cantidad']);

        return back()->with('exito',
            "Se agregaron {$datos['cantidad']} unidades a '{$producto->nombre}'. Stock actual: {$producto->stock}.");
    }

    public function destroy(Producto $producto)
    {
        try {
            $producto->delete();
        } catch (QueryException $e) {
            return back()->with('error',
                "No se puede eliminar '{$producto->nombre}': ya tiene ventas registradas. Déjalo en 0 de stock.");
        }

        return redirect()->route('productos.index')->with('exito', 'Producto eliminado.');
    }
}