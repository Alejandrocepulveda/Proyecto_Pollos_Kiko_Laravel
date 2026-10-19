@extends('layouts.panel')
@section('titulo', 'Panel principal')

@section('contenido')

    {{-- Saludo y acción principal --}}
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <p class="text-xl font-black">¡Hola, {{ auth()->user()->name }}! </p>
            <p class="text-sm">{{ $fecha }}</p>
        </div>
        <a href="{{ route('ventas.create') }}" class="k-btn text-lg">💰 Nueva venta</a>
    </div>

    {{-- Tarjetas del día --}}
    <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="k-tarjeta flex items-center gap-4 p-5">
            <span class="text-4xl">💰</span>
            <div>
                <p class="text-sm">{{ $esAdmin ? 'Ventas de hoy' : 'Mis ventas de hoy' }}</p>
                <p class="text-2xl font-black">${{ number_format($ventasHoy, 2) }}</p>
                @if ($esAdmin && $cambio !== null)
                    <p class="text-xs font-bold" style="color: {{ $cambio >= 0 ? 'var(--k-ok)' : 'var(--k-malo)' }}">
                        {{ $cambio >= 0 ? '▲' : '▼' }} {{ abs($cambio) }}% vs. ayer
                    </p>
                @endif
            </div>
        </div>

        <div class="k-tarjeta flex items-center gap-4 p-5">
            <span class="text-4xl">🧾</span>
            <div>
                <p class="text-sm">Notas emitidas hoy</p>
                <p class="text-2xl font-black">{{ $notasHoy }}</p>
            </div>
        </div>

        <div class="k-tarjeta flex items-center gap-4 p-5">
            <span class="text-4xl">🎯</span>
            <div>
                <p class="text-sm">Ticket promedio</p>
                <p class="text-2xl font-black">${{ number_format($ticket, 2) }}</p>
            </div>
        </div>

        <div class="k-tarjeta flex items-center gap-4 p-5"
             style="{{ $stockBajo->count() ? 'border-color: var(--k-malo)' : '' }}">
            <span class="text-4xl">⚠️</span>
            <div>
                <p class="text-sm">Stock bajo (≤ {{ $umbral }})</p>
                <p class="text-2xl font-black" style="{{ $stockBajo->count() ? 'color: var(--k-malo)' : '' }}">
                    {{ $stockBajo->count() }}
                </p>
            </div>
        </div>
    </div>

    {{-- Gráfica de 7 días y más vendidos --}}
    <div class="mb-6 grid gap-6 lg:grid-cols-3">
        <div class="k-tarjeta p-5 lg:col-span-2">
            <h2 class="mb-4 text-lg font-black">Ventas de los últimos 7 días</h2>

            <div class="flex h-56 gap-2">
                @foreach ($semana as $d)
                    <div class="flex flex-1 flex-col items-center gap-1">
                        <span class="h-4 text-xs font-bold">{{ $d['total'] > 0 ? '$' . number_format($d['total'], 0) : '' }}</span>
                        <div class="flex w-full flex-1 items-end">
                            <div class="w-full rounded-t-lg"
                                 style="height: {{ $d['total'] > 0 ? max(4, round($d['total'] / $maxSemana * 100)) : 2 }}%;
                                        background: {{ $d['hoy'] ? 'var(--k-acento)' : 'var(--k-secundario)' }};
                                        border: 2px solid var(--k-borde);"></div>
                        </div>
                        <span class="text-xs font-bold">{{ $d['etiqueta'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="k-tarjeta p-5">
            <h2 class="mb-4 text-lg font-black">Más vendidos (7 días)</h2>

            @php $maxUnidades = max(1, $masVendidos->max('unidades')); @endphp
            @forelse ($masVendidos as $i => $m)
                <div class="mb-3">
                    <div class="flex justify-between gap-2 text-sm font-bold">
                        <span class="truncate">{{ $i + 1 }}. {{ $m->producto->nombre }}</span>
                        <span class="whitespace-nowrap">{{ $m->unidades }} uds.</span>
                    </div>
                    <div class="mt-1 h-3 w-full overflow-hidden rounded-full" style="background:#fff; border: 2px solid var(--k-borde)">
                        <div class="h-full" style="width: {{ round($m->unidades / $maxUnidades * 100) }}%; background: var(--k-acento)"></div>
                    </div>
                </div>
            @empty
                <p class="text-sm">Todavía no hay ventas esta semana.</p>
            @endforelse
        </div>
    </div>

    {{-- Stock bajo y últimas ventas --}}
    <div class="mb-6 grid gap-6 lg:grid-cols-3">
        <div class="k-tarjeta p-5">
            <h2 class="mb-4 text-lg font-black">Productos por reponer</h2>

            @forelse ($stockBajo->take(6) as $p)
                <div class="mb-2 flex items-center justify-between gap-2 text-sm font-bold">
                    <span class="truncate">{{ $p->nombre }}</span>
                    <span class="k-badge k-badge-malo whitespace-nowrap">
                        {{ $p->stock == 0 ? 'Agotado' : $p->stock . ' uds.' }}
                    </span>
                </div>
            @empty
                <p class="text-sm">✅ Todo el inventario está bien surtido.</p>
            @endforelse

            @if ($stockBajo->count() > 6)
                <p class="mt-2 text-xs">y {{ $stockBajo->count() - 6 }} más…</p>
            @endif

            <a href="{{ route('productos.index') }}" class="k-btn k-btn-sec k-btn-sm mt-4">Ir al inventario</a>
        </div>

        <div class="lg:col-span-2">
            <div class="mb-2 flex items-center justify-between">
                <h2 class="text-lg font-black">Últimas ventas</h2>
                <a href="{{ route('ventas.index') }}" class="k-btn k-btn-sec k-btn-sm">Ver historial</a>
            </div>

            <div class="k-tabla-wrap">
                <table class="k-tabla">
                    <thead>
                        <tr><th>Folio</th><th>Hora</th><th>Cliente</th><th>Total</th><th>Nota</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($ultimas as $v)
                            <tr>
                                <td>#{{ $v->id }}</td>
                                <td>{{ $v->created_at->format('d/m H:i') }}</td>
                                <td>{{ $v->cliente->nombre ?? 'Cliente General' }}</td>
                                <td>${{ number_format($v->total, 2) }}</td>
                                <td><a href="{{ route('ventas.nota', $v) }}" target="_blank" class="k-btn k-btn-sec k-btn-sm">PDF</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center">Aún no hay ventas registradas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Solo administrador --}}
    @if ($esAdmin)
        <div class="grid gap-6 lg:grid-cols-3">
            <div class="k-tarjeta flex items-center gap-4 p-5">
                <span class="text-4xl">🚚</span>
                <div>
                    <p class="text-sm">Adeudo total a proveedores</p>
                    <p class="text-2xl font-black" style="{{ $adeudo > 0 ? 'color: var(--k-malo)' : '' }}">
                        ${{ number_format($adeudo, 2) }}
                    </p>
                    <a href="{{ route('proveedores.index') }}" class="text-xs font-bold underline">Ver proveedores</a>
                </div>
            </div>

            <div class="lg:col-span-2">
                <h2 class="mb-2 text-lg font-black">Ventas de hoy por cajero</h2>
                <div class="k-tabla-wrap">
                    <table class="k-tabla">
                        <thead>
                            <tr><th>Cajero</th><th>Notas</th><th>Total</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($porCajero as $c)
                                <tr>
                                    <td>{{ $c->user->name }} {{ $c->user->apellidos }}</td>
                                    <td>{{ $c->notas }}</td>
                                    <td>${{ number_format($c->total, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center">Todavía no hay ventas hoy.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

@endsection