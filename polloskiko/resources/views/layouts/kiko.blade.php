<!DOCTYPE html>
<html lang="es">
<head>
    <mezta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pollos Kiko - @yield('titulo')</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Arial Rounded MT Bold', Arial, sans-serif; }
        body { background: #ffe894; display: flex; justify-content: center; min-height: 100vh; padding: 20px; }
        .main-card { background: #fffde0; width: 100%; max-width: 900px; padding: 40px; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,.1); align-self: flex-start; }
        .barra { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; font-size: 14px; }
        .barra a, .barra button { color: #ff5233; font-weight: bold; text-decoration: none; background: none; border: 0; cursor: pointer; font-size: 14px; }
        .header-section { display: flex; align-items: center; gap: 20px; margin-bottom: 30px; }
        .logo { width: 100px; height: 100px; border: 3px dashed #ff5233; border-radius: 10px; background: #fff; padding: 5px; display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0; }
        .logo img { max-width: 100%; max-height: 100%; object-fit: contain; }
        .title { font-size: clamp(28px, 6vw, 48px); font-weight: 900; text-shadow: 2px 2px 0 #fff, 4px 4px 0 rgba(0,0,0,.1); }
        .table-container { overflow-x: auto; margin: 20px 0; border-radius: 15px; }
        table { width: 100%; border-collapse: separate; border-spacing: 0; background: #fff; border: 3px solid #ff5233; border-radius: 15px; overflow: hidden; }
        th { background: linear-gradient(180deg, #ff5233, #ff3314); padding: 12px; font-size: 18px; }
        td { padding: 12px; text-align: center; border-bottom: 1px solid #ffd0c7; font-weight: bold; }
        .field { margin-bottom: 18px; }
        .field label { display: block; font-weight: bold; margin-bottom: 6px; }
        .field input, .field select { width: 100%; height: 50px; border: 3px solid #ff5233; border-radius: 15px; padding: 0 15px; font-size: 18px; font-weight: bold; outline: none; background: #fff; }
        .btn { display: inline-block; border: 0; border-radius: 12px; padding: 10px 22px; font-size: 18px; font-weight: bold; font-style: italic; cursor: pointer; text-decoration: none; color: #000; background: linear-gradient(180deg, #ff5233, #ff3314); box-shadow: 0 4px 0 #b32a14; }
        .btn-sec { background: #ffd24d; box-shadow: 0 4px 0 #b38f1c; }
        .btn-sm { padding: 6px 14px; font-size: 14px; }
        .alert { padding: 12px 16px; border-radius: 10px; margin-bottom: 20px; font-weight: bold; }
        .alert-success { background: #d4f7d4; border: 2px solid #2e9e2e; }
        .alert-error { background: #ffd6d6; border: 2px solid #cc0000; }
        .acciones { display: flex; gap: 8px; justify-content: center; }
    </style>
</head>
<body>
<div class="main-card">
    <div class="barra">
        <span>{{ auth()->user()->name }} ({{ auth()->user()->role->nombre }})</span>
        <span>
            <a href="{{ route('dashboard') }}">Menú</a> ·
            <form method="POST" action="{{ route('logout') }}" style="display:inline">
                @csrf
                <button type="submit">Cerrar sesión</button>
            </form>
        </span>
    </div>

    <div class="header-section">
        <div class="logo"><img src="{{ asset('img/logo.png') }}" alt="Pollos Kiko"></div>
        <h1 class="title">@yield('titulo')</h1>
    </div>

    @if (session('exito'))
        <div class="alert alert-success">{{ session('exito') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-error">
            @foreach ($errors->all() as $e) <div>{{ $e }}</div> @endforeach
        </div>
    @endif

    @yield('contenido')
</div>
</body>
</html>
