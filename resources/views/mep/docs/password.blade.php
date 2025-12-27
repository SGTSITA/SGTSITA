@extends('layouts.externo')

@section('content')
    <div class="d-flex flex-column flex-root">
        <div class="d-flex flex-center flex-column flex-column-fluid p-10">

            <div class="card shadow-sm w-lg-500px w-100">
                <div class="card-body p-10">

                    <div class="text-center mb-8">
                        <i class="ki-duotone ki-lock fs-3x text-primary mb-3">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>

                        <h2 class="fw-bold">Acceso a documentos</h2>
                        <p class="text-muted">
                            Ingrese la contraseña proporcionada para visualizar los archivos del contenedor
                        </p>
                    </div>

                    <form method="POST" action="{{ route('externos.validarPassword', $token) }}" id="formAccesoDocs">
                        @csrf

                        <div class="mb-5">
                            <label class="form-label fw-semibold">Contraseña de acceso</label>
                            <input type="password" name="password" class="form-control form-control-lg"
                                placeholder="********" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ki-duotone ki-key fs-4 me-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Acceder
                        </button>
                    </form>

                </div>
            </div>

        </div>
    </div>
@endsection
