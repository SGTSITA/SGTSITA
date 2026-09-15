@extends('layouts.app')

@section('template_title')
    Crear Bitácora - App Móvil SGT Logistics
@endsection

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
                <form action="{{ route('app-movil-admin.store') }}" method="POST">
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

@section('js')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            width: '100%'
        });
    });
</script>
@endsection
