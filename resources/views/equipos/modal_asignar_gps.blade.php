<div class="modal fade" id="asignarGpsModal-{{ $item->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <form method="POST" action="{{ route('equipos.asignarGps', $item->id) }}" class="form-asignar-gps">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Asignar GPS a {{ $item->id_equipo }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    {{-- Select de proveedor GPS --}}
                    <div class="mb-3">
                        <label for="gps_company_{{ $item->id }}" class="form-label">Proveedor GPS</label>
                        <select class="form-select" name="gps_company_id" id="gps_company_{{ $item->id }}">
                            <option value="">-- Selecciona un proveedor --</option>
                            @foreach ($gps_companies as $gps)
                                <option
                                    value="{{ $gps->id }}"
                                    {{ $item->gps_company_id == $gps->id ? 'selected' : '' }}
                                >
                                    {{ $gps->nombre }} ({{ $gps->url }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Campo de IMEI --}}
                    <div class="mb-3">
                        <label for="imei_{{ $item->id }}" class="form-label">IMEI del dispositivo</label>
                        <input
                            type="text"
                            class="form-control"
                            name="imei"
                            id="imei_{{ $item->id }}"
                            value="{{ $item->imei ?? '' }}"
                            placeholder="Ej. 352094083132191"
                        />
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>
