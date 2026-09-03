@extends('layouts.app')

@section('template_title')
    Editar Bitácora - App Móvil SGT Logistics
@endsection

@section('content')
<div class="row">
    <div class="col-md-11 mx-auto">
        <div class="card">
            <div class="card-header pb-0">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="mb-0">Administración de Bitácora de Viaje: Asignación #{{ $bitacora->id_asignacion }}</h5>
                        <p class="text-sm text-muted mb-0">
                            Operador: <strong>{{ $bitacora->Asignacion?->Operador?->nombre ?? 'N/A' }}</strong> | 
                            Contenedor: <strong>{{ $bitacora->Asignacion?->Contenedor?->num_contenedor ?? 'N/A' }}</strong>
                        </p>
                    </div>
                    <a href="{{ route('app-movil-admin.index') }}" class="btn btn-sm btn-secondary mb-0">
                        Regresar
                    </a>
                </div>
            </div>

            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger text-white">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Navigation Tabs styled like standard bootstrap tabs -->
                <ul class="nav nav-tabs mb-4" id="bitacoraTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="combustible-tab" data-bs-toggle="tab" data-bs-target="#combustible" type="button" role="tab" aria-controls="combustible" aria-selected="true">
                            <i class="fa fa-gas-pump"></i> 1. Carga de Diésel y Urea
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="carga-tab" data-bs-toggle="tab" data-bs-target="#carga" type="button" role="tab" aria-controls="carga" aria-selected="false">
                            <i class="fa fa-truck-loading"></i> 2. Inicio Viaje (Carga Contenedor)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="entrega-tab" data-bs-toggle="tab" data-bs-target="#entrega" type="button" role="tab" aria-controls="entrega" aria-selected="false">
                            <i class="fa fa-check-circle"></i> 3. Conclusión de Viaje
                        </button>
                    </li>
                </ul>

                <form action="{{ route('app-movil-admin.update', $bitacora->id) }}" method="POST" enctype="multipart/form-data" id="bitacoraForm">
                    @csrf
                    @method('PUT')

                    <div class="tab-content" id="bitacoraTabsContent">
                        
                        <!-- TAB 1: COMBUSTIBLE (DIESEL Y UREA) -->
                        <div class="tab-pane fade show active" id="combustible" role="tabpanel" aria-labelledby="combustible-tab">
                            <div class="row">
                                <div class="col-md-6 border-end">
                                    <h6 class="text-uppercase text-body text-xs font-weight-bolder mb-3">Datos de Diésel</h6>
                                    @if($dieselPagado)
                                        <div class="alert alert-warning text-white p-2" role="alert">
                                            <strong><i class="fa fa-warning"></i> Advertencia:</strong> El Diésel ya tiene un gasto <strong>PAGADO</strong>.
                                            <div class="form-check form-switch mt-2">
                                                <input class="form-check-input" type="checkbox" name="forzar_pago_diesel" id="forzar_pago_diesel" value="1">
                                                <label class="form-check-label text-white" for="forzar_pago_diesel">Forzar sobrescritura del costo</label>
                                            </div>
                                        </div>
                                    @endif
                                    <div class="form-group">
                                        <label for="litros" class="form-control-label">Litros de Diésel</label>
                                        <input class="form-control" type="number" step="0.01" name="litros" id="litros" value="{{ old('litros', $bitacora->litros) }}">
                                    </div>
                                    <div class="form-group">
                                        <label for="costo" class="form-control-label">Costo Diésel Total ($)</label>
                                        <input class="form-control" type="number" step="0.01" name="costo" id="costo" value="{{ old('costo', $bitacora->costo) }}">
                                    </div>
                                    <div class="form-group">
                                        <label for="comprobante_diesel_file" class="form-control-label">Comprobante de Diésel (Foto del Ticket)</label>
                                        <input class="form-control" type="file" name="comprobante_diesel_file" id="comprobante_diesel_file" accept="image/*">
                                        @if($bitacora->comprobante)
                                            @php $dieselImgs = json_decode($bitacora->comprobante, true) ?: [$bitacora->comprobante]; @endphp
                                            <div class="mt-2">
                                                @foreach($dieselImgs as $img)
                                                    <a href="{{ asset($img) }}" target="_blank">
                                                        <img src="{{ asset($img) }}" alt="Comprobante Diesel" style="max-height: 80px;" class="img-thumbnail me-1">
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <h6 class="text-uppercase text-body text-xs font-weight-bolder mb-3">Datos de Urea</h6>
                                    @if($ureaPagada)
                                        <div class="alert alert-warning text-white p-2" role="alert">
                                            <strong><i class="fa fa-warning"></i> Advertencia:</strong> La Urea ya tiene un gasto <strong>PAGADO</strong>.
                                            <div class="form-check form-switch mt-2">
                                                <input class="form-check-input" type="checkbox" name="forzar_pago_urea" id="forzar_pago_urea" value="1">
                                                <label class="form-check-label text-white" for="forzar_pago_urea">Forzar sobrescritura del costo</label>
                                            </div>
                                        </div>
                                    @endif
                                    <div class="form-group">
                                        <label for="litros_urea" class="form-control-label">Litros de Urea</label>
                                        <input class="form-control" type="number" step="0.01" name="litros_urea" id="litros_urea" value="{{ old('litros_urea', $bitacora->litros_urea) }}">
                                    </div>
                                    <div class="form-group">
                                        <label for="costo_urea" class="form-control-label">Costo Urea Total ($)</label>
                                        <input class="form-control" type="number" step="0.01" name="costo_urea" id="costo_urea" value="{{ old('costo_urea', $bitacora->costo_urea) }}">
                                    </div>
                                    <div class="form-group">
                                        <label for="comprobante_urea_file" class="form-control-label">Comprobante de Urea (Foto del Ticket)</label>
                                        <input class="form-control" type="file" name="comprobante_urea_file" id="comprobante_urea_file" accept="image/*">
                                        @if($bitacora->comprobante_urea)
                                            @php $ureaImgs = json_decode($bitacora->comprobante_urea, true) ?: [$bitacora->comprobante_urea]; @endphp
                                            <div class="mt-2">
                                                @foreach($ureaImgs as $img)
                                                    <a href="{{ asset($img) }}" target="_blank">
                                                        <img src="{{ asset($img) }}" alt="Comprobante Urea" style="max-height: 80px;" class="img-thumbnail me-1">
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="odometro" class="form-control-label">Kilometraje / Odómetro</label>
                                        <input class="form-control" type="number" step="0.01" name="odometro" id="odometro" value="{{ old('odometro', $bitacora->odometro) }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="latitud" class="form-control-label">Latitud Carga Diésel/Urea</label>
                                        <input class="form-control" type="text" name="latitud" id="latitud" value="{{ old('latitud', $bitacora->latitud) }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="longitud" class="form-control-label">Longitud Carga Diésel/Urea</label>
                                        <input class="form-control" type="text" name="longitud" id="longitud" value="{{ old('longitud', $bitacora->longitud) }}">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <button class="btn btn-sm btn-info text-white" type="button" onclick="abrirModalMapa('latitud', 'longitud')">
                                        <i class="fa fa-map-marker-alt"></i> Localizar Carga Combustible
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 2: INICIO VIAJE (CARGA CONTENEDOR) -->
                        <div class="tab-pane fade" id="carga" role="tabpanel" aria-labelledby="carga-tab">
                            <h6 class="text-uppercase text-body text-xs font-weight-bolder mb-3">Coordenadas y Evidencias de Inicio de Viaje</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="latitud_carga" class="form-control-label">Latitud de Inicio</label>
                                        <input class="form-control" type="text" name="latitud_carga" id="latitud_carga" value="{{ old('latitud_carga', $bitacora->latitud_carga) }}">
                                    </div>
                                    <div class="form-group">
                                        <label for="longitud_carga" class="form-control-label">Longitud de Inicio</label>
                                        <input class="form-control" type="text" name="longitud_carga" id="longitud_carga" value="{{ old('longitud_carga', $bitacora->longitud_carga) }}">
                                    </div>
                                    <button class="btn btn-sm btn-info text-white mb-3" type="button" onclick="abrirModalMapa('latitud_carga', 'longitud_carga')">
                                        <i class="fa fa-map-marker-alt"></i> Seleccionar en Mapa / Google Maps URL
                                    </button>

                                    <div class="form-group mt-3">
                                        <label class="form-control-label">Fotos de Carga (Inicio Viaje)</label>
                                        <input class="form-control" type="file" name="fotos_carga_files[]" multiple accept="image/*">
                                        @if($bitacora->fotos_carga)
                                            @php $cargaImgs = json_decode($bitacora->fotos_carga, true) ?: []; @endphp
                                            <div class="mt-2 row">
                                                @foreach($cargaImgs as $img)
                                                    <div class="col-4 text-center mb-2">
                                                        <a href="{{ asset($img) }}" target="_blank">
                                                            <img src="{{ asset($img) }}" alt="Foto Carga" style="max-height: 80px;" class="img-thumbnail d-block mx-auto mb-1">
                                                        </a>
                                                        <div class="form-check d-inline-block">
                                                            <input class="form-check-input" type="checkbox" name="eliminar_fotos_carga[]" value="{{ $img }}" id="eliminar_carga_{{ $loop->index }}">
                                                            <label class="form-check-label text-xs" for="eliminar_carga_{{ $loop->index }}">Eliminar</label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6 text-center">
                                    <p class="text-sm text-muted">Las evidencias fotográficas deben cargarse al inicio del contenedor por parte del operador.</p>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 3: CONCLUSIÓN DE VIAJE -->
                        <div class="tab-pane fade" id="entrega" role="tabpanel" aria-labelledby="entrega-tab">
                            <h6 class="text-uppercase text-body text-xs font-weight-bolder mb-3">Coordenadas y Evidencias de Conclusión de Viaje</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="latitud_fin" class="form-control-label">Latitud de Fin</label>
                                        <input class="form-control" type="text" name="latitud_fin" id="latitud_fin" value="{{ old('latitud_fin', $bitacora->latitud_fin) }}">
                                    </div>
                                    <div class="form-group">
                                        <label for="longitud_fin" class="form-control-label">Longitud de Fin</label>
                                        <input class="form-control" type="text" name="longitud_fin" id="longitud_fin" value="{{ old('longitud_fin', $bitacora->longitud_fin) }}">
                                    </div>
                                    <button class="btn btn-sm btn-info text-white mb-3" type="button" onclick="abrirModalMapa('latitud_fin', 'longitud_fin')">
                                        <i class="fa fa-map-marker-alt"></i> Seleccionar en Mapa / Google Maps URL
                                    </button>

                                    <div class="form-group mt-3">
                                        <label class="form-control-label">Fotos de Entrega (Fin Viaje)</label>
                                        <input class="form-control" type="file" name="fotos_fin_files[]" multiple accept="image/*">
                                        @if($bitacora->fotos_fin)
                                            @php $finImgs = json_decode($bitacora->fotos_fin, true) ?: []; @endphp
                                            <div class="mt-2 row">
                                                @foreach($finImgs as $img)
                                                    <div class="col-4 text-center mb-2">
                                                        <a href="{{ asset($img) }}" target="_blank">
                                                            <img src="{{ asset($img) }}" alt="Foto Fin" style="max-height: 80px;" class="img-thumbnail d-block mx-auto mb-1">
                                                        </a>
                                                        <div class="form-check d-inline-block">
                                                            <input class="form-check-input" type="checkbox" name="eliminar_fotos_fin[]" value="{{ $img }}" id="eliminar_fin_{{ $loop->index }}">
                                                            <label class="form-check-label text-xs" for="eliminar_fin_{{ $loop->index }}">Eliminar</label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6 text-center">
                                    <p class="text-sm text-muted">Las evidencias fotográficas deben cargarse al finalizar la entrega del contenedor.</p>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="text-end mt-4">
                        <button type="submit" class="btn text-white" style="background: {{ $configuracion->color_boton_save ?? '#2dce89' }}">
                            <i class="fa fa-save"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Selector de Coordenadas y Link de Google Maps -->
