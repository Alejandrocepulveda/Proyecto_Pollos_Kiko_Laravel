<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pollos Kiko - @yield('titulo')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }

        /* =================== COLORES: cambia solo esta sección =================== */
        :root {
            --k-fondo:       #ffe894;  /* fondo de toda la página            */
            --k-tarjeta:     #fffde0;  /* tarjetas y barra superior          */
            --k-acento:      #ff5233;  /* menú lateral, encabezados de tabla */
            --k-acento-osc:  #ff3314;  /* parte baja del degradado y botones */
            --k-sombra:      #b32a14;  /* sombra de los botones principales  */
            --k-secundario:  #ffd24d;  /* menú activo y botones secundarios  */
            --k-sombra-sec:  #b38f1c;  /* sombra de los botones secundarios  */
            --k-borde:       #ff5233;  /* bordes de tablas, tarjetas, campos */
            --k-linea:       #ffd0c7;  /* líneas entre filas de la tabla     */
            --k-texto:       #000000;  /* color del texto                    */
            --k-ok:          #1f7a1f;  /* texto verde (sin adeudo)           */
            --k-ok-fondo:    #d4f7d4;
            --k-malo:        #cc0000;  /* texto rojo (adeudo, errores)       */
            --k-malo-fondo:  #ffd6d6;
        }
        /* ========================================================================== */

        body { background: var(--k-fondo); color: var(--k-texto); font-family: 'Arial Rounded MT Bold', Arial, sans-serif; }

        .k-lateral { background: linear-gradient(180deg, var(--k-acento), var(--k-acento-osc)); }
        .k-lateral-borde { border-color: rgba(0, 0, 0, .2); }
        .k-item { display: flex; align-items: center; gap: 12px; padding: 10px 12px; border-radius: 12px; font-weight: bold; color: var(--k-texto); transition: background .15s; }
        .k-item:hover { background: var(--k-tarjeta); }
        .k-item.activo { background: var(--k-secundario); box-shadow: 0 3px 0 var(--k-sombra-sec); }
        .k-sticker { border: 4px solid #fff; border-radius: 18px; transform: rotate(-3deg); box-shadow: 0 4px 10px rgba(0, 0, 0, .25); }

        .k-barra { background: var(--k-tarjeta); border-bottom: 3px solid var(--k-borde); }
        .k-titulo { font-weight: 900; text-shadow: 2px 2px 0 #fff, 4px 4px 0 rgba(0, 0, 0, .1); }
        .k-tarjeta { background: var(--k-tarjeta); border: 3px solid var(--k-borde); border-radius: 15px; }

        .k-tabla-wrap { overflow-x: auto; background: #fff; border: 3px solid var(--k-borde); border-radius: 15px; }
        .k-tabla { width: 100%; border-collapse: separate; border-spacing: 0; }
        .k-tabla th { background: linear-gradient(180deg, var(--k-acento), var(--k-acento-osc)); padding: 12px; text-align: left; font-weight: bold; white-space: nowrap; }
        .k-tabla td { padding: 12px; border-bottom: 1px solid var(--k-linea); font-weight: bold; }
        .k-tabla tbody tr:hover { background: var(--k-tarjeta); }

        .k-btn { display: inline-block; border: 0; border-radius: 12px; padding: 10px 22px; font-size: 16px; font-weight: bold; font-style: italic; cursor: pointer; text-decoration: none; color: var(--k-texto); background: linear-gradient(180deg, var(--k-acento), var(--k-acento-osc)); box-shadow: 0 4px 0 var(--k-sombra); }
        .k-btn:active { transform: translateY(3px); box-shadow: 0 1px 0 var(--k-sombra); }
        .k-btn-sec { background: var(--k-secundario); box-shadow: 0 4px 0 var(--k-sombra-sec); }
        .k-btn-sm { padding: 6px 14px; font-size: 13px; }

        .k-label { display: block; font-weight: bold; margin-bottom: 6px; }
        .k-input { width: 100%; height: 50px; border: 3px solid var(--k-borde); border-radius: 15px; padding: 0 15px; font-size: 16px; font-weight: bold; background: #fff; color: var(--k-texto); outline: none; }
        .k-input:focus { box-shadow: 0 0 0 3px var(--k-secundario); }

        .k-badge { display: inline-block; border-radius: 999px; padding: 3px 12px; font-size: 13px; font-weight: bold; }
        .k-badge-ok { background: var(--k-ok-fondo); color: var(--k-ok); }
        .k-badge-malo { background: var(--k-malo-fondo); color: var(--k-malo); }

        .k-alerta { border-radius: 12px; padding: 12px 16px; margin-bottom: 20px; font-weight: bold; }
        .k-alerta-ok { background: var(--k-ok-fondo); border: 2px solid var(--k-ok); }
        .k-alerta-error { background: var(--k-malo-fondo); border: 2px solid var(--k-malo); }
    </style>
</head>
<body x-data="{ abierto: false }">

@php
    $menu = [
        ['Panel',       route('dashboard'),         request()->routeIs('dashboard'),     '🏠'],
        ['Nueva venta', route('ventas.create'),     request()->routeIs('ventas.create'), '💰'],
        ['Historial',   route('ventas.index'),      request()->routeIs('ventas.index'),  '🧾'],
        ['Inventario',  route('productos.index'),   request()->routeIs('productos.*'),   '📦'],
        ['Clientes',    route('clientes.index'),    request()->routeIs('clientes.*'),    '🐔'],
        ['Proveedores', route('proveedores.index'), request()->routeIs('proveedores.*'), '🚚'],
    ];
    if (auth()->user()->esAdmin()) {
        $menu[] = ['Usuarios', route('usuarios.index'), request()->routeIs('usuarios.*'), '👤'];
    }
    $hayLogo = file_exists(public_path('img/logo.jpeg'));
@endphp

<div class="flex min-h-screen">

    {{-- Fondo oscuro al abrir el menú en celular --}}
    <div x-show="abierto" x-cloak @click="abierto = false" class="fixed inset-0 z-30 bg-black/40 lg:hidden"></div>

    {{-- BARRA LATERAL --}}
    <aside :class="abierto ? 'translate-x-0' : '-translate-x-full'"
           class="k-lateral fixed inset-y-0 left-0 z-40 flex w-64 flex-col transition-transform lg:static lg:translate-x-0">

        <div class="k-lateral-borde flex h-16 items-center gap-3 border-b-2 px-5">
            <span class="text-2xl">🍗</span>
            <span class="text-xl font-black">Pollos Kiko</span>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto p-4">
            @foreach ($menu as [$etiqueta, $url, $activo, $icono])
                <a href="{{ $url }}" class="k-item {{ $activo ? 'activo' : '' }}">
                    <span class="text-lg">{{ $icono }}</span>
                    {{ $etiqueta }}
                </a>
            @endforeach
        </nav>

        {{-- Sticker del pollo: si no existe la imagen, simplemente no se muestra --}}
        @if ($hayLogo)
            <div class="px-6 pb-4">
                <img src="{{ asset('img/logo.jpeg') }}" alt="Pollos Kiko" class="k-sticker mx-auto w-28">
            </div>
        @endif

        <div class="k-lateral-borde border-t-2 p-4">
            <p class="truncate text-sm font-bold">{{ auth()->user()->name }}</p>
            <p class="mb-3 text-xs">{{ auth()->user()->role->nombre }}</p>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="k-btn k-btn-sec k-btn-sm w-full">Cerrar sesión</button>
            </form>
        </div>
    </aside>

    {{-- CONTENIDO --}}
    <div class="flex min-w-0 flex-1 flex-col">
        <header class="k-barra flex h-16 items-center gap-4 px-4 lg:px-8">
            <button @click="abierto = true" class="text-2xl lg:hidden" aria-label="Abrir menú">☰</button>
            <h1 class="k-titulo text-2xl">@yield('titulo')</h1>
        </header>

        <main class="p-4 lg:p-8">
            @if (session('exito'))
                <div class="k-alerta k-alerta-ok">{{ session('exito') }}</div>
            @endif
            @if (session('error'))
                <div class="k-alerta k-alerta-error">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="k-alerta k-alerta-error">
                    @foreach ($errors->all() as $e) <div>{{ $e }}</div> @endforeach
                </div>
            @endif

            @yield('contenido')
        </main>
    </div>
</div>
</body>
</html>