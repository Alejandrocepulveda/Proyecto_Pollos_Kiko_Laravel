@extends('layouts.panel')
@section('titulo', 'Nueva venta')

@section('contenido')
    <style>
        .k-prod { display: flex; flex-direction: column; gap: 4px; text-align: left; padding: 12px; background: #fff; border: 3px solid var(--k-borde); border-radius: 15px; cursor: pointer; transition: transform .1s, background .15s; }
        .k-prod:hover:not(:disabled) { background: var(--k-secundario); transform: translateY(-2px); }
        .k-prod:active:not(:disabled) { transform: translateY(1px); }
        .k-prod:disabled { opacity: .45; cursor: not-allowed; }
        .k-prod-nombre { font-weight: 900; line-height: 1.2; }
        .k-prod-precio { font-weight: bold; color: var(--k-sombra); }
        .k-prod-stock { font-size: 12px; font-weight: bold; }
        .k-prod-stock.bajo { color: var(--k-malo); }
        .k-linea { display: flex; align-items: center; gap: 8px; padding: 8px 0; border-bottom: 1px dashed var(--k-linea); }
        .k-mini { width: 30px; height: 30px; border: 2px solid var(--k-borde); border-radius: 8px; background: #fff; font-weight: 900; cursor: pointer; line-height: 1; }
        .k-mini:hover { background: var(--k-secundario); }
        .k-cant { width: 52px; height: 30px; border: 2px solid var(--k-borde); border-radius: 8px; text-align: center; font-weight: bold; background: #fff; }
    </style>

    @if (session('nota'))
        <div class="k-tarjeta mb-6 flex flex-wrap items-center justify-between gap-3 p-4">
            <p class="font-black">🧾 La nota de la venta está lista.</p>
            <a href="{{ session('nota') }}" target="_blank" class="k-btn">Ver nota PDF</a>
        </div>
    @endif

    <div x-data="venta()" class="grid gap-6 lg:grid-cols-5">

        {{-- ================= PRODUCTOS ================= --}}
        <div class="lg:col-span-3">
            <input type="text" x-model="buscar" placeholder="Buscar producto… (Enter agrega el primero)"
                   @keydown.enter.prevent="if (filtrados.length) agregar(filtrados[0].id)"
                   class="k-input mb-4" autocomplete="off" autofocus>

            @if ($productos->isEmpty())
                <div class="k-tarjeta p-6">
                    <p class="font-black">No hay productos con stock disponible.</p>
                    <p class="mb-3 text-sm">Agrega productos o repón existencias para poder vender.</p>
                    <a href="{{ route('productos.index') }}" class="k-btn k-btn-sec k-btn-sm">Ir al inventario</a>
                </div>
            @else
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                    <template x-for="p in filtrados" :key="p.id">
                        <button type="button" class="k-prod" @click="agregar(p.id)" :disabled="restante(p) === 0">
                            <span class="k-prod-nombre" x-text="p.nombre"></span>
                            <span class="k-prod-precio" x-text="dinero(p.precio)"></span>
                            <span class="k-prod-stock" :class="restante(p) <= 5 ? 'bajo' : ''"
                                  x-text="restante(p) + ' disponibles'"></span>
                        </button>
                    </template>
                </div>

                <p x-show="filtrados.length === 0" x-cloak class="mt-4 font-bold">
                    No se encontró ningún producto con esa búsqueda.
                </p>
            @endif
        </div>

        {{-- ================= NOTA DE VENTA ================= --}}
        <div class="lg:col-span-2">
            <form method="POST" action="{{ route('ventas.store') }}" @submit="enviando = true"
                  class="k-tarjeta p-5 lg:sticky lg:top-4">
                @csrf

                <h2 class="mb-4 text-lg font-black">🧾 Nota de venta</h2>

                <div class="mb-4">
                    <label class="k-label">Cliente</label>
                    <select name="cliente_id" class="k-input" style="height:44px">
                        <option value="">Cliente General</option>
                        @foreach ($clientes as $c)
                            <option value="{{ $c->id }}" @selected(old('cliente_id') == $c->id)>{{ $c->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Líneas de la venta --}}
                <div class="mb-2 min-h-16">
                    <p x-show="carrito.length === 0" class="py-4 text-center text-sm font-bold">
                        Pulsa un producto para agregarlo 👈
                    </p>

                    <template x-for="l in carrito" :key="l.id">
                        <div class="k-linea">
                            <div class="min-w-0 flex-1">
                                <p class="truncate font-bold" x-text="producto(l.id).nombre"></p>
                                <p class="text-xs" x-text="dinero(producto(l.id).precio) + ' c/u'"></p>
                            </div>
                            <div class="flex items-center gap-1">
                                <button type="button" class="k-mini" @click="cambiar(l, -1)">−</button>
                                <input type="number" class="k-cant" min="1" :max="producto(l.id).stock"
                                       x-model.number="l.cantidad" @change="ajustar(l)">
                                <button type="button" class="k-mini" @click="cambiar(l, 1)">+</button>
                            </div>
                            <p class="w-20 text-right font-black" x-text="dinero(producto(l.id).precio * l.cantidad)"></p>
                            <button type="button" class="k-mini" @click="quitar(l.id)" title="Quitar">✕</button>
                        </div>
                    </template>
                </div>

                {{-- Datos que se envían al servidor --}}
                <template x-for="(l, i) in carrito" :key="'h' + l.id">
                    <div>
                        <input type="hidden" :name="'items[' + i + '][producto_id]'" :value="l.id">
                        <input type="hidden" :name="'items[' + i + '][cantidad]'" :value="l.cantidad">
                    </div>
                </template>

                {{-- Total --}}
                <div class="my-4 flex items-center justify-between border-t-2 pt-4" style="border-color: var(--k-borde)">
                    <span class="text-lg font-black">TOTAL</span>
                    <span class="text-3xl font-black" style="color: var(--k-sombra)" x-text="dinero(total)"></span>
                </div>

                {{-- Efectivo y cambio (solo para cuentas, no se guarda) --}}
                <div class="mb-4" x-show="carrito.length > 0" x-cloak>
                    <label class="k-label">Efectivo recibido</label>
                    <input type="number" min="0" step="0.01" x-model="recibido" placeholder="0.00" class="k-input" style="height:44px">

                    <div class="mt-2 flex flex-wrap gap-2">
                        <button type="button" class="k-btn k-btn-sec k-btn-sm" @click="recibido = total.toFixed(2)">Exacto</button>
                        <template x-for="m in [50, 100, 200, 500]" :key="m">
                            <button type="button" class="k-btn k-btn-sec k-btn-sm" @click="recibido = m" x-text="'$' + m"></button>
                        </template>
                    </div>

                    <p x-show="cambio !== null" x-cloak class="mt-3 text-lg font-black"
                       :style="'color:' + (cambio >= 0 ? 'var(--k-ok)' : 'var(--k-malo)')"
                       x-text="cambio >= 0 ? 'Cambio: ' + dinero(cambio) : 'Faltan: ' + dinero(-cambio)"></p>
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="k-btn flex-1 text-lg" :disabled="carrito.length === 0 || enviando"
                            :style="(carrito.length === 0 || enviando) ? 'opacity:.5; cursor:not-allowed' : ''">
                        <span x-text="enviando ? 'Registrando…' : 'Registrar venta'"></span>
                    </button>
                    <button type="button" class="k-btn k-btn-sec" x-show="carrito.length > 0" x-cloak
                            @click="carrito = []; recibido = ''">Vaciar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function venta() {
            return {
                productos: @json($productos),
                carrito: [],
                buscar: '',
                recibido: '',
                enviando: false,

                init() {
                    // Si el servidor rechazó la venta, se reconstruye lo que ya se había armado
                    Object.values(@json(old('items', []))).forEach(i => {
                        this.agregar(parseInt(i.producto_id), parseInt(i.cantidad) || 1);
                    });
                },

                get filtrados() {
                    const q = this.buscar.trim().toLowerCase();
                    return this.productos.filter(p => p.nombre.toLowerCase().includes(q));
                },

                get total() {
                    return this.carrito.reduce((t, l) => t + this.producto(l.id).precio * l.cantidad, 0);
                },

                get cambio() {
                    const r = parseFloat(this.recibido);
                    return isNaN(r) ? null : Math.round((r - this.total) * 100) / 100;
                },

                producto(id) {
                    return this.productos.find(p => p.id === id);
                },

                restante(p) {
                    const l = this.carrito.find(x => x.id === p.id);
                    return p.stock - (l ? l.cantidad : 0);
                },

                agregar(id, n = 1) {
                    const p = this.producto(id);
                    if (!p) return;
                    const l = this.carrito.find(x => x.id === id);
                    if (l) {
                        l.cantidad = Math.min(p.stock, l.cantidad + n);
                    } else {
                        this.carrito.push({ id: id, cantidad: Math.min(p.stock, n) });
                    }
                },

                cambiar(l, delta) {
                    const n = l.cantidad + delta;
                    if (n < 1) { this.quitar(l.id); return; }
                    l.cantidad = Math.min(n, this.producto(l.id).stock);
                },

                ajustar(l) {
                    const v = parseInt(l.cantidad) || 1;
                    l.cantidad = Math.max(1, Math.min(v, this.producto(l.id).stock));
                },

                quitar(id) {
                    this.carrito = this.carrito.filter(l => l.id !== id);
                },

                dinero(n) {
                    return '$' + Number(n).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                },
            };
        }
    </script>
@endsection