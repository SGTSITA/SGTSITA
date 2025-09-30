@extends('layouts.app')

@section('template_title')
    Prestamos a Operadores
@endsection

@section('content')
<div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
<!-- Asegúrate de tener en tu <head> -->
<!-- <meta name="csrf-token" content="{{ csrf_token() }}"> -->

<div class="card shadow-sm">
  <div class="card-header bg-light text-dark">
    <h5 class="mb-0">Registro de Préstamo</h5>
  </div>
  <div class="card-body">
    <form id="formPrestamo" novalidate>
      <div class="row g-3">
        <!-- Nombre de operador -->
        <div class="col-md-6">
          <label for="id_operador" class="form-label">Nombre de operador</label>
          <select id="id_operador" name="id_operador" class="form-select" required>
            <option value="">Seleccione un operador</option>
            @foreach($operadores as $o)
            <option value="{{$o->id}}">{{$o->nombre}}</option>
            @endforeach

          </select>
          <div class="invalid-feedback">Debe seleccionar un operador.</div>
        </div>

        <!-- Cantidad de préstamo -->
        <div class="col-md-6">
          <label for="cantidad" class="form-label">Cantidad de préstamo</label>
          <input type="number" name="cantidad" id="cantidad" class="form-control" placeholder="Ingrese la cantidad" required min="0.01" step="0.01">
          <div class="invalid-feedback">Ingrese una cantidad válida mayor a 0.</div>
        </div>

        <!-- Tipo de descuento -->
        <div class="col-md-6">
          <label for="tipo_descuento" class="form-label">Tipo de descuento</label>
          <select id="tipo_descuento" name="tipo_descuento" class="form-select" required>
            <option value="">Seleccione un tipo</option>
            <option value="exhibicion">Una sola exhibición</option>
            <option value="parcialidades">En parcialidades</option>
          </select>
          <div class="invalid-feedback">Seleccione el tipo de descuento.</div>
        </div>

        <div class="col-md-6">
          <label for="id_banco" class="form-label">Banco de retiro</label>
          <select id="id_banco" name="id_banco" class="form-select" required>
            <option value="">Seleccione un banco</option>
            @foreach($bancos as $b)
            <option value="{{$b->id}}">{{$b->nombre_banco}}</option>
            @endforeach
          </select>
          <div class="invalid-feedback">Seleccione banco de retiro.</div>
        </div>

        <!-- Fecha de compromiso / pago (siempre visible) -->
        <div class="col-md-6">
          <label for="fecha_pago" class="form-label">Fecha de compromiso</label>
          <input type="date" name="fecha_pago" id="fecha_pago" class="form-control" required>
          <div class="invalid-feedback">Ingrese la fecha de compromiso.</div>
        </div>
      </div>

      <!-- Campos adicionales si es en parcialidades -->
      <div id="parcialidadesFields" class="row g-3 mt-2" style="display: none;">
        <div class="col-md-4">
          <label for="num_parcialidades" class="form-label">Número de parcialidades</label>
          <input type="number" name="num_parcialidades" id="num_parcialidades" class="form-control" placeholder="Ej. 6" min="1">
          <div class="invalid-feedback">Ingrese el número de parcialidades (mín. 1).</div>
        </div>

        <div class="col-md-4">
          <label for="frecuencia" class="form-label">Frecuencia</label>
          <select id="frecuencia" name="frecuencia" class="form-select">
            <option value="">Seleccione</option>
            <option value="semanal">Semanal</option>
            <option value="quincenal">Quincenal</option>
            <option value="mensual">Mensual</option>
          </select>
          <div class="invalid-feedback">Seleccione la frecuencia.</div>
        </div>
      </div>

      <div class="d-flex justify-content-end mt-4">
        <button type="submit" class="btn btn-success px-4">Guardar</button>
      </div>
    </form>
  </div>
</div>



            </div>
        </div>
</div>
@endsection

@push('custom-javascript')
<script>
$(function () {
  const $form = $("#formPrestamo");
  const $tipo = $("#tipo_descuento");
  const $parcialFields = $("#parcialidadesFields");
  const csrfToken = $('meta[name="csrf-token"]').attr('content') || '';

  function toggleParcialFields() {
    if ($tipo.val() === "parcialidades") {
      $parcialFields.show();
      $("#num_parcialidades").prop('required', true);
      $("#frecuencia").prop('required', true);
    } else {
      $parcialFields.hide();
      $("#num_parcialidades").prop('required', false).removeClass('is-invalid');
      $("#frecuencia").prop('required', false).removeClass('is-invalid');
      $("#num_parcialidades").val('');
      $("#frecuencia").val('');
    }
  }

  // Inicial
  toggleParcialFields();

  $tipo.on('change', function () {
    toggleParcialFields();
  });

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
    const tipo_descuento = $("#tipo_descuento").val();
    const fecha_pago = $("#fecha_pago").val();
    const num_parcialidades = $("#num_parcialidades").val();
    const frecuencia = $("#frecuencia").val();

    if (!id_operador) {
      $("#id_operador").addClass('is-invalid'); invalid = true;
    }
    if (!id_banco) {
      $("#id_banco").addClass('is-invalid'); invalid = true;
    }
    if (!cantidad || Number(cantidad) <= 0) {
      $("#cantidad").addClass('is-invalid'); invalid = true;
    }
    if (!tipo_descuento) {
      $("#tipo_descuento").addClass('is-invalid'); invalid = true;
    }
    if (!fecha_pago) {
      $("#fecha_pago").addClass('is-invalid'); invalid = true;
    }
    if (tipo_descuento === 'parcialidades') {
      if (!num_parcialidades || Number(num_parcialidades) < 1) {
        $("#num_parcialidades").addClass('is-invalid'); invalid = true;
      }
      if (!frecuencia) {
        $("#frecuencia").addClass('is-invalid'); invalid = true;
      }
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
      tipo_descuento,
      fecha_pago,
      num_parcialidades: tipo_descuento === 'parcialidades' ? num_parcialidades : null,
      frecuencia: tipo_descuento === 'parcialidades' ? frecuencia : null,
      _token: csrfToken
    };

    $.ajax({
      url: "/prestamos/store",
      method: "POST",
      data: payload,
      success: function (resp) {
        // éxito
        alert(resp.message || 'Préstamo guardado correctamente ✅');
        // opcional: reset form
        $form[0].reset();
        toggleParcialFields();
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
          alert('Ocurrió un error al guardar. Revisa la consola.');
        }
      }
    });
  });
});
</script>
@endpush