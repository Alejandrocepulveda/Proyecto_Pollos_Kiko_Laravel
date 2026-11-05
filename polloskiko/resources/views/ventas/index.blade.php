@extends('layouts.panel')
@section('titulo', 'Historial de ventas')

@section('contenido')

    {{-- Resumen de lo filtrado --}}
    <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="k-tarjeta flex items-center gap-4 p-5">
            <span class="text-4xl">💰</span>
            <div>
                <p class="text-sm">{{ $hayFiltros ? 'Total del filtro' : 'Total vendido' }}</p>
                <p class="text-2xl font-black">${{ number_format($totalVendido, 2) }}</p>
            </div>
        </div>
        <div class="k-tarjeta flex items-center gap-4 p-5">
            <span class="text-4xl">🧾</span>
            <div>
                <p class="text-sm">Notas emitidas</p>
                <p class="text-2xl font-black">{{ $notas }}</p>
            </div>
        </div>
        <div class="k-tarjeta flex items-center gap-4 p-5">
            <span class="text-4xl">🍗</span>
            <div>
                <p class="text-sm">Unidades vendidas</p>
                <p class="text-2xl font-black">{{ $unidades }}</p>
            </div>
        </div>
        <div class="k-tarjeta flex items-center gap-4 p-5">
            <span class="text-4xl">🎯</span>
            <div>
                <p class="text-sm">Ticket promedio</p>
                <p class="text-2xl font-black">${{ number_format($ticket, 2) }}</p>
            </div>
        </div>
    </div>

    {{-- Filtros --}}
    <form method="GET" action="{{ route('ventas.index') }}" class="k-tarjeta mb-6 p-5">
        <div class="grid gap-4 sm:grid-cols-2 {{ $esAdmin ? 'lg:grid-cols-4' : 'lg:grid-cols-3' }}">
            <div>
                <label class="k-label">Desde</label>
                <input type="date" name="desde" value="{{ $desde }}" class="k-input" style="height:44px">
            </div>
            <div>
                <label class="k-label">Hasta</label>
                <input type="date" name="hasta" value="{{ $hasta }}" class="k-input" style="height:44px">
            </div>
            <div>
                <label class="k-label">Cliente</label>
                <select name="cliente" class="k-input" style="height:44px">
                    <option value="">Todos</option>
                    <option value="general" @selected($cliente === 'general')>Cliente General</option>
                    @foreach ($clientes as $c)
                        <option value="{{ $c->id }}" @selected($cliente === (string) $c->id)>{{ $c->nombre }}</option>
                    @endforeach
                </select>
            </div>
            @if ($esAdmin)
                <div>
                    <label class="k-label">Cajero</label>
                    <select name="cajero" class="k-input" style="height:44px">
                        <option value="">Todos</option>
                        @foreach ($cajeros as $u)
                            <option value="{{ $u->id }}" @selected((string) $cajero === (string) $u->id)>{{ $u->name }} {{ $u->apellidos }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>

        <div class="mt-4 flex flex-wrap items-center gap-2">
            <button type="submit" class="k-btn k-btn-sm">Filtrar</button>
            <a href="{{ route('ventas.index', ['desde' => today()->toDateString(), 'hasta' => today()->toDateString()]) }}"
               class="k-btn k-btn-sec k-btn-sm">Hoy</a>
            <a href="{{ route('ventas.index', ['desde' => now()->startOfWeek()->toDateString(), 'hasta' => today()->toDateString()]) }}"
               class="k-btn k-btn-sec k-btn-sm">Esta semana</a>
            <a href="{{ route('ventas.index', ['desde' => now()->startOfMonth()->toDateString(), 'hasta' => today()->toDateString()]) }}"
               class="k-btn k-btn-sec k-btn-sm">Este mes</a>
            @if ($hayFiltros)
                <a href="{{ route('ventas.index') }}" class="k-btn k-btn-sec k-btn-sm">Quitar filtros</a>
            @endif
            <a href="{{ route('ventas.create') }}" class="k-btn k-btn-sm" style="margin-left:auto">+ Nueva venta</a>
        </div>
    </form>

    {{-- Tabla --}}
    <div class="k-tabla-wrap">
        <table class="k-tabla">
            <thead>
                <tr>
                    <th>Folio</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Atendió</th>
                    <th>Artículos</th>
                    <th>Total</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            @forelse ($ventas as $v)
                <tbody x-data="{ abierto: false }">
                    <tr>
                        <td>#{{ $v->id }}</td>
                        <td>{{ $v->created_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $v->cliente->nombre ?? 'Cliente General' }}</td>
                        <td>{{ $v->user->name }}</td>
                        <td>{{ $v->detalles->sum('cantidad') }}</td>
                        <td>${{ number_format($v->total, 2) }}</td>
                        <td>
                            <div class="flex gap-2">
                                <button type="button" class="k-btn k-btn-sec k-btn-sm" @click="abierto = !abierto"
                                        x-text="abierto ? 'Ocultar' : 'Detalle'"></button>
                                <a href="{{ route('ventas.nota', $v) }}" target="_blank" class="k-btn k-btn-sm">PDF</a>
                            </div>
                        </td>
                    </tr>
                    <tr x-show="abierto" x-cloak style="background: var(--k-tarjeta)">
                        <td colspan="7">
                            @foreach ($v->detalles as $d)
                                <div class="flex flex-wrap justify-between gap-2 py-1 text-sm">
                                    <span>{{ $d->cantidad }} × {{ $d->producto->nombre }}</span>
                                    <span>${{ number_format($d->precio_unitario, 2) }} c/u
                                        → <strong>${{ number_format($d->precio_unitario * $d->cantidad, 2) }}</strong></span>
                                </div>
                            @endforeach
                        </td>
                    </tr>
                </tbody>
            @empty
                <tbody>
                    <tr>
                        <td colspan="7" class="text-center">
                            {{ $hayFiltros ? 'No hay ventas con esos filtros.' : 'Aún no hay ventas registradas.' }}
                        </td>
                    </tr>
                </tbody>
            @endforelse
        </table>
    </div>

    {{-- Paginación --}}
    @if ($ventas->hasPages())
        <div class="mt-4 flex items-center justify-between gap-3">
            @if ($ventas->onFirstPage())
                <span class="k-btn k-btn-sm" style="opacity:.4; cursor:not-allowed">← Anterior</span>
            @else
                <a href="{{ $ventas->previousPageUrl() }}" class="k-btn k-btn-sec k-btn-sm">← Anterior</a>
            @endif

            <span class="text-sm font-bold">Página {{ $ventas->currentPage() }} de {{ $ventas->lastPage() }}</span>

            @if ($ventas->hasMorePages())
                <a href="{{ $ventas->nextPageUrl() }}" class="k-btn k-btn-sec k-btn-sm">Siguiente →</a>
            @else
                <span class="k-btn k-btn-sm" style="opacity:.4; cursor:not-allowed">Siguiente →</span>
            @endif
        </div>
    @endif
@endsection
