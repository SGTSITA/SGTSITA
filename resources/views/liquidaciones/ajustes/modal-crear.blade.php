<div class="modal fade" id="modalNuevoAjuste" tabindex="-1" role="dialog" aria-labelledby="modalNuevoAjusteLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-gradient-dark text-white">
                <h5 class="modal-title text-white" id="modalNuevoAjusteLabel">
                    <i class="fa fa-file-invoice-dollar text-success me-2"></i> Nuevo Ajuste / Complemento de Liquidación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="formNuevoAjuste" onsubmit="guardarNuevoAjuste(event)">
                @csrf
                <div class="modal-body p-4">
                    <!-- 1. BÚSQUEDA Y VINCULACIÓN DE LIQUIDACIÓN PREVIA -->
                    <div class="card mb-3 border-light bg-light">
                        <div class="card-body p-3">
                            <label class="font-weight-bold text-dark mb-1">
                                1. Seleccione la Liquidación Original que afecta <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-sm mb-2">
                                <span class="input-group-text bg-white"><i class="fa fa-search text-muted"></i></span>
                                <input type="text" id="buscar_liq_input" class="form-control" 
                                    placeholder="Escriba el Folio de Liquidación (ej. 145), nombre del operador o contenedor..." 
                                    autocomplete="off"
                                />
                                <button type="button" class="btn btn-outline-secondary" onclick="buscarLiquidacionesDropdown()">
                                    Buscar
                                </button>
                            </div>
                            
                            <div id="contenedorResultadosBusqueda" class="list-group mb-2 shadow-sm" style="display: none; max-height: 200px; overflow-y: auto;">
                                <!-- Se llena dinámicamente -->
                            </div>

                            <input type="hidden" name="id_liquidacion" id="input_id_liquidacion" required />
                            <input type="hidden" id="input_id_operador" />

                            <!-- Tarjeta de confirmación de liquidación seleccionada -->
                            <div id="cardLiquidacionSeleccionada" class="p-3 bg-white rounded border border-primary" style="display: none;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="badge bg-primary text-white mb-1" id="resumen_folio_liq">LIQ #</span>
                                        <h6 class="mb-1 text-dark font-weight-bold" id="resumen_operador_nombre">Operador</h6>
                                        <div class="small text-muted">
                                            <span class="me-3"><i class="fa fa-calendar me-1"></i> Fecha: <strong id="resumen_fecha_liq">-</strong></span>
                                            <span class="me-3"><i class="fa fa-box me-1"></i> Contenedores: <strong id="resumen_contenedores">-</strong></span>
                                            <span><i class="fa fa-dollar-sign me-1"></i> Liquidado Original: <strong id="resumen_total_original" class="text-success">$0.00</strong></span>
                                        </div>
                                    </div>
                                    <div>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="limpiarLiquidacionSeleccionada()">
                                            <i class="fa fa-times"></i> Cambiar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. DATOS GENERALES DEL AJUSTE -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label font-weight-bold text-dark small mb-1">
                                Fecha del Ajuste / Pago <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="fecha" id="fecha_ajuste" class="form-control form-control-sm" required value="{{ date('Y-m-d') }}" />
                        </div>
                        <div class="col-md-5">
                            <label class="form-label font-weight-bold text-dark small mb-1">
                                Cuenta Bancaria de Egreso (Salida de dinero)
                            </label>
                            <select name="id_banco" id="id_banco_ajuste" class="form-select form-select-sm">
                                <option value="">-- Sin dispersión bancaria directa --</option>
                                @foreach($bancos as $banco)
                                    <option value="{{ $banco->id }}">{{ $banco->nombre_banco }} ({{ $banco->cuenta_bancaria }}) - Saldo: ${{ number_format($banco->saldo, 2) }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted d-block" style="font-size: 11px;">Requerida si el total neto a pagar es mayor a $0.00.</small>
                        </div>
                        <div class="col-md-3 d-flex flex-column justify-content-end">
                            <div class="card bg-gradient-light border-0 p-2 text-center">
                                <span class="text-muted small">Total Neto a Dispersar</span>
                                <h4 class="mb-0 font-weight-bold text-success" id="badgeTotalNetoHeader">$0.00</h4>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label font-weight-bold text-dark small mb-1">
                                Motivo / Justificación del Ajuste <span class="text-danger">*</span>
                            </label>
                            <textarea name="motivo" id="motivo_ajuste" class="form-control form-control-sm" rows="2" 
                                placeholder="Describa la razón del ajuste (ej. Se omitió viático de maniobra foránea; comprobante extemporáneo de caseta; etc.)" required></textarea>
                        </div>
                    </div>

                    <!-- 3. DESGLOSE DE CONCEPTOS / PARTIDAS DINÁMICAS -->
                    <div class="card border">
                        <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                            <span class="font-weight-bold text-dark small">
                                <i class="fa fa-list-ul me-1"></i> Conceptos y Partidas del Ajuste (Percepciones y Deducciones)
                            </span>
                            <button type="button" class="btn btn-xs btn-success text-white py-1 px-2" onclick="agregarFilaConcepto()">
                                <i class="fa fa-plus me-1"></i> Agregar Concepto
                            </button>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered mb-0 align-middle" id="tablaPartidasAjuste">
                                    <thead class="table-secondary small text-center">
                                        <tr>
                                            <th style="width: 140px;">Tipo</th>
                                            <th style="width: 220px;">Concepto</th>
                                            <th>Descripción / Referencia</th>
                                            <th style="width: 150px;">Monto ($)</th>
                                            <th style="width: 45px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="bodyPartidasAjuste">
                                        <!-- Filas dinámicas -->
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <td colspan="3" class="text-end font-weight-bold text-muted small">Total Percepciones (+):</td>
                                            <td class="text-end font-weight-bold text-success" id="resumenPercepciones">$0.00</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="text-end font-weight-bold text-muted small">Total Deducciones (-):</td>
                                            <td class="text-end font-weight-bold text-danger" id="resumenDeducciones">$0.00</td>
                                            <td></td>
                                        </tr>
                                        <tr class="table-warning">
                                            <td colspan="3" class="text-end font-weight-bolder text-dark">IMPORTE NETO A PAGAR:</td>
                                            <td class="text-end font-weight-bolder text-dark fs-6" id="resumenNetoFinal">$0.00</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-sm btn-primary" id="btnGuardarAjuste">
                        <i class="fa fa-check me-1"></i> Aplicar y Guardar Ajuste
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
