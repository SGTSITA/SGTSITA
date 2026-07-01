{{-- resources/views/auth/scb-login.blade.php --}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso - Control Bancario</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('favicon/scb-icon.png') }}" />
    <link rel="icon" type="image/png" href="{{ asset('favicon/scb-icon.png') }}" />

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

    <link href="{{ asset('assets/css/argon-dashboard.css?v=2.0.4') }}" rel="stylesheet">

    <style>
        :root {
            --scb-primary: #12355b;
            --scb-primary-dark: #071f38;
            --scb-accent: #1f7a8c;
            --scb-soft: #edf6f9;
            --scb-border: #d9e2ec;
            --scb-text: #1f2937;
            --scb-muted: #6b7280;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            width: 100%;
            min-height: 100%;
            margin: 0;
            overflow-x: hidden;
        }

        body {
            font-family: 'Inter', sans-serif;
            background:
                radial-gradient(circle at top left, rgba(31, 122, 140, .20), transparent 28rem),
                radial-gradient(circle at bottom right, rgba(18, 53, 91, .22), transparent 30rem),
                linear-gradient(135deg, #f8fbff 0%, #eaf1f8 100%);
            color: var(--scb-text);
        }

        .scb-login-wrapper {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1.05fr .95fr;
        }

        .scb-login-brand {
            position: relative;
            padding: 4rem;
            background:
                linear-gradient(135deg, rgba(7, 31, 56, .98), rgba(18, 53, 91, .95)),
                url("{{ asset('assets/img/curved-images/curved14.jpg') }}");
            background-size: cover;
            background-position: center;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
        }

        .scb-login-brand::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 20% 20%, rgba(31, 122, 140, .38), transparent 18rem),
                radial-gradient(circle at 80% 80%, rgba(255, 255, 255, .10), transparent 16rem);
            pointer-events: none;
        }

        .scb-brand-content {
            position: relative;
            z-index: 1;
        }

        .scb-logo-box {
            width: 58px;
            height: 58px;
            border-radius: 20px;
            background: rgba(255, 255, 255, .13);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            margin-bottom: 1.5rem;
            box-shadow: 0 14px 30px rgba(0, 0, 0, .20);
        }

        .scb-brand-title {
            font-size: 42px;
            line-height: 1.05;
            font-weight: 800;
            max-width: 520px;
            margin-bottom: 1rem;
        }

        .scb-brand-text {
            font-size: 15px;
            line-height: 1.7;
            color: rgba(255, 255, 255, .76);
            max-width: 520px;
            margin: 0;
        }

        .scb-brand-stats {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: .8rem;
            max-width: 560px;
        }

        .scb-stat-card {
            border: 1px solid rgba(255, 255, 255, .14);
            background: rgba(255, 255, 255, .08);
            border-radius: 18px;
            padding: 1rem;
            backdrop-filter: blur(8px);
        }

        .scb-stat-card i {
            font-size: 18px;
            margin-bottom: .55rem;
            color: #b8f3ff;
        }

        .scb-stat-title {
            font-size: 12px;
            color: rgba(255, 255, 255, .65);
            margin-bottom: .2rem;
        }

        .scb-stat-value {
            font-size: 15px;
            font-weight: 800;
            color: #fff;
        }

        .scb-login-panel {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .scb-login-card {
            width: 100%;
            max-width: 430px;
            background: rgba(255, 255, 255, .92);
            border: 1px solid rgba(217, 226, 236, .90);
            border-radius: 28px;
            box-shadow: 0 24px 70px rgba(15, 23, 42, .14);
            padding: 2rem;
            backdrop-filter: blur(12px);
        }

        .scb-login-icon {
            width: 54px;
            height: 54px;
            border-radius: 18px;
            background: var(--scb-soft);
            color: var(--scb-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 23px;
            margin-bottom: 1rem;
        }

        .scb-login-title {
            font-size: 25px;
            font-weight: 800;
            margin-bottom: .35rem;
            color: var(--scb-primary-dark);
        }

        .scb-login-subtitle {
            font-size: 13px;
            color: var(--scb-muted);
            margin-bottom: 1.5rem;
        }

        .scb-form-label {
            font-size: 12px;
            font-weight: 800;
            color: #344767;
            margin-bottom: .35rem;
        }

        .scb-input-group {
            position: relative;
        }

        .scb-input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #8a97a8;
            font-size: 14px;
            z-index: 2;
        }

        .scb-form-control {
            height: 46px;
            border-radius: 14px;
            border: 1px solid var(--scb-border);
            padding-left: 40px;
            font-size: 14px;
            background: #fff;
        }

        .scb-form-control:focus {
            border-color: var(--scb-accent);
            box-shadow: 0 0 0 .18rem rgba(31, 122, 140, .14);
        }

        .scb-btn-login {
            width: 100%;
            height: 46px;
            border-radius: 14px;
            border: none;
            background: linear-gradient(135deg, var(--scb-primary), var(--scb-accent));
            color: white;
            font-weight: 800;
            font-size: 14px;
            box-shadow: 0 12px 26px rgba(18, 53, 91, .24);
            transition: .18s ease;
        }

        .scb-btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 16px 34px rgba(18, 53, 91, .30);
        }

        .scb-login-footer {
            margin-top: 1.25rem;
            text-align: center;
            font-size: 12px;
            color: var(--scb-muted);
        }

        .scb-back-link {
            color: var(--scb-primary);
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
        }

        .scb-back-link:hover {
            color: var(--scb-accent);
        }

        .alert {
            border-radius: 14px;
            font-size: 13px;
        }

        @media (max-width: 991.98px) {
            .scb-login-wrapper {
                grid-template-columns: 1fr;
            }

            .scb-login-brand {
                display: none;
            }

            .scb-login-panel {
                min-height: 100vh;
            }
        }

        @media (max-width: 575.98px) {
            .scb-login-panel {
                padding: 1rem;
            }

            .scb-login-card {
                padding: 1.4rem;
                border-radius: 22px;
            }
        }
    </style>
