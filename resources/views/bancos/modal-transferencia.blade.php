 <div class="modal fade" id="modalTransferencia" tabindex="-1">
     <div class="modal-dialog modal-lg modal-dialog-centered">
         <div class="modal-content">

             <form id="formTransferencia">
                 @csrf

                 {{-- HEADER --}}
                 <div class="modal-header">
                     <h5 class="modal-title">
                         <i class="fa fa-exchange-alt me-2"></i>
                         Transferencia entre cuentas
                     </h5>
                     <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                 </div>

                 {{-- BODY --}}
                 <div class="modal-body">
                     <div class="row g-3">

                         {{-- Cuenta origen --}}
                         <div class="col-md-6">
                             <label class="form-label">Cuenta origen *</label>
                             <select name="cuenta_origen" class="form-select" required>
                                 <option value="">Seleccionar</option>
                                 @foreach ($cuentas as $cta)
                                     <option value="{{ $cta->id }}">
                                         {{ $cta->nombre_beneficiario }} · {{ $cta->moneda }}
                                         · {{ $cta->cuenta_bancaria }}
                                     </option>
                                 @endforeach
                             </select>
                         </div>

                         {{-- Cuenta destino --}}
                         <div class="col-md-6">
                             <label class="form-label">Cuenta destino *</label>
                             <select name="cuenta_destino" class="form-select" required>
                                 <option value="">Seleccionar</option>
                                 @foreach ($cuentas as $cta)
                                     <option value="{{ $cta->id }}">
                                         {{ $cta->nombre_beneficiario }} · {{ $cta->moneda }}
                                         · {{ $cta->cuenta_bancaria }}
                                     </option>
                                 @endforeach
                             </select>
                         </div>

                         {{-- Concepto --}}
                         <div class="col-md-12">
                             <label class="form-label">Concepto *</label>
                             <input type="text" name="concepto" class="form-control"
                                 placeholder="Ej. Traspaso entre cuentas" required>
                         </div>

                         {{-- Monto --}}
                         <div class="col-md-6">
                             <label class="form-label">Monto *</label>
                             <input type="number" step="0.01" min="0.01" name="monto" class="form-control"
                                 required>
                         </div>

                         {{-- Fecha de aplicación --}}
                         <div class="col-md-6">
                             <label class="form-label">Fecha de aplicación *</label>
                             <input type="date" name="fecha_aplicacion" class="form-control"
                                 value="{{ now()->format('Y-m-d') }}" required>
                         </div>

                     </div>
                 </div>

                 {{-- FOOTER --}}
                 <div class="modal-footer">
                     <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                         Cancelar
                     </button>
                     <button type="submit" class="btn btn-success">
                         <i class="fa fa-check me-1"></i>
                         Aplicar transferencia
                     </button>
                 </div>

             </form>

         </div>
     </div>
 </div>
