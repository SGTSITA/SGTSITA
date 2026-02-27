@extends('layouts.externos-docs')

@section('content')
    <div class="card">

        {{-- HEADER --}}
        <div
            class="card-header d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between">
            <h3 class="card-title mb-3 mb-md-0">
                📁 Documentos compartidos del viaje {{ $DocDocumento->tipo_viaje ?? 'Sencillo' }}
            </h3>

            @if ($DocDocumento->tipo_viaje === 'Full')
                <a href="{{ route('externos.documentos.download.full', $token) }}" class="btn btn-danger btn-sm">
                    <i class="fas fa-file-archive me-2"></i>
                    Descargar todo (Full)
                </a>
            @endif

            {{-- Si hay un solo contenedor --}}
            @if (count($tabs) === 1)
                <div>
                    <div class="fw-semibold text-gray-600 fs-7">Contenedor</div>
                    <div class="fw-bold fs-6 text-primary">
                        {{ $tabs[0]['label'] ?? '—' }}
                    </div>
                </div>
            @endif
        </div>

        <div class="card-body">

            {{-- INFO GENERAL --}}
            <div class="row mb-10">
                <div class="col-md-3">
                    <div class="fw-semibold text-gray-600 fs-7">Proveedor</div>
                    <div class="fw-bold fs-6">{{ $DocDocumento->proveedor ?? '—' }}</div>
                </div>

                <div class="col-md-5">
                    <div class="fw-semibold text-gray-600 fs-7">Subcliente</div>
                    <div class="fw-bold fs-6">{{ $DocDocumento->subcliente ?? '—' }}</div>
                </div>

                <div class="col-md-3">
                    <div class="fw-semibold text-gray-600 fs-7">Cliente</div>
                    <div class="fw-bold fs-6">{{ $DocDocumento->cliente ?? '—' }}</div>
                </div>
            </div>

            {{-- TABS SOLO SI ES FULL --}}
            @if (count($tabs) > 1)
                <ul class="nav nav-tabs mb-5" role="tablist">
                    @foreach ($tabs as $i => $tab)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $i === 0 ? 'active' : '' }}" data-bs-toggle="tab"
                                data-bs-target="#{{ $tab['id'] }}" type="button" role="tab">
                                📦 {{ $tab['label'] }}
                            </button>
                        </li>
                    @endforeach
                </ul>
            @endif

            {{-- CONTENIDO DE TABS --}}
            <div class="tab-content">

                @foreach ($tabs as $i => $tab)
                    <div class="tab-pane fade {{ $i === 0 ? 'show active' : '' }}" id="{{ $tab['id'] }}"
                        role="tabpanel">
                        <div class="contenedor-docs" data-doc-id="{{ $tab['id'] }}">

                            {{-- ACCIONES MASIVAS --}}
                            <div class="mb-4 d-none accionesMasivas">

                                <button class="btn btn-success btn-sm btnDescargarZip">
                                    <i class="fas fa-file-archive me-2"></i>
                                    Descargar seleccionados
                                </button>
                            </div>

                            {{-- TABLA --}}
                            <div class="table-responsive">
                                <table class="table align-middle table-row-dashed fs-6 gy-5 tablaDocumentos">
                                    <thead>
                                        <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                                            <th class="w-10px">
                                                <div class="form-check form-check-sm form-check-custom form-check-solid">
                                                    <input class="form-check-input checkAll" type="checkbox"
                                                        data-doc-id="{{ $tab['id'] }}">
                                                </div>
                                            </th>
                                            <th class="text-center">Archivo</th>
                                            <th class="d-none d-md-table-cell">Tipo</th>
                                            <th class="d-none d-md-table-cell">Fecha</th>
                                            <th class="text-end">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($tab['documentos'] as $doc)
                                            <tr>
                                                <td>
                                                    <div
                                                        class="form-check form-check-sm form-check-custom form-check-solid">
                                                        <input class="form-check-input check-doc" type="checkbox"
                                                            value="{{ $doc['fileCode'] }}"
                                                            data-doc-id="{{ $tab['id'] }}>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="fw-semibold">{{ $doc['secondaryFileName'] }}
                                                    </div>
                                                    <div class="d-md-none text-muted fs-7">
                                                        {{ $doc['fileType'] }}
                                                    </div>
                                                </td>
                                                <td class="d-none d-md-table-cell">{{ $doc['fileType'] }}</td>
                                                <td class="d-none d-md-table-cell">{{ $doc['fileDate'] }}</td>
                                                <td class="text-end">
                                                    <a href="{{ url("/externos/documentos/$token/download/{$doc['identifier']}/{$doc['filePath']}") }}"
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
                @endforeach

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



        document.addEventListener('change', e => {

            // SELECT ALL por contenedor
            if (e.target.classList.contains('checkAll')) {
                const contenedor = e.target.closest('.contenedor-docs');

                contenedor.querySelectorAll('.check-doc')
                    .forEach(c => c.checked = e.target.checked);

                actualizarAcciones(contenedor);
            }

            // CHECK individual
            if (e.target.classList.contains('check-doc')) {
                const contenedor = e.target.closest('.contenedor-docs');
                actualizarAcciones(contenedor);
            }
        });

        function actualizarAcciones(contenedor) {
            const checks = contenedor.querySelectorAll('.check-doc:checked');
            const acciones = contenedor.querySelector('.accionesMasivas');
            const btnZip = contenedor.querySelector('.btnDescargarZip');

            if (checks.length < 2) {
                acciones.classList.add('d-none');
                btnZip.disabled = true;
                return;
            }

            acciones.classList.remove('d-none');
            btnZip.disabled = false;
        }



        document.addEventListener('click', e => {
            if (!e.target.classList.contains('btnDescargarZip')) return;

            const contenedor = e.target.closest('.contenedor-docs');

            const archivos = Array.from(
                contenedor.querySelectorAll('.check-doc:checked')
            ).map(el => ({
                fileCode: el.value
            }));

            if (archivos.length < 2) return;

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/externos/documentos/{{ $token }}/download-zip`;

            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = document.querySelector('meta[name="csrf-token"]').content;

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'files';
            input.value = JSON.stringify(archivos);

            form.appendChild(csrf);
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
            form.remove();
        });
    </script>
@endpush
