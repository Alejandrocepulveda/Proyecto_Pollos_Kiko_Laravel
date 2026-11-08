@php
    // Pestaña que se muestra al cargar: "registro" si venimos de /register o si falló el formulario de registro
    $inicial  = (($tab ?? null) === 'registro' || old('_form') === 'registro') ? 'registro' : 'login';
    $hayLogo  = file_exists(public_path('img/logo.jpeg'));
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pollos Kiko - Acceso</title>
    <style>
        :root {
            --k-fondo: #ffe894; --k-tarjeta: #fffde0; --k-acento: #ff5233; --k-acento-osc: #ff3314;
            --k-sombra: #b32a14; --k-secundario: #ffd24d; --k-sombra-sec: #b38f1c; --k-borde: #ff5233;
            --k-texto: #000; --k-ok: #1f7a1f; --k-ok-fondo: #d4f7d4; --k-malo: #cc0000; --k-malo-fondo: #ffd6d6;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Arial Rounded MT Bold', Arial, sans-serif; }
        body { background: var(--k-fondo); color: var(--k-texto); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }

        .caja { width: 100%; max-width: 960px; display: grid; grid-template-columns: 1fr 1.2fr; background: var(--k-tarjeta); border: 4px solid var(--k-borde); border-radius: 24px; overflow: hidden; box-shadow: 0 12px 30px rgba(0, 0, 0, .18); }

        .lado { background: linear-gradient(180deg, var(--k-acento), var(--k-acento-osc)); padding: 36px 28px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 18px; text-align: center; }
        .marca { font-size: clamp(30px, 5vw, 44px); font-weight: 900; line-height: 1; text-transform: uppercase; color: #fff; -webkit-text-stroke: 2px #000; text-shadow: 3px 3px 0 #000; }
        .lema { font-weight: bold; }
        .sticker { width: 170px; max-width: 100%; border: 5px solid #fff; border-radius: 22px; transform: rotate(-3deg); box-shadow: 0 6px 14px rgba(0, 0, 0, .3); background: #fff; }
        .emoji { font-size: 90px; line-height: 1; }

        .formulario { padding: 32px 36px; }
        .tabs { display: flex; gap: 8px; margin-bottom: 22px; }
        .tab { flex: 1; padding: 10px; border: 3px solid var(--k-borde); border-radius: 14px; background: #fff; font-weight: 900; font-size: 15px; cursor: pointer; color: var(--k-texto); }
        .tab.activa { background: var(--k-secundario); box-shadow: 0 3px 0 var(--k-sombra-sec); }
        .panel[hidden] { display: none; }

        .titulo { font-size: 22px; font-weight: 900; margin-bottom: 4px; }
        .subtitulo { font-size: 14px; margin-bottom: 16px; }

        .roles { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 16px; }
        .rol { position: relative; }
        .rol input { position: absolute; opacity: 0; pointer-events: none; }
        .rol span { display: block; text-align: center; padding: 12px 8px; border: 3px solid var(--k-borde); border-radius: 14px; background: #fff; font-weight: 900; cursor: pointer; }
        .rol input:checked + span { background: var(--k-acento); box-shadow: 0 3px 0 var(--k-sombra); }
        .rol input:focus-visible + span { outline: 3px solid var(--k-secundario); }

        .fila { display: grid; gap: 12px; margin-bottom: 12px; }
        .fila.dos { grid-template-columns: 1fr 1fr; }
        label.etq { display: block; font-size: 13px; font-weight: bold; margin-bottom: 4px; }
        .campo { width: 100%; height: 46px; border: 3px solid var(--k-borde); border-radius: 14px; padding: 0 14px; font-size: 15px; font-weight: bold; background: #fff; color: var(--k-texto); outline: none; }
        .campo:focus { box-shadow: 0 0 0 3px var(--k-secundario); }

        .btn { width: 100%; height: 48px; border: 0; border-radius: 14px; font-size: 18px; font-weight: bold; font-style: italic; cursor: pointer; color: var(--k-texto); background: linear-gradient(180deg, var(--k-acento), var(--k-acento-osc)); box-shadow: 0 4px 0 var(--k-sombra); margin-top: 6px; }
        .btn:active { transform: translateY(3px); box-shadow: 0 1px 0 var(--k-sombra); }

        .aviso { border-radius: 12px; padding: 10px 14px; margin-bottom: 14px; font-weight: bold; font-size: 14px; }
        .aviso.ok { background: var(--k-ok-fondo); border: 2px solid var(--k-ok); }
        .aviso.error { background: var(--k-malo-fondo); border: 2px solid var(--k-malo); }
        .nota { font-size: 12px; margin-top: 12px; }

        @media (max-width: 760px) {
            .caja { grid-template-columns: 1fr; }
            .lado { padding: 20px; gap: 10px; }
            .sticker { width: 100px; }
            .emoji { font-size: 56px; }
            .formulario { padding: 24px 20px; }
            .fila.dos { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="caja">

    <div class="lado">
        @if ($hayLogo)
            <img src="{{ asset('img/logo.jpeg') }}" alt="Pollos Kiko" class="sticker">
        @else
            <div class="emoji">🍗</div>
        @endif
        <h1 class="marca">Pollos Kiko</h1>
        <p class="lema">Sistema de ventas e inventario</p>
    </div>

    <div class="formulario">
        <div class="tabs">
            <button type="button" class="tab" data-tab="login">Iniciar sesión</button>
            <button type="button" class="tab" data-tab="registro">Solicitar acceso</button>
        </div>

        {{-- ============ INICIAR SESIÓN ============ --}}
        <div class="panel" data-panel="login">
            <p class="titulo">¡Bienvenido!</p>
            <p class="subtitulo">Elige cómo vas a entrar.</p>

            @if (session('status'))
                <div class="aviso ok">{{ session('status') }}</div>
            @endif
            @if ($errors->any() && $inicial === 'login')
                <div class="aviso error">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="roles">
                    <label class="rol">
                        <input type="radio" name="acceso" value="admin" @checked(old('acceso') === 'admin')>
                        <span>🔑 Administrador</span>
                    </label>
                    <label class="rol">
                        <input type="radio" name="acceso" value="cajero" @checked(old('acceso', 'cajero') === 'cajero')>
                        <span>💰 Cajero</span>
                    </label>
                </div>

                <div class="fila">
                    <div>
                        <label class="etq" for="email">Correo electrónico</label>
                        <input id="email" type="email" name="email" class="campo" required autofocus
                               autocomplete="username" value="{{ $inicial === 'login' ? old('email') : '' }}">
                    </div>
                    <div>
                        <label class="etq" for="password">Contraseña</label>
                        <input id="password" type="password" name="password" class="campo" required
                               autocomplete="current-password">
                    </div>
                </div>

                <button type="submit" class="btn">Ingresar</button>
            </form>
        </div>

        {{-- ============ SOLICITAR ACCESO ============ --}}
        <div class="panel" data-panel="registro">
            <p class="titulo">Solicitar acceso</p>
            <p class="subtitulo">Llena tus datos. Un administrador aprobará tu cuenta.</p>

            @if ($errors->any() && $inicial === 'registro')
                <div class="aviso error">
                    @foreach ($errors->all() as $e)
                        <div>{{ $e }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}">
                @csrf
                <input type="hidden" name="_form" value="registro">

                <div class="fila dos">
                    <div>
                        <label class="etq">Nombre</label>
                        <input type="text" name="name" class="campo" maxlength="100" required autocomplete="given-name"
                               value="{{ old('_form') === 'registro' ? old('name') : '' }}">
                    </div>
                    <div>
                        <label class="etq">Apellidos</label>
                        <input type="text" name="apellidos" class="campo" maxlength="100" required autocomplete="family-name"
                               value="{{ old('_form') === 'registro' ? old('apellidos') : '' }}">
                    </div>
                </div>
                <div class="fila">
                    <div>
                        <label class="etq">Domicilio</label>
                        <input type="text" name="domicilio" class="campo" maxlength="150" required autocomplete="street-address"
                               value="{{ old('_form') === 'registro' ? old('domicilio') : '' }}">
                    </div>
                </div>
                <div class="fila dos">
                    <div>
                        <label class="etq">Teléfono</label>
                        <input type="tel" name="telefono" class="campo" maxlength="20" required autocomplete="tel"
                               value="{{ old('_form') === 'registro' ? old('telefono') : '' }}">
                    </div>
                    <div>
                        <label class="etq">Correo electrónico</label>
                        <input type="email" name="email" class="campo" maxlength="255" required autocomplete="email"
                               value="{{ old('_form') === 'registro' ? old('email') : '' }}">
                    </div>
                </div>
                <div class="fila dos">
                    <div>
                        <label class="etq">Contraseña</label>
                        <input type="password" name="password" class="campo" required autocomplete="new-password">
                    </div>
                    <div>
                        <label class="etq">Repite la contraseña</label>
                        <input type="password" name="password_confirmation" class="campo" required autocomplete="new-password">
                    </div>
                </div>

                <button type="submit" class="btn">Enviar solicitud</button>
                <p class="nota">Mínimo 8 caracteres, con letras y números. Tu cuenta se crea como Cajero y podrás entrar cuando un administrador la apruebe.</p>
            </form>
        </div>
    </div>
</div>

<script>
    const tabs = document.querySelectorAll('[data-tab]');
    const panels = document.querySelectorAll('[data-panel]');

    function mostrar(nombre) {
        tabs.forEach(t => t.classList.toggle('activa', t.dataset.tab === nombre));
        panels.forEach(p => p.hidden = p.dataset.panel !== nombre);
    }

    tabs.forEach(t => t.addEventListener('click', () => mostrar(t.dataset.tab)));
    mostrar('{{ $inicial }}');
</script>
</body>
</html>
