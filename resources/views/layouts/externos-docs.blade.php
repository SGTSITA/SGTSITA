<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Documentos compartidos</title>

    <!-- Bootstrap base -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome (botones) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

    <style>
        body {
            background-color: #f8f9fa;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
        }
    </style>

    @yield('css')
</head>

<body>

    <nav class="navbar navbar-light bg-white shadow-sm mb-4">
        <div class="container-fluid container-lg">
            <span class="navbar-brand fw-bold">

            </span>
        </div>
    </nav>

    <main class="container-fluid container-lg pb-5">
        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts')
</body>

</html>
