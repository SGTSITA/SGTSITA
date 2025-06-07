<div class="modal fade" id="asignarGpsModal-{{ $item->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Asignar GPS a {{ $item->id_equipo }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                @foreach ($gps_companies as $gps)
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input switch-toggle-gps" type="checkbox"
                            data-equipo-id="{{ $item->id }}" data-gps-id="{{ $gps->id }}"
                            id="gps_toggle_{{ $item->id }}_{{ $gps->id }}"
                            {{ $item->gps_company_id == $gps->id ? 'checked' : '' }}>
                        <label class="form-check-label" for="gps_toggle_{{ $item->id }}_{{ $gps->id }}">
                            {{ $gps->nombre }} <small class="text-muted">({{ $gps->url }})</small>
                        </label>
                    </div>
                @endforeach


            </div>
        </div>
    </div>
</div>
