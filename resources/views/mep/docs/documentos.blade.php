@extends('layouts.externos-docs')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                📁 Documentos compartidos
            </h3>
            <div class="col-md-3">
                <div class="fw-semibold text-gray-600 fs-7">Contenedor</div>
                <div class="fw-bold fs-6 text-primary">
                    {{ $DocDocumento->num_contenedor ?? '—' }}
                </div>
            </div>
        </div>

        <div class="card-body">


            <div class="row mb-6">
                <div class="col-md-3">
                    <div class="fw-semibold text-gray-600 fs-7">Proveedor</div>
                    <div class="fw-bold fs-6">
                        {{ $DocDocumento->proveedor ?? '—' }}
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="fw-semibold text-gray-600 fs-7">Subcliente</div>
                    <div class="fw-bold fs-6">
                        {{ $DocDocumento->subcliente ?? '—' }}
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="fw-semibold text-gray-600 fs-7">Cliente</div>
                    <div class="fw-bold fs-6">
                        {{ $DocDocumento->cliente ?? '—' }}
                    </div>
                </div>


            </div>

            <div class="mb-4 d-none" id="accionesMasivas">
                <button class="btn btn-success btn-sm" id="btnDescargarZip">
                    <i class="fas fa-file-archive me-2"></i>
                    Descargar seleccionados
                </button>
            </div>

            <div class="table-responsive">
                <table id="tablaDocumentos" class="table align-middle table-row-dashed fs-6 gy-5">
                    <thead>
                        <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                            <th class="w-10px">
                                <div class="form-check form-check-sm form-check-custom form-check-solid">
                                    <input class="form-check-input" type="checkbox" id="checkAll">
                                </div>
                            </th>
                            <th>Archivo</th>
                            <th class="d-none d-md-table-cell">Tipo</th>
                            <th class="d-none d-md-table-cell">Tamaño</th>
                            <th class="d-none d-md-table-cell">Fecha</th>
                            <th class="text-end">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($documentos as $doc)
                            <tr>
                                <td>
                                    <div class="form-check form-check-sm form-check-custom form-check-solid">
                                        <input class="form-check-input check-doc" type="checkbox"
                                            value="{{ $doc['fileCode'] }}">
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $doc['secondaryFileName'] }}</div>
                                    <div class="d-md-none text-muted fs-7">
                                        {{ $doc['fileType'] }} · {{ $doc['fileSize'] }}
                                    </div>
                                </td>
                                <td class="d-none d-md-table-cell">{{ $doc['fileType'] }}</td>
                                <td class="d-none d-md-table-cell">{{ $doc['fileSize'] }}</td>
                                <td class="d-none d-md-table-cell">{{ $doc['fileDate'] }}</td>
                                <td class="text-end">
                                    <a href="{{ url("/externos/documentos/$token/download/" . $doc['filePath']) }}"
                                        class="btn btn-sm btn-primary">
                                        Descargar
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        setInterval(async () => {
            try {
                const res = await fetch('/externos/acceso/validar/{{ $token }}');
                const data = await res.json();

                if (!data.activo) {
                    swal.fire({
                        icon: 'warning',
                        title: 'Acceso revocado',
                        text: 'El acceso a los documentos ha sido revocado.',
                    }).then(() => {
                        location.reload();
                    });

                }
            } catch (e) {
                location.reload();
            }
        }, 30000);




        const checkAll = document.getElementById('checkAll');
        const accionesMasivas = document.getElementById('accionesMasivas');
        const btnZip = document.getElementById('btnDescargarZip');

        function actualizarAcciones() {
            const checks = document.querySelectorAll('.check-doc:checked');
            accionesMasivas.classList.toggle('d-none', checks.length < 2);
        }

        // Check / uncheck todos
        checkAll.addEventListener('change', () => {
            document.querySelectorAll('.check-doc').forEach(c => {
                c.checked = checkAll.checked;
            });
            actualizarAcciones();
        });

        // Check individual
        document.addEventListener('change', e => {
            if (e.target.classList.contains('check-doc')) {
                actualizarAcciones();
            }
        });


        btnZip.addEventListener('click', () => {
            let archivos = [];

            archivos = Array.from(document.querySelectorAll('.check-doc:checked'))
                .map(el => ({
                    fileCode: el.value
                }));

            if (archivos.length < 2) return;

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/externos/documentos/{{ $token }}/download-zip`;

            // CSRF desde meta (más limpio)
            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = document
                .querySelector('meta[name="csrf-token"]').content;

            form.appendChild(csrf);

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'files';
            input.value = JSON.stringify(archivos);
            form.appendChild(input);

            document.body.appendChild(form);
            form.submit();
            form.remove();
        });
    </script>
@endpush
