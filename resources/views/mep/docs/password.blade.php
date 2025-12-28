@extends('layouts.externo')

@section('content')
    <div class="d-flex flex-column flex-root min-vh-100">
        <div class="d-flex flex-center flex-column flex-column-fluid p-4 p-md-10">

            <div class="card shadow-sm w-100" style="max-width:420px;">
                <div class="card-body p-5 p-md-10">

                    <div class="text-center mb-6">
                        <i class="ki-duotone ki-lock fs-3x text-primary mb-3"></i>
                        <h2 class="fw-bold fs-4 fs-md-2">Acceso a documentos</h2>
                        <p class="text-muted fs-7 fs-md-6">
                            Ingrese la contraseña proporcionada para visualizar los archivos del contenedor
                        </p>
                    </div>

                    <form method="POST" action="{{ route('externos.validarPassword', $token) }}">
                        @csrf

                        <div class="mb-5">
                            <label class="form-label fw-semibold">Contraseña de acceso</label>
                            <input type="password" name="password" class="form-control form-control-lg"
                                placeholder="********" autocomplete="current-password" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 btn-lg">
                            Acceder
                        </button>
                    </form>

                </div>
            </div>

        </div>
    </div>
@endsection
