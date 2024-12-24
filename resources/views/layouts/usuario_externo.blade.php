<!DOCTYPE html>
<html lang="es">
  <head>
    <base href="/" />
    <title>Sistema de Gestión de Transporte</title>
    <meta charset="utf-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="id-cliente" content="{{ Auth::User()->id_cliente }}">
    <link rel="shortcut icon" href="/assets/metronic/media/logos/favicon.ico" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
    <link href="/assets/metronic/plugins/custom/datatables/datatables.bundle.css" rel="stylesheet" type="text/css" />
    <link href="/assets/metronic/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="/assets/metronic/css/style.bundle.css" rel="stylesheet" type="text/css" /> 
    @stack('handsontable')
  </head>
  <body id="kt_body" class="header-fixed">
    <script>
      var defaultThemeMode = "light";
      var themeMode;
      if (document.documentElement) {
        if (document.documentElement.hasAttribute("data-bs-theme-mode")) {
          themeMode = document.documentElement.getAttribute("data-bs-theme-mode");
        } else {
          if (localStorage.getItem("data-bs-theme") !== null) {
            themeMode = localStorage.getItem("data-bs-theme");
          } else {
            themeMode = defaultThemeMode;
          }
        }
        if (themeMode === "system") {
          themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
        }
        document.documentElement.setAttribute("data-bs-theme", themeMode);
      }
    </script>
    <div class="d-flex flex-column flex-root">
      <div class="page d-flex flex-row flex-column-fluid">
        <div id="kt_aside" class="aside py-9" data-kt-drawer="true" data-kt-drawer-name="aside" data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="{default:'200px', '300px': '250px'}" data-kt-drawer-direction="start" data-kt-drawer-toggle="#kt_aside_toggle">
          <div class="aside-logo flex-column-auto px-9 mb-9" id="kt_aside_logo">
            <a href="/home">
              <img alt="Logo" src="/assets/metronic/logo-color-sgt.png" class="h-25px logo theme-light-show" />
              <img alt="Logo" src="/assets/metronic/logo-gris-sgt.png" class="h-25px logo theme-dark-show" />
            </a>
          </div>
          <div class="aside-menu flex-column-fluid ps-5 pe-3 mb-9" id="kt_aside_menu">
            <div class="w-100 hover-scroll-overlay-y d-flex pe-3" id="kt_aside_menu_wrapper" data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-height="auto" data-kt-scroll-dependencies="#kt_aside_logo, #kt_aside_footer" data-kt-scroll-wrappers="#kt_aside, #kt_aside_menu, #kt_aside_menu_wrapper" data-kt-scroll-offset="100">
              <div class="menu menu-column menu-rounded menu-sub-indention menu-active-bg fw-semibold my-auto" id="#kt_aside_menu" data-kt-menu="true">
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
                      <a href="{{route('subcliente.list')}}" class="menu-link">
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
                      <a href="{{route('dashboard')}}" class="menu-link">
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
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="aside-footer flex-column-auto px-9" id="kt_aside_footer">
            <div class="d-flex flex-stack">
              <div class="d-flex align-items-center">
                <div class="symbol symbol-circle symbol-40px">
                  <img src="/assets/metronic/media/avatars/300-1.jpg" alt="photo" />
                </div>
                <div class="ms-2">
                  <a href="#" class="text-gray-800 text-hover-primary fs-6 fw-bold lh-1">{{Auth::User()->name}}</a>
                  <span class="text-muted fw-semibold d-block fs-7 lh-1">{{Auth::User()->name}}</span>
                </div>
              </div>
              <div class="ms-1">
                <div class="btn btn-sm btn-icon btn-active-color-primary position-relative me-n2" data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-overflow="true" data-kt-menu-placement="top-end">
                  <i class="ki-duotone ki-setting-2 fs-1">
                    <span class="path1"></span>
                    <span class="path2"></span>
                  </i>
                </div>
                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg menu-state-color fw-semibold py-4 fs-6 w-275px" data-kt-menu="true">
                  <div class="menu-item px-3">
                    <div class="menu-content d-flex align-items-center px-3">
                      <div class="d-flex flex-column">
                        <div class="fw-bold d-flex align-items-center fs-5">{{Auth::User()->name}}
                          <span class="badge badge-light-success fw-bold fs-8 px-2 py-1 ms-2">Perfil Cliente</span>
                        </div>
                        <a href="#" class="fw-semibold text-muted text-hover-primary fs-7">{{Auth::User()->email}}</a>
                      </div>
                    </div>
                  </div>
                  <div class="separator my-2"></div>
                  <!--div class="menu-item px-5">
                    <a href="account/overview.html" class="menu-link px-5">My Profile</a>
                  </div-->
                  <!--div class="separator my-2"></div-->
                  <!--div class="menu-item px-5 my-1">
                    <a href="account/settings.html" class="menu-link px-5">Account Settings</a>
                  </div-->
                  <div class="menu-item px-5">
                    <a href="/signout" class="menu-link px-5">Cerrar sesión</a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="wrapper d-flex flex-column flex-row-fluid" id="kt_wrapper">
          <div id="kt_header" class="header mt-0 mt-lg-0 pt-lg-0" data-kt-sticky="true" data-kt-sticky-name="header" data-kt-sticky-offset="{lg: '300px'}">
            <div class="container-fluid d-flex flex-stack flex-wrap gap-4" id="kt_header_container">
              <div class="page-title d-flex flex-column align-items-start justify-content-center flex-wrap me-lg-2 pb-10 pb-lg-0" data-kt-swapper="true" data-kt-swapper-mode="prepend" data-kt-swapper-parent="{default: '#kt_content_container', lg: '#kt_header_container'}">
                <h1 class="d-flex flex-column text-gray-900 fw-bold my-0 fs-1">Hola, {{Auth::User()->name}}</h1> 
                @yield('BreadCrumb')
              </div>
              <div class="d-flex d-lg-none align-items-center ms-n3 me-2">
                <div class="btn btn-icon btn-active-icon-primary" id="kt_aside_toggle">
                  <i class="ki-duotone ki-abstract-14 fs-1 mt-1">
                    <span class="path1"></span>
                    <span class="path2"></span>
                  </i>
                </div>
                <a href="/" class="d-flex align-items-center">
                  <img alt="Logo" src="/assets/metronic/media/logos/demo3.svg" class="theme-light-show h-20px" />
                  <img alt="Logo" src="/assets/metronic/media/logos/demo3-dark.svg" class="theme-dark-show h-20px" />
                </a>
              </div>
              <div class="d-flex align-items-center flex-shrink-0 mb-0 mb-lg-0">
                <div id="kt_header_search" class="header-search d-flex align-items-center w-lg-250px" data-kt-search-keypress="true" data-kt-search-min-length="2" data-kt-search-enter="enter" data-kt-search-layout="menu" data-kt-search-responsive="lg" data-kt-menu-trigger="auto" data-kt-menu-permanent="true" data-kt-menu-placement="bottom-end">
                  <div data-kt-search-element="toggle" class="search-toggle-mobile d-flex d-lg-none align-items-center">
                    <div class="d-flex btn btn-icon btn-color-gray-700 btn-active-color-primary btn-outline w-40px h-40px">
                      <i class="ki-duotone ki-magnifier fs-1">
                        <span class="path1"></span>
                        <span class="path2"></span>
                      </i>
                    </div>
                  </div>
                  <form data-kt-search-element="form" class="d-none d-lg-block w-100 position-relative mb-2 mb-lg-0" autocomplete="off">
                    <input type="hidden" />
                    <i class="ki-duotone ki-magnifier fs-2 text-gray-700 position-absolute top-50 translate-middle-y ms-4">
                      <span class="path1"></span>
                      <span class="path2"></span>
                    </i>
                    <input type="text" class="form-control bg-transparent ps-13 fs-7 h-40px" name="search" value="" placeholder="Buscar contenedor" data-kt-search-element="input" />
                    <span class="position-absolute top-50 end-0 translate-middle-y lh-0 d-none me-5" data-kt-search-element="spinner">
                      <span class="spinner-border h-15px w-15px align-middle text-gray-500"></span>
                    </span>
                    <span class="btn btn-flush btn-active-color-primary position-absolute top-50 end-0 translate-middle-y lh-0 d-none me-4" data-kt-search-element="clear">
                      <i class="ki-duotone ki-cross fs-2 fs-lg-1 me-0">
                        <span class="path1"></span>
                        <span class="path2"></span>
                      </i>
                    </span>
                  </form>
                </div>
                <div class="d-flex align-items-center ms-3 ms-lg-4">
                  <a href="#" class="btn btn-icon btn-color-gray-700 btn-active-color-primary btn-outline w-40px h-40px" data-kt-menu-trigger="{default:'click', lg: 'hover'}" data-kt-menu-attach="parent" data-kt-menu-placement="bottom-end">
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
                  <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-title-gray-700 menu-icon-gray-500 menu-active-bg menu-state-color fw-semibold py-4 fs-base w-150px" data-kt-menu="true" data-kt-element="theme-mode-menu">
                    <div class="menu-item px-3 my-0">
                      <a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="light">
                        <span class="menu-icon" data-kt-element="icon">
                          <i class="ki-duotone ki-night-day fs-2">
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
                        </span>
                        <span class="menu-title">Light</span>
                      </a>
                    </div>
                    <div class="menu-item px-3 my-0">
                      <a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="dark">
                        <span class="menu-icon" data-kt-element="icon">
                          <i class="ki-duotone ki-moon fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                          </i>
                        </span>
                        <span class="menu-title">Dark</span>
                      </a>
                    </div>
                    <div class="menu-item px-3 my-0">
                      <a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="system">
                        <span class="menu-icon" data-kt-element="icon">
                          <i class="ki-duotone ki-screen fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                            <span class="path4"></span>
                          </i>
                        </span>
                        <span class="menu-title">System</span>
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
            <div class="container-fluid" id="kt_content_container"> 
                @yield('WorkSpace') 
            </div>
          </div>
          <div class="footer py-4 d-flex flex-lg-column" id="kt_footer">
            <div class="container-fluid d-flex flex-column flex-md-row flex-stack">
              <div class="text-gray-900 order-2 order-md-1">
                <span class="text-gray-500 fw-semibold me-1">SGT - Sistema de Gestión de Transporte</span>
                
              </div>
              <ul class="menu menu-gray-600 menu-hover-primary fw-semibold order-1">
                <!--li class="menu-item">
                  <a href="#" target="_blank" class="menu-link px-2">Acerca de</a>
                </li>
                <li class="menu-item">
                  <a href="#" target="_blank" class="menu-link px-2">Soporte</a>
                </li-->
                <li class="menu-item">
                  <a href="#" target="_blank" class="menu-link px-2">800 000 0000</a>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div id="kt_scrolltop" class="scrolltop" data-kt-scrolltop="true">
      <i class="ki-duotone ki-arrow-up">
        <span class="path1"></span>
        <span class="path2"></span>
      </i>
    </div>

    <script>
      var hostUrl = "/assets/metronic/";
    </script>
    <script src="/assets/metronic/plugins/global/plugins.bundle.js"></script>
    <script src="/assets/metronic/js/scripts.bundle.js"></script>
    <script src="/assets/metronic/plugins/custom/datatables/datatables.bundle.js"></script>
    <script src="/assets/metronic/js/widgets.bundle.js"></script>
    <script src="/assets/metronic/js/custom/widgets.js"></script> 
    @stack('javascript')

  </body>
</html>