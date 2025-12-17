<div class="row">
    <div class="col-6 form-group">
        <label for="name">Fecha inicio</label>
        <div class="input-group">
            <span class="input-group-text" id="basic-addon1">
                <img src="{{ asset('img/icon/calendar-dar.webp') }}" alt="" width="25px" />
            </span>
            <input
                name="fecha_inicio_proveedor"
                type="date"
                class="form-control"
                value="{{ \Carbon\Carbon::parse($cotizacion->DocCotizacion->Asignaciones->fecha_inicio)->format('Y-m-d') }}"
            />
        </div>
    </div>

    <div class="col-6 form-group">
        <label for="name">Fecha fin</label>
        <div class="input-group">
            <span class="input-group-text" id="basic-addon1">
                <img src="{{ asset('img/icon/calendar-dar.webp') }}" alt="" width="25px" />
            </span>
            <input
                name="fecha_fin_proveedor"
                type="date"
                class="form-control"
                value="{{ \Carbon\Carbon::parse($cotizacion->DocCotizacion->Asignaciones->fecha_fin)->format('Y-m-d') }}"
            />
        </div>
    </div>

    <div class="col-12 form-group">
        <label for="name">Proveedor</label>
        <select
            class="form-select d-inline-block"
            id="id_proveedor"
            name="id_proveedor"
            value="{{ old('id_proveedor') }}"
        >
            @if ($cotizacion->DocCotizacion->Asignaciones->id_proveedor == null)
                <option value="">Seleccione opcion</option>
            @else
                <option value="{{ $cotizacion->DocCotizacion->Asignaciones->id_proveedor }}">
                    {{ $cotizacion->DocCotizacion->Asignaciones->Proveedor->nombre }} /
                    {{ $cotizacion->DocCotizacion->Asignaciones->Proveedor->telefono }}
                </option>
            @endif
            @foreach ($proveedores as $item)
                <option value="{{ $item->id }}">{{ $item->nombre }}</option>
            @endforeach
        </select>
    </div>

    <div class="row">
        <div class="col-3 form-group">
            <label for="name">Costo viaje</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1">
                    <img src="{{ asset('img/icon/metodo-de-pago.webp') }}" alt="" width="25px" />
                </span>
                <input
                    name="precio"
                    id="cot_precio_{{ $cotizacion->DocCotizacion->Asignaciones->id }}"
                    type="number"
                    class="form-control"
                    value="{{ $cotizacion->DocCotizacion->Asignaciones->precio }}"
                />
            </div>
        </div>

        <div class="col-3 form-group">
            <label for="name">Burreo</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1">
                    <img src="{{ asset('img/icon/burro.png') }}" alt="" width="25px" />
                </span>
                <input
                    name="cot_burreo"
                    id="cot_burreo_{{ $cotizacion->DocCotizacion->Asignaciones->id }}"
                    type="float"
                    class="form-control"
                    value="{{ $cotizacion->DocCotizacion->Asignaciones->burreo }}"
                />
            </div>
        </div>

        <div class="col-3 form-group">
            <label for="name">Maniobra</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1">
                    <img src="{{ asset('img/icon/logistica.png') }}" alt="" width="25px" />
                </span>
                <input
                    name="cot_maniobra"
                    id="cot_maniobra_{{ $cotizacion->DocCotizacion->Asignaciones->id }}"
                    type="float"
                    class="form-control"
                    value="{{ $cotizacion->DocCotizacion->Asignaciones->maniobra }}"
                />
            </div>
        </div>

        <div class="col-3 form-group">
            <label for="name">Estadia</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1">
                    <img src="{{ asset('img/icon/servidor-en-la-nube.png') }}" alt="" width="25px" />
                </span>
                <input
                    name="cot_estadia"
                    id="cot_estadia_{{ $cotizacion->DocCotizacion->Asignaciones->id }}"
                    type="float"
                    class="form-control"
                    value="{{ $cotizacion->DocCotizacion->Asignaciones->estadia }}"
                />
            </div>
        </div>

        <div class="col-4 form-group">
            <label for="name">Otros</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1">
                    <img src="{{ asset('img/icon/inventario.png.webp') }}" alt="" width="25px" />
                </span>
                <input
                    name="cot_otro"
                    id="cot_otro_{{ $cotizacion->DocCotizacion->Asignaciones->id }}"
                    type="float"
                    class="form-control"
                    value="{{ $cotizacion->DocCotizacion->Asignaciones->otro }}"
                />
            </div>
        </div>

        <div class="col-4 form-group">
            <label for="name">IVA</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1">
                    <img src="{{ asset('img/icon/impuesto.png') }}" alt="" width="25px" />
                </span>
                <input
                    name="cot_iva"
                    id="cot_iva_{{ $cotizacion->DocCotizacion->Asignaciones->id }}"
                    type="number"
                    class="form-control"
                    value="{{ $cotizacion->DocCotizacion->Asignaciones->iva }}"
                />
            </div>
        </div>

        <div class="col-4 form-group">
            <label for="name">Retención</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1">
                    <img src="{{ asset('img/icon/monedas.webp') }}" alt="" width="25px" />
                </span>
                <input
                    name="cot_retencion"
                    id="cot_retencion_{{ $cotizacion->DocCotizacion->Asignaciones->id }}"
                    type="float"
                    class="form-control"
                    value="{{ $cotizacion->DocCotizacion->Asignaciones->retencion }}"
                />
            </div>
        </div>

        <div class="col-4 form-group">
            <label for="name">Total</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1">
                    <img src="{{ asset('img/icon/monedas.webp') }}" alt="" width="25px" />
                </span>
                <input
                    name="total_proveedor"
                    id="total_proveedor_{{ $cotizacion->DocCotizacion->Asignaciones->id }}"
                    type="number"
                    class="form-control"
                    value="{{ $cotizacion->DocCotizacion->Asignaciones->total_proveedor }}"
                    readonly
                />
            </div>
        </div>
    </div>
</div>