<div class="modal fade" id="mapModal" tabindex="-1" role="dialog" aria-labelledby="mapModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mapModalLabel">Localizar Coordenadas</h5>
                <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="direccionInput" class="form-control-label">Dirección, Buscar o pegar link de Google Maps y presionar Enter</label>
                    <input type="text" id="direccionInput" class="form-control" placeholder="Buscar dirección o pegar url (ej: https://maps.app.goo.gl/...)" autocomplete="off">
                </div>
                
                <div id="map" style="width: 100%; height: 400px; border-radius: 8px;" class="mb-3"></div>
                
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label class="form-control-label">Latitud Detectada</label>
                            <input type="text" id="mapLat" class="form-control" readonly>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label class="form-control-label">Longitud Detectada</label>
                            <input type="text" id="mapLng" class="form-control" readonly>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="confirmarCoordenadasSeleccionadas()">Seleccionar ubicación</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('datatable')
<!-- Cargar librería de Google Maps -->
<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_KEY') }}&libraries=places"></script>
<script>
    let map;
    let marker;
    let activeLatId = '';
    let activeLngId = '';

    function abrirModalMapa(latInputId, lngInputId) {
        activeLatId = latInputId;
        activeLngId = lngInputId;

        const modal = new bootstrap.Modal(document.getElementById('mapModal'));
        modal.show();

        // Obtener coordenadas de los inputs actuales, si están definidos
        let latVal = parseFloat(document.getElementById(latInputId).value);
        let lngVal = parseFloat(document.getElementById(lngInputId).value);

        if (isNaN(latVal) || isNaN(lngVal)) {
            // Centro por defecto: Ciudad de México o el centro del país
            latVal = 19.4326;
            lngVal = -99.1332;
        }

        document.getElementById('mapLat').value = latVal;
        document.getElementById('mapLng').value = lngVal;

        setTimeout(() => {
            inicializarMapa(latVal, lngVal);
        }, 300);
    }

    function inicializarMapa(lat, lng) {
        const centerLatLng = { lat: lat, lng: lng };
        map = new google.maps.Map(document.getElementById('map'), {
            zoom: 15,
            center: centerLatLng
        });

        marker = new google.maps.Marker({
            position: centerLatLng,
            map: map,
            draggable: true
        });

        // Eventos del marker
        marker.addListener('dragend', function() {
            const pos = marker.getPosition();
            document.getElementById('mapLat').value = pos.lat();
            document.getElementById('mapLng').value = pos.lng();
        });

        // Clic en el mapa para posicionar
        map.addListener('click', function(e) {
            marker.setPosition(e.latLng);
            document.getElementById('mapLat').value = e.latLng.lat();
            document.getElementById('mapLng').value = e.latLng.lng();
        });

        // Autocomplete
        const input = document.getElementById('direccionInput');
        const autocomplete = new google.maps.places.Autocomplete(input);
        autocomplete.bindTo('bounds', map);

        autocomplete.addListener('place_changed', function() {
            const place = autocomplete.getPlace();
            if (!place.geometry || !place.geometry.location) {
                return;
            }
            map.setCenter(place.geometry.location);
            marker.setPosition(place.geometry.location);
            document.getElementById('mapLat').value = place.geometry.location.lat();
            document.getElementById('mapLng').value = place.geometry.location.lng();
        });

        // Keydown Enter para links cortos de Google Maps
        input.addEventListener('keydown', async function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const urlVal = input.value.trim();
                if (urlVal.startsWith('http://') || urlVal.startsWith('https://')) {
                    const _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    try {
                        const response = await fetch('/coordenadas/resolver-link-google', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': _token,
                                Accept: 'application/json',
                            },
                            body: JSON.stringify({
                                shortUrl: urlVal
                            }),
                        });

                        const data = await response.json();
                        if (data.lat && data.lng) {
                            const newLatLng = { lat: parseFloat(data.lat), lng: parseFloat(data.lng) };
                            map.setCenter(newLatLng);
                            marker.setPosition(newLatLng);
                            document.getElementById('mapLat').value = data.lat;
                            document.getElementById('mapLng').value = data.lng;
                        } else {
                            alert('No se pudo resolver la URL de Google Maps.');
                        }
                    } catch (error) {
                        alert('Error al resolver la URL de Google Maps.');
                    }
                }
            }
        });
    }

    function confirmarCoordenadasSeleccionadas() {
        const latVal = document.getElementById('mapLat').value;
        const lngVal = document.getElementById('mapLng').value;

        document.getElementById(activeLatId).value = latVal;
        document.getElementById(activeLngId).value = lngVal;

        const modalEl = document.getElementById('mapModal');
        const modal = bootstrap.Modal.getInstance(modalEl);
        modal.hide();
    }
</script>
@endsection
