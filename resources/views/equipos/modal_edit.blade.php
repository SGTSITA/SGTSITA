<div
    class="modal fade"
    id="equipoEditModal-{{ $item->id }}"
    tabindex="-1"
    aria-labelledby="equipoModalLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-sm">
            <div class="modal-header text-white">
                <h5 class="modal-title">Editar Equipo: {{ $item->id_equipo }}</h5>
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
                data-tipo="{{ $item->tipo }}"
            >
                @csrf
                <input type="hidden" name="_method" value="PATCH" />
                <input type="hidden" name="tipo" value="{{ $item->tipo }}" />
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Folio</label>
                            <input type="text" name="id_equipo" class="form-control" value="{{ $item->id_equipo }}" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Fecha de Alta</label>
                            <input type="date" name="fecha" class="form-control" value="{{ $item->fecha }}" />
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Año</label>
                            <input type="number" name="year" class="form-control" value="{{ $item->year }}" />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Marca</label>
                            <input type="text" name="marca" class="form-control" value="{{ $item->marca }}" />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Modelo</label>
                            <input type="text" name="modelo" class="form-control" value="{{ $item->modelo }}" />
                        </div>

                        @if ($item->tipo !== 'Dolys')
                            <div class="col-md-6">
                                <label class="form-label">Motor</label>
                                <input type="text" name="motor" class="form-control" value="{{ $item->motor }}" />
                            </div>
                        @endif

                        <div class="col-md-6">
                            <label class="form-label">Placas</label>
                            <input type="text" name="placas" class="form-control" value="{{ $item->placas }}" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Número de Serie</label>
                            <input type="text" name="num_serie" class="form-control" value="{{ $item->num_serie }}" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Acceso</label>
                            <input type="text" name="acceso" class="form-control" value="{{ $item->acceso }}" />
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Tarjeta de Circulación</label>
                            <input type="file" name="tarjeta_circulacion" class="form-control" />

                            @if ($item->tarjeta_circulacion)
                                <small class="text-muted mt-1 d-block">
                                    Documento actual:
                                    <strong>{{ $item->tarjeta_circulacion }}</strong>
                                </small>
                            @endif
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Póliza de Seguro</label>
                            <input type="file" name="poliza_seguro" class="form-control" />

                            @if ($item->poliza_seguro)
                                <small class="text-muted mt-1 d-block">
                                    Documento actual:
                                    <strong>{{ $item->poliza_seguro }}</strong>
                                </small>
                            @endif
                        </div>

                        @if ($item->tipo === 'Chasis / Plataforma')
                            <div class="col-md-12">
                                <label class="form-label">Tipo</label>
                                <select name="folio" class="form-select">
                                    <option value="">Seleccione una opción</option>
                                    <option value="B9 40P" {{ $item->folio === 'B9 40P' ? 'selected' : '' }}>
                                        B9 40P
                                    </option>
                                    <option value="B10 20P" {{ $item->folio === 'B10 20P' ? 'selected' : '' }}>
                                        B10 20P
                                    </option>
                                    <option value="B11 20/40P" {{ $item->folio === 'B11 20/40P' ? 'selected' : '' }}>
                                        B11 20/40P
                                    </option>
                                    <option
                                        value="B12 Abatible"
                                        {{ $item->folio === 'B12 Abatible' ? 'selected' : '' }}
                                    >
                                        B12 Abatible
                                    </option>
                                    <option
                                        value="B13 Retractil"
                                        {{ $item->folio === 'B13 Retractil' ? 'selected' : '' }}
                                    >
                                        B13 Retractil
                                    </option>
                                </select>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>
