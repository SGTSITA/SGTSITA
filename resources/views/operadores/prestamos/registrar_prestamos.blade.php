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