</head>

<body>

    <div class="scb-login-wrapper">

        <section class="scb-login-brand">
            <div class="scb-brand-content">
                <div class="scb-logo-box">
                    <i class="fas fa-building-columns"></i>
                </div>

                <h1 class="scb-brand-title">
                    Control Bancario Empresarial
                </h1>

                <p class="scb-brand-text">
                    Administra cuentas, cargos, abonos y detalles por unidad desde un módulo independiente,
                    limpio y enfocado en movimientos bancarios.
                </p>
            </div>

            <div class="scb-brand-stats">
                <div class="scb-stat-card">
                    <i class="fas fa-money-bill-transfer"></i>
                    <div class="scb-stat-title">Movimientos</div>
                    <div class="scb-stat-value">Cargos / Abonos</div>
                </div>

                <div class="scb-stat-card">
                    <i class="fas fa-truck"></i>
                    <div class="scb-stat-title">Detalle</div>
                    <div class="scb-stat-value">Por unidad</div>
                </div>

                <div class="scb-stat-card">
                    <i class="fas fa-chart-line"></i>
                    <div class="scb-stat-title">Reportes</div>
                    <div class="scb-stat-value">Saldos claros</div>
                </div>
            </div>
        </section>

        <section class="scb-login-panel">
            <div class="scb-login-card">

                <div class="scb-login-icon">
                    <i class="fas fa-lock"></i>
                </div>

                <h2 class="scb-login-title">
                    Acceso al módulo
                </h2>

                <p class="scb-login-subtitle">
                    Ingresa tus credenciales para continuar al sistema de control bancario.
                </p>

                @if ($errors->any())
                    <div class="alert alert-danger mb-3">
                        <strong>Acceso no válido.</strong>
                        <div>{{ $errors->first() }}</div>
                    </div>
                @endif

                @if (session('status'))
                    <div class="alert alert-success mb-3">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('scb.login.post') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="scb-form-label" for="email">
                            Correo electrónico
                        </label>

                        <div class="scb-input-group">
                            <i class="fas fa-envelope scb-input-icon"></i>

                            <input type="email" name="email" id="email"
                                class="form-control scb-form-control @error('email') is-invalid @enderror"
                                value="{{ old('email') }}" placeholder="usuario@empresa.com" autocomplete="email"
                                required autofocus>
                        </div>

                        @error('email')
                            <div class="text-danger small mt-1">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="scb-form-label" for="password">
                            Contraseña
                        </label>

                        <div class="scb-input-group">
                            <i class="fas fa-key scb-input-icon"></i>

                            <input type="password" name="password" id="password"
                                class="form-control scb-form-control @error('password') is-invalid @enderror"
                                placeholder="••••••••" autocomplete="current-password" required>
                        </div>

                        @error('password')
                            <div class="text-danger small mt-1">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember"
                                {{ old('remember') ? 'checked' : '' }}>

                            <label class="form-check-label small" for="remember">
                                Recordarme
                            </label>
                        </div>

                        @if (Route::has('password.request'))
                            <a class="scb-back-link" href="{{ route('password.request') }}">
                                ¿Olvidaste tu contraseña?
                            </a>
                        @endif
                    </div>

                    <button type="submit" class="scb-btn-login">
                        <i class="fas fa-right-to-bracket me-1"></i>
                        Entrar al módulo
                    </button>
                </form>

                <div class="scb-login-footer">

                    <div class="mt-3">
                        SCB · Sistema de Control Bancario
                    </div>
                </div>

            </div>
        </section>

    </div>

    <script src="{{ asset('assets/js/core/bootstrap.min.js') }}"></script>
</body>

</html>
