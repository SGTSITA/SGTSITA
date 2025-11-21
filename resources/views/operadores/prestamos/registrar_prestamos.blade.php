@extends('layouts.app')

@section('template_title')
    Prestamos a Operadores
@endsection

@section('content')
<style>
    .btn-abonar {
      background-color: #28a745;
      color: white;
      border: none;
      padding: 4px 8px;
      border-radius: 6px;
      cursor: pointer;
      transition: background 0.2s;
  }
  .btn-abonar:hover {
      background-color: #218838;
  }
</style>
<div class="container-fluid">
  <div class="row">
    <div class="col-sm-12">
      <div class="card shadow-sm">
        <div class="card-header bg-light text-dark">
          <h5 class="mb-0">Registro de Préstamo</h5>
        </div>

        <div class="card-body">
          <form id="formPrestamo" novalidate>
            <div class="row align-items-end g-3">
              <!-- Nombre de operador -->
              <div class="col-md-4">
                <label for="id_operador" class="form-label fw-semibold">Nombre de operador</label>
                <select id="id_operador" name="id_operador" class="form-select" required>
                  <option value="">Seleccione un operador</option>
                  @foreach($operadores as $o)
                    <option value="{{ $o->id }}">{{ $o->nombre }}</option>
                  @endforeach
                </select>
                <div class="invalid-feedback">Debe seleccionar un operador.</div>
              </div>

              <!-- Cantidad -->
              <div class="col-md-2">
                <label for="cantidad" class="form-label fw-semibold">Cantidad de préstamo</label>
                <div class="input-group">
                  <span class="input-group-text bg-light border-end-0">$</span>
                  <input type="number" name="cantidad" id="cantidad"
                         class="form-control border-start-0" 
                         placeholder="Ingrese la cantidad" required min="0.01" step="0.01">
                </div>
                <div class="invalid-feedback">Ingrese una cantidad válida mayor a 0.</div>
              </div>

              <!-- Banco -->
              <div class="col-md-4">
                <label for="id_banco" class="form-label fw-semibold">Banco de retiro</label>
                <select id="id_banco" name="id_banco" class="form-select" required>
                  <option value="">Seleccione un banco</option>
                  @foreach($bancos as $b)
                    <option value="{{ $b->id }}">{{ $b->nombre_banco }}</option>
                  @endforeach
                </select>
                <div class="invalid-feedback">Seleccione banco de retiro.</div>
              </div>

              <!-- Botón -->
              <div class="col-md-2 text-end">
                <button type="submit" class="btn btn-success w-100">
                  <i class="bi bi-save me-1"></i> Guardar
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="container-fluid mt-4">
  <div class="row">
    <div class="col-sm-12">
      <div class="card shadow-sm">
        <div class="card-header bg-light text-dark d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Lista de Préstamos</h5>
          <button class="btn btn-outline-secondary btn-sm" id="btnRecargarGrid">
            <i class="bi bi-arrow-repeat me-1"></i> Recargar
          </button>
        </div>
        <div class="card-body">
          <!-- Aquí irá tu AG Grid -->
          <div id="gridPrestamosActivos" class="ag-theme-alpine" style="height: 400px; width: 100%;"></div>
        </div>
      </div>
    </div>
  </div>
</div>
@include('operadores.prestamos.modal-abono-prestamo')
@include('operadores.prestamos.modal-detalle-abonos')
@endsection

<script>

  window.prestamos = @json($prestamos);
</script>

@push('custom-javascript')


<script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>
<script src="{{ asset('js/sgt/operadores/prestamos.js') }}?v={{ filemtime(public_path('js/sgt/operadores/prestamos.js')) }}"></script>  
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>

<script>
$(function () {
  const $form = $("#formPrestamo");

  const csrfToken = $('meta[name="csrf-token"]').attr('content') || '';



  

  // Limpiar errores al cambiar inputs
  $form.on('input change', 'input, select', function () {
    $(this).removeClass('is-invalid');
  });

  $form.on('submit', function (e) {
    e.preventDefault();

    // Limpiar marcas previas
    $form.find('.is-invalid').removeClass('is-invalid');

    // Validación simple en cliente
    let invalid = false;

    const id_operador = $("#id_operador").val();
    const id_banco = $("#id_banco").val();

    const cantidad = $("#cantidad").val();
    

    if (!id_operador) {
      $("#id_operador").addClass('is-invalid'); invalid = true;
    }
    if (!id_banco) {
      $("#id_banco").addClass('is-invalid'); invalid = true;
    }
    if (!cantidad || Number(cantidad) <= 0) {
      $("#cantidad").addClass('is-invalid'); invalid = true;
    }



    if (invalid) {
      // no enviamos si hay errores cliente
      return;
    }

    // Payload
    const payload = {
      id_operador,
      id_banco,
      cantidad,
     
      _token: csrfToken
    };

    $.ajax({
      url: "/prestamos/store",
      method: "POST",
      data: payload,
      success: function (resp) {
        // éxito
        Swal.fire('Préstamo guardado correctamente','','success');
        // opcional: reset form
        $form[0].reset();
       
      },
      error: function (xhr) {
        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
          const errors = xhr.responseJSON.errors;
          // asigna errores a campos
          Object.keys(errors).forEach(function (field) {
            // los nombres son iguales a los name/id usados en el form
            const $el = $("#" + field);
            if ($el.length) {
              $el.addClass('is-invalid');
              // muestra mensaje en el .invalid-feedback (el primero)
              $el.next('.invalid-feedback').text(errors[field][0]);
            }
          });
        } else {
          console.error(xhr.responseText);
          Swal.fire('Ocurrió un error al guardar.','Revisa la consola.','error');
        }
      }
    });
  });
});
</script>
@endpush