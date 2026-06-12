{{-- resources/views/layouts/scb.blade.php --}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <meta name="csrf-token" content="{{ csrf_token() }}" />

    @yield('meta-tags')

    <title>@yield('template_title', 'Control Bancario') - SCB</title>

    {{-- Favicon --}}
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('favicon/scb-icon.png') }}" />
    <link rel="icon" type="image/png" href="{{ asset('favicon/scb-icon.png') }}" />

    {{-- Fuentes --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet" />

    {{-- Iconos --}}
    <link href="{{ asset('assets/css/icons.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/nucleo-icons.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/nucleo-svg.css') }}" rel="stylesheet" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link rel="stylesheet"
        href="{{ asset('css/scb/scb-loading.css') }}?v={{ filemtime(public_path('css/scb/scb-loading.css')) }}">
    <link rel="stylesheet"
        href="{{ asset('css/scb/principal.css') }}?v={{ filemtime(public_path('css/scb/principal.css')) }}">
    {{-- SweetAlert --}}
    <link rel="stylesheet" href="{{ asset('assets/css/sweetalert2.css?v=2') }}" />

    {{-- Bootstrap / Argon base --}}
    <link id="pagestyle" href="{{ asset('assets/css/argon-dashboard.css?v=2.0.4') }}" rel="stylesheet" />

    {{-- Loading --}}
    <link rel="stylesheet" href="{{ asset('css/sgt/loading.css') }}" />

    @yield('css')

    <style>
        :root {
            --scb-primary: #12355b;
            --scb-primary-dark: #0b233d;
            --scb-primary-soft: #eaf1f8;
            --scb-accent: #1f7a8c;
            --scb-bg: #f4f7fb;
            --scb-border: #d9e2ec;
            --scb-text: #1f2937;
            --scb-muted: #6b7280;
        }

        html,
        body {
            width: 100%;
            max-width: 100%;
            min-height: 100%;
            overflow-x: hidden;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--scb-bg);
            color: var(--scb-text);
        }

        .scb-wrapper {
            min-height: 100vh;
            display: flex;
            background:
                radial-gradient(circle at top left, rgba(31, 122, 140, .14), transparent 32rem),
                linear-gradient(180deg, #f8fbff 0%, #eef3f8 100%);
        }

        .scb-sidebar {
            width: 270px;
            min-width: 270px;
            min-height: 100vh;
            background: linear-gradient(180deg, var(--scb-primary-dark), var(--scb-primary));
            color: #fff;
            position: sticky;
            top: 0;
            align-self: flex-start;
            box-shadow: 8px 0 26px rgba(15, 23, 42, .16);
            z-index: 20;
        }

        .scb-brand {
            padding: 1.25rem 1.25rem 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, .12);
        }

        .scb-brand-icon {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            background: rgba(255, 255, 255, .12);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .scb-brand-title {
            font-size: 15px;
            font-weight: 800;
            line-height: 1.1;
            margin: 0;
        }

        .scb-brand-subtitle {
            font-size: 11px;
            color: rgba(255, 255, 255, .68);
            margin: 2px 0 0;
        }

        .scb-menu {
            padding: 1rem .85rem;
        }

        .scb-menu-title {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .09em;
            color: rgba(255, 255, 255, .48);
            font-weight: 800;
            padding: .75rem .75rem .35rem;
        }

        .scb-menu-link {
            display: flex;
            align-items: center;
            gap: .75rem;
            color: rgba(255, 255, 255, .82);
            padding: .72rem .8rem;
            border-radius: 12px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 700;
            transition: .18s ease;
        }

        .scb-menu-link:hover,
        .scb-menu-link.active {
            background: rgba(255, 255, 255, .12);
            color: #fff;
            transform: translateX(2px);
        }

        .scb-menu-link i {
            width: 18px;
            text-align: center;
            font-size: 14px;
        }

        .scb-main {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
        }

        .scb-topbar {
            height: 70px;
            background: rgba(255, 255, 255, .78);
            backdrop-filter: blur(14px);
            border-bottom: 1px solid rgba(217, 226, 236, .82);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.35rem;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .scb-page-title {
            font-size: 18px;
            font-weight: 800;
            margin: 0;
            color: var(--scb-primary-dark);
        }

        .scb-page-subtitle {
            font-size: 12px;
            margin: 2px 0 0;
            color: var(--scb-muted);
        }

        .scb-user {
            display: flex;
            align-items: center;
            gap: .75rem;
        }

        .scb-user-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: var(--scb-primary-soft);
            color: var(--scb-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
        }

        .scb-user-name {
            font-size: 13px;
            font-weight: 800;
            margin: 0;
        }

        .scb-user-role {
            font-size: 11px;
            color: var(--scb-muted);
            margin: 0;
        }

        .scb-content {
            padding: 1.25rem;
            width: 100%;
            max-width: 100%;
            min-width: 0;
        }

        .scb-card {
            background: #fff;
            border: 1px solid var(--scb-border);
            border-radius: 18px;
            box-shadow: 0 10px 26px rgba(15, 23, 42, .06);
        }

        .scb-card-header {
            padding: 1rem 1.1rem;
            border-bottom: 1px solid var(--scb-border);
        }

        .scb-card-body {
            padding: 1.1rem;
        }

        .scb-btn-primary {
            background: var(--scb-primary);
            border-color: var(--scb-primary);
            color: #fff;
        }

        .scb-btn-primary:hover {
            background: var(--scb-primary-dark);
            border-color: var(--scb-primary-dark);
            color: #fff;
        }

        .app-scroll-x,
        .scb-scroll-x {
            width: 100%;
            max-width: 100%;
            overflow-x: auto;
            overflow-y: hidden;
        }

        .table,
        .card,
        .scb-card,
        .tab-content,
        .table-responsive {
            max-width: 100%;
        }

        #page-loader,
        #loading-overlay {
            z-index: 999999;
        }

        @media (max-width: 991.98px) {
            .scb-wrapper {
                display: block;
            }

            .scb-sidebar {
                position: relative;
                width: 100%;
                min-width: 100%;
                min-height: auto;
            }

            .scb-menu {
                display: flex;
                gap: .5rem;
                overflow-x: auto;
                padding: .75rem;
            }

            .scb-menu-title {
                display: none;
            }

            .scb-menu-link {
                white-space: nowrap;
                flex: 0 0 auto;
            }

            .scb-topbar {
                position: relative;
            }
        }

        @media (max-width: 575.98px) {
            .scb-content {
                padding: .85rem;
            }

            .scb-topbar {
                height: auto;
                min-height: 70px;
                align-items: flex-start;
                gap: .75rem;
                flex-direction: column;
                padding: 1rem;
            }

            .scb-user {
                width: 100%;
                justify-content: space-between;
            }
        }

        .scb-logout-btn {
            width: 38px;
            height: 38px;
            border: 1px solid #d9e2ec;
            border-radius: 12px;
            background: #ffffff;
            color: #b42318;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: .18s ease;
            box-shadow: 0 6px 16px rgba(15, 23, 42, .06);
        }

        .scb-logout-btn:hover {
            background: #fff5f5;
            border-color: #fecaca;
            color: #991b1b;
            transform: translateY(-1px);
        }

        #scb-logout-overlay {
            position: fixed;
            inset: 0;
            z-index: 9999999;
            display: none;
            align-items: center;
            justify-content: center;
            background: rgba(7, 31, 56, .72);
            backdrop-filter: blur(6px);
        }

        .scb-logout-box {
            width: min(360px, calc(100% - 32px));
            background: #ffffff;
            border-radius: 24px;
            padding: 28px;
            text-align: center;
            box-shadow: 0 24px 70px rgba(15, 23, 42, .28);
            border: 1px solid rgba(217, 226, 236, .9);
        }

        .scb-logout-icon {
            width: 58px;
            height: 58px;
            border-radius: 18px;
            margin: 0 auto 16px;
            background: #eaf1f8;
            color: #12355b;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .scb-logout-spinner {
            width: 42px;
            height: 42px;
            border: 4px solid #e5edf5;
            border-top-color: #12355b;
            border-radius: 50%;
            margin: 0 auto 16px;
            animation: scb-spin .8s linear infinite;
        }

        .scb-logout-title {
            font-size: 18px;
            font-weight: 800;
            color: #12355b;
            margin-bottom: 6px;
        }

        .scb-logout-text {
            font-size: 13px;
            color: #6b7280;
            margin: 0;
        }

        @keyframes scb-spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body>
    <div id="page-loader">
        <span class="preloader-interior"></span>
    </div>

    <div id="loading-overlay">
        <div class="loading-content">
            <div class="sk-circle">
                <div class="sk-circle1 sk-child"></div>
                <div class="sk-circle2 sk-child"></div>
                <div class="sk-circle3 sk-child"></div>
                <div class="sk-circle4 sk-child"></div>
                <div class="sk-circle5 sk-child"></div>
                <div class="sk-circle6 sk-child"></div>
                <div class="sk-circle7 sk-child"></div>
                <div class="sk-circle8 sk-child"></div>
                <div class="sk-circle9 sk-child"></div>
                <div class="sk-circle10 sk-child"></div>
                <div class="sk-circle11 sk-child"></div>
                <div class="sk-circle12 sk-child"></div>
            </div>

            <div class="loading-text" id="loading-text">
                Procesando solicitud…
            </div>
        </div>
    </div>

    <div class="scb-wrapper">

        {{-- Sidebar propio del módulo --}}
        <aside class="scb-sidebar">
            <div class="scb-brand">
                <div class="d-flex align-items-center gap-3">
                    <div class="scb-brand-icon">
                        <i class="fas fa-building-columns"></i>
                    </div>

                    <div>
                        <p class="scb-brand-title">
                            Control Bancario
                        </p>
                        <p class="scb-brand-subtitle">
                            Movimientos y conciliación
                        </p>
                    </div>
                </div>
            </div>

            <nav class="scb-menu">

                @canany(['SCB-Dashboard', 'SCB-Movimientos-Index'])
                    <div class="scb-menu-title">
                        Principal
                    </div>
                @endcanany

                @can('SCB-Dashboard')
                    <a href="{{ route('scb.dashboard') }}"
                        class="scb-menu-link {{ request()->routeIs('scb.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-chart-line"></i>
                        <span>Inicio</span>
                    </a>
                @endcan

                @can('SCB-Movimientos-Index')
                    <a href="{{ route('scb.movimientos.index') }}"
                        class="scb-menu-link {{ request()->routeIs('scb.movimientos.*') ? 'active' : '' }}">
                        <i class="fas fa-money-bill-transfer"></i>
                        <span>Movimientos</span>
                    </a>
                @endcan

                @canany(['SCB-Bancos-Index', 'SCB-Cuentas-Index', 'SCB-Unidades-Index'])
                    <div class="scb-menu-title">
                        Catálogos
                    </div>
                @endcanany

                @can('SCB-Bancos-Index')
                    <a href="{{ route('scb.bancos.index') }}"
                        class="scb-menu-link {{ request()->routeIs('scb.bancos.*') ? 'active' : '' }}">
                        <i class="fas fa-building-columns"></i>
                        <span>Bancos</span>
                    </a>
                @endcan

                @can('SCB-Cuentas-Index')
                    <a href="{{ route('scb.cuentas.index') }}"
                        class="scb-menu-link {{ request()->routeIs('scb.cuentas.*') ? 'active' : '' }}">
                        <i class="fas fa-credit-card"></i>
                        <span>Cuentas</span>
                    </a>
                @endcan

                @can('SCB-Unidades-Index')
                    <a href="{{ route('scb.unidades.index') }}"
                        class="scb-menu-link {{ request()->routeIs('scb.unidades.*') ? 'active' : '' }}">
                        <i class="fas fa-truck"></i>
                        <span>Unidades</span>
                    </a>
                @endcan

                @can('SCB-Reportes-Index')
                    <div class="scb-menu-title">
                        Reportes
                    </div>

                    <a href="{{ route('scb.reportes.index') }}"
                        class="scb-menu-link {{ request()->routeIs('scb.reportes.*') ? 'active' : '' }}">
                        <i class="fas fa-file-invoice-dollar"></i>
                        <span>Reportes</span>
                    </a>
                @endcan

            </nav>
        </aside>

        <main class="scb-main">

            {{-- Topbar limpio --}}
            <header class="scb-topbar">
                <div>
                    <h1 class="scb-page-title">
                        @yield('page_title', 'Control Bancario')
                    </h1>
                    <p class="scb-page-subtitle">
                        @yield('page_subtitle', 'Administración de bancos, cuentas y movimientos')
                    </p>
                </div>

                <div class="scb-user">
                    <div class="text-end d-none d-sm-block">
                        <p class="scb-user-name">
                            {{ auth()->user()->name ?? (auth()->user()->nombre ?? 'Usuario') }}
                        </p>
                        <p class="scb-user-role">
                            Módulo bancario
                        </p>
                    </div>

                    <div class="scb-user-avatar">
                        {{ strtoupper(substr(auth()->user()->name ?? (auth()->user()->nombre ?? 'U'), 0, 1)) }}
                    </div>
                    <button type="button" class="scb-logout-btn" id="btnScbLogout" title="Cerrar sesión">
                        <i class="fas fa-right-from-bracket"></i>
                    </button>
                </div>
            </header>

            <section class="scb-content">
                @include('layouts.simple_alert')

                @yield('breadcrumb')

                @yield('content')
            </section>
        </main>
    </div>

    <div id="scb-logout-overlay">
        <div class="scb-logout-box">
            <div class="scb-logout-spinner"></div>

            <div class="scb-logout-title">
                Cerrando sesión
            </div>

            <p class="scb-logout-text">
                Estamos protegiendo tu acceso al módulo bancario...
            </p>
        </div>
    </div>

    <script src="{{ asset('js/sgt/common.js') }}"></script>

    {{-- Core --}}
    <script src="{{ asset('assets/js/core/popper.min.js') }}"></script>
    <script src="{{ asset('assets/js/core/bootstrap.min.js') }}"></script>

    {{-- jQuery --}}
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    {{-- Plugins comunes --}}
    <script src="{{ asset('assets/js/plugins/datatables.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/js/sweetalert2.all.min.js') }}"></script>
    <script src="{{ asset('js/scb/scb-loading.js') }}?v={{ filemtime(public_path('js/scb/scb-loading.js')) }}"></script>
    <script>
        var token = $('meta[name="csrf-token"]').attr('content');
    </script>

    @yield('js_custom')
    @yield('datatable')
    @yield('alerta')
    @stack('custom-javascript')
    @stack('scripts')

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const btnLogout = document.getElementById("btnScbLogout");
            const logoutOverlay = document.getElementById("scb-logout-overlay");

            if (!btnLogout) return;

            btnLogout.addEventListener("click", async function() {
                const confirmacion = await Swal.fire({
                    icon: "question",
                    title: "¿Cerrar sesión?",
                    text: "Saldrás del módulo de control bancario.",
                    showCancelButton: true,
                    confirmButtonText: "Sí, cerrar sesión",
                    cancelButtonText: "Cancelar",
                    confirmButtonColor: "#12355b",
                    cancelButtonColor: "#6b7280",
                    reverseButtons: true,
                });

                if (!confirmacion.isConfirmed) return;

                if (logoutOverlay) {
                    logoutOverlay.style.display = "flex";
                }

                try {
                    const response = await fetch("{{ route('scb.logout') }}", {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')
                                .content,
                            "Accept": "application/json",
                        },
                    });

                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        if (logoutOverlay) {
                            logoutOverlay.style.display = "none";
                        }

                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: data.message || "No se pudo cerrar la sesión.",
                        });

                        return;
                    }

                    window.location.href = data.redirect || "{{ route('scb.login') }}";
                } catch (error) {
                    console.error(error);

                    if (logoutOverlay) {
                        logoutOverlay.style.display = "none";
                    }

                    Swal.fire({
                        icon: "error",
                        title: "Error inesperado",
                        text: "No se pudo cerrar la sesión.",
                    });
                }
            });
        });
    </script>
</body>

</html>
