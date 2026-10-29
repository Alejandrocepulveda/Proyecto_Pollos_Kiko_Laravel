@extends('layouts.panel')
@section('titulo', 'Clientes frecuentes')

@section('contenido')
    <div class="mb-6 grid gap-4 sm:grid-cols-2">
        <div class="k-tarjeta flex items-center gap-4 p-5">
            <span class="text-4xl">👥</span>
            <div>
                <p class="text-sm">Clientes registrados</p>
                <p class="text-2xl font-black">{{ $totalClientes }}</p>
            </div>
        </div>
        <div class="k-tarjeta flex items-center gap-4 p-5">
            <span class="text-4xl">🛍️</span>
            <div>
                <p class="text-sm">Compras de clientes frecuentes</p>
                <p class="text-2xl font-black">${{ number_format($clientes->sum('ventas_sum_total'), 2) }}</p>
            </div>
        </div>
    </div>

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <form method="GET" action="{{ route('clientes.index') }}" class="flex flex-wrap gap-2">
            <input type="text" name="q" value="{{ $buscar }}" placeholder="Buscar por nombre o teléfono"
                   class="k-input" style="height:44px; width:280px; max-width:100%">
            <button type="submit" class="k-btn k-btn-sec k-btn-sm">Buscar</button>
            @if ($buscar !== '')
                <a href="{{ route('clientes.index') }}" class="k-btn k-btn-sm">Limpiar</a>
            @endif
        </form>

        <a href="{{ route('clientes.create') }}" class="k-btn">+ Nuevo cliente</a>
    </div>

    <div class="k-tabla-wrap">
        <table class="k-tabla">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Teléfono</th>
                    <th>Dirección</th>
                    <th>Compras</th>
                    <th>Total gastado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($clientes as $c)
                    <tr>
                        <td>{{ $c->nombre }}</td>
                        <td>{{ $c->telefono }}</td>
                        <td>{{ $c->direccion }}</td>
                        <td>
                            @if ($c->ventas_count > 0)
                                <span class="k-badge k-badge-ok">{{ $c->ventas_count }}</span>
                            @else
                                <span class="k-badge" style="background:#eee">0</span>
                            @endif
                        </td>
                        <td>${{ number_format($c->ventas_sum_total ?? 0, 2) }}</td>
                        <td>
                            <div class="flex gap-2">
                                <a href="{{ route('clientes.edit', $c) }}" class="k-btn k-btn-sec k-btn-sm">Editar</a>
                                <form method="POST" action="{{ route('clientes.destroy', $c) }}"
                                      onsubmit="return confirm('¿Eliminar a {{ $c->nombre }}? Sus ventas anteriores se conservan como Cliente General.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="k-btn k-btn-sm">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">
                            {{ $buscar !== '' ? 'No se encontraron clientes con esa búsqueda.' : 'Aún no hay clientes registrados.' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
