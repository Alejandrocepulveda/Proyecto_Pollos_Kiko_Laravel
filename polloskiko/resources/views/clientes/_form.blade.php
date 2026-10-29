<div class="k-tarjeta grid max-w-2xl gap-5 p-6 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <label class="k-label">Nombre</label>
        <input type="text" name="nombre" maxlength="80" class="k-input"
               value="{{ old('nombre', $cliente->nombre ?? '') }}" required autocomplete="off">
    </div>
    <div class="sm:col-span-2">
        <label class="k-label">Teléfono</label>
        <input type="tel" name="telefono" maxlength="20" class="k-input"
               value="{{ old('telefono', $cliente->telefono ?? '') }}" required autocomplete="off">
    </div>
    <div class="sm:col-span-2">
        <label class="k-label">Dirección</label>
        <input type="text" name="direccion" maxlength="150" class="k-input"
               value="{{ old('direccion', $cliente->direccion ?? '') }}" required autocomplete="off">
    </div>

    <div class="flex gap-3 pt-2 sm:col-span-2">
        <button type="submit" class="k-btn">Guardar</button>
        <a href="{{ route('clientes.index') }}" class="k-btn k-btn-sec">Cancelar</a>
    </div>
</div>
