<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Vital Clean — Acceso</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,500;1,600&display=swap" rel="stylesheet">
    <style>
        :root {
            --azul: #26437D;
            --azul-claro: #17A9E0;
            --blanco: #FFFFFF;
            --gris-claro: #F5F5F5;
            --rojo: #C0392B;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Poppins', system-ui, -apple-system, "Segoe UI", sans-serif;
            background: var(--azul);
        }
        .login-card {
            background: var(--blanco);
            width: 100%;
            max-width: 360px;
            padding: 2rem 1.75rem;
            border-radius: .75rem;
            box-shadow: 0 10px 30px rgba(0,0,0,.25);
        }
        .login-card h1 {
            font-size: 1.4rem;
            text-align: center;
            color: var(--azul);
            margin: 0 0 .25rem;
        }
        .login-card p.subtitle {
            text-align: center;
            color: #6b7280;
            margin: 0 0 1.5rem;
            font-size: .9rem;
        }
        label { display: block; font-size: .85rem; font-weight: 600; margin-bottom: .3rem; }
        input[type=text], input[type=password] {
            width: 100%;
            padding: .7rem .8rem;
            font-size: 1.05rem;
            border: 1px solid #d1d5db;
            border-radius: .375rem;
            margin-bottom: 1rem;
        }
        .remember { display: flex; align-items: center; gap: .5rem; margin-bottom: 1.25rem; font-size: .9rem; }
        .remember input { width: auto; margin: 0; }
        button.btn-login {
            width: 100%;
            background: var(--rojo);
            color: var(--blanco);
            border: none;
            padding: .85rem;
            font-size: 1.05rem;
            font-weight: 600;
            border-radius: .375rem;
            cursor: pointer;
        }
        button.btn-login:hover { opacity: .92; }
        .errors {
            background: #fdecea;
            border: 1px solid var(--rojo);
            color: var(--rojo);
            padding: .6rem .8rem;
            border-radius: .375rem;
            font-size: .85rem;
            margin-bottom: 1rem;
        }

        /* Identidad de marca — logo real del cliente (resources/images/logo-vital-clean.png) */
        .login-logo { display: block; width: 168px; max-width: 70%; margin: 0 auto .75rem; }
    </style>
</head>
<body>
    <div class="login-card">
        <img src="{{ asset('images/logo-vital-clean.png') }}" alt="Lavandería Vital Clean" class="login-logo">
        <p class="subtitle">Sistema de Gestión Operativa</p>

        @if ($errors->any())
            <div class="errors">
                @foreach ($errors->all() as $error)
                    {{ $error }}<br>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login.store') }}">
            @csrf
            <label for="username">Nombre de Usuario</label>
            <input type="text" id="username" name="username" value="{{ old('username') }}"
                   placeholder="Ej. vendedor.vitalclean" autofocus required>

            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password" required>

            <label class="remember">
                <input type="checkbox" name="remember" value="1"> Recordar usuario
            </label>

            <button type="submit" class="btn-login">Iniciar Sesión</button>
        </form>
    </div>
</body>
</html>
