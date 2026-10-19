<div class="k-tarjeta grid max-w-2xl gap-5 p-6 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <label class="k-label">Nombre del proveedor</label>
        <input type="text" name="nombre" maxlength="80" class="k-input"
               value="{{ old('nombre', $proveedor->nombre ?? '') }}" required autocomplete="off">
    </div>
    <div>
        <label class="k-label">Teléfono</label>
        <input type="tel" name="telefono" maxlength="20" class="k-input"
               value="{{ old('telefono', $proveedor->telefono ?? '') }}" required autocomplete="off">
    </div>
    <div>
        <label class="k-label">Adeudo actual</label>
        <input type="number" name="adeudo" step="0.01" min="0" class="k-input"
               value="{{ old('adeudo', $proveedor->adeudo ?? '0.00') }}" required>
    </div>
    <div class="sm:col-span-2">
        <label class="k-label">Dirección</label>
        <input type="text" name="direccion" maxlength="150" class="k-input"
               value="{{ old('direccion', $proveedor->direccion ?? '') }}" required autocomplete="off">
    </div>
    <div class="sm:col-span-2">
        <label class="k-label">Materias surtidas</label>
        <input type="text" name="materias_surtidas" maxlength="200" placeholder="Ej: Pollo, Carbón, Especias" class="k-input"
               value="{{ old('materias_surtidas', $proveedor->materias_surtidas ?? '') }}" autocomplete="off">
    </div>

    <div class="flex gap-3 pt-2 sm:col-span-2">
        <button type="submit" class="k-btn">Guardar</button>
        <a href="{{ route('proveedores.index') }}" class="k-btn k-btn-sec">Cancelar</a>
    </div>
</div>