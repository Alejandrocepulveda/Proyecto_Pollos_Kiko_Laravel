@extends('layouts.panel')
@section('titulo', 'Avisar faltante')

@section('contenido')
    <div class="k-tarjeta mb-6 p-5">
        <p class="font-black">🔔 ¿Se está acabando algo?</p>
        <p class="text-sm">Pulsa <strong>Avisar</strong> en el producto y el administrador lo verá en su panel. Puedes dejarle una nota opcional.</p>
    </div>

    <form method="GET" action="{{ route('avisos.create') }}" class="mb-4 flex flex-wrap gap-2">
        <input type="text" name="q" value="{{ $buscar }}" placeholder="Buscar producto"
               class="k-input" style="height:44px; width:280px; max-width:100%">
        <button type="submit" class="k-btn k-btn-sec k-btn-sm">Buscar</button>
        @if ($buscar !== '')
            <a href="{{ route('avisos.create') }}" class="k-btn k-btn-sm">Limpiar</a>
        @endif
    </form>

    <div class="k-tabla-wrap">
        <table class="k-tabla">
            <thead>
                <tr><th>Producto</th><th>Stock</th><th>Aviso</th></tr>
            </thead>
            <tbody>
                @forelse ($productos as $p)
                    <tr>
                        <td>{{ $p->nombre }}</td>
                        <td>
                            @if ($p->stock == 0)
                                <span class="k-badge k-badge-malo">Agotado</span>
                            @elseif ($p->stock <= \App\Models\Producto::STOCK_BAJO)
                                <span class="k-badge" style="background: var(--k-secundario)">{{ $p->stock }} · bajo</span>
                            @else
                                <span class="k-badge k-badge-ok">{{ $p->stock }}</span>
                            @endif
                        </td>
                        <td>
                            @if ($pendientes->has($p->id))
                                @php $a = $pendientes->get($p->id); @endphp
                                <span class="k-badge k-badge-ok">Avisado ✓</span>
                                <span class="text-xs font-normal">
                                    por {{ $a->user->name }}, {{ $a->created_at->locale('es')->diffForHumans() }}
                                </span>
                            @else
                                <form method="POST" action="{{ route('avisos.store') }}" class="flex flex-wrap gap-2">
                                    @csrf
                                    <input type="hidden" name="producto_id" value="{{ $p->id }}">
                                    <input type="text" name="nota" maxlength="150" placeholder="Nota (opcional)"
                                           class="k-input" style="height:36px; width:200px; max-width:100%; font-size:14px; padding:0 10px;">
                                    <button type="submit" class="k-btn k-btn-sm">🔔 Avisar</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center">No se encontraron productos.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
