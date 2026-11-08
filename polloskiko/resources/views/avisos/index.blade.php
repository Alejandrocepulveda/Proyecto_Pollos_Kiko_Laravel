@extends('layouts.panel')
@section('titulo', 'Avisos de stock')

@section('contenido')

    <div class="mb-6 grid gap-4 sm:grid-cols-2">
        <div class="k-tarjeta flex items-center gap-4 p-5" style="{{ $pendientes->count() ? 'border-color: var(--k-malo)' : '' }}">
            <span class="text-4xl">🔔</span>
            <div>
                <p class="text-sm">Avisos pendientes</p>
                <p class="text-2xl font-black" style="{{ $pendientes->count() ? 'color: var(--k-malo)' : '' }}">{{ $pendientes->count() }}</p>
            </div>
        </div>
        <div class="k-tarjeta flex items-center gap-4 p-5">
            <span class="text-4xl">✅</span>
            <div>
                <p class="text-sm">Atendidos hoy</p>
                <p class="text-2xl font-black">{{ $atendidosHoy }}</p>
            </div>
        </div>
    </div>

    {{-- Pendientes --}}
    <h2 class="mb-2 text-lg font-black">Pendientes</h2>
    <div class="k-tabla-wrap mb-8">
        <table class="k-tabla">
            <thead>
                <tr><th>Producto</th><th>Stock actual</th><th>Avisó</th><th>Nota</th><th>Acciones</th></tr>
            </thead>
            <tbody>
                @forelse ($pendientes as $a)
                    <tr>
                        <td>{{ $a->producto->nombre }}</td>
                        <td>
                            <span class="k-badge {{ $a->producto->stock <= \App\Models\Producto::STOCK_BAJO ? 'k-badge-malo' : 'k-badge-ok' }}">
                                {{ $a->producto->stock == 0 ? 'Agotado' : $a->producto->stock . ' uds.' }}
                            </span>
                        </td>
                        <td>
                            {{ $a->user->name }}
                            <span class="block text-xs font-normal">{{ $a->created_at->locale('es')->diffForHumans() }}</span>
                        </td>
                        <td class="font-normal">{{ $a->nota ?: '—' }}</td>
                        <td>
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('productos.index', ['q' => $a->producto->nombre]) }}" class="k-btn k-btn-sec k-btn-sm">Ir a reponer</a>
                                <form method="POST" action="{{ route('avisos.atender', $a) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="k-btn k-btn-sm">Atendido</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center">✅ No hay avisos pendientes.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Últimos atendidos --}}
    <h2 class="mb-2 text-lg font-black">Últimos atendidos</h2>
    <div class="k-tabla-wrap">
        <table class="k-tabla">
            <thead>
                <tr><th>Producto</th><th>Avisó</th><th>Atendió</th><th>Cuándo</th></tr>
            </thead>
            <tbody>
                @forelse ($atendidos as $a)
                    <tr>
                        <td>{{ $a->producto->nombre }}</td>
                        <td>{{ $a->user->name }}</td>
                        <td>{{ $a->atendidoPor->name ?? '—' }}</td>
                        <td>{{ $a->atendido_at->locale('es')->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center">Todavía no hay avisos atendidos.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
