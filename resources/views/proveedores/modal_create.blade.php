<style>
    .alert-success {
        display: none !important;
    }
</style>

<div class="modal fade" id="proveedores" tabindex="-1" aria-labelledby="proveedoresLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content shadow-lg">
            <div class="modal-header text-white bg-primary">
                <h5 class="modal-title">Crear Proveedor</h5>
                <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="formCrearProveedor" method="POST" action="{{ route('store.proveedores') }}" enctype="multipart/form-data">
                @csrf

                <div class="modal-body">
                    <div class="row g-3">

                        <!-- Nombre Completo -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Nombre Completo" required>
                                <label for="nombre">Nombre Completo *</label>
                            </div>
                        </div>

                        <!-- Correo Electrónico -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="email" class="form-control" id="correo" name="correo" placeholder="Correo Electrónico" required>
                                <label for="correo">Correo Electrónico *</label>
                            </div>
                        </div>

                        <!-- Teléfono -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="tel" class="form-control" id="telefono" name="telefono" placeholder="Teléfono" required>
                                <label for="telefono">Teléfono *</label>
                            </div>
                        </div>

                        <!-- Dirección -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="direccion" name="direccion" placeholder="Dirección">
                                <label for="direccion">Dirección</label>
                            </div>
                        </div>

                        <!-- Régimen Fiscal -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="regimen_fiscal" name="regimen_fiscal" placeholder="Régimen Fiscal">
                                <label for="regimen_fiscal">Régimen Fiscal</label>
                            </div>
                        </div>

                        <!-- RFC -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="rfc" name="rfc" placeholder="RFC" required>
                                <label for="rfc">RFC *</label>
                            </div>
                        </div>

                        <!-- Tipo de Servicio -->
                        <div class="col-6">
                            <div class="form-floating">
                                <select class="form-select" id="tipo" name="tipo">
                                    <option value="servicio mecánico">Servicio Mecánico</option>
                                    <option value="servicio de burreo">Servicio de Burreo</option>
                                    <option value="servicio de viaje">Servicio de Viaje</option>
                                    <option value="servicio de patio">Servicio de Patio</option>
                                    <option value="Otro">Otro</option>
                                </select>
                                <label for="tipo">Tipo de Servicio *</label>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="form-floating">
                                <select class="form-select" id="edit_tipo_viaje" name="tipo_viaje">
                                        @foreach($tipoViaje as $key => $value)
                                            <option value="{{ $key }}">{{ $value }}</option>
                                        @endforeach
                                </select>
                                <label for="edit_tipo_viaje">Tipo de Viaje *</label>
                            </div>
                        </div>

                        <hr class="mt-4">

                        <!-- Tipo de Empresa -->
                        <div class="col-12">
                            <div class="form-floating">
                                <select class="form-select" id="tipo_empresa" name="tipo_empresa" required>
                                    <option value="lista">Empresa existente</option>
                                    <option value="mep">(MEP)</option>
                                </select>
                                <label for="tipo_empresa">Tipo de Empresa *</label>
                            </div>
                        </div>

                        <!-- Empresa existente -->
                        <div class="col-12" id="empresa_existente">
                            <div class="form-floating mt-2">
                                <select class="form-select" name="id_empresa">
                                    <option value="">Selecciona una empresa...</option>
                                    @foreach ($empresas as $empresa)
                                        <option value="{{ $empresa->id }}">{{ $empresa->nombre }}</option>
                                    @endforeach
                                </select>
                                <label for="id_empresa">Empresa Existente</label>
                            </div>
                        </div>

                        <!-- Usuario del sistema -->
                        <div id="empresa_mep" style="display:none;">
                        <div class="col-md-6">
                            <div class="form-floating mb-2">
                            <input type="password" class="form-control" id="password" name="password" placeholder="Contraseña" required>
                            <label for="password">Contraseña (para acceso al sistema) *</label>
                            </div>
                            <div class="form-check mt-1">
                            <input class="form-check-input" type="checkbox" id="showPassword" onclick="togglePassword()">
                            <label class="form-check-label" for="showPassword">
                                Mostrar contraseña
                            </label>
                            </div>
                        </div>
                        </div>

                        <hr class="mt-4">




                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('custom-javascript')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // SweetAlert para mensajes
    document.addEventListener("DOMContentLoaded", function() {
        @if (session('success'))
            Swal.fire({
                title: "¡Éxito!",
                text: "{{ session('success') }}",
                icon: "success",
                confirmButtonText: "Aceptar"
            });
            @php session()->forget('success'); @endphp
        @endif
    });

    // Mostrar u ocultar secciones según el tipo de empresa
    document.getElementById('tipo_empresa').addEventListener('change', function() {
        const esMEP = this.value === 'mep';
        document.getElementById('empresa_existente').style.display = esMEP ? 'none' : 'block';
        document.getElementById('empresa_mep').style.display = esMEP ? 'block' : 'none';
    });
</script>
@endpush
