@extends('layouts.app')

@section('template_title')
    Planeacion
@endsection

@section('content')
    <div class="row">
        <div class="col-12 col-lg-8 mx-auto my-4">
            <div class="card">
                <div class="card-body">
                    <div class="multisteps-form__progress">
                        <!-- PASO 1 -->
                        <button class="multisteps-form__progress-btn js-active" type="button" title="User Info">
                            <span>Contenedor</span>
                        </button>

                        <!-- PASO 2 — OCULTO PARA PROVEEDOR DIRECTO -->
                        @cannot('Proveedor Autonomo 11am')
                            <button class="multisteps-form__progress-btn" type="button" title="Tipo de servicio">
                                <span>Tipo de servicio</span>
                            </button>
                        @endcannot

                        <!-- PASO 3 -->
                        <button class="multisteps-form__progress-btn" type="button" title="Address">
                            Datos del transporte
                        </button>

                        <!-- PASO 4 -->
                        <button class="multisteps-form__progress-btn" type="button" title="Socials">
                            Fechas del viaje
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FORM PANELS -->
    <div class="row">
        <div class="col-12 col-lg-12 m-auto">
            <form class="multisteps-form__form">
                <!-- PANEL 1 -->
                <div class="card multisteps-form__panel p-3 border-radius-xl bg-white js-active" data-animation="FadeIn">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-3">
                                <h5 class="font-weight-normal mb-1 text-left">¡Empecemos!</h5>
                                <p class="text-left mb-0">Seleccione un contenedor para iniciar la planeación</p>
                            </div>

                            <div class="col-6 text-center">
                                <h5 class="mb-1 font-weight-bolder numContenedorLabel" id="numContenedor"></h5>
                                <p class="mb-0 font-weight-bold text-sm nombreClienteLabel"></p>
                            </div>

                            <div class="col-3 d-flex justify-content-end">
                                <button class="btn bg-gradient-info btn-sm mb-0 js-btn-next" id="nextOne" disabled
                                    type="button">
                                    Siguiente
                                    <i class="fa fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>

                        <div class="row">
                            <div id="gridAprobadas" class="ag-theme-alpine position-relative" style="height: 500px">
                                <div id="gridLoadingOverlay" class="loading-overlay" style="display: none">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Cargando...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PANEL 2 — OCULTO PARA PROVEEDOR DIRECTO -->
                @cannot('Proveedor Autonomo 11am')
                    <div class="card multisteps-form__panel p-3 border-radius-xl bg-white" data-animation="FadeIn">
                        <div class="row align-items-center">
                            <div class="col-1 text-start">
                                <button class="btn bg-gradient-info btn-sm mb-0 js-btn-prev" type="button">
                                    <i class="fa fa-arrow-left"></i>
                                    Anterior
                                </button>
                            </div>

                            <div class="col-4">
                                <h5 class="font-weight-normal">¿Cuál medio utilizará para el envío contenedor?</h5>
                                <p>Indique como se realizará el viaje</p>
                            </div>

                            <div class="col-5 text-center">
                                <div>
                                    <h5 class="mb-1 font-weight-bolder numContenedorLabel"></h5>
                                    <p class="mb-0 font-weight-bold text-sm nombreClienteLabel"></p>
                                </div>
                            </div>

                            <div class="col-2 d-flex justify-content-end">
                                <button class="btn bg-gradient-info btn-sm mb-0 js-btn-next" id="nextTwo" disabled
                                    type="button">
                                    Siguiente
                                    <i class="fa fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>

                        <div class="multisteps-form__content">
                            <div class="row mt-4">
                                <div class="custom-radio-group">
                                    <label class="custom-radio">
                                        <input type="radio" name="option" value="propio" onclick="setTipoViaje('propio')" />
                                        <div class="content">
                                            <i class="fas fa-truck-moving"></i>
                                            <span>Propio</span>
                                        </div>
                                    </label>

                                    <label class="custom-radio">
                                        <input type="radio" name="option" value="proveedor"
                                            onclick="setTipoViaje('proveedor')" />
                                        <div class="content">
                                            <i class="fas fa-trailer"></i>
                                            <span>Sub Contratado</span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                @endcannot

                <!-- PANEL 3 -->
                <div class="card multisteps-form__panel p-3 border-radius-xl bg-white" data-animation="FadeIn">
                    <div class="row align-items-center mt-3">
                        <div class="col-1 text-start">
                            <button class="btn bg-gradient-info btn-sm mb-0 js-btn-prev" type="button">
                                <i class="fa fa-arrow-left"></i>
                                Anterior
                            </button>
                        </div>

                        <div class="col-4 text-start">
                            <h5 class="font-weight-normal">Información general del viaje</h5>
                            <p class="mb-0">Necesitamos algo de información para programar el viaje</p>
                        </div>

                        <div class="col-4 text-center">
                            <div>
                                <h5 class="mb-1 font-weight-bolder numContenedorLabel"></h5>
                                <p class="mb-0 font-weight-bold text-sm nombreClienteLabel"></p>
                            </div>
                        </div>

                        <div class="col-3 d-flex justify-content-end">
                            <button class="btn bg-gradient-success btn-sm mb-0" type="button" id="btnProgramar">
                                Programar viaje
                            </button>
                        </div>
                    </div>

                    <div class="multisteps-form__content">
                        <div class="row mt-0 align-items-start">
                            <!-- ======================= -->
                            <!-- BLOQUE: FECHAS (Agrupado) -->
                            <!-- ======================= -->
                            <div class="col-lg-4 col-12 mb-4">
                                <h6 class="mb-0">Fecha de viaje</h6>
                                <p class="text-sm">Seleccione rango de fechas para el viaje.</p>

                                <div class="row mt-3">
                                    <!-- Fecha salida -->
                                    <div class="col-md-6 col-lg-5 mb-3">
                                        <label class="fw-bold">Fecha salida</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fa fa-calendar text-danger"></i>
                                            </span>
                                            <input class="form-control dateInput" name="txtFechaInicio"
                                                id="txtFechaInicio" placeholder="Fecha inicio" type="text" />
                                        </div>
                                    </div>

                                    <!-- Fecha entrega -->
                                    <div class="col-md-6 col-lg-5 mb-3">
                                        <label class="fw-bold">Fecha entrega</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fa fa-calendar text-danger"></i>
                                            </span>
                                            <input class="form-control dateInput" name="txtFechaFinal" id="txtFechaFinal"
                                                placeholder="Fecha fin" type="text" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- =============================== -->
                            <!-- BLOQUE: Proveedor (alineado igual que fechas) -->
                            <!-- =============================== -->
                            <div class="col-lg-5 col-12 mb-4 d-none" id="proveedorSubcontratado">
                                <h6 class="mb-0">Proveedor</h6>
                                <p class="text-sm">Seleccione el proveedor que transportará el contenedor.</p>

                                <label class="fw-bold text-xs mb-2 d-block">Proveedor</label>

                                <select class="form-control" name="cmbProveedor" id="cmbProveedor">
                                    <option value="">Seleccione Proveedor</option>
                                    @foreach ($proveedores as $item)
                                        <option value="{{ $item->id }}">{{ $item->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- ====================================== -->
                            <!-- BLOQUE: Peso + Dirección (Centrado) -->
                            <!-- ====================================== -->
                            <div class="col-lg-3 col-12 mb-4 d-flex justify-content-center d-none" id="BloqueDireccionEn">
                                <div class="border rounded-3 p-4 shadow-sm bg-light text-center w-100">
                                    <label class="fw-bold d-block mb-1 text-dark">Peso</label>
                                    <span id="pesoContenedorSub" class="fs-5 text-success d-block mb-3">--</span>

                                    <label class="fw-bold d-flex justify-content-center align-items-center mb-1 text-dark">
                                        <i class="ni ni-pin-3 text-danger me-2"></i>
                                        Dirección de entrega
                                    </label>
                                    <span id="direccionEntregaSub" class="fs-6 text-success d-block">--</span>
                                </div>
                            </div>
                        </div>

                        <!-- VIAJE PROPIO -->
                        <div id="viaje-propio" class="d-none">
                            @include('planeacion.viaje_propio')
                        </div>

                        <!-- VIAJE SUBCONTRATADO -->
                        <div id="viaje-proveedor" class="d-none">
                            @include('planeacion.viaje_subcontratado')
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('custom-javascript')
    <style>
        .custom-radio-group {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-top: 2rem;
        }

        .custom-radio {
            cursor: pointer;
            text-align: center;
            width: 160px;
            height: 160px;
            position: relative;
        }

        .custom-radio input[type='radio'] {
            display: none;
        }

        .custom-radio .content {
            border: 1px dashed #ccc;
            border-radius: 15px;
            width: 100%;
            height: 100%;
            padding: 1rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            transition: all 0.3s ease;
        }

        .custom-radio .content i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #555;
        }

        .custom-radio .content span {
            font-size: 1.2rem;
            color: #333;
        }

        /* Cuando está seleccionado */
        .custom-radio input[type='radio']:checked+.content {
            border: 1px solid #007bff;
            /* Borde sólido azul */
        }

        .custom-radio input[type='radio']:checked+.content i,
        .custom-radio input[type='radio']:checked+.content span {
            color: #007bff;
        }

        /* Hover efecto */
        .custom-radio:hover .content {
            border-color: #007bff;
        }

        input.flatpickr-input[readonly] {
            background-color: #fff !important;
            /* Fondo blanco */
            cursor: pointer;
            /* Opcional: para que el mouse cambie a "manita" */
        }

        .flatpickr-day .today {
            background: #28a745 !important;
            /* verde */
            border-color: #28a745 !important;
            color: #fff;
            /* texto blanco */
        }

        .gasto-item .form-control {
            height: calc(2.5rem + 2px);
        }

        /* Toggle personalizado */
        .toggle-switch {
            position: relative;
            width: 45px;
            height: 24px;
            display: inline-block;
        }

        .toggle-switch input {
            display: none;
        }

        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #d1d5db;
            transition: 0.3s;
            border-radius: 34px;
        }

        .toggle-slider:before {
            position: absolute;
            content: '';
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: 0.3s;
            border-radius: 50%;
        }

        .toggle-switch input:checked+.toggle-slider {
            background-color: #2dce89;
        }

        .toggle-switch input:checked+.toggle-slider:before {
            transform: translateX(21px);
        }

        .multisteps-form__content {
            padding-bottom: 1rem !important;
        }
    </style>
    <!-- AG Grid -->
    <script src="https://unpkg.com/ag-grid-community/dist/ag-grid-community.min.js"></script>

    <!-- Nuestro JavaScript unificado -->
    <script src="/js/sgt/cotizaciones/aprobadas_list.js?v=1744206575"></script>
    <script src="/js/sgt/common.js?v=1744206575"></script>

    <script src="/assets/js/plugins/multistep-form.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            flatpickr(".dateInput", {
                dateFormat: "d/m/Y",
                locale: "es"
            });

            let moneyformatInput = document.querySelectorAll('.moneyformat');

            moneyformatInput.forEach((r) => r.value = moneyFormat(r.value))








        });

        const botonGastos = document.getElementById('btnAddGasto');
        const container = document.getElementById('otrosGastosContainer');

        const opcionesGasto = [{
                value: 'GCM01',
                text: 'GCM01 - Comisión'
            },
            {
                value: 'GDI02',
                text: 'GDI02 - Diesel'
            },
            {
                value: 'GBV01',
                text: 'GBV01 - Burrero Vacio'
            },
        ];

        if (botonGastos) {
            botonGastos.addEventListener('click', function() {
                const total = container.querySelectorAll('.gasto-item').length;

                if (total >= 2) {
                    alert('Solo puedes agregar un máximo de 2 gastos.');
                    return;
                }

                const gastoHTML = `
              <div class="row gasto-item align-items-center mb-3 border-bottom pb-3">
                <div class="col-md-3">
                  <label class="form-label mb-1">Motivo del gasto</label>
                  <select class="form-control gasto-select" name="gasto_nombre[]" required>
                    <option value="">Seleccione un motivo</option>
                    ${opcionesGasto.map(op => `<option value="${op.value}">${op.text}</option>`).join('')}
                  </select>
                </div>

                <div class="col-md-2">
                  <label class="form-label mb-1">Monto</label>
                  <div class="input-group">
                    <span class="input-group-text bg-gradient-success text-white">
                      <i class="ni ni-money-coins"></i>
                    </span>
                    <input type="number" step="0.01" min="0" class="form-control" name="gasto_monto[]" placeholder="0.00" required>
                  </div>
                </div>

                <div class="col-md-1">
                  <label class="form-label mb-1 d-block">Pago inmediato</label>
                  <label class="toggle-switch">
                    <input type="checkbox" class="pagoInmediatoCheck" name="gasto_pago_inmediato[]">
                    <span class="toggle-slider"></span>
                  </label>
                </div>

                <div class="col-md-3 banco-col" style="display:none;">
                  <label class="form-label mb-1">Banco</label>
                  <select class="form-control" name="gasto_banco_id[]">
                    <option value="">Seleccione banco</option>
                    @foreach ($bancos as $item)
                       <option value="{{ $item['id'] }}">
                                    {{ $item['display'] }}
                                </option>
                    @endforeach
                  </select>
                </div>
                 <div class="col-2 col-md- fecha-col" style="display:none;">
                    <label for="txtFechaInicio">Fecha Aplicación</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fa fa-calendar text-danger"></i>
                        </span>
                        <input class="form-control dateInput" name="fechaAplicacion[]"
                                    placeholder="Fecha Aplicación" type="text" />
                    </div>
                </div>

                <div class="col-md-1 text-end">
                  <label class="form-label mb-1 d-block">&nbsp;</label>
                  <button type="button" class="btn btn-danger btn-sm removeGastoBtn">
                    <i class="ni ni-fat-remove"></i>
                  </button>
                </div>
              </div>`;

                container.insertAdjacentHTML('beforeend', gastoHTML);

                const ultimoDateInput = container.querySelector('.gasto-item:last-child .dateInput');

                if (!ultimoDateInput._flatpickr) {
                    flatpickr(ultimoDateInput, {
                        dateFormat: "d/m/Y",
                        locale: "es"
                    });
                }
                actualizarDisponibles();
            });
        }

        // Mostrar/ocultar banco según pago inmediato
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('pagoInmediatoCheck')) {
                const row = e.target.closest('.gasto-item');
                const bancoCol = row.querySelector('.banco-col');
                bancoCol.style.display = e.target.checked ? 'block' : 'none';
                if (!e.target.checked) bancoCol.querySelector('select').value = '';

                const fechaCol = row.querySelector('.fecha-col');
                fechaCol.style.display = e.target.checked ? 'block' : 'none';
            }

            if (e.target.name === 'gasto_nombre[]') {
                actualizarDisponibles();
            }
        });

        // Eliminar gasto
        document.addEventListener('click', function(e) {
            if (e.target.closest('.removeGastoBtn')) {
                e.target.closest('.gasto-item').remove();
                actualizarDisponibles();
            }
        });

        // Función principal: sincroniza selects
        function actualizarDisponibles() {
            const selects = Array.from(container.querySelectorAll('select[name="gasto_nombre[]"]'));
            const seleccionados = selects.map(s => s.value).filter(v => v !== '');

            selects.forEach((select) => {
                const valorActual = select.value;

                // reconstruimos las opciones
                const opciones = ['<option value="">Seleccione un motivo</option>'];
                opcionesGasto.forEach(op => {
                    const ocupadoPorOtro = seleccionados.includes(op.value) && op.value !== valorActual;
                    opciones.push(
                        `<option value="${op.value}" ${ocupadoPorOtro ? 'disabled' : ''}>${op.text}</option>`
                    );
                });

                // reemplazamos el contenido del select
                select.innerHTML = opciones.join('');

                // mantenemos su valor si sigue siendo válido
                if (valorActual && [...select.options].some(o => o.value === valorActual && !o.disabled)) {
                    select.value = valorActual;
                } else {
                    select.value = '';
                }
            });
        }
    </script>
    @can('Proveedor Autonomo 11am')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                setTipoViaje('propio');

                // Habilitar paso siguiente
                const nextTwo = document.getElementById('nextTwo');
                if (nextTwo) nextTwo.disabled = false;

                // Mostrar/ocultar bloques
                document.getElementById('viaje-propio').classList.remove('d-none');
                document.getElementById('viaje-proveedor').classList.add('d-none');

                // Avanzar automáticamente al panel 3
                setTimeout(() => {
                    nextTwo.click();
                }, 300);
            });
        </script>
    @endcan
@endpush
