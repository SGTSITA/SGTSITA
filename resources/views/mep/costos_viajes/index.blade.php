@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <div class="card p-3">
            <h4 class="mb-3">Costos Viajes</h4>

            <div id="hot" class="handsontable mb-4"></div>

            <div class="d-flex justify-content-end">
                <button id="guardar" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1">
                    <i class="fas fa-save me-1"></i> Guardar Cambios de costo
                </button>
            </div>

        </div>
    </div>
@endsection


@push('custom-javascript')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/handsontable/dist/handsontable.full.min.css">
    <script src="https://cdn.jsdelivr.net/npm/handsontable/dist/handsontable.full.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const container = document.getElementById('hot');

            const hot = new Handsontable(container, {
                data: @json($data),
                colHeaders: ['# Contenedor', 'Subcliente', 'Tipo de viaje', 'Estatus', 'Carta Porte',
                    'XML CP', 'Base 1', 'Base 2'
                ],
                columns: [{
                        data: 'num_contenedor',
                        readOnly: true
                    },
                    {
                        data: 'subcliente',
                        readOnly: true
                    },
                    {
                        data: 'tipo_viaje',
                        readOnly: true
                    },
                    {
                        data: 'estatus',
                        readOnly: true
                    },
                    {
                        data: 'carta_porte',
                        readOnly: true,
                        renderer: (instance, td, row, col, prop, value) => {
                            td.innerHTML = value ?
                                '<i class="fas fa-circle-check text-success"></i>' :
                                '<i class="fas fa-circle-xmark text-secondary"></i>';
                        }
                    },
                    {
                        data: 'carta_porte_xml',
                        readOnly: true,
                        renderer: (instance, td, row, col, prop, value) => {
                            td.innerHTML = value ?
                                '<i class="fas fa-circle-check text-success"></i>' :
                                '<i class="fas fa-circle-xmark text-secondary"></i>';
                        }
                    },
                    {
                        data: 'base1',
                        type: 'numeric',
                        numericFormat: {
                            pattern: '0,0.[000000]',
                            culture: 'es-MX'
                        }
                    },
                    {
                        data: 'base2',
                        type: 'numeric',
                        numericFormat: {
                            pattern: '0,0.[000000]',
                            culture: 'es-MX'
                        }
                    }
                ],
                licenseKey: 'non-commercial-and-evaluation',
                stretchH: 'all',
                height: 'auto',
                rowHeaders: true,
                filters: true,
                dropdownMenu: true,
            });

            document.getElementById('guardar').addEventListener('click', () => {
                const datos = hot.getData().map((row, i) => {
                    const dataAtRow = hot.getSourceDataAtRow(i);
                    return {
                        viaje_id: dataAtRow.viaje_id,
                        base1: dataAtRow.base1,
                        base2: dataAtRow.base2,
                    };
                });

                fetch("{{ route('costos-viajes.guardar') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(
                            datos
                        )
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('Error en la petición');
                        return response.json();
                    })
                    .then(resp => {
                        if (resp.success) {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Guardado!',
                                text: 'Los costos fueron guardados correctamente.',
                                confirmButtonText: 'Aceptar'
                            });
                        } else {
                            throw new Error(resp.message || 'Ocurrió un error al guardar');
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: error.message || 'Error al guardar los datos',
                            confirmButtonText: 'Cerrar'
                        });
                    });
            });
        });
    </script>
@endpush
