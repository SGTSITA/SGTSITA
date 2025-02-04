@extends('layouts.app')

@section('template_title')
    Correos
@endsection

@section('content')
<div class="container-fluid mt-3">
    <div class="row">
        <div class="col">
            <div class="card">
                <!-- Card header con título alineado a la izquierda -->
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="mb-0">Correos</h3>
                    <div>
                        <button id="addRowButton" class="btn btn-outline-secondary btn-sm rounded-3 px-4 py-2">
                            <i class="fas fa-plus"></i> Agregar Fila
                        </button>
                    </div>
                </div>

                <div style="display: flex; justify-content: center; padding: 20px;">
                    <div id="correosTable"></div>
                </div>

                <!-- Botón Guardar Cambios alineado a la derecha -->
                <div class="card-footer d-flex justify-content-end">
                    <button id="saveChangesButton" class="btn btn-outline-success btn-sm rounded-3 px-4 py-2">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Handsontable CSS y JS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/handsontable@12.0.1/dist/handsontable.full.min.css">
<script src="https://cdn.jsdelivr.net/npm/handsontable@12.0.1/dist/handsontable.full.min.js"></script>

<!-- Font Awesome para los íconos de los botones -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<!-- SweetAlert2 CSS y JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('correosTable');

    // Datos iniciales (pueden ser vacíos o cargados desde el servidor)
    const initialData = @json($correos).map(correo => [
        correo.id || null,                 // ID
        correo.correo || '',               // Correo
        correo.tipo_correo || 'Personal',  // Tipo de Correo
        correo.referencia || '',           // Referencia
        !!correo.notificacion_nueva,       // Notificación Nueva (checkbox)
        !!correo.cancelacion_viaje,        // Cancelación de Viaje (checkbox)
        !!correo.nuevo_documento,          // Nuevo Documento Cargado (checkbox)
        !!correo.viaje_modificado          // Viaje Modificado (checkbox)
    ]);

    let nextId = initialData.length > 0 ? Math.max(...initialData.map(row => row[0])) + 1 : 1;

    // Inicializar Handsontable con checkboxes centrados
    const hot = new Handsontable(container, {
        data: initialData,
        rowHeaders: true,
        colHeaders: [
            'ID',
            'Correo',
            'Tipo de Correo',
            'Referencia',
            'Notificación Nueva',
            'Cancelación de Viaje',
            'Nuevo Documento Cargado',
            'Viaje Modificado'
        ],
        columns: [
            { data: 0, type: 'numeric', readOnly: true }, // ID (solo lectura)
            { data: 1, type: 'text' },                   // Correo
            { data: 2, type: 'dropdown', source: ['SGT', 'MEC'] }, // Tipo de Correo
            { data: 3, type: 'text' },                   // Referencia
            { data: 4, type: 'checkbox', className: 'htCenter' }, // Notificación Nueva
            { data: 5, type: 'checkbox', className: 'htCenter' }, // Cancelación de Viaje
            { data: 6, type: 'checkbox', className: 'htCenter' }, // Nuevo Documento Cargado
            { data: 7, type: 'checkbox', className: 'htCenter' }  // Viaje Modificado
        ],
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
            'Personal', // Tipo de Correo por defecto
            '',       // Referencia
            false,    // Notificación Nueva (checkbox por defecto)
            false,    // Cancelación de Viaje (checkbox por defecto)
            false,    // Nuevo Documento Cargado (checkbox por defecto)
            false     // Viaje Modificado (checkbox por defecto)
        ];
        hot.alter('insert_row', hot.countRows()); // Inserta una nueva fila
        hot.setDataAtRowProp(hot.countRows() - 1, undefined, newRow); // Asigna valores a la nueva fila
    });

    // Botón para guardar cambios con confirmación SweetAlert
    document.getElementById('saveChangesButton').addEventListener('click', function () {
        Swal.fire({
            title: "¿Estás seguro?",
            text: "Se guardarán los cambios en la base de datos.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, guardar",
            cancelButtonText: "Cancelar"
        }).then((result) => {
            if (result.isConfirmed) {
                const updatedData = hot.getData();

                fetch('/correo', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(updatedData)
                })
                .then(response => response.json())
                .then(result => {
                    Swal.fire({
                        title: "Guardado",
                        text: result.message,
                        icon: "success",
                        confirmButtonText: "OK"
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        title: "Error",
                        text: "No se pudieron guardar los cambios.",
                        icon: "error",
                        confirmButtonText: "Cerrar"
                    });
                });
            }
        });
    });
});
</script>



@endsection

