<style>
    .alert-success {
        display: none !important;
    }
</style>
<div class="modal fade" id="proveedores" tabindex="-1" aria-labelledby="proveedoresLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content shadow-lg">
            <div class="modal-header  text-white">
                <h5 class="modal-title">Crear Proveedor</h5>
                <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="formCrearProveedor" method="POST" action="{{ route('store.proveedores') }}"
                enctype="multipart/form-data">
                @csrf
                <!-- Campos del formulario -->


                <div class="modal-body">
                    <div class="row g-3">

                        <!-- Nombre Completo -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="nombre" name="nombre"
                                    placeholder="Nombre Completo" required>
                                <label for="nombre">Nombre Completo *</label>
                            </div>
                        </div>

                        <!-- Correo Electrónico -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="email" class="form-control" id="correo" name="correo"
                                    placeholder="Correo Electrónico" required>
                                <label for="correo">Correo Electrónico *</label>
                            </div>
                        </div>

                        <!-- Teléfono -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="tel" class="form-control" id="telefono" name="telefono"
                                    placeholder="Teléfono" required>
                                <label for="telefono">Teléfono *</label>
                            </div>
                        </div>

                        <!-- Dirección -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="direccion" name="direccion"
                                    placeholder="Dirección">
                                <label for="direccion">Dirección</label>
                            </div>
                        </div>

                        <!-- Régimen Fiscal -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="regimen_fiscal" name="regimen_fiscal"
                                    placeholder="Régimen Fiscal">
                                <label for="regimen_fiscal">Régimen Fiscal</label>
                            </div>
                        </div>

                        <!-- RFC -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="rfc" name="rfc" required>

                                <label for="rfc">RFC</label>
                            </div>
                        </div>

                        <!-- Tipo de Servicio -->
                        <div class="col-12">
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

                    </div> <!-- Cierre de .row -->
                </div> <!-- Cierre de .modal-body -->

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
        document.addEventListener("DOMContentLoaded", function() {
            @if (session('success'))
                Swal.fire({
                    title: "¡Éxito!",
                    text: "{{ session('success') }}",
                    icon: "success",
                    confirmButtonText: "Aceptar"
                });
                @php session()->forget('success'); @endphp // 🔹 Elimina el mensaje después de mostrarlo
            @endif
        });
    </script>
@endpush
