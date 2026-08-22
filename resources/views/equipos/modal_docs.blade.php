<div
    class="modal fade"
    id="documenotsdigitales-{{ $item->id }}"
    tabindex="-1"
    aria-labelledby="equipoModalLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-sm">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title">
                    Documentos del Equipo
                    <strong>#{{ $item->id_equipo }}</strong>
                </h5>
                <button
                    type="button"
                    class="btn-close btn-close-white"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                ></button>
            </div>

            <form
                method="POST"
                action="{{ route('update.equipos', $item->id) }}"
                enctype="multipart/form-data"
                class="form-editar-equipo"
                data-id="{{ $item->id }}"
            >
                @csrf
                <input type="hidden" name="_method" value="PATCH" />
                <input type="hidden" name="tipo" value="{{ $item->tipo }}" />

                <div class="modal-body">
                    <h5 class="text-center mb-4">Documentos Digitales</h5>

                    <div class="row g-4">
                        <!-- Tarjeta de Circulación -->
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header text-center bg-secondary text-white">
                                    <strong>Tarjeta de Circulación</strong>
                                </div>
                                <div class="card-body text-center">
                                    @if (! $item->tarjeta_circulacion)
                                        <div class="mb-3">
                                            <label class="form-label">Subir archivo *</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                                <input type="file" name="tarjeta_circulacion" class="form-control" />
                                            </div>
                                        </div>
                                    @else
                                        @php
                                            $ext = pathinfo($item->tarjeta_circulacion, PATHINFO_EXTENSION);
                                        @endphp

                                        @if ($ext === 'pdf')
                                            <iframe
                                                src="{{ asset('equipos/' . $item->tarjeta_circulacion) }}"
                                                style="width: 100%; height: 250px"
                                                class="rounded border"
                                            ></iframe>
                                        @elseif (in_array($ext, ['doc', 'docx']))
                                            <img
                                                src="{{ asset('assets/user/icons/docx.png') }}"
                                                alt="Documento Word"
                                                class="img-fluid"
                                                style="max-height: 150px"
                                            />
                                        @else
                                            <img
                                                src="{{ asset('equipos/' . $item->tarjeta_circulacion) }}"
                                                alt="Imagen"
                                                class="img-fluid rounded"
                                                style="max-height: 150px"
                                            />
                                        @endif
                                        <a
                                            href="{{ asset('equipos/' . $item->tarjeta_circulacion) }}"
                                            target="_blank"
                                            class="btn btn-sm mt-2 btn-outline-primary w-100"
                                        >
                                            Ver / Descargar
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Póliza de Seguro -->
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header text-center bg-secondary text-white">
                                    <strong>Póliza de Seguro</strong>
                                </div>
                                <div class="card-body text-center">
                                    @if (! $item->poliza_seguro)
                                        <div class="mb-3">
                                            <label class="form-label">Subir archivo *</label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="fas fa-file-invoice"></i>
                                                </span>
                                                <input type="file" name="poliza_seguro" class="form-control" />
                                            </div>
                                        </div>
                                    @else
                                        @php
                                            $ext = pathinfo($item->poliza_seguro, PATHINFO_EXTENSION);
                                        @endphp

                                        @if ($ext === 'pdf')
                                            <iframe
                                                src="{{ asset('equipos/' . $item->poliza_seguro) }}"
                                                style="width: 100%; height: 250px"
                                                class="rounded border"
                                            ></iframe>
                                        @elseif (in_array($ext, ['doc', 'docx']))
                                            <img
                                                src="{{ asset('assets/user/icons/docx.png') }}"
                                                alt="Documento Word"
                                                class="img-fluid"
                                                style="max-height: 150px"
                                            />
                                        @else
                                            <img
                                                src="{{ asset('equipos/' . $item->poliza_seguro) }}"
                                                alt="Imagen"
                                                class="img-fluid rounded"
                                                style="max-height: 150px"
                                            />
                                        @endif
                                        <a
                                            href="{{ asset('equipos/' . $item->poliza_seguro) }}"
                                            target="_blank"
                                            class="btn btn-sm mt-2 btn-outline-primary w-100"
                                        >
                                            Ver / Descargar
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times-circle"></i>
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
