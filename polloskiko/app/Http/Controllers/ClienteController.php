<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    private function reglas(): array
    {
        return [
            'nombre'    => ['required', 'string', 'max:80'],
            'telefono'  => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
            'direccion' => ['required', 'string', 'max:150'],
        ];
    }

    public function index(Request $request)
    {
        $buscar = trim((string) $request->query('q', ''));

        $clientes = Cliente::query()
            ->withCount('ventas')
            ->withSum('ventas', 'total')
            ->when($buscar !== '', fn ($q) => $q->where(fn ($q) => $q
                ->where('nombre', 'like', "%{$buscar}%")
                ->orWhere('telefono', 'like', "%{$buscar}%")))
            ->orderBy('nombre')
            ->get();

        $totalClientes = Cliente::count();

        return view('clientes.index', compact('clientes', 'buscar', 'totalClientes'));
    }

    public function create()
    {
        return view('clientes.create');
    }

    public function store(Request $request)
    {
        $cliente = Cliente::create($request->validate($this->reglas()));

        return redirect()->route('clientes.index')
            ->with('exito', "¡Cliente '{$cliente->nombre}' registrado con éxito!");
    }

    public function edit(Cliente $cliente)
    {
        return view('clientes.edit', compact('cliente'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        $cliente->update($request->validate($this->reglas()));

        return redirect()->route('clientes.index')
            ->with('exito', "¡Cliente '{$cliente->nombre}' actualizado con éxito!");
    }

    public function destroy(Cliente $cliente)
    {
        $cliente->delete();

        return redirect()->route('clientes.index')->with('exito', 'Cliente eliminado.');
    }
}