@php $esYo = isset($usuario) && $usuario->id === auth()->id(); @endphp

<div class="k-tarjeta grid max-w-2xl gap-5 p-6 sm:grid-cols-2">
    <div>
        <label class="k-label">Nombre</label>
        <input type="text" name="name" maxlength="100" class="k-input"
               value="{{ old('name', $usuario->name ?? '') }}" required autocomplete="off">
    </div>
    <div>
        <label class="k-label">Apellidos</label>
        <input type="text" name="apellidos" maxlength="100" class="k-input"
               value="{{ old('apellidos', $usuario->apellidos ?? '') }}" required autocomplete="off">
    </div>
    <div class="sm:col-span-2">
        <label class="k-label">Domicilio</label>
        <input type="text" name="domicilio" maxlength="150" class="k-input"
               value="{{ old('domicilio', $usuario->domicilio ?? '') }}" required autocomplete="off">
    </div>
    <div>
        <label class="k-label">Teléfono</label>
        <input type="tel" name="telefono" maxlength="20" class="k-input"
               value="{{ old('telefono', $usuario->telefono ?? '') }}" required autocomplete="off">
    </div>
    <div>
        <label class="k-label">Rol</label>
        @if ($esYo)
            {{-- Nadie puede cambiarse su propio rol --}}
            <input type="hidden" name="role_id" value="{{ $usuario->role_id }}">
            <input type="text" class="k-input" value="{{ $usuario->role->nombre }} (tu cuenta)" disabled>
        @else
            <select name="role_id" class="k-input" required>
                @foreach ($roles as $r)
                    <option value="{{ $r->id }}" @selected(old('role_id', $usuario->role_id ?? 2) == $r->id)>{{ $r->nombre }}</option>
                @endforeach
            </select>
        @endif
    </div>

    <div class="sm:col-span-2">
        <label class="k-label">Correo de acceso</label>
        <input type="email" name="email" maxlength="255" class="k-input"
               value="{{ old('email', $usuario->email ?? '') }}" required autocomplete="off">
    </div>
    <div class="sm:col-span-2">
        <label class="k-label">
            {{ isset($usuario) ? 'Nueva contraseña (déjala vacía para no cambiarla)' : 'Contraseña' }}
        </label>
        <input type="password" name="password" class="k-input" autocomplete="new-password"
               {{ isset($usuario) ? '' : 'required' }}>
        <p class="mt-1 text-xs">Mínimo 8 caracteres, con letras y números.</p>
    </div>

    @if (isset($usuario) && ! $esYo)
        <div class="sm:col-span-2">
            <label class="flex items-center gap-3 font-bold">
                <input type="checkbox" name="activo" value="1" style="width:22px; height:22px;"
                       @checked(old('activo', $usuario->activo))>
                Usuario activo (puede iniciar sesión)
            </label>
        </div>
    @endif

    <div class="flex gap-3 pt-2 sm:col-span-2">
        <button type="submit" class="k-btn">Guardar</button>
        <a href="{{ route('usuarios.index') }}" class="k-btn k-btn-sec">Cancelar</a>
    </div>
</div>
