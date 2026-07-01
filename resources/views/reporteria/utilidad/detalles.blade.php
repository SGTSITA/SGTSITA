<div
    class="modal fade"
    id="detalles{{ $cotizacion->id }}"
    tabindex="-1"
    aria-labelledby="exampleModalLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalles - Contenedor: {{ $cotizacion->Contenedor->num_contenedor }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label for="name">Total</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1">
                                    <img src="{{ asset('img/icon/efectivo.webp') }}" alt="" width="25px" />
                                </span>
                                <input
                                    type="text"
                                    class="form-control"
                                    value=" ${{ number_format($cotizacion->total, 2, '.', ',') }}"
                                    readonly
                                />
                            </div>
                        </div>
                    </div>

                    <div class="col-6">
                        <div class="form-group">
                            <label for="name">Operador / Proveedor</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1">
                                    <img src="{{ asset('img/icon/efectivo.webp') }}" alt="" width="25px" />
                                </span>

                                @if ($cotizacion->total_proveedor == null)
                                    <input
                                        type="text"
                                        class="form-control"
                                        value=" ${{ number_format($cotizacion->pago_operador, 2, '.', ',') }}"
                                        readonly
                                    />
                                @else
                                    <input
                                        type="text"
                                        class="form-control"
                                        value=" ${{ number_format($cotizacion->total_proveedor, 2, '.', ',') }}"
                                        readonly
                                    />
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-6">
                        <div class="form-group">
                            <label for="name">Utilidad</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1">
                                    <img src="{{ asset('img/icon/efectivo.webp') }}" alt="" width="25px" />
                                </span>
                                @php
                                    if ($cotizacion->total_proveedor == null) {
                                        $utilidad = $cotizacion->total - $cotizacion->pago_operador;
                                    } elseif ($cotizacion->total_proveedor != null) {
                                        $utilidad = $cotizacion->total - $cotizacion->total_proveedor;
                                    } else {
                                        $utilidad = 0;
                                    }
                                @endphp

                                <input
                                    type="text"
                                    class="form-control"
                                    id="pago_operador"
                                    name="pago_operador"
                                    value=" ${{ number_format($utilidad, 2, '.', ',') }}"
                                    readonly
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@section('select2')
    <script src="{{ asset('assets/vendor/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/select2/dist/js/select2.min.js') }}"></script>

    <script></script>
@endsection
