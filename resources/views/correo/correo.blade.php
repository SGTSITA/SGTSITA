@extends('layouts.usuario_externo')

@section('WorkSpace')
<div class="row gx-5 gx-xl-10">
  <div class="col-sm-12 mb-5 mb-xl-10">
    <div class="card card-flush h-lg-100">
      <div class="card-header">
        <h3 class="card-title align-items-start flex-column">
          <span class="card-label fw-bold text-gray-900">Correos</span>
          <span class="text-gray-500 mt-1 fw-semibold fs-6">
            Lista de <span class="text-gray-600 fw-bold">Correos</span>
          </span>
        </h3>
        <div class="card-toolbar">
          <button id="addRowButton" class="btn btn-sm btn-primary me-3">
            <i class="fas fa-plus"></i> Agregar Fila
          </button>
        </div>
      </div>

      <div class="card-body">
        <div class="table-responsive">
          <div id="correosTable" style="height: 450px; width: 100%;">
          </div>
        </div>
      </div>

      <div class="card-footer d-flex justify-content-end">
        <button id="saveChangesButton" class="btn btn-outline-success btn-sm rounded-3 px-4 py-2">
          <i class="fas fa-save"></i> Guardar Cambios
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Handsontable CSS y JS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/handsontable@12.0.1/dist/handsontable.full.min.css">
<script src="https://cdn.jsdelivr.net/npm/handsontable@12.0.1/dist/handsontable.full.min.js"></script>

<!-- Font Awesome para iconos -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<!-- SweetAlert2 CSS y JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('correosTable');

    const initialData = @json($correos).map(correo => [
        correo.id || null,         // ID
        correo.correo || '',       // Correo
        'MEC',                     // Tipo de Correo (Fijo)
        !!correo.cotizacion_nueva,  // Cotización Nueva
        !!correo.cancelacion_viaje, // Cancelación de Viaje
        !!correo.nuevo_documento,   // Nuevo Documento Cargado
        !!correo.viaje_modificado   // Viaje Modificado
    ]);

    let nextId = initialData.length > 0 ? Math.max(...initialData.map(row => row[0])) + 1 : 1;

    const hot = new Handsontable(container, {
        data: initialData,
        rowHeaders: true,
        colHeaders: [
            'ID',
            'Correo',
            'Tipo de Correo',
            'Cotización Nueva',
            'Cancelación de Viaje',
            'Nuevo Documento Cargado',
            'Viaje Modificado'
        ],
        columns: [
            { data: 0, type: 'numeric', readOnly: true }, // ID (solo lectura)
            { data: 1, type: 'text' },                   // Correo
            { data: 2, type: 'text', readOnly: true },   // Tipo de Correo (Siempre MEC)
            { data: 3, type: 'checkbox', className: 'htCenter' }, // Cotización Nueva
            { data: 4, type: 'checkbox', className: 'htCenter' }, // Cancelación de Viaje
            { data: 5, type: 'checkbox', className: 'htCenter' }, // Nuevo Documento Cargado
            { data: 6, type: 'checkbox', className: 'htCenter' }  // Viaje Modificado
        ],
        hiddenColumns: {
            columns: [0,2], // Ocultamos la columna "Tipo de Correo"
            indicators: false // No muestra el indicador de columna oculta
        },
        licenseKey: 'non-commercial-and-evaluation',
        width: '100%',
        height: 'auto',
        stretchH: 'all',
    });

    // Botón para agregar fila
    document.getElementById('addRowButton').addEventListener('click', function () {
        const newRow = [
            nextId++, // ID automático
            '',       // Correo
            'MEC',    // Tipo de Correo fijo
            false,    // Cotización Nueva
            false,    // Cancelación de Viaje
            false,    // Nuevo Documento Cargado
            false     // Viaje Modificado
        ];
        hot.alter('insert_row', hot.countRows());
        hot.setDataAtRowProp(hot.countRows() - 1, undefined, newRow);
    });

    // Botón para guardar cambios
    document.getElementById('saveChangesButton').addEventListener('click', function () {
        Swal.fire({
            title: "¿Estás seguro?",
            text: "Se guardarán los cambios.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, guardar",
            cancelButtonText: "Cancelar"
        }).then((result) => {
            if (result.isConfirmed) {
                const updatedData = hot.getData();

                fetch('{{ route("configmec.update") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(updatedData)
                })
                .then(response => response.json())
                .then(result => {
                    Swal.fire({ title: "Guardado", text: result.message, icon: "success" });
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({ title: "Error", text: "No se guardaron los cambios.", icon: "error" });
                });
            }
        });
    });
});
</script>
@endsection