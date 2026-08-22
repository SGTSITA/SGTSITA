<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    @yield('meta-tags')
    <link rel="stylesheet" href="{{ asset('assets/css/sweetalert2.css?v=2') }}" />
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('favicon/' . $configuracion->favicon) }}" />
    <link rel="icon" type="image/png" href="{{ asset('favicon/' . $configuracion->favicon) }}" />
    <title>@yield('template_title') - {{ $configuracion->nombre_sistema }}</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap"
        rel="stylesheet" />

    <!-- Nucleo Icons -->
    <link href="{{ asset('assets/css/icons.css') }}" rel="stylesheet" />
    <!--link href="{{ asset('assets/css/styles.css') }}" rel="stylesheet" /-->
    <link href="{{ asset('assets/css/nucleo-icons.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/nucleo-svg.css') }}" rel="stylesheet" />
    <!-- Font Awesome Icons -->
    <link href="{{ asset('assets/css/nucleo-svg.css') }}" rel="stylesheet" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="{{ asset('assets/css/nucleo-svg.css') }}" rel="stylesheet" />
    @yield('css')
    <!-- Select2  -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/select2/dist/css/select2.min.css') }}" />

    <!-- CSS Files -->
    <link id="pagestyle" href="{{ asset('assets/css/argon-dashboard.css?v=2.0.4') }}" rel="stylesheet" />

    <link rel="stylesheet" href="{{ asset('css/sgt/loading.css') }}" />

    <style>
        input:before {
            content: attr(data-date);
            display: inline-block;
            color: black;
        }

        .dataTable-wrapper .dataTable-container .table tbody tr td {
            padding: 0px 0.3rem !important;
            font-size: 13px !important;
        }

        .dataTable-wrapper .dataTable-top {
            padding: 0rem 1.5rem 0rem 1.5rem;
        }

        .card .card-header {
            padding: 1.5rem 1.5rem 0 1.5rem;
        }

        body {
            font-family: 'Inter', sans-serif;
        }

        .ag-theme-quartz .row-even {
            background-color: #f9fafb;
        }

        .ag-theme-quartz .row-odd {
            background-color: #ffffff;
        }

        /* ============================
       FIX GLOBAL LAYOUT
       ============================ */

        html,
        body {
            width: 100%;
            max-width: 100%;
            overflow-x: hidden;
        }

        body>.min-height-300.position-absolute.w-100 {
            max-width: 100%;
            overflow-x: hidden;
        }

        .app-main-content {
            min-width: 0;
            max-width: 100%;
        }

        .app-content-container {
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
        }

        .app-content-container>* {
            max-width: 100%;
        }

        .app-content-container input,
        .app-content-container select,
        .app-content-container textarea,
        .app-content-container .form-control,
        .app-content-container .form-select {
            max-width: 100%;
        }

        .app-content-container .card,
        .app-content-container .tab-content,
        .app-content-container .table-responsive,
        .app-content-container .ag-theme-alpine,
        .app-content-container .ag-theme-quartz,
        .app-content-container .dataTable-wrapper {
            max-width: 100%;
        }

        .app-scroll-x {
            width: 100%;
            max-width: 100%;
            overflow-x: auto;
            overflow-y: hidden;
        }

        @media (max-width: 767.98px) {
            .app-content-container {
                padding-left: .75rem !important;
                padding-right: .75rem !important;
            }
        }

        .main-content,
        .app-main-content,
        .main-content.ps,
        .app-main-content.ps {
            overflow-x: hidden !important;
        }

        /* Oculta la barra horizontal generada por Perfect Scrollbar */
        .main-content .ps__rail-x,
        .main-content .ps__thumb-x,
        .app-main-content .ps__rail-x,
        .app-main-content .ps__thumb-x {
            display: none !important;
            height: 0 !important;
            opacity: 0 !important;
            pointer-events: none !important;
        }
    </style>
</head>

<body class="g-sidenav-show bg-gray-100">
    <div class="min-height-300 position-absolute w-100"
        style="background-color: {{ $configuracion->color_principal }} !important"></div>
    <div id="page-loader"><span class="preloader-interior"></span></div>
    <!-- Loading Overlay -->
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
            <div class="loading-text" id="loading-text">Procesando solicitud…</div>
        </div>
    </div>

    <!-- Sidenav -->
    @include('layouts.sidebar')

    <main class="main-content position-relative border-radius-lg app-main-content">
        @include('layouts.navbar')

        <div class="container-fluid px-3 px-md-4 app-content-container">
            {{-- @include('layouts.header') --}}
            @include('layouts.simple_alert')
            @yield('breadcrumb')
            @yield('content')

            @include('client.modal_create')
            @include('operadores.modal_create')
            {{-- @include('proveedores.modal_create') --}}
            @include('equipos.modal_create')
            @include('bancos.modal_create')
        </div>
    </main>

    @auth
        <script>
            window.SGT_SESSION_TIMEOUT_MS = {{ config('session.lifetime') * 60 * 1000 }};
            window.SGT_LOGOUT_URL = "{{ route('logout') }}";
            window.SGT_LOGIN_URL = "{{ url('login') }}";
        </script>

        <script src="{{ asset('js/sgt/common/sessionAutoLogout.js') }}"></script>
    @endauth
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('js/sgt/common.js') }}"></script>
    @yield('js_custom')

    <!-- Modal lateral Congif -->
    {{-- @include('layouts.modal_config') --}}
    <!-- End Modal lateral Congif -->

    <!--   Core JS Files   -->
    {{-- <script src="{{ asset('assets/vendor/jquery/dist/jquery.min.js')}}"></script> --}}

    <script src="{{ asset('assets/js/core/popper.min.js') }}"></script>
    <!--script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js"></script-->
    <script src="{{ asset('assets/js/core/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/smooth-scrollbar.min.js') }}"></script>
    {{-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script> --}}

    <script src="{{ asset('assets/js/plugins/datatables.js') }}"></script>

    <script src="{{ asset('assets/js/argon-dashboard.min.js') }}"></script>

    <script type="text/javascript" src="{{ asset('assets/js/sweetalert2.all.min.js') }}"></script>

    @auth
        <script src="{{ asset('js/sgt/notificaciones/notificaciones_navbar.js') }}"></script>
    @endauth

    <script>
        var token = $('meta[name="csrf-token"]').attr('content');
    </script>

    @yield('datatable')

    @yield('fullcalendar')
    @yield('alerta')

    @yield('select2')

    @stack('custom-javascript')
    @stack('scripts')
</body>

</html>
