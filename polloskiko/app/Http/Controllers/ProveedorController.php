<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\Request;

class ProveedorController extends Controller
{
    private function reglas(): array
    {
        return [
            'nombre'            => ['required', 'string', 'max:80'],
            'telefono'          => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
            'direccion'         => ['required', 'string', 'max:150'],
            'materias_surtidas' => ['nullable', 'string', 'max:200'],
            'adeudo'            => ['required', 'numeric', 'min:0', 'max:99999999'],
        ];
    }

    public function index()
    {
        $proveedores = Proveedor::orderByDesc('id')->get();
        return view('proveedores.index', compact('proveedores'));
    }

    public function create()
    {
        return view('proveedores.create');
    }

    public function store(Request $request)
    {
        $proveedor = Proveedor::create($request->validate($this->reglas()));

        return redirect()->route('proveedores.index')
            ->with('exito', "¡Proveedor '{$proveedor->nombre}' dado de alta correctamente!");
    }

    public function edit(Proveedor $proveedor)
    {
        return view('proveedores.edit', compact('proveedor'));
    }

    public function update(Request $request, Proveedor $proveedor)
    {
        $proveedor->update($request->validate($this->reglas()));

        return redirect()->route('proveedores.index')
            ->with('exito', "¡Proveedor '{$proveedor->nombre}' actualizado con éxito!");
    }

    public function destroy(Proveedor $proveedor)
    {
        $proveedor->delete();
        return redirect()->route('proveedores.index')->with('exito', 'Proveedor eliminado.');
    }
}