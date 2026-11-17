@extends('layouts.app')

@section('template_title')
    Crear Bitácora - App Móvil SGT Logistics
@endsection
@section('disable_simple_alert', 'true')

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header pb-0">
                <div class="d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">Vincular Nueva Bitácora de Viaje</h5>
                    <a href="{{ route('app-movil-admin.index') }}" class="btn btn-sm btn-secondary mb-0">
                        Regresar
                    </a>
                </div>
            </div>

            <div class="card-body">
                @if ($errors->any())
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const errorList = @json($errors->all());
                            Swal.fire({
                                icon: 'error',
                                title: 'Error de validación',
                                html: '<ul class="text-start mb-0 ps-3">' + errorList.map(e => `<li>${e}</li>`).join('') + '</ul>',
                                confirmButtonColor: '#5e72e4',
                                confirmButtonText: 'Entendido'
                            });
                        });
                    </script>
                @endif
                @if (session('error'))
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: "{{ session('error') }}",
                                confirmButtonColor: '#5e72e4',
                                confirmButtonText: 'Entendido'
                            });
                        });
                    </script>
                @endif

                <form id="formCrearBitacora" action="{{ route('app-movil-admin.store') }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label for="id_asignacion" class="form-control-label">Seleccionar Viaje Asignado (Que no posea bitácora)</label>
                        <select name="id_asignacion" id="id_asignacion" class="form-control select2" required>
                            <option value="">-- Seleccione una Asignación --</option>
                            @foreach ($asignaciones as $asignacion)
                                <option value="{{ $asignacion->id }}">
                                    Asignación #{{ $asignacion->id }} - Operador: {{ $asignacion->Operador?->nombre ?? 'N/A' }} - Contenedor: {{ $asignacion->Contenedor?->num_contenedor ?? 'N/A' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="text-end mt-4">
                        <button type="submit" class="btn text-white" style="background: {{ $configuracion->color_boton_save ?? '#2dce89' }}">
                            <i class="fa fa-save"></i> Generar Registro Bitácora
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('datatable')
<script src="{{ asset('assets/vendor/select2/dist/js/select2.min.js') }}"></script>
<script>
    $(document).ready(function() {
        if ($.fn.select2) {
            $('.select2').select2({
                width: '100%'
            });
        }

        $('#formCrearBitacora').on('submit', function(e) {
            const asig = $('#id_asignacion').val();
            if (!asig) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Campo Requerido',
                    text: 'Por favor seleccione una asignación de viaje.',
                    confirmButtonColor: '#5e72e4'
                });
                return false;
            }

            Swal.fire({
                title: 'Vinculando bitácora...',
                html: '<div class="py-2 text-center"><p class="text-sm text-secondary mb-0">Generando registro de bitácora y preparando viaje móvil. Por favor espere...</p></div>',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        });
    });
</script>
@endsection
