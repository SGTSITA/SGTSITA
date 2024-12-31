<aside class="sidenav bg-white navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-4 " id="sidenav-main">
    <div class="sidenav-header">
      <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
      <a class="navbar-brand m-0" href="{{ route('dashboard') }}" target="">
        <img src="{{asset('logo/'.$configuracion->logo) }}" class="navbar-brand-img h-100" alt="main_logo">
        <span class="ms-1 font-weight-bold"></span>
      </a>
    </div>
    <hr class="horizontal dark mt-0">
    <div class="collapse navbar-collapse  w-auto h-auto" id="sidenav-collapse-main">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link {{ (Request::is('dashboard*') ? 'active' : '') }}" href="{{ route('dashboard') }}" target="">
            <div class="icon icon-shape icon-sm text-center  me-2 d-flex align-items-center justify-content-center">
             <i class="ni ni-calendar-grid-58 text-sm opacity-10" style="color: {{$configuracion->color_iconos_sidebar}}"></i>
            </div>
            <span class="nav-link-text ms-1">Inicio</span>
          </a>
        </li>

        @can('clientes-list')
        <li class="nav-item">
            <a class="nav-link {{ (Request::is('clients*') ? 'active' : '') }}" href="{{ route('clients.index') }}" target="">
              <div class="icon icon-shape icon-sm text-center  me-2 d-flex align-items-center justify-content-center">
                  <img src="{{ asset('img/icon/empleados.webp') }}" alt="" width="20px">
              </div>
              <span class="nav-link-text ms-1"><b>I</b> Clientes</span>
            </a>
          </li>
        @endcan

        @can('proovedores-list')
          <li class="nav-item">
            <a class="nav-link {{ (Request::is('proveedores*') ? 'active' : '') }}" href="{{ route('index.proveedores') }}" target="">
              <div class="icon icon-shape icon-sm text-center  me-2 d-flex align-items-center justify-content-center">
                <img src="{{ asset('img/icon/edificios_ciudad.webp') }}" alt="" width="20px">
              </div>
              <span class="nav-link-text ms-1"><b>II</b> Proveedores</span>
            </a>
          </li>
          @endcan

        @can('equipos-list')
          <li class="nav-item">
            <a class="nav-link {{ (Request::is('equipos*') ? 'active' : '') }}" href="{{ route('index.equipos') }}" target="">
              <div class="icon icon-shape icon-sm text-center  me-2 d-flex align-items-center justify-content-center">
                <img src="{{ asset('img/icon/referencia.webp') }}" alt="" width="20px">
              </div>
              <span class="nav-link-text ms-1"><b>III</b> Equipos</span>
            </a>
          </li>
          @endcan

        @can('operadores-list')
          <li class="nav-item">
            <a class="nav-link {{ (Request::is('operadores*') ? 'active' : '') }}" href="{{ route('index.operadores') }}" target="">
              <div class="icon icon-shape icon-sm text-center  me-2 d-flex align-items-center justify-content-center">
                <img src="{{ asset('img/icon/camion.png') }}" alt="" width="20px">
              </div>
              <span class="nav-link-text ms-1"><b>IV</b> Operadores</span>
            </a>
          </li>
          @endcan


          @can('cotizaciones-externas')
          <li class="nav-item">
            <a class="nav-link {{ (Request::is('cotizaciones*') ? 'active' : '') }}" href="{{ route('index.cotizaciones_manual') }}" target="">
              <div class="icon icon-shape icon-sm text-center  me-2 d-flex align-items-center justify-content-center">
                <img src="{{ asset('img/icon/factura.png.webp') }}" alt="" width="20px">
              </div>
              <span class="nav-link-text ms-1"><b>V</b> Cotizaciones Manual</span>
            </a>
          </li>
          @endcan

        @can('cotizaciones-list')

          <a data-bs-toggle="collapse" href="#pagesExamplesCotizaciones" class="nav-link {{ (Request::is('cotizaciones*') ? 'active' : '') }}" aria-controls="pagesExamplesCotizaciones" role="button" aria-expanded="false">
                <div class="icon icon-shape icon-sm text-center  me-2 d-flex align-items-center justify-content-center">
                    <img src="{{ asset('img/icon/factura.png.webp') }}" alt="" width="20px">
                </div>
                <span class="nav-link-text ms-1"><b>V</b> Cotizaciones</span>
            </a>

            <div class="collapse " id="pagesExamplesCotizaciones">
                <ul class="nav ms-4">
                    <li class="nav-item ">
                        <a class="nav-link {{ (Request::is('cotizaciones/busqueda') ? 'show' : '') }}" href="{{ route('busqueda.cotizaciones') }}">
                          <span class="sidenav-mini-icon"> P </span>
                          <span class="sidenav-normal">Busqueda</span>
                        </a>
                        <a class="nav-link {{ (Request::is('cotizaciones*') ? 'show' : '') }}" href="{{ route('create.cotizaciones') }}">
                          <span class="sidenav-mini-icon"> P </span>
                          <span class="sidenav-normal">Crear Cotización</span>
                        </a>
                        <a class="nav-link {{ (Request::is('cotizaciones*') ? 'show' : '') }}" href="{{ route('index.cotizaciones') }}">
                          <span class="sidenav-mini-icon"> P </span>
                          <span class="sidenav-normal">Seguimiento</span>
                        </a>

                        <a class="nav-link {{ (Request::is('cotizaciones/busqueda') ? 'show' : '') }}" href="{{ route('cotizaciones.entrantes') }}">
                          <span class="sidenav-mini-icon"> P </span>
                          <span class="sidenav-normal">Solicitudes entrantes</span>
                        </a>
                    </li>
                </ul>
            </div>
          @endcan
        @can('planeacion-list')
          <li class="nav-item">
            <a class="nav-link {{ (Request::is('planeaciones*') ? 'active' : '') }}" href="{{ route('index.planeaciones') }}" target="">
              <div class="icon icon-shape icon-sm text-center  me-2 d-flex align-items-center justify-content-center">
                <img src="{{ asset('img/icon/inventario.png.webp') }}" alt="" width="20px">
              </div>
              <span class="nav-link-text ms-1"><b>VI</b> Planeación</span>
            </a>
          </li>
          @endcan

          @can('bancos-list')
            <li class="nav-item">
                <a class="nav-link {{ (Request::is('bancos*') ? 'active' : '') }}" href="{{ route('index.bancos') }}" target="">
                <div class="icon icon-shape icon-sm text-center  me-2 d-flex align-items-center justify-content-center">
                    <img src="{{ asset('img/icon/banco.png') }}" alt="" width="20px">
                </div>
                <span class="nav-link-text ms-1"><b>VII</b> Bancos</span>
                </a>
            </li>
          @endcan

          @can('cuentas-cobrar')
            <li class="nav-item">
                <a class="nav-link {{ (Request::is('cuentas/cobrar*') ? 'active' : '') }}" href="{{ route('index.cobrar') }}" target="">
                <div class="icon icon-shape icon-sm text-center  me-2 d-flex align-items-center justify-content-center">
                    <img src="{{ asset('img/icon/bolsa-de-dinero.webp') }}" alt="" width="20px">
                </div>
                <span class="nav-link-text ms-1"><b>VIII</b> Cuentas por cobrar</span>
                </a>
            </li>
          @endcan

          @can('cuentas-pagar')
            <li class="nav-item">
                <a class="nav-link {{ (Request::is('cuentas/pagar*') ? 'active' : '') }}" href="{{ route('index.pagar') }}" target="">
                <div class="icon icon-shape icon-sm text-center  me-2 d-flex align-items-center justify-content-center">
                    <img src="{{ asset('img/icon/gastos.png.webp') }}" alt="" width="20px">
                </div>
                <span class="nav-link-text ms-1"><b>IX</b> Cuentas por pagar</span>
                </a>
            </li>
          @endcan

          @can('gastos-generales')
            <li class="nav-item">
                <a class="nav-link {{ (Request::is('gastos/generales*') ? 'active' : '') }}" href="{{ route('index.gastos_generales') }}" target="">
                <div class="icon icon-shape icon-sm text-center  me-2 d-flex align-items-center justify-content-center">
                    <img src="{{ asset('img/icon/billetera.png') }}" alt="" width="20px">
                </div>
                <span class="nav-link-text ms-1"><b>X</b> Gastos Generales</span>
                </a>
            </li>
          @endcan

          @can('reportes')
            <a data-bs-toggle="collapse" href="#pagesExamplesReporteria" class="nav-link {{ (Request::is('reporteria/cotizaciones*') ? 'active' : '') }}" aria-controls="pagesExamplesReporteria" role="button" aria-expanded="false">
                <div class="icon icon-shape icon-sm text-center  me-2 d-flex align-items-center justify-content-center">
                    <img src="{{ asset('img/icon/pdf.webp') }}" alt="" width="20px">
                </div>
                <span class="nav-link-text ms-1"><b>XI</b> Reporteria</span>
            </a>

            <div class="collapse " id="pagesExamplesReporteria">
                <ul class="nav ms-4">
                    <li class="nav-item ">
                        <a class="nav-link {{ (Request::is('reporteria/cotizaciones/cxc*') ? 'show' : '') }}" href="{{ route('index.reporteria') }}">
                        <span class="sidenav-mini-icon"> P </span>
                        <span class="sidenav-normal">Cuentas por cobrar</span>
                        </a>

                        <a class="nav-link {{ (Request::is('reporteria/cotizaciones/cxp*') ? 'show' : '') }}" href="{{ route('index_cxp.reporteria') }}">
                        <span class="sidenav-mini-icon"> P </span>
                        <span class="sidenav-normal">Cuentas por pagar</span>
                        </a>
                        
                        @can('reportes-viajes')
                        <a class="nav-link {{ (Request::is('reporteria/viajes*') ? 'show' : '') }}" href="{{ route('index_viajes.reporteria') }}">
                        <span class="sidenav-mini-icon"> P </span>
                        <span class="sidenav-normal">Viajes</span>
                        </a>
                        @endcan

                        <a class="nav-link {{ (Request::is('reporteria/utilidad*') ? 'show' : '') }}" href="{{ route('index_utilidad.reporteria') }}">
                            <span class="sidenav-mini-icon"> P </span>
                            <span class="sidenav-normal">Reporte de utilidad</span>
                        </a>

                        <a class="nav-link {{ (Request::is('reporteria/documentos*') ? 'show' : '') }}" href="{{ route('index_documentos.reporteria') }}">
                            <span class="sidenav-mini-icon"> P </span>
                            <span class="sidenav-normal">Reporte de documentos</span>
                        </a>

                        <a class="nav-link {{ (Request::is('reporteria/liquidados/cxc*') ? 'show' : '') }}" href="{{ route('index_liquidados_cxc.reporteria') }}">
                            <span class="sidenav-mini-icon"> P </span>
                            <span class="sidenav-normal">Liquidados cxc</span>
                        </a>

                        <a class="nav-link {{ (Request::is('reporteria/liquidados/cxp*') ? 'show' : '') }}" href="{{ route('index_liquidados_cxp.reporteria') }}">
                            <span class="sidenav-mini-icon"> P </span>
                            <span class="sidenav-normal">Liquidados cxp</span>
                        </a>
                    </li>
                </ul>
            </div>

          @endcan

          @can('liquidaciones')
            <li class="nav-item">
                <a class="nav-link {{ (Request::is('liquidaciones*') ? 'active' : '') }}" href="{{ route('index.liquidacion') }}" target="">
                <div class="icon icon-shape icon-sm text-center  me-2 d-flex align-items-center justify-content-center">
                    <img src="{{ asset('img/icon/pago-en-efectivo.png') }}" alt="" width="20px">
                </div>
                <span class="nav-link-text ms-1"><b>XII</b> Liquidaciones</span>
                </a>
            </li>
            @endcan

            @can('catalogo')
            <li class="nav-item">
                <a class="nav-link {{ (Request::is('catalogo*') ? 'active' : '') }}" href="{{ route('index.catalogo') }}" target="">
                <div class="icon icon-shape icon-sm text-center  me-2 d-flex align-items-center justify-content-center">
                    <img src="{{ asset('img/icon/catalogo.webp.webp') }}" alt="" width="20px">
                </div>
                <span class="nav-link-text ms-1"><b>XIII</b> Catálogo</span>
                </a>
            </li>
            @endcan

          <a data-bs-toggle="collapse" href="#pagesExamples" class="nav-link {{ (Request::is('users*') ? 'active' : '') }}{{ (Request::is('roles*') ? 'active' : '') }}" aria-controls="pagesExamples" role="button" aria-expanded="false">
            <div class="icon icon-shape icon-sm text-center d-flex align-items-center justify-content-center">
              <i class="ni ni-settings text-sm opacity-10" style="color: {{$configuracion->color_iconos_sidebar}}"></i>
            </div>
            <span class="nav-link-text ms-1">Roles y Permisos</span>
          </a>

          <div class="collapse " id="pagesExamples">
            <ul class="nav ms-4">
               @can('roles-permisos-users')
                <li class="nav-item ">
                    <a class="nav-link {{ (Request::is('users*') ? 'show' : '') }}" href="{{ route('users.index') }}">
                    <span class="sidenav-mini-icon"> P </span>
                    <span class="sidenav-normal">Usuarios</span>
                    </a>
                    @endcan

                    @can('roles-permisos-users')
                    <a class="nav-link {{ (Request::is('roles*') ? 'show' : '') }}" href="{{ route('roles.index') }}">
                    <span class="sidenav-mini-icon"> P </span>
                    <span class="sidenav-normal">Roles</span>
                    </a>
                    @endcan

                    @can('empresas-list')
                    <a class="nav-link {{ (Request::is('empresas*') ? 'show' : '') }}" href="{{ route('empresas.index') }}">
                        <span class="sidenav-mini-icon"> P </span>
                        <span class="sidenav-normal">Empresas</span>
                    </a>
                </li>
               @endcan
            </ul>
          </div>
        </li>

        @can('configuracion-list')
        <li class="nav-item mt-3">
          <h6 class="ps-4  ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Configuraciones</h6>
        </li>

        <li class="nav-item">
          <a data-bs-toggle="collapse" href="#sistem" class="nav-link {{ (Request::is('users*') ? 'active' : '') }}{{ (Request::is('roles*') ? 'active' : '') }}" aria-controls="sistem" role="button" aria-expanded="false">
            <div class="icon icon-shape icon-sm text-center d-flex align-items-center justify-content-center">
              <i class="ni ni-settings-gear-65 text-sm opacity-10" style="color: {{$configuracion->color_iconos_sidebar}}"></i>
            </div>
            <span class="nav-link-text ms-1">Configuraciones</span>
          </a>
          <div class="collapse " id="sistem">
            <ul class="nav ms-4">
              <li class="nav-item ">
                <a class="nav-link {{ (Request::is('configuracion*') ? 'show' : '') }}" href="#">
                  <span class="sidenav-mini-icon">U</span>
                  <span class="sidenav-normal">Horarios</span>
                </a>
              </li>
            </ul>
          </div>
          <div class="collapse " id="sistem">
            <ul class="nav ms-4">
              <li class="nav-item ">
                <a class="nav-link {{ (Request::is('configuracion*') ? 'show' : '') }}" href="{{ route('index.configuracion',auth()->user()->Empresa->Configuracion->id) }}">
                  <span class="sidenav-mini-icon">U</span>
                  <span class="sidenav-normal">Pagina</span>
                </a>
              </li>
            </ul>
          </div>
        </li>
        @endcan
      </ul>

    </div>

  </aside>
