@extends('scb.layouts')

@section('template_title', 'Movimientos')
@section('page_title', 'Movimientos bancarios')
@section('page_subtitle', 'Registro de cargos, abonos y detalles por unidad')

@section('content')
    <div class="scb-card">
        <div class="scb-card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Movimientos registrados</h5>
                <small class="text-muted">Administra cargos, abonos y sus detalles.</small>
            </div>

            <button type="button" class="btn scb-btn-primary" id="btnNuevoMovimiento">
                <i class="fas fa-plus me-1"></i>
                Nuevo movimiento
            </button>
        </div>

        <div class="scb-card-body">
            <div class="table-responsive">
                <table class="table align-items-center mb-0" id="tablaMovimientos">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Fecha</th>
                            <th>Banco</th>
                            <th>Cuenta</th>
                            <th>Tipo</th>
                            <th>Concepto</th>
                            <th>Referencia</th>
                            <th class="text-end">Total</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($movimientos as $movimiento)
                            <tr id="movimiento-row-{{ $movimiento->id }}">
                                <td>{{ $movimiento->id }}</td>

                                <td class="mov-fecha">
                                    {{ $movimiento->fecha_movimiento?->format('d/m/Y') }}
                                </td>

                                <td class="mov-banco fw-bold">
                                    {{ $movimiento->cuenta?->banco?->nombre ?? 'S/N' }}
                                </td>

                                <td class="mov-cuenta">
                                    <div>{{ $movimiento->cuenta?->beneficiario ?? 'S/N' }}</div>
                                    <small class="text-muted">
                                        {{ $movimiento->cuenta?->numero_cuenta ?? 'Sin cuenta' }}
                                    </small>
                                </td>

                                <td class="mov-tipo">
                                    @if ($movimiento->tipo === 'abono')
                                        <span class="badge bg-success">Abono</span>
                                    @else
                                        <span class="badge bg-danger">Cargo</span>
                                    @endif
                                </td>

                                <td class="mov-concepto">
                                    {{ $movimiento->concepto }}
                                </td>

                                <td class="mov-referencia">
                                    {{ $movimiento->referencia_bancaria ?? 'S/N' }}
                                </td>

                                <td class="mov-total text-end fw-bold">
                                    ${{ number_format($movimiento->total ?? 0, 2) }}
                                </td>

                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-outline-info btnVerMovimiento"
                                        data-id="{{ $movimiento->id }}">
                                        <i class="fas fa-eye"></i>
                                    </button>

                                    <button type="button" class="btn btn-sm btn-outline-primary btnEditarMovimiento"
                                        data-id="{{ $movimiento->id }}">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <button type="button" class="btn btn-sm btn-outline-danger btnEliminarMovimiento"
                                        data-id="{{ $movimiento->id }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr id="movimientos-empty-row">
                                <td colspan="9" class="text-center text-muted py-4">
                                    No hay movimientos registrados.
                                </td>
                            </tr>
                        @endforelse
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
                            <div class="col-md-4">
                                <label class="form-label">Cuenta bancaria</label>
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
                                <label class="form-label">Tipo</label>
                                <select name="tipo" id="tipo" class="form-select" required>
                                    <option value="cargo">Cargo</option>
                                    <option value="abono">Abono</option>
                                </select>
                                <div class="invalid-feedback" id="error_tipo"></div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Fecha</label>
                                <input type="date" name="fecha_movimiento" id="fecha_movimiento" class="form-control"
                                    required>
                                <div class="invalid-feedback" id="error_fecha_movimiento"></div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Referencia bancaria</label>
                                <input type="text" name="referencia_bancaria" id="referencia_bancaria"
                                    class="form-control" placeholder="Referencia bancaria">
                                <div class="invalid-feedback" id="error_referencia_bancaria"></div>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Concepto</label>
                                <input type="text" name="concepto" id="concepto" class="form-control"
                                    placeholder="Ej. Pago diesel mes abril" required>
                                <div class="invalid-feedback" id="error_concepto"></div>
                            </div>

                            <div class="col-md-12">
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
                                <small class="text-muted">Agrega las unidades y montos que componen el movimiento.</small>
                            </div>

                            <button type="button" class="btn btn-sm btn-outline-primary" id="btnAgregarDetalle">
                                <i class="fas fa-plus me-1"></i>
                                Agregar detalle
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-sm align-items-center" id="tablaDetallesMovimiento">
                                <thead>
                                    <tr>
                                        <th style="min-width:190px;">Unidad</th>
                                        <th style="min-width:220px;">Descripción</th>
                                        <th style="min-width:150px;">Referencia</th>
                                        <th style="min-width:130px;" class="text-end">Monto</th>
                                        <th style="min-width:180px;">Observaciones</th>
                                        <th class="text-end">Acción</th>
                                    </tr>
                                </thead>

                                <tbody></tbody>

                                <tfoot>
                                    <tr>
                                        <th colspan="3" class="text-end">Total</th>
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
                <input type="number" class="form-control detalle-monto text-end" step="0.01" min="0.01"
                    value="0.00" required>
            </td>

            <td>
                <input type="text" class="form-control detalle-observaciones" placeholder="Observaciones">
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
