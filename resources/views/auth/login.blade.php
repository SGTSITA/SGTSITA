<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('assets/img/apple-icon.png')}}">
  <link rel="icon" type="image/png" href="https://paradisus.mx/favicon/639893ee3d1ff63891f2fbd91b277248048_670190130923536_7018383830884135385_n__1_-removebg-preview.png">
  <title>
    SGT
  </title>
  <!--     Fonts and icons     -->
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
  <!-- Nucleo Icons -->
  <link href="{{ asset('assets/css/nucleo-icons.css')}}" rel="stylesheet" />
  <link href="{{ asset('assets/css/nucleo-svg.css')}}" rel="stylesheet" />
  <!-- Font Awesome Icons -->
  <script src="https://kit.fontawesome.com/42d5adcbca.js')}}" crossorigin="anonymous"></script>
  <link href="{{ asset('assets/css/nucleo-svg.css')}}" rel="stylesheet" />
  <!-- CSS Files -->
  <link id="pagestyle" href="{{ asset('assets/css/argon-dashboard.css?v=2.0.4')}}" rel="stylesheet" />
</head>

<body class="">

  <main class="main-content main-content-bg mt-0">
    <div class="page-header min-vh-100" style="background-image: url('{{ asset('img/contenedores.jpg') }}');">
      <span class="mask bg-gradient-dark opacity-6"></span>
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-lg-4 col-md-7">
            <div class="card border-0 mb-0">
              <div class="card-header bg-transparent">
                <h5 class="text-dark text-center mt-2 mb-3">¡Bienvenido!</h5>

                <h4 class="text-dark text-center mt-2 mb-3">
                    Sistema de Gestion de Transporte
                </h4>

              </div>
              <div class="card-body px-lg-5 pt-0">
                <div class="text-center text-muted mb-4">
                  <small></small>
                </div>

                {{-- <form method="POST" action="{{ route('login') }}"> --}}
                <form method="POST" action="{{ route('login.custom') }}">
                  @csrf

                  <div class="mb-3">
                    <input id="email" type="email" placeholder="Email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                  </div>

                  <div class="mb-3">
                    <input id="password" type="password" placeholder="*****" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">
                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                  </div>

                  <div class="form-check form-switch">
                    <input class="form-check-input" id=" customCheckLogin" type="checkbox" name="remember" id="remember"{{ old('remember') ? 'checked' : '' }}>
                    <label class="form-check-label" for="rememberMe">{{ __('Remember Me') }}</label>
                  </div>

                  <div class="text-center">
                    <button type="submit" class="btn btn-primary w-100 my-4 mb-2"> {{ __('Login') }}</button>
                  </div>


                </form>

              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
  <!-- -------- START FOOTER 3 w/ COMPANY DESCRIPTION WITH LINKS & SOCIAL ICONS & COPYRIGHT ------- -->
  <footer class="footer py-2">
    <div class="container">
      <div class="row">
        <div class="col-8 mx-auto text-center mt-1">
          <p class="mb-0 text-secondary">
            Power By <script>
              document.write(new Date().getFullYear())
            </script> WebTech
          </p>
        </div>
      </div>
    </div>
  </footer>
  <!-- -------- END FOOTER 3 w/ COMPANY DESCRIPTION WITH LINKS & SOCIAL ICONS & COPYRIGHT ------- -->
  <!--   Core JS Files   -->
  <script src="{{ asset('assets/js/core/popper.min.js')}}"></script>
  <script src="{{ asset('assets/js/core/bootstrap.min.js')}}"></script>
  <script src="{{ asset('assets/js/plugins/perfect-scrollbar.min.js')}}"></script>
  <script src="{{ asset('assets/js/plugins/smooth-scrollbar.min.js')}}"></script>
  <!-- Kanban scripts -->
  <script src="{{ asset('assets/js/plugins/dragula/dragula.min.js')}}"></script>
  <script src="{{ asset('assets/js/plugins/jkanban/jkanban.js')}}"></script>
  <script>
    var win = navigator.platform.indexOf('Win') > -1;
    if (win && document.querySelector('#sidenav-scrollbar')) {
      var options = {
        damping: '0.5'
      }
      Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
    }
  </script>
  <!-- Github buttons -->
  <script async defer src="https://buttons.github.io/buttons.js"></script>
  <!-- Control Center for Soft Dashboard: parallax effects, scripts for the example pages etc -->
  <script src="{{ asset('assets/js/argon-dashboard.min.js?v=2.0.4')}}"></script>
</body>

</html>
