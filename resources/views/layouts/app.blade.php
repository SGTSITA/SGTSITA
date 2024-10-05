<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

  <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('favicon/'. $configuracion->favicon) }}">
  <link rel="icon" type="image/png" href="{{ asset('favicon/'. $configuracion->favicon) }}">
  <title>
    @yield('template_title') - {{$configuracion->nombre_sistema}}
  </title>

  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
  <!-- Nucleo Icons -->
  <link href="{{ asset('assets/css/nucleo-icons.css')}}" rel="stylesheet" />
  <link href="{{ asset('assets/css/nucleo-svg.css')}}" rel="stylesheet" />
  <!-- Font Awesome Icons -->
  <link href="{{ asset('assets/css/nucleo-svg.css')}}" rel="stylesheet" />

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />  <link href="{{ asset('assets/css/nucleo-svg.css')}}" rel="stylesheet" />
  @yield('css')
   <!-- Select2  -->
   <link rel="stylesheet" href="{{ asset('assets/vendor/select2/dist/css/select2.min.css')}}">

  <!-- CSS Files -->
  <link id="pagestyle" href="{{ asset('assets/css/argon-dashboard.css?v=2.0.4')}}" rel="stylesheet" />
  <link rel="stylesheet" href="{{ asset('assets/css/sweetalert2.css') }}">

  <style>
        input:before {
            content: attr(data-date);
            display: inline-block;
            color: black;
        }

        .dataTable-wrapper .dataTable-container .table tbody tr td {
            padding: 0px 0.3rem!important;
            font-size: 13px!important;
        }

        .dataTable-wrapper .dataTable-top {
            padding: 0rem 1.5rem 0rem 1.5rem;
        }

        .card .card-header {
            padding: 1.5rem 1.5rem 0 1.5rem;
        }
    </style>

</head>

<body class="g-sidenav-show   bg-gray-100">
  <div class="min-height-300  position-absolute w-100" style="background-color: {{$configuracion->color_principal}}!important;"></div>
  <div id="page-loader"><span class="preloader-interior"></span></div>

   <!-- Sidenav -->
    @include('layouts.sidebar')

  <main class="main-content position-relative border-radius-lg ">
    <!-- Navbar -->
    @include('layouts.navbar')

    <!-- End Navbar -->

    <div class="container-fluid">

        {{-- @include('layouts.header') --}}
        @include('layouts.simple_alert')
        @yield('breadcrumb')
        @yield('content')

        @include('client.modal_create')
        @include('operadores.modal_create')
        @include('proveedores.modal_create')
        @include('equipos.modal_create')
        @include('bancos.modal_create')


       <!-- Modal lateral Congif -->
        @include('layouts.footer')
      <!-- End Modal lateral Congif -->

    </div>
  </main>

  @yield('js_custom')

   <!-- Modal lateral Congif -->
    {{-- @include('layouts.modal_config') --}}
  <!-- End Modal lateral Congif -->


  <!--   Core JS Files   -->
  {{-- <script src="{{ asset('assets/vendor/jquery/dist/jquery.min.js')}}"></script> --}}

  <script src="{{ asset('assets/js/core/popper.min.js')}}"></script>

  <script src="{{ asset('assets/js/core/bootstrap.min.js')}}"></script>
  <script src="{{ asset('assets/js/plugins/perfect-scrollbar.min.js')}}"></script>
  <script src="{{ asset('assets/js/plugins/smooth-scrollbar.min.js')}}"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>

  <script src="{{ asset('assets/js/plugins/datatables.js')}}"></script>

  <script src="{{ asset('assets/js/argon-dashboard.min.js')}}"></script>


  <script type="text/javascript" src="{{ asset('assets/js/sweetalert2.all.min.js') }}"></script>


    <script>
        var token = $('meta[name="csrf-token"]').attr('content');
    </script>

<script>
    document.getElementById('backButton').addEventListener('click', function(event) {
        event.preventDefault(); // Evitar el comportamiento predeterminado del enlace
        history.back(); // Volver a la página anterior

    });
</script>

  @yield('datatable')

  @yield('fullcalendar')
  @yield('alerta')

  <!-- Github buttons -->
  {{-- <script async defer src="https://buttons.github.io/buttons.js"></script> --}}

  @yield('select2')

</body>

</html>
