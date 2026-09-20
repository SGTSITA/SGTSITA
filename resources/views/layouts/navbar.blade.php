<nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky" id="navbarBlur"
    data-scroll="false">
    <div class="container-fluid py-1 px-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
                <li class="breadcrumb-item text-sm">
                    <a class="text-white" href="javascript:;">
                        <i class="ni ni-box-2"></i>
                    </a>
                </li>
                <li class="breadcrumb-item text-sm text-white">
                    <a class="opacity-5 text-white" href="{{ route('dashboard') }}">Inicio</a>
                </li>
                <li class="breadcrumb-item text-sm text-white active" aria-current="page">
                    @yield('page_actuality')
                </li>
            </ol>
        </nav>

        <div class="sidenav-toggler sidenav-toggler-inner px-3" style="">
            <a href="javascript:;" class="nav-link p-0">
                <div class="sidenav-toggler-inner">
                    <i class="sidenav-toggler-line bg-white"></i>
                    <i class="sidenav-toggler-line bg-white"></i>
                    <i class="sidenav-toggler-line bg-white"></i>
                </div>
            </a>
        </div>

        <div class="ms-md-auto pe-md-3 d-flex align-items-center">

            {{-- Campanita de notificaciones --}}
            <div class="dropdown me-3" id="notificacionesDropdownContainer">
                <button class="btn position-relative" type="button" id="dropdownNotificacionesButton"
                    data-bs-toggle="dropdown" aria-expanded="false" style="border: 2px solid #fff; color: #fff">
                    <i class="fas fa-bell"></i>

                    <span id="badgeNotificaciones"
                        class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none"
                        style="font-size: 10px;">
                        0
                    </span>
                </button>

                <div class="dropdown-menu dropdown-menu-end p-0 shadow" aria-labelledby="dropdownNotificacionesButton"
                    style="width: 380px; max-width: 90vw;">
                    <div class="px-3 py-2 border-bottom d-flex justify-content-between align-items-center">
                        <strong class="text-dark">
                            <i class="fas fa-bell me-1"></i>
                            Notificaciones
                        </strong>

                        <button type="button" class="btn btn-link btn-sm p-0" id="btnMarcarTodasNotificaciones">
                            Marcar todas
                        </button>
                    </div>

                    <div id="listaNotificacionesNavbar" style="max-height: 420px; overflow-y: auto;">
                        <div class="p-3 text-center text-muted">
                            Cargando notificaciones...
                        </div>
                    </div>

                    <div class="border-top text-center p-2">
                        <a href="{{ route('notificaciones.mis-notificaciones') }}" class="small">
                            Ver todas
                        </a>
                    </div>
                </div>
            </div>

            {{-- Dropdown usuario --}}
            <div class="dropdown">
                <button class="btn dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown"
                    aria-expanded="false" style="border: 2px solid #fff; color: #fff">
                    {{ Auth::user()->name }}
                </button>

                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <li>
                        <a class="dropdown-item" href="{{ route('signout') }}">
                            <div class="icon icon-shape icon-sm text-center me-2 d-flex align-items-center justify-content-center"
                                style="display: inline-block !important">
                                <i class="fa fa-arrow-right text-dark"
                                    style="color: {{ $configuracion->color_iconos_sidebar }}"></i>
                            </div>
                            <span class="ms-1 text-dark">Cerrar Sesión</span>
                        </a>

                        <form id="logout-form" action="" method="POST" style="display: none">
                            @csrf
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>
