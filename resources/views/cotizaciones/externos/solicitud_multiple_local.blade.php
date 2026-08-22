@extends('layouts.usuario_externo')

@section('WorkSpace')
    <div class="row gx-5 gx-xl-10">
        <div class="col-sm-12 mb-5 mb-xl-10">
            <div class="card card-flush h-lg-100">
                <div class="card-header">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-900">Solicitud Multiple</span>
                        <span class="text-gray-500 mt-1 fw-semibold fs-6">
                            Solicitud de servicio de gestion de transporte
                        </span>
                    </h3>
                    <div class="card-toolbar"></div>
                </div>
                <div class="card-body">
                    <div id="cotizacion-multiple"></div>
                </div>
                <input type="hidden" value="MEC-Multiple-local" id="origen_captura" name="origen_captura" />
                <div class="card-footer border-0 text-end">
                    <div class="separator separator-dashed mb-8"></div>
                    <button
                        type="button"
                        id="btnSolicitar"
                        class="btn btn-lg btn-primary"
                        data-kt-stepper-action="next"
                    >
                        Solicitar viajes
                        <!--begin::Svg Icon | path: icons/duotune/arrows/arr064.svg-->
                        <span class="svg-icon svg-icon-4 ms-1">
                            <svg
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <rect
                                    opacity="0.5"
                                    x="18"
                                    y="13"
                                    width="13"
                                    height="2"
                                    rx="1"
                                    transform="rotate(-180 18 13)"
                                    fill="currentColor"
                                ></rect>
                                <path
                                    d="M15.4343 12.5657L11.25 16.75C10.8358 17.1642 10.8358 17.8358 11.25 18.25C11.6642 18.6642 12.3358 18.6642 12.75 18.25L18.2929 12.7071C18.6834 12.3166 18.6834 11.6834 18.2929 11.2929L12.75 5.75C12.3358 5.33579 11.6642 5.33579 11.25 5.75C10.8358 6.16421 10.8358 6.83579 11.25 7.25L15.4343 11.4343C15.7467 11.7467 15.7467 12.2533 15.4343 12.5657Z"
                                    fill="currentColor"
                                ></path>
                            </svg>
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('javascript')
    <script>
        const canElegirProveedor = @can('mec-elegir-proveedor') true @else false @endcan;
        //const canElegirProveedor= true;
            // LISTAS PARA SELECTS
        var proveedoresLista =@json($proveedores);
        var transportistasLista =@json($transportista);
    </script>
    <link href="{{ asset('assets/handsontable/handsontable.full.min.css') }}" rel="stylesheet" media="screen" />
    <script src="{{ asset('assets/handsontable/handsontable.full.min.js') }}"></script>
    <script src="{{ asset('assets/handsontable/all.js') }}"></script>
    <script src="{{ asset('js/sgt/common.js') }}?v={{ filemtime(public_path('js/sgt/common.js')) }}"></script>
    <script src="{{ asset('js/sgt/cotizaciones/cotizacion-multiplelocal.js') }}?v={{ filemtime(public_path('js/sgt/cotizaciones/cotizacion-multiplelocal.js')) }}"></script>
    <script>
        $(document).ready(async () => {
            let clientes = await getClientesLocal({{ Auth::User()->id_cliente }});
            const handsontable = buildHandsOntableLocal();
            var btn = document.querySelector('#btnSolicitar');
            btn.addEventListener('click', (i) => handsontable.validarSolicitudLocal());

            var genericUUID = localStorage.getItem('uuid');
            if (genericUUID == null) {
                genericUUID = generateUUID();
                localStorage.setItem('uuid', genericUUID);
            }
        });
    </script>
@endpush
