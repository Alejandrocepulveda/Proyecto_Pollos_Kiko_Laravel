@extends('layouts.panel')
@section('titulo', 'Usuarios')

@section('contenido')

    {{-- Resumen --}}
    <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="k-tarjeta flex items-center gap-4 p-5">
            <span class="text-4xl">👤</span>
            <div>
                <p class="text-sm">Usuarios registrados</p>
                <p class="text-2xl font-black">{{ $resumen['total'] }}</p>
            </div>
        </div>
        <div class="k-tarjeta flex items-center gap-4 p-5">
            <span class="text-4xl">✅</span>
            <div>
                <p class="text-sm">Activos</p>
                <p class="text-2xl font-black">{{ $resumen['activos'] }}</p>
            </div>
        </div>
        <div class="k-tarjeta flex items-center gap-4 p-5">
            <span class="text-4xl">🔑</span>
            <div>
                <p class="text-sm">Administradores activos</p>
                <p class="text-2xl font-black">{{ $resumen['admins'] }}</p>
            </div>
        </div>
        <div class="k-tarjeta flex items-center gap-4 p-5" style="{{ $resumen['inactivos'] ? 'border-color: var(--k-malo)' : '' }}">
            <span class="text-4xl">🚫</span>
            <div>
                <p class="text-sm">Inactivos</p>
                <p class="text-2xl font-black" style="{{ $resumen['inactivos'] ? 'color: var(--k-malo)' : '' }}">{{ $resumen['inactivos'] }}</p>
            </div>
        </div>
    </div>

    {{-- Búsqueda y alta --}}
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <form method="GET" action="{{ route('usuarios.index') }}" class="flex flex-wrap gap-2">
            <input type="text" name="q" value="{{ $buscar }}" placeholder="Buscar por nombre o correo"
                   class="k-input" style="height:44px; width:280px; max-width:100%">
            <button type="submit" class="k-btn k-btn-sec k-btn-sm">Buscar</button>
            @if ($buscar !== '')
                <a href="{{ route('usuarios.index') }}" class="k-btn k-btn-sm">Limpiar</a>
            @endif
        </form>

        <a href="{{ route('usuarios.create') }}" class="k-btn">+ Nuevo usuario</a>
    </div>

    {{-- Tabla --}}
    <div class="k-tabla-wrap">
        <table class="k-tabla">
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Teléfono</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Ventas</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($usuarios as $u)
                    @php
                        $esYo = $u->id === auth()->id();
                        $v = $ventas->get($u->id);
                    @endphp
                    <tr style="{{ $u->activo ? '' : 'opacity:.6' }}">
                        <td>
                            <p>{{ $u->name }} {{ $u->apellidos }} @if ($esYo)<span class="k-badge" style="background: var(--k-secundario)">Tú</span>@endif</p>
                            <p class="text-xs font-normal">{{ $u->email }}</p>
                        </td>
                        <td>{{ $u->telefono }}</td>
                        <td>
                            <span class="k-badge" style="{{ $u->esAdmin() ? 'background: var(--k-secundario)' : 'background: #eee' }}">
                                {{ $u->role->nombre }}
                            </span>
                        </td>
                        <td>
                            <span class="k-badge {{ $u->activo ? 'k-badge-ok' : 'k-badge-malo' }}">
                                {{ $u->activo ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td>
                            @if ($v)
                                {{ $v->notas }} · ${{ number_format($v->total, 2) }}
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            <div class="flex gap-2">
                                <a href="{{ route('usuarios.edit', $u) }}" class="k-btn k-btn-sec k-btn-sm">Editar</a>

                                @unless ($esYo)
                                    <form method="POST" action="{{ route('usuarios.estado', $u) }}"
                                          onsubmit="return confirm('{{ $u->activo ? '¿Desactivar a ' . $u->name . '? Se cerrará su sesión y no podrá entrar.' : '¿Activar a ' . $u->name . '?' }}')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="k-btn k-btn-sm">{{ $u->activo ? 'Desactivar' : 'Activar' }}</button>
                                    </form>
                                @endunless
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center">No se encontraron usuarios.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <p class="mt-3 text-sm">Los usuarios no se eliminan para conservar el historial de ventas: se desactivan.</p>
@endsection
