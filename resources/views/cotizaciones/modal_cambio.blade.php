@if ($cotizacion->DocCotizacion->Asignaciones)
    <div
        class="modal fade"
        id="cambioModal{{ $cotizacion->DocCotizacion->Asignaciones->id }}"
        tabindex="-1"
        aria-labelledby="exampleModalLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cambio de tipo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form
                        method="POST"
                        action="{{ route('update_cambio.cotizaciones', $cotizacion->DocCotizacion->Asignaciones->id) }}"
                        id=""
                        enctype="multipart/form-data"
                        role="form"
                    >
                        <input type="hidden" name="_method" value="PATCH" />
                        @csrf
                        <div>
                            <!-- Radiobuttons -->
                            <label>
                                <input
                                    type="radio"
                                    name="formType{{ $cotizacion->DocCotizacion->Asignaciones->id }}"
                                    value="propio"
                                    id="propio{{ $cotizacion->DocCotizacion->Asignaciones->id }}"
                                    checked
                                />
                                Propio
                            </label>
                            <label>
                                <input
                                    type="radio"
                                    name="formType{{ $cotizacion->DocCotizacion->Asignaciones->id }}"
                                    value="subcontratado"
                                    id="subcontratado{{ $cotizacion->DocCotizacion->Asignaciones->id }}"
                                />
                                Subcontratado
                            </label>
                        </div>

                        <!-- Formulario Propio -->
                        <div id="formPropio{{ $cotizacion->DocCotizacion->Asignaciones->id }}" style="display: none">
                            <!-- Campos del formulario de propio -->
                            @include('cotizaciones.formulario_propio')
                            <!-- Agrega más campos según sea necesario -->
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    Cancelar
                                </button>
                                <button type="submit" class="btn btn-primary">Guardar</button>
                            </div>
                        </div>

                        <!-- Formulario Subcontratado -->
                        <div
                            id="formSubcontratado{{ $cotizacion->DocCotizacion->Asignaciones->id }}"
                            style="display: none"
                        >
                            @include('cotizaciones.formulario_subcontratado')
                            <!-- Agrega más campos según sea necesario -->
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    Cancelar
                                </button>
                                <button type="submit" class="btn btn-primary">Guardar</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endif
