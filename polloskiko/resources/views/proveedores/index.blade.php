@extends('layouts.panel')
@section('titulo', 'Proveedores')

@section('contenido')
    <div class="mb-6 grid gap-4 sm:grid-cols-2">
        <div class="k-tarjeta flex items-center gap-4 p-5">
            <span class="text-4xl">🚚</span>
            <div>
                <p class="text-sm">Proveedores registrados</p>
                <p class="text-2xl font-black">{{ $proveedores->count() }}</p>
            </div>
        </div>
        <div class="k-tarjeta flex items-center gap-4 p-5">
            <span class="text-4xl">💵</span>
            <div>
                <p class="text-sm">Adeudo total</p>
                <p class="text-2xl font-black">${{ number_format($proveedores->sum('adeudo'), 2) }}</p>
            </div>
        </div>
    </div>

    <div class="mb-4 flex items-center justify-between">
        <h2 class="text-lg font-bold">Listado</h2>
        <a href="{{ route('proveedores.create') }}" class="k-btn">+ Nuevo proveedor</a>
    </div>

    <div class="k-tabla-wrap">
        <table class="k-tabla">
            <thead>
                <tr>
                    <th>Proveedor</th>
                    <th>Teléfono</th>
                    <th>Dirección</th>
                    <th>Materias</th>
                    <th>Adeudo</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($proveedores as $p)
                    <tr>
                        <td>{{ $p->nombre }}</td>
                        <td>{{ $p->telefono }}</td>
                        <td>{{ $p->direccion }}</td>
                        <td>{{ $p->materias_surtidas ?: '—' }}</td>
                        <td>
                            @if ($p->adeudo > 0)
                                <span class="k-badge k-badge-malo">${{ number_format($p->adeudo, 2) }}</span>
                            @else
                                <span class="k-badge k-badge-ok">Sin adeudo</span>
                            @endif
                        </td>
                        <td>
                            <div class="flex gap-2">
                                <a href="{{ route('proveedores.edit', $p) }}" class="k-btn k-btn-sec k-btn-sm">Editar</a>
                                <form method="POST" action="{{ route('proveedores.destroy', $p) }}"
                                      onsubmit="return confirm('¿Eliminar a {{ $p->nombre }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="k-btn k-btn-sm">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center">Aún no hay proveedores registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection