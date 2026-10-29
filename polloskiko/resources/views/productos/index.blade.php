@extends('layouts.panel')
@section('titulo', 'Inventario')

@section('contenido')

    {{-- Resumen --}}
    <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="k-tarjeta flex items-center gap-4 p-5">
            <span class="text-4xl">📦</span>
            <div>
                <p class="text-sm">Productos</p>
                <p class="text-2xl font-black">{{ $resumen['total'] }}</p>
            </div>
        </div>
        <div class="k-tarjeta flex items-center gap-4 p-5">
            <span class="text-4xl">💲</span>
            <div>
                <p class="text-sm">Valor del inventario</p>
                <p class="text-2xl font-black">${{ number_format($resumen['valor'], 2) }}</p>
            </div>
        </div>
        <div class="k-tarjeta flex items-center gap-4 p-5" style="{{ $resumen['bajo'] ? 'border-color: var(--k-malo)' : '' }}">
            <span class="text-4xl">⚠️</span>
            <div>
                <p class="text-sm">Stock bajo (≤ {{ \App\Models\Producto::STOCK_BAJO }})</p>
                <p class="text-2xl font-black" style="{{ $resumen['bajo'] ? 'color: var(--k-malo)' : '' }}">{{ $resumen['bajo'] }}</p>
            </div>
        </div>
        <div class="k-tarjeta flex items-center gap-4 p-5" style="{{ $resumen['agotados'] ? 'border-color: var(--k-malo)' : '' }}">
            <span class="text-4xl">🚫</span>
            <div>
                <p class="text-sm">Agotados</p>
                <p class="text-2xl font-black" style="{{ $resumen['agotados'] ? 'color: var(--k-malo)' : '' }}">{{ $resumen['agotados'] }}</p>
            </div>
        </div>
    </div>

    {{-- Búsqueda, filtro y alta --}}
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <form method="GET" action="{{ route('productos.index') }}" class="flex flex-wrap items-center gap-2">
            <input type="text" name="q" value="{{ $buscar }}" placeholder="Buscar producto"
                   class="k-input" style="height:44px; width:240px; max-width:100%">
            <label class="flex items-center gap-2 text-sm font-bold">
                <input type="checkbox" name="bajo" value="1" @checked($soloBajo)
                       onchange="this.form.submit()" style="width:18px; height:18px;">
                Solo stock bajo
            </label>
            <button type="submit" class="k-btn k-btn-sec k-btn-sm">Buscar</button>
            @if ($buscar !== '' || $soloBajo)
                <a href="{{ route('productos.index') }}" class="k-btn k-btn-sm">Limpiar</a>
            @endif
        </form>

        <a href="{{ route('productos.create') }}" class="k-btn">+ Nuevo producto</a>
    </div>

    {{-- Tabla --}}
    <div class="k-tabla-wrap">
        <table class="k-tabla">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Precio</th>
                    <th>Stock</th>
                    <th>Vendidas</th>
                    <th>Reponer</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($productos as $p)
                    <tr>
                        <td>{{ $p->nombre }}</td>
                        <td>${{ number_format($p->precio_venta, 2) }}</td>
                        <td>
                            @if ($p->stock == 0)
                                <span class="k-badge k-badge-malo">Agotado</span>
                            @elseif ($p->stock <= \App\Models\Producto::STOCK_BAJO)
                                <span class="k-badge" style="background: var(--k-secundario)">{{ $p->stock }} · bajo</span>
                            @else
                                <span class="k-badge k-badge-ok">{{ $p->stock }}</span>
                            @endif
                        </td>
                        <td>{{ (int) $p->detalles_sum_cantidad }}</td>
                        <td>
                            <form method="POST" action="{{ route('productos.reponer', $p) }}" class="flex gap-1">
                                @csrf
                                @method('PATCH')
                                <input type="number" name="cantidad" min="1" max="100000" placeholder="+" required
                                       class="k-input" style="height:36px; width:76px; font-size:14px; padding:0 8px;">
                                <button type="submit" class="k-btn k-btn-sec k-btn-sm">+ Stock</button>
                            </form>
                        </td>
                        <td>
                            <div class="flex gap-2">
                                <a href="{{ route('productos.edit', $p) }}" class="k-btn k-btn-sec k-btn-sm">Editar</a>
                                <form method="POST" action="{{ route('productos.destroy', $p) }}"
                                      onsubmit="return confirm('¿Eliminar {{ $p->nombre }}?')">
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
                            {{ ($buscar !== '' || $soloBajo) ? 'No hay productos con ese filtro.' : 'Aún no hay productos registrados.' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
