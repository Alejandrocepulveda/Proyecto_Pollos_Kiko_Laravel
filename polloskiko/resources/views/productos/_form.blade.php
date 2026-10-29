<div class="k-tarjeta grid max-w-2xl gap-5 p-6 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <label class="k-label">Producto</label>
        <input type="text" name="nombre" maxlength="80" class="k-input"
               value="{{ old('nombre', $producto->nombre ?? '') }}" required autocomplete="off">
    </div>
    <div>
        <label class="k-label">Cantidad en stock</label>
        <input type="number" name="stock" min="0" class="k-input"
               value="{{ old('stock', $producto->stock ?? 0) }}" required>
    </div>
    <div>
        <label class="k-label">Precio de venta</label>
        <input type="number" name="precio_venta" step="0.01" min="0.01" class="k-input"
               value="{{ old('precio_venta', $producto->precio_venta ?? '') }}" required>
    </div>

    <div class="flex gap-3 pt-2 sm:col-span-2">
        <button type="submit" class="k-btn">Guardar</button>
        <a href="{{ route('productos.index') }}" class="k-btn k-btn-sec">Cancelar</a>
    </div>
</div>
