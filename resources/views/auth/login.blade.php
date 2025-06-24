<!DOCTYPE html>
<html lang="es">
  <head>
    <title>SGT</title>
    <meta charset="utf-8" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
    <link href="/assets/metronic/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="/assets/metronic/css/style.bundle.css" rel="stylesheet" type="text/css" />
    <script>
        if (window.top != window.self) {
            window.top.location.replace(window.self.location.href);
        }
    </script>
  </head>
  <body id="kt_body" class="auth-bg">
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
      <div class="d-flex flex-column flex-lg-row flex-column-fluid">
        <div class="d-flex flex-column flex-lg-row-fluid w-lg-50 p-10 order-2 order-lg-2">
          <div class="d-flex flex-center flex-column flex-lg-row-fluid">
            <div class="w-lg-500px p-10">
              <form class="form w-100" novalidate="novalidate" id="kt_sign_in_form" data-kt-redirect-url="/dashboard" method="POST" action="{{ route('login.custom') }}"> @csrf <div class="text-center mb-11">
                  <h1 class="text-gray-900 fw-bolder mb-3"> Hola, bienvenido </h1>
                  <div class="text-gray-500 fw-semibold fs-6"> Introduzca sus credenciales para iniciar sesión </div>
                </div>
      
                <a href="{{ url('/auth/google') }}">Iniciar sesión con Google</a>
                <div class="separator separator-content my-14"></div>
                <div class="fv-row mb-8">
                  <input type="text" placeholder="Email" name="email" autocomplete="off" class="form-control bg-transparent" />
                </div>
                <div class="fv-row mb-3">
                  <input type="password" placeholder="Password" name="password" autocomplete="off" class="form-control bg-transparent" />
                </div>
                <div class="d-flex flex-stack flex-wrap gap-3 fs-base fw-semibold mb-8">
                  <div></div>
                  <a href="/" class="link-primary"> ¿Olvidó su contraseña? </a>
                </div>
                <div class="d-grid mb-10">
                  <button type="submit" id="kt_sign_in_submit" class="btn btn-primary">
                    <span class="indicator-label"> Iniciar sesión</span>
                    <span class="indicator-progress"> Espere un momento... <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                    </span>
                  </button>
                </div>
                <div class="text-gray-500 text-center fw-semibold fs-6"> ¿Aún no tiene cuenta? <a href="/accounts/register" class="link-primary"> Registrese </a>
                </div>
              </form>
            </div>
          </div>
          <!--begin::Footer-->
          <!--div class="text-center w-lg-500px d-flex flex-stack px-10 mx-auto "><div class="d-flex fw-semibold text-primary fs-base gap-5"><a href="/" target="_blank">Aviso de Privacidad</a><a href="/" target="_blank">Planes</a><a href="/" target="_blank">Contacto</a></div></div-->
          <!--end::Footer-->
        </div>
        <div class="d-flex flex-lg-row-fluid w-lg-50 bgi-size-cover bgi-position-center order-1 order-lg-1" style="background-image: url(/assets/metronic/background-cfdi-stack.jpg)">
          <div class="d-flex flex-column flex-center py-7 py-lg-15 px-5 px-md-15 w-100">
            <a href="/" class="mb-0 mb-lg-8">
              <img alt="Logo" src="/assets/metronic/logo-blanco-sgt.png" class="h-35px h-lg-40px" />
            </a>
            <h1 class="d-none d-lg-block text-white fs-2qx fw-bolder text-center mb-8"> Sistema de Gestión de Transporte </h1>
            <div class="d-none d-lg-block text-white fs-base text-center"> Planeación, coordinación y control del movimiento de mercancías de un lugar a otro. Esto incluye la planificación, implementación y control del transporte de productos y servicios. <a href="#" class="opacity-75-hover text-warning fw-bold me-1"></a>
            </div>

            <img class="d-none d-lg-block mx-auto w-275px w-md-50 w-xl-500px mb-10 mb-lg-15" src="/assets/metronic/ship.webp" alt="" />

          </div>
        </div>
      </div>
    </div>

    <script>
      var hostUrl = "/assets/metronic/";
    </script>
    <script src="/assets/metronic/plugins/global/plugins.bundle.js"></script>
    <script src="/assets/metronic/js/scripts.bundle.js"></script>
    <script src="/assets/metronic/js/custom/authentication/sign-in/general.js"></script>
    <script>
      $(document).ready(() => {
        localStorage.removeItem('uuid')
      })
    </script>
  </body>
</html>