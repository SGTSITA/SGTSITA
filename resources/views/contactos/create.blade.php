@extends('layouts.usuario_externo')

<style>
    .perfil-wrapper {
        cursor: pointer;
    }

    .perfil-wrapper .overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background-color: rgba(0, 0, 0, 0.4);
        display: flex;
        justify-content: center;
        align-items: center;
        opacity: 0;
        transition: 0.3s ease;
    }

    .perfil-wrapper:hover .overlay {
        opacity: 1;
    }
</style>

@section('WorkSpace')
    <div class="card shadow-sm">
        <div class="card-header bg-white border-bottom">
            <h3 class="card-title fw-bold">
                <i class="fas fa-address-card me-2 text-primary"></i>
                Agregar Contacto
            </h3>
        </div>
        <div class="card-body">
            <form id="formContacto" enctype="multipart/form-data">
                @csrf

                <div class="mb-3 text-center">
                    <div class="position-relative d-inline-block perfil-wrapper mb-2">
                        <label for="input-foto" style="margin: 0; cursor: pointer">
                            <img
                                id="preview-foto"
                                src="{{ asset('assets/images/faces/default-avatar.png') }}"
                                class="rounded-circle border"
                                width="120"
                                height="120"
                                style="object-fit: cover"
                            />
                            <div class="overlay">
                                <i class="fas fa-plus fa-lg text-white"></i>
                            </div>
                        </label>
                        <input type="file" id="input-foto" name="foto" accept="image/*" style="display: none" />
                    </div>
                    <div>
                        <span class="text-muted small d-block">Foto del contacto</span>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" class="form-control" name="nombre" placeholder="Ej. Juan Pérez" required />
                    </div>
                </div>

                <div class="mb-3">
                    <label for="telefono" class="form-label">Teléfono</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                        <input type="text" class="form-control" name="telefono" placeholder="Ej. 9931234567" required />
                    </div>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Correo</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" class="form-control" name="email" placeholder="correo@ejemplo.com" />
                    </div>
                </div>

                <div class="mb-3">
                    <label for="empresa" class="form-label">Empresa</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-building"></i></span>
                        <input type="text" class="form-control" name="empresa" placeholder="Nombre de empresa" />
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('javascript')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const input = document.getElementById('input-foto');
            const preview = document.getElementById('preview-foto');

            input.addEventListener('change', function (e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function (event) {
                        preview.src = event.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            });

            const form = document.getElementById('formContacto');

            form.addEventListener('submit', async function (e) {
                e.preventDefault();

                const formData = new FormData(form);

                try {
                    const response = await fetch('{{ route('contactos.store') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            Accept: 'application/json',
                        },
                        body: formData,
                    });

                    const result = await response.json();

                    if (!result.success) {
                        Swal.fire({
                            icon: 'info',
                            title: 'Atención',
                            html: result.message,
                            confirmButtonText: 'Entendido',
                        });
                        return;
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Contacto guardado',
                        text: result.message,
                        timer: 1500,
                        showConfirmButton: false,
                    }).then(() => {
                        window.location.href = '{{ route('contactos.index') }}';
                    });
                } catch (error) {
                    console.error(error);
                    Swal.fire('Error', 'Ocurrió un error al guardar el contacto.', 'error');
                }
            });
        });
    </script>
@endpush
