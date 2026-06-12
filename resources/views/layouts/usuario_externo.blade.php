<!DOCTYPE html>
<html lang="es">

<head>
    <base href="/" />
    <title> Sistema de Gestión de Transporte</title>
    <meta charset="utf-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="id-cliente" content="{{ Auth::User()->id_cliente }}" />
    <link rel="shortcut icon" href="/assets/metronic/media/logos/favicon.ico" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
    <link href="/assets/metronic/plugins/custom/datatables/datatables.bundle.css" rel="stylesheet" type="text/css" />
    <link href="/assets/metronic/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="/assets/metronic/css/style.bundle.css" rel="stylesheet" type="text/css" />
    @stack('handsontable')

    <style>
        :root {
            --sgt-aside-width: 250px;
            --sgt-page-bg: #f5f6fa;
            --sgt-aside-bg: #f1f2f7;
        }

        html,
        body {
            min-height: 100%;
            overflow-x: hidden;
            background: var(--sgt-page-bg);
        }

        body.header-fixed {
            background: var(--sgt-page-bg);
        }

        .sgt-root {
            min-height: 100vh;
            background: var(--sgt-page-bg);
        }

        .sgt-page {
            min-height: 100vh;
            width: 100%;
            display: flex;
            align-items: stretch;
            overflow-x: hidden;
        }

        .sgt-aside {
            background: var(--sgt-aside-bg) !important;
            border-top-right-radius: 24px;
            border-bottom-right-radius: 24px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
            overflow: hidden;
        }

        .sgt-main {
            min-width: 0;
            flex: 1 1 auto;
            display: flex;
            flex-direction: column;
            background: var(--sgt-page-bg);
        }

        .sgt-header {
            min-width: 0;
            background: #ffffff;
            border-bottom: 1px solid #edf0f5;
        }

        .sgt-header-inner {
            min-width: 0;
            min-height: 78px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 16px 24px;
        }

        .sgt-mobile-toggle {
            flex: 0 0 auto;
        }

        .sgt-header-title {
            min-width: 0;
            flex: 1 1 auto;
        }

        .sgt-header-title h1 {
            margin: 0;
            color: #071b46;
            font-weight: 800;
            line-height: 1.15;
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        .sgt-header-actions {
            flex: 0 0 auto;
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .sgt-content {
            min-width: 0;
            flex: 1 1 auto;
            padding: 24px;
            overflow-x: hidden;
        }

        .sgt-content-container {
            width: 100%;
            max-width: 100%;
            min-width: 0;
        }

        .sgt-footer {
            padding: 14px 24px;
            background: transparent;
        }

        .sgt-aside-logo {
            height: 76px;
            display: flex;
            align-items: center;
            padding: 0 22px;
            flex: 0 0 auto;
        }

        .sgt-aside-menu {
            min-height: 0;
            flex: 1 1 auto;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 12px 12px 20px;
        }

        .sgt-aside-menu .menu {
            width: 100%;
        }

        .sgt-aside-menu .menu-link {
            min-width: 0;
        }

        .sgt-aside-menu .menu-title {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .sgt-aside-footer {
            flex: 0 0 auto;
            padding: 14px 12px 20px;
        }

        .sgt-aside-footer-box {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            min-width: 0;
        }

        .sgt-aside-user {
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 0;
            flex: 1 1 auto;
        }

        .sgt-aside-user-text {
            min-width: 0;
            flex: 1 1 auto;
        }

        .sgt-aside-user-name,
        .sgt-aside-user-email {
            display: block;
            max-width: 125px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .sgt-aside-user-name {
            color: #0f172a;
            font-size: 13px;
            font-weight: 800;
            line-height: 1.1;
        }

        .sgt-aside-user-email {
            color: #64748b;
            font-size: 11px;
            font-weight: 600;
            line-height: 1.1;
            margin-top: 2px;
        }

        .sgt-user-dropdown-name,
        .sgt-user-dropdown-email {
            max-width: 210px;
            overflow-wrap: anywhere;
            word-break: break-word;
            line-height: 1.25;
        }

        .sgt-search-wrapper {
            width: 250px;
            max-width: 250px;
        }

        @media (min-width: 992px) {
            .sgt-aside {
                position: sticky !important;
                top: 0;
                height: 100vh;
                width: var(--sgt-aside-width) !important;
                min-width: var(--sgt-aside-width) !important;
                max-width: var(--sgt-aside-width) !important;
                flex: 0 0 var(--sgt-aside-width) !important;
                transform: none !important;
                z-index: 5;
                display: flex !important;
                flex-direction: column;
            }

            .sgt-main {
                width: calc(100% - var(--sgt-aside-width));
                max-width: calc(100% - var(--sgt-aside-width));
            }
        }

        @media (max-width: 991.98px) {
            .sgt-page {
                display: block;
            }

            .sgt-main {
                width: 100%;
                max-width: 100%;
            }

            .sgt-header-inner {
                flex-wrap: wrap;
                padding: 12px;
                min-height: auto;
            }

            .sgt-mobile-toggle {
                display: flex !important;
            }

            .sgt-header-title {
                order: 2;
                flex-basis: 100%;
            }

            .sgt-header-title h1 {
                font-size: 1.35rem;
            }

            .sgt-header-actions {
                order: 1;
                flex: 1 1 auto;
                justify-content: flex-end;
                gap: 8px;
            }

            .sgt-search-wrapper {
                width: auto;
                max-width: 100%;
            }

            .sgt-content {
                padding: 12px;
            }

            .sgt-footer {
                padding: 12px;
            }
        }
    </style>
</head>

<body id="kt_body" class="header-fixed">
    <script>
        var defaultThemeMode = 'light';
        var themeMode;
        if (document.documentElement) {
            if (document.documentElement.hasAttribute('data-bs-theme-mode')) {
                themeMode = document.documentElement.getAttribute('data-bs-theme-mode');
            } else {
                if (localStorage.getItem('data-bs-theme') !== null) {
                    themeMode = localStorage.getItem('data-bs-theme');
                } else {
                    themeMode = defaultThemeMode;
                }
            }
            if (themeMode === 'system') {
                themeMode = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            }
            document.documentElement.setAttribute('data-bs-theme', themeMode);
        }
    </script>
    <div class="sgt-root">
        <div class="sgt-page">
            <aside id="kt_aside" class="aside sgt-aside" data-kt-drawer="true" data-kt-drawer-name="aside"
                data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true"
                data-kt-drawer-width="{default:'250px'}" data-kt-drawer-direction="start"
                data-kt-drawer-toggle="#kt_aside_toggle">
                <div class="sgt-aside-logo" id="kt_aside_logo">
                    <a href="{{ route('dashboard') }}">
                        <img alt="Logo" src="/assets/metronic/logo-color-sgt.png"
                            class="h-25px logo theme-light-show" />
                        <img alt="Logo" src="/assets/metronic/logo-gris-sgt.png"
                            class="h-25px logo theme-dark-show" />
                    </a>
                </div>

                <div class="sgt-aside-menu" id="kt_aside_menu">
                    <div class="menu menu-column menu-rounded menu-sub-indention menu-active-bg fw-semibold"
                        data-kt-menu="true">

                        <div class="menu-item">
                            <a class="menu-link" href="{{ route('dashboard') }}">
                                <span class="menu-icon">
                                    <i class="ki-duotone ki-black-right fs-2"></i>
                                </span>
                                <span class="menu-title">Inicio</span>
                            </a>
                        </div>
                        <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                            <span class="menu-link">
                                <span class="menu-icon">
                                    <i class="ki-duotone ki-black-right fs-2"></i>
                                </span>
                                <span class="menu-title">Sub Clientes</span>
                                <span class="menu-arrow"></span>
                            </span>
                            <div class="menu-sub menu-sub-accordion">
                                <div class="menu-item">
                                    <a class="menu-link" href="{{ route('subcliente.index') }}">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">Crear Sub Cliente</span>
                                    </a>
                                </div>
                                <div class="menu-item">
                                    <a href="{{ route('client.subcliente.list') }}" class="menu-link">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">Editar Sub Cliente</span>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                            <span class="menu-link">
                                <span class="menu-icon">
                                    <i class="ki-duotone ki-black-right fs-2"></i>
                                </span>
                                <span class="menu-title">Viajes</span>
                                <span class="menu-arrow"></span>
                            </span>
                            <div class="menu-sub menu-sub-accordion">
                                <div class="menu-item">
                                    <a href="{{ route('viajes.solicitar') }}" class="menu-link">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">Solicitar Viaje</span>
                                    </a>
                                </div>

                                <div class="menu-item">
                                    <a href="{{ route('mis.viajes') }}" class="menu-link">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">Mis Viajes</span>
                                    </a>
                                </div>
                                @can('Burrero MEC')
                                    <div class="menu-item">
                                        <a href="{{ route('viajes.local') }}" class="menu-link">
                                            <span class="menu-bullet">
                                                <span class="bullet bullet-dot"></span>
                                            </span>
                                            <span class="menu-title">Solicitar Local</span>
                                        </a>
                                    </div>

                                    <div class="menu-item">
                                        <a href="{{ route('mis.viajeslocal') }}" class="menu-link">
                                            <span class="menu-bullet">
                                                <span class="bullet bullet-dot"></span>
                                            </span>
                                            <span class="menu-title">Viajes Locales</span>
                                        </a>
                                    </div>

                                    {{-- <div class="menu-item">
                                                <a href="{{ route('mis.patiolocal') }}" class="menu-link">
                                                    <span class="menu-bullet">
                                                        <span class="bullet bullet-dot"></span>
                                                    </span>
                                                    <span class="menu-title">En patio</span>
                                                </a>
                                            </div> --}}
                                @endcan
                            </div>
                        </div>
                        <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                            <span class="menu-link">
                                <span class="menu-icon">
                                    <i class="ki-duotone ki-black-right fs-2"></i>
                                </span>
                                <span class="menu-title">Correo</span>
                                <span class="menu-arrow"></span>
                            </span>
                            <div class="menu-sub menu-sub-accordion">
                                <div class="menu-item">
                                    <a href="{{ route('configmec') }}" class="menu-link">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">Correos</span>
                                    </a>
                                </div>
                            </div>
                        </div>

                        @can('Coordenadas MEC')
                            <!--coordenadas -->
                            <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                                <span class="menu-link">
                                    <span class="menu-icon">
                                        <i class="ki-duotone ki-black-right fs-2"></i>
                                    </span>
                                    <span class="menu-title">Coordenadas</span>
                                    <span class="menu-arrow"></span>
                                </span>
                                <div class="menu-sub menu-sub-accordion">
                                    @can('Coordenadas MEC-P-verificacion')
                                        <div class="menu-item">
                                            <a href="{{ route('ver.extcoordenadamapa') }}" class="menu-link">
                                                <span class="menu-bullet">
                                                    <span class="bullet bullet-dot"></span>
                                                </span>
                                                <span class="menu-title">Punto de verificación</span>
                                            </a>
                                        </div>
                                    @endcan

                                    @can('Coordenadas MEC-Bitacora-verificacion')
                                        <div class="menu-item">
                                            <a href="{{ route('seach.extcoordenadas') }}" class="menu-link">
                                                <span class="menu-bullet">
                                                    <span class="bullet bullet-dot"></span>
                                                </span>
                                                <span class="menu-title">Bitácora de Verificación</span>
                                            </a>
                                        </div>
                                    @endcan

                                    @can('Coordenadas MEC-B-busqueda Cuestionarios')
                                        <div class="menu-item">
                                            <a href="{{ route('extcompartircoor') }}" class="menu-link">
                                                <span class="menu-bullet">
                                                    <span class="bullet bullet-dot"></span>
                                                </span>
                                                <span class="menu-title">Compartir Cuestionarios</span>
                                            </a>
                                        </div>
                                    @endcan

                                    @can('Coordenadas MEC-Rastrear')
                                        <div class="menu-item">
                                            <a href="{{ route('exrastrearContenedor') }}" class="menu-link">
                                                <span class="menu-bullet">
                                                    <span class="bullet bullet-dot"></span>
                                                </span>
                                                <span class="menu-title">Rastrear</span>
                                            </a>
                                        </div>
                                    @endcan

                                    {{--
                                                <div class="menu-item">
                                                <a href="{{ route('exindex.conboys') }}" class="menu-link">
                                                <span class="menu-bullet">
                                                <span class="bullet bullet-dot"></span>
                                                </span>
                                                <span class="menu-title">Convoys Virtuales</span>
                                                </a>
                                                </div>
                                            --}}
                                </div>
                            </div>
                            <!--END coordenadas -->
                        @endcan

                        <!-- contactos -->
                        <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                            <span class="menu-link">
                                <span class="menu-icon">
                                    <i class="ki-duotone ki-black-right fs-2"></i>
                                </span>
                                <span class="menu-title">Contactos</span>
                                <span class="menu-arrow"></span>
                            </span>
                            <div class="menu-sub menu-sub-accordion">
                                <div class="menu-item">
                                    <a href="{{ route('contactos.index') }}" class="menu-link">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">Ver Contactos</span>
                                    </a>
                                </div>
                                <div class="menu-item">
                                    <a href="{{ route('contactos.create') }}" class="menu-link">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">Agregar Contacto</span>
                                    </a>
                                </div>
                            </div>
                        </div>


                        <!--Reportes de documentos -->
                        @can('Reportes MEC')
                            <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                                <span class="menu-link">
                                    <span class="menu-icon">
                                        <i class="ki-duotone ki-black-right fs-2"></i>
                                    </span>
                                    <span class="menu-title">Reportes</span>
                                    <span class="menu-arrow"></span>
                                </span>
                                <div class="menu-sub menu-sub-accordion">
                                    <div class="menu-item">
                                        <a href="{{ route('ext_index_documentos.reporteria') }}" class="menu-link">
                                            <span class="menu-bullet">
                                                <span class="bullet bullet-dot"></span>
                                            </span>
                                            <span class="menu-title">Documentos</span>
                                        </a>
                                    </div>
                                    {{-- <div class="menu-item">
                                            <a href="{{ route('contactos.create') }}" class="menu-link">
                                                <span class="menu-bullet">
                                                    <span class="bullet bullet-dot"></span>
                                                </span>
                                                <span class="menu-title">Agregar Contacto</span>
                                            </a>
                                        </div> --}}
                                </div>
                            </div>
                        @endcan
                    </div>
                </div>

                <div class="sgt-aside-footer" id="kt_aside_footer">
                    <div class="sgt-aside-footer-box">
                        <div class="sgt-aside-user">
                            <div class="dropdown" id="notificacionesDropdownContainer">
                                <button class="btn btn-icon btn-light position-relative" type="button"
                                    id="dropdownNotificacionesButton" data-bs-toggle="dropdown" aria-expanded="false"
                                    title="Notificaciones">
                                    <i class="fas fa-bell"></i>

                                    <span id="badgeNotificaciones"
                                        class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none"
                                        style="font-size: 10px;">
                                        0
                                    </span>
                                </button>

                                <div class="dropdown-menu dropdown-menu-end p-0 shadow"
                                    aria-labelledby="dropdownNotificacionesButton"
                                    style="width: 380px; max-width: 90vw;">
                                    <div
                                        class="px-3 py-2 border-bottom d-flex justify-content-between align-items-center">
                                        <strong class="text-dark">
                                            <i class="fas fa-bell me-1"></i>
                                            Notificaciones
                                        </strong>

                                        <button type="button" class="btn btn-link btn-sm p-0"
                                            id="btnMarcarTodasNotificaciones">
                                            Marcar todas
                                        </button>
                                    </div>

                                    <div id="listaNotificacionesNavbar" style="max-height: 420px; overflow-y: auto;">
                                        <div class="p-3 text-center text-muted">
                                            Cargando notificaciones...
                                        </div>
                                    </div>

                                    <div class="border-top text-center p-2">
                                        <a href="{{ route('notificaciones.mis-notificaciones-clientes') }}"
                                            class="small">
                                            Ver todas
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="symbol symbol-circle symbol-40px">
                                <img src="/img/icon-user.jpg" alt="photo" />
                            </div>

                            <div class="sgt-aside-user-text">
                                <span class="sgt-aside-user-name" title="{{ Auth::user()->name }}">
                                    {{ Auth::user()->name }}
                                </span>

                                <span class="sgt-aside-user-email" title="{{ Auth::user()->email }}">
                                    {{ Auth::user()->email }}
                                </span>
                            </div>
                        </div>

                        <div class="flex-shrink-0">
                            <div class="btn btn-sm btn-icon btn-active-color-primary"
                                data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-overflow="true"
                                data-kt-menu-placement="top-end">
                                <i class="ki-duotone ki-setting-2 fs-1">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </div>

                            <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg menu-state-color fw-semibold py-4 fs-6 w-275px"
                                data-kt-menu="true">
                                <div class="menu-item px-3">
                                    <div class="menu-content d-flex align-items-center px-3">
                                        <div class="d-flex flex-column">
                                            <div class="fw-bold d-flex align-items-start fs-5 gap-2 flex-wrap">
                                                <span class="sgt-user-dropdown-name">
                                                    {{ Auth::user()->name }}
                                                </span>
                                                <span class="badge badge-light-success fw-bold fs-8 px-2 py-1">
                                                    Perfil Cliente
                                                </span>
                                            </div>
                                            <a href="#"
                                                class="fw-semibold text-muted text-hover-primary fs-7 sgt-user-dropdown-email">
                                                {{ Auth::user()->email }}
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div class="separator my-2"></div>

                                <div class="menu-item px-5">
                                    <a href="/signout" class="menu-link px-5">Cerrar sesión</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>

            <main class="sgt-main" id="kt_wrapper">
                <header id="kt_header" class="sgt-header">
                    <div class="sgt-header-inner" id="kt_header_container">
                        <div class="sgt-mobile-toggle d-flex d-lg-none align-items-center">
                            <button type="button" class="btn btn-icon btn-active-icon-primary" id="kt_aside_toggle">
                                <i class="ki-duotone ki-abstract-14 fs-1 mt-1">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </button>
                        </div>

                        <div class="sgt-header-title">
                            <h1>
                                Hola, {{ Auth::user()->name }}
                            </h1>
                            @yield('BreadCrumb')
                        </div>

                        <div class="sgt-header-actions">
                            <div id="kt_header_search"
                                class="header-search d-flex align-items-center sgt-search-wrapper"
                                data-kt-search-keypress="true" data-kt-search-min-length="2"
                                data-kt-search-enter="enter" data-kt-search-layout="menu"
                                data-kt-search-responsive="lg" data-kt-menu-trigger="auto"
                                data-kt-menu-permanent="true" data-kt-menu-placement="bottom-end">
                                <div data-kt-search-element="toggle"
                                    class="search-toggle-mobile d-flex d-lg-none align-items-center">
                                    <div
                                        class="d-flex btn btn-icon btn-color-gray-700 btn-active-color-primary btn-outline w-40px h-40px">
                                        <i class="ki-duotone ki-magnifier fs-1">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                    </div>
                                </div>

                                <form data-kt-search-element="form"
                                    class="d-none d-lg-block w-100 position-relative mb-2 mb-lg-0" autocomplete="off">
                                    <input type="hidden" />
                                    <i
                                        class="ki-duotone ki-magnifier fs-2 text-gray-700 position-absolute top-50 translate-middle-y ms-4">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                    <input type="text" class="form-control bg-transparent ps-13 fs-7 h-40px"
                                        name="search" value="" placeholder="Buscar contenedor"
                                        data-kt-search-element="input" />
                                    <span class="position-absolute top-50 end-0 translate-middle-y lh-0 d-none me-5"
                                        data-kt-search-element="spinner">
                                        <span class="spinner-border h-15px w-15px align-middle text-gray-500"></span>
                                    </span>
                                    <span
                                        class="btn btn-flush btn-active-color-primary position-absolute top-50 end-0 translate-middle-y lh-0 d-none me-4"
                                        data-kt-search-element="clear">
                                        <i class="ki-duotone ki-cross fs-2 fs-lg-1 me-0">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                    </span>
                                </form>
                            </div>

                            <div class="d-flex align-items-center">
                                <a href="#"
                                    class="btn btn-icon btn-color-gray-700 btn-active-color-primary btn-outline w-40px h-40px"
                                    data-kt-menu-trigger="{default:'click', lg: 'hover'}" data-kt-menu-attach="parent"
                                    data-kt-menu-placement="bottom-end">
                                    <i class="ki-duotone ki-night-day theme-light-show fs-1">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                        <span class="path4"></span>
                                        <span class="path5"></span>
                                        <span class="path6"></span>
                                        <span class="path7"></span>
                                        <span class="path8"></span>
                                        <span class="path9"></span>
                                        <span class="path10"></span>
                                    </i>
                                    <i class="ki-duotone ki-moon theme-dark-show fs-1">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                </a>
                                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-title-gray-700 menu-icon-gray-500 menu-active-bg menu-state-color fw-semibold py-4 fs-base w-150px"
                                    data-kt-menu="true" data-kt-element="theme-mode-menu">
                                    <div class="menu-item px-3 my-0">
                                        <a href="#" class="menu-link px-3 py-2" data-kt-element="mode"
                                            data-kt-value="light">
                                            <span class="menu-title">Light</span>
                                        </a>
                                    </div>
                                    <div class="menu-item px-3 my-0">
                                        <a href="#" class="menu-link px-3 py-2" data-kt-element="mode"
                                            data-kt-value="dark">
                                            <span class="menu-title">Dark</span>
                                        </a>
                                    </div>
                                    <div class="menu-item px-3 my-0">
                                        <a href="#" class="menu-link px-3 py-2" data-kt-element="mode"
                                            data-kt-value="system">
                                            <span class="menu-title">System</span>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex align-items-center">
                                <a href="#" id="waIconStatus"
                                    class="btn btn-color-gray-700 btn-active-color-primary btn-outline h-40px">
                                    <i class="ki-duotone ki-whatsapp fs-1">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                    <span class="fs-8 text-dark" id="waTextStatus">...</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </header>

                <section class="sgt-content" id="kt_content">
                    <div class="sgt-content-container" id="kt_content_container">
                        @yield('WorkSpace')
                    </div>
                </section>

                <footer class="sgt-footer" id="kt_footer">
                    <div class="d-flex flex-column flex-md-row flex-stack gap-2">
                        <div class="text-gray-900 order-2 order-md-1">
                            <span class="text-gray-500 fw-semibold me-1">
                                SGT - Sistema de Gestión de Transporte
                            </span>
                        </div>
                        <ul class="menu menu-gray-600 menu-hover-primary fw-semibold order-1">
                            <li class="menu-item">
                                <a href="#" target="_blank" class="menu-link px-2">+52 561 068 5796</a>
                            </li>
                        </ul>
                    </div>
                </footer>
            </main>
        </div>
    </div>

    @include('cotizaciones.externos.modal_whatsapp_login')

    <div id="kt_scrolltop" class="scrolltop" data-kt-scrolltop="true">
        <i class="ki-duotone ki-arrow-up">
            <span class="path1"></span>
            <span class="path2"></span>
        </i>
    </div>

    <script>
        var hostUrl = '/assets/metronic/';
        var waClient = {{ auth()->user()->id }};
        var waHost = '{{ env('WHATSAPP_HOST') }}';
    </script>
    <script src="/assets/metronic/plugins/global/plugins.bundle.js"></script>
    <script src="/assets/metronic/js/scripts.bundle.js"></script>
    <script src="/assets/metronic/plugins/custom/datatables/datatables.bundle.js"></script>
    <script src="/assets/metronic/js/widgets.bundle.js"></script>
    <script src="/assets/metronic/js/custom/widgets.js"></script>
    <script src="/js/sgt/common/whatsAppClient.js"></script>
    @auth
        <script src="{{ asset('js/sgt/notificaciones/notificaciones_navbar.js') }}"></script>
    @endauth
    <script>
        $(document).ready(async () => {
            var genericUUID = localStorage.getItem('uuid');
            if (genericUUID == null) {
                genericUUID = generateUUID();
                localStorage.setItem('uuid', genericUUID);
            }

            // await getWaQr();

            let waElements = document.querySelectorAll('.waElements');

            // if (waStatus != 'ready') {
            //     waElements.forEach((el) => {
            //         el.classList.add('d-none');
            //     });

            //     setTimeout(() => {
            //         const modalElement = document.getElementById('kt_modal_whatsapp_login');
            //         const whastAppModal = new bootstrap.Modal(modalElement);
            //         whastAppModal.show();
            //     }, 500);
            // } else {
            //     waReadyComponents();
            // }
        });
    </script>

    @stack('javascript')
</body>

</html>
