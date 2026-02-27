@extends('layouts.externo')

@section('content')
    <div class="d-flex flex-column flex-root">
        <div class="d-flex flex-center flex-column flex-column-fluid p-10">

            <div class="card shadow-sm w-lg-500px w-100 border border-danger">
                <div class="card-body p-10 text-center">

                    <div class="mb-8">
                        <i class="ki-duotone ki-shield-cross fs-3x text-danger mb-4">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>

                        <h2 class="fw-bold text-danger">

                            {{ $titmesage ?? 'Acceso revocado' }}
                        </h2>

                        <p class="text-muted mt-3">

                            {{ $messag ?? ' El acceso a estos documentos ya no es válido.' }}
                            <br>
                            {{ $submessag ?? 'La contraseña fue revocada o el enlace ha expirado.' }}
                        </p>
                    </div>

                    <div class="alert alert-warning d-flex align-items-center text-start">
                        <i class="fas fa-exclamation-triangle me-3 fs-4"></i>
                        <div>
                            Si considera que esto es un error, solicite un nuevo enlace
                            al responsable que le compartió los documentos.
                        </div>
                    </div>

                    <a href="javascript:void(0)" onclick="window.close();" class="btn btn-outline-secondary w-100 mt-4">
                        <i class="fas fa-times me-2"></i>
                        Cerrar
                    </a>

                </div>
            </div>

        </div>
    </div>
@endsection
