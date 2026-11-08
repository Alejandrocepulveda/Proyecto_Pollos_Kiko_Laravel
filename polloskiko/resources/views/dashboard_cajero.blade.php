@extends('layouts.panel')
@section('titulo', 'Mi panel')

@section('contenido')

    {{-- Saludo y acción principal --}}
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <p class="text-xl font-black">¡Hola, {{ auth()->user()->name }}! 👋</p>
            <p class="text-sm">{{ $fecha }}</p>
        </div>
        <a href="{{ route('ventas.create') }}" class="k-btn text-lg">💰 Nueva venta</a>
    </div>

    {{-- Mis números de hoy --}}
    <div class="mb-6 grid gap-4 sm:grid-cols-3">
        <div class="k-tarjeta flex items-center gap-4 p-5">
            <span class="text-4xl">💰</span>
            <div>
                <p class="text-sm">Mis ventas de hoy</p>
                <p class="text-2xl font-black">${{ number_format($ventasHoy, 2) }}</p>
            </div>
        </div>
        <div class="k-tarjeta flex items-center gap-4 p-5">
            <span class="text-4xl">🧾</span>
            <div>
                <p class="text-sm">Notas emitidas hoy</p>
                <p class="text-2xl font-black">{{ $notasHoy }}</p>
            </div>
        </div>
        <div class="k-tarjeta flex items-center gap-4 p-5" style="{{ $totalBajos ? 'border-color: var(--k-malo)' : '' }}">
            <span class="text-4xl">⚠️</span>
            <div>
                <p class="text-sm">Productos por agotarse</p>
                <p class="text-2xl font-black" style="{{ $totalBajos ? 'color: var(--k-malo)' : '' }}">{{ $totalBajos }}</p>
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">

        {{-- Productos por agotarse + aviso rápido --}}
        <div class="k-tarjeta p-5">
            <h2 class="mb-1 text-lg font-black">Productos por agotarse</h2>
            <p class="mb-4 text-sm">Si ves que algo se acaba, avisa al administrador con un toque.</p>

            @forelse ($bajos as $p)
                <div class="mb-3 flex items-center justify-between gap-2">
                    <div class="min-w-0">
                        <p class="truncate font-bold">{{ $p->nombre }}</p>
                        <span class="k-badge k-badge-malo">{{ $p->stock == 0 ? 'Agotado' : $p->stock . ' uds.' }}</span>
                    </div>

                    @if ($avisados->has($p->id))
                        <span class="k-badge k-badge-ok whitespace-nowrap">Avisado ✓</span>
                    @else
                        <form method="POST" action="{{ route('avisos.store') }}">
                            @csrf
                            <input type="hidden" name="producto_id" value="{{ $p->id }}">
                            <button type="submit" class="k-btn k-btn-sm whitespace-nowrap">🔔 Avisar</button>
                        </form>
                    @endif
                </div>
            @empty
                <p class="font-bold">✅ Todo el inventario está bien surtido.</p>
            @endforelse

            @if ($totalBajos > $bajos->count())
                <p class="mt-1 text-xs">y {{ $totalBajos - $bajos->count() }} más…</p>
            @endif

            <a href="{{ route('avisos.create') }}" class="k-btn k-btn-sec k-btn-sm mt-3">Avisar de otro producto</a>
        </div>

        {{-- Mis últimas ventas --}}
        <div>
            <div class="mb-2 flex items-center justify-between">
                <h2 class="text-lg font-black">Mis últimas ventas</h2>
                <a href="{{ route('ventas.index') }}" class="k-btn k-btn-sec k-btn-sm">Ver todas</a>
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
                            <tr><td colspan="5" class="text-center">Aún no has registrado ventas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
