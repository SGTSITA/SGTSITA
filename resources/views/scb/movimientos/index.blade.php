@extends('scb.layouts')

@section('template_title', 'Movimientos')
@section('page_title', 'Movimientos bancarios')
@section('page_subtitle', 'Registro de cargos, abonos y detalles por unidad')

@section('content')
    <div class="scb-card">
        <div class="scb-card">
            <div class="scb-card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h5 class="mb-0">Estado de cuenta bancario</h5>
                    <small class="text-muted">
                        Consulta cargos, abonos y saldo por cuenta bancaria.
                    </small>
                </div>

                <button type="button" class="btn scb-btn-primary" id="btnNuevoMovimiento">
                    <i class="fas fa-plus me-1"></i>
                    Nuevo movimiento
                </button>
            </div>

            <div class="scb-card-body">

                {{-- FILTROS --}}
                <div class="border rounded-4 p-3 bg-light mb-4">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label fw-bold">Cuenta bancaria</label>
                            <select id="filtro_cuenta_id" class="form-select">
                                <option value="">Seleccione cuenta</option>
                                @foreach ($cuentas as $cuenta)
                                    <option value="{{ $cuenta->id }}">
                                        {{ $cuenta->banco?->nombre }} -
                                        {{ $cuenta->beneficiario ?? 'S/N' }} -
                                        {{ $cuenta->numero_cuenta ?? 'Sin cuenta' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-bold">Fecha inicio</label>
                            <input type="date" id="filtro_fecha_inicio" class="form-control">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-bold">Fecha fin</label>
                            <input type="date" id="filtro_fecha_fin" class="form-control">
                        </div>

                        <div class="col-md-3">
                            <button type="button" class="btn scb-btn-primary w-100" id="btnBuscarEstadoCuenta">
                                <i class="fas fa-search me-1"></i>
                                Buscar estado
                            </button>
                        </div>
                    </div>
                </div>

                {{-- RESUMEN --}}
                <div class="row g-3 mb-4 d-none" id="resumenEstadoCuenta">
                    <div class="col-md-3">
                        <div class="estado-card estado-card-saldo-inicial">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small>Saldo inicial</small>
                                    <h5 id="lblSaldoInicial">$0.00</h5>
                                </div>
                                <i class="fas fa-wallet"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="estado-card estado-card-cargos">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small>Cargos</small>
                                    <h5 id="lblTotalCargos">$0.00</h5>
                                </div>
                                <i class="fas fa-arrow-down"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="estado-card estado-card-abonos">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small>Abonos</small>
                                    <h5 id="lblTotalAbonos">$0.00</h5>
                                </div>
                                <i class="fas fa-arrow-up"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="estado-card estado-card-saldo-final">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small>Saldo final</small>
                                    <h5 id="lblSaldoFinal">$0.00</h5>
                                </div>
                                <i class="fas fa-balance-scale"></i>
                            </div>
                        </div>
                    </div>
                </div>


                {{-- TABLA ESTADO DE CUENTA --}}
                <div class="table-responsive">
                    <table class="table align-items-center mb-0" id="tablaMovimientos">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Concepto</th>
                                <th>Referencia</th>
                                <th class="text-end">Cargo</th>
                                <th class="text-end">Abono</th>
                                <th class="text-end">Saldo</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>

                        <tbody id="tbodyMovimientos">
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    Selecciona cuenta bancaria y rango de fechas para consultar.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Modal Crear / Editar --}}
        <div class="modal fade" id="modalMovimiento" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-xl">
                <div class="modal-content rounded-4">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalMovimientoTitulo">Nuevo movimiento</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>

                    <form id="formMovimiento">
                        @csrf

                        <input type="hidden" id="movimiento_id" name="movimiento_id">

                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-5">
                                    <label class="form-label fw-bold">Cuenta bancaria</label>
                                    <select name="cuenta_id" id="cuenta_id" class="form-select" required>
                                        <option value="">Seleccione cuenta</option>
                                        @foreach ($cuentas as $cuenta)
                                            <option value="{{ $cuenta->id }}">
                                                {{ $cuenta->banco?->nombre }} -
                                                {{ $cuenta->beneficiario ?? 'S/N' }} -
                                                {{ $cuenta->numero_cuenta ?? 'Sin cuenta' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" id="error_cuenta_id"></div>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label fw-bold">Tipo</label>
                                    <select name="tipo" id="tipo" class="form-select" required>
                                        <option value="cargo">Cargo</option>
                                        <option value="abono">Abono</option>
                                    </select>
                                    <div class="invalid-feedback" id="error_tipo"></div>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label fw-bold">Fecha</label>
                                    <input type="date" name="fecha_movimiento" id="fecha_movimiento"
                                        class="form-control" required>
                                    <div class="invalid-feedback" id="error_fecha_movimiento"></div>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Total movimiento</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" name="total_movimiento" id="total_movimiento"
                                            class="form-control text-end" step="0.01" min="0.01"
                                            placeholder="0.00" required>
                                    </div>
                                    <small class="text-muted">Debe coincidir con el total de detalles.</small>
                                    <div class="invalid-feedback" id="error_total_movimiento"></div>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Referencia bancaria</label>
                                    <input type="text" name="referencia_bancaria" id="referencia_bancaria"
                                        class="form-control" placeholder="Referencia bancaria">
                                    <div class="invalid-feedback" id="error_referencia_bancaria"></div>
                                </div>

                                <div class="col-md-8">
                                    <label class="form-label fw-bold">Concepto</label>
                                    <input type="text" name="concepto" id="concepto" class="form-control"
                                        placeholder="Ej. Pago diesel mes abril" required>
                                    <div class="invalid-feedback" id="error_concepto"></div>
                                </div>

                                <div class="col-md-12 d-none">
                                    <label class="form-label">Observaciones</label>
                                    <textarea name="observaciones" id="observaciones" rows="2" class="form-control"
                                        placeholder="Observaciones generales"></textarea>
                                    <div class="invalid-feedback" id="error_observaciones"></div>
                                </div>
                            </div>

                            <hr>

                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <h6 class="mb-0 fw-bold">Detalles del movimiento</h6>
                                    <small class="text-muted">Agrega las unidades y montos que componen el
                                        movimiento.</small>
                                </div>

                                <button type="button" class="btn btn-sm btn-outline-primary" id="btnAgregarDetalle">
                                    <i class="fas fa-plus me-1"></i>
                                    Agregar detalle
                                </button>
                            </div>
                            <div class="border rounded-4 p-3 bg-light mb-3">
                                <div class="row g-3 align-items-center">
                                    <div class="col-md-4">
                                        <small class="text-muted fw-bold">Total capturado</small>
                                        <div class="fs-5 fw-bold" id="lblTotalCapturado">$0.00</div>
                                    </div>

                                    <div class="col-md-4">
                                        <small class="text-muted fw-bold">Total detalles</small>
                                        <div class="fs-5 fw-bold" id="lblTotalDetallesModal">$0.00</div>
                                    </div>

                                    <div class="col-md-4">
                                        <small class="text-muted fw-bold">Diferencia</small>
                                        <div class="fs-5 fw-bold text-danger" id="lblDiferenciaMovimiento">$0.00</div>
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm align-items-center" id="tablaDetallesMovimiento">
                                    <thead>
                                        <tr>
                                            <th style="min-width:190px;">Unidad</th>
                                            <th style="min-width:220px;">Descripción</th>
                                            <th style="min-width:150px;">Referencia</th>
                                            <th style="min-width:130px;" class="text-end">Monto</th>

                                            <th class="text-end">Acción</th>
                                        </tr>
                                    </thead>

                                    <tbody></tbody>

                                    <tfoot>
                                        <tr>
                                            <th colspan="3" class="text-end">Total detalles</th>
                                            <th class="text-end" id="totalDetalles">$0.00</th>
                                            <th colspan="2"></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <div class="text-danger small fw-bold d-none" id="error_detalles"></div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                Cancelar
                            </button>

                            <button type="submit" class="btn scb-btn-primary">
                                <i class="fas fa-save me-1"></i>
                                Guardar movimiento
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Modal Ver --}}
        <div class="modal fade" id="modalVerMovimiento" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content rounded-4">
                    <div class="modal-header">
                        <h5 class="modal-title">Detalle del movimiento</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>

                    <div class="modal-body" id="contenidoVerMovimiento"></div>
                </div>
            </div>
        </div>

        <template id="templateDetalleMovimiento">
            <tr class="detalle-row">
                <td>
                    <select class="form-select detalle-unidad">
                        <option value="">Sin unidad</option>
                        @foreach ($unidades as $unidad)
                            <option value="{{ $unidad->id }}">
                                {{ $unidad->descripcion }} {{ $unidad->placas ? '- ' . $unidad->placas : '' }}
                            </option>
                        @endforeach
                    </select>
                </td>

                <td>
                    <input type="text" class="form-control detalle-descripcion" placeholder="Descripción" required>
                </td>

                <td>
                    <input type="text" class="form-control detalle-referencia" placeholder="Referencia">
                </td>

                <td>
                    <input type="number" class="form-control detalle-monto text-end" step=".01" min="0.01"
                        value="0.00" required>
                </td>



                <td class="text-end">
                    <button type="button" class="btn btn-sm btn-outline-danger btnEliminarDetalle">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        </template>
    @endsection

    @push('custom-javascript')
        <script src="{{ asset('js/scb/movimientos.js') }}?v={{ filemtime(public_path('js/scb/movimientos.js')) }}"></script>
    @endpush
