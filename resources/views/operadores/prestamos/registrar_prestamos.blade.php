@extends('layouts.app')

@section('template_title')
    Prestamos a Operadores
@endsection

@section('content')
    <style>
        .btn-abonar {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 4px 8px;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-abonar:hover {
            background-color: #218838;
        }
    </style>


    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-sm-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-light text-dark d-flex justify-content-between align-items-center">

                        <h5 class="mb-0">
                            Resumen de prestamos/adelantos por Operador
                        </h5>

                        <div class="d-flex gap-2">

                            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalNuevoPrestamo">
                                <i class="bi bi-plus-circle me-1"></i>
                                Nuevo Prestamo/Adelanto
                            </button>

                            <button class="btn btn-primary btn-sm" id="btnRecargarGrid">
                                <i class="bi bi-arrow-repeat me-1"></i>

                            </button>

                        </div>

                    </div>
                    <div class="card-body">
                        <div id="gridPrestamosActivos" class="ag-theme-alpine" style="height: 650px;  border-radius:8px;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('operadores.prestamos.modal-registrar-prestamo')
@endsection


@push('custom-javascript')
    <script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>
    <script
        src="{{ asset('js/sgt/operadores/prestamos.js') }}?v={{ filemtime(public_path('js/sgt/operadores/prestamos.js')) }}">
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
    <script>
        flatpickr(".dateInput", {
            dateFormat: "d/m/Y",
            locale: "es"
        });
        $(function() {

            $('#btnGuardarPrestamoModal').on('click', function() {

                let invalid = false;

                const id_operador = $('#modal_id_operador').val();
                const id_banco = $('#modal_id_banco').val();
                const cantidad = $('#modal_cantidad').val();
                const FechaAplicacion = $('#modal_FechaAplicacion').val();
                const tipo = $('#modal_tipo').val();

                $('.is-invalid').removeClass('is-invalid');

                if (!id_operador) {
                    $('#modal_id_operador').addClass('is-invalid');
                    invalid = true;
                }

                if (!id_banco) {
                    $('#modal_id_banco').addClass('is-invalid');
                    invalid = true;
                }

                if (!cantidad || Number(cantidad) <= 0) {
                    $('#modal_cantidad').addClass('is-invalid');
                    invalid = true;
                }

                if (invalid) return;

                $.ajax({
                    url: '/prestamos/store',
                    method: 'POST',
                    data: {
                        id_operador,
                        id_banco,
                        cantidad,
                        FechaAplicacion,
                        tipo,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(resp) {

                        if (resp.success === false) {
                            Swal.fire('Error', resp.Mensaje, 'error');
                            return;
                        }

                        Swal.fire('Guardado correctamente', '', 'success');

                        $('#modalNuevoPrestamo').modal('hide');
                        $('#formPrestamoModal')[0].reset();

                        // Aquí puedes recargar grid
                        $('#btnRecargarGrid').click();
                    }
                });

            });

        });
    </script>
@endpush
