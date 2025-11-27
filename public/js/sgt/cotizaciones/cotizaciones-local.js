let map, marker;
let mapex, markerext;
const tasa_iva = 0.16;
const tasa_retencion = 0.04;
const catalogo_clientes = document.querySelector("#txtClientes");
const formCotizacion = document.querySelector('#cotizacionCreateMultiple')
const frmMode = (formCotizacion) ? formCotizacion.getAttribute("sgt-cotizacion-action") : null;

const formFields = [
    // Hidden
    { field: 'id_cliente', id: 'id_cliente', label: 'ID Cliente', required: false, type: 'hidden', master: false },

    // Selects principales
    { field: 'id_subcliente', id: 'id_subcliente', label: 'Sub Cliente', required: true, type: 'select', master: true },
    { field: 'id_proveedor', id: 'id_proveedor', label: 'Proveedor', required: false, type: 'select', master: true },
    { field: 'id_transportista', id: 'id_transportista', label: 'Transportista', required: false, type: 'select', master: true },

    // Datos del contenedor
    { field: 'num_contenedor', id: 'num_contenedor', label: 'Número de Contenedor', required: true, type: 'text', master: false },
    { field: 'tamano', id: 'tamano', label: 'Tamaño de Contenedor', required: true, type: 'numeric', master: false },
    { field: 'peso_reglamentario', id: 'peso_reglamentario', label: 'Peso Reglamentario', required: false, type: 'numeric', master: false },
    { field: 'sobrepeso', id: 'sobrepeso', label: 'Sobrepeso', required: false, type: 'numeric', master: false },
    { field: 'precio_sobre_peso', id: 'precio_sobre_peso', label: 'Precio Sobre Peso', required: false, type: 'numeric', master: true },
    { field: 'precio_tonelada', id: 'precio_tonelada', label: 'Precio Tonelada', required: false, type: 'numeric', master: false },
    { field: 'peso_contenedor', id: 'peso_contenedor', label: 'Peso Contenedor', required: true, type: 'numeric', master: false },
    { field: 'destino', id: 'destino', label: 'Destino', required: true, type: 'text', master: false },
    { field: 'Costomaniobra', id: 'Costomaniobra', label: 'Costo Maniobra', required: true, type: 'numeric', master: false },
    { field: 'estado_contenedor', id: 'estado_contenedor', label: 'Estado contenedor', required: false, type: 'text', master: false },


    // Datos de ruta
    { field: 'origen', id: 'origen', label: 'Origen', required: true, type: 'text', master: true },
    { field: 'fecha_modulacion', id: 'fecha_modulacion', label: 'Fecha Modulación', required: true, type: 'text', master: false },
    { field: 'num_pedimento', id: 'num_pedimento', label: 'Num. Pedimento', required: true, type: 'text', master: false },
    { field: 'cp_clase_ped', id: 'cp_clase_ped', label: 'Clase Pedimento', required: true, type: 'select', master: false },

    // Estadías / pernoctas
    { field: 'tarifa_estadia', id: 'tarifa_estadia', label: 'Tarifa Estadía', required: false, type: 'numeric', master: false },
    { field: 'dias_estadia', id: 'dias_estadia', label: 'Días Estadía', required: false, type: 'numeric', master: false },
    { field: 'total_estadia', id: 'total_estadia', label: 'Total Estadía', required: false, type: 'numeric', master: false },

    { field: 'tarifa_pernocta', id: 'tarifa_pernocta', label: 'Tarifa Pernocta', required: false, type: 'numeric', master: false },
    { field: 'dias_pernocta', id: 'dias_pernocta', label: 'Días Pernocta', required: false, type: 'numeric', master: false },
    { field: 'total_pernocta', id: 'total_pernocta', label: 'Total Pernocta', required: false, type: 'numeric', master: false },

    { field: 'total_general', id: 'total_general', label: 'Total General', required: false, type: 'numeric', master: false },

    // Campos comentados (los agrego por si se activan después)
    { field: 'fecha_liberacion', id: 'fecha_liberacion', label: 'Fecha Liberación', required: false, type: 'datetime', master: false },
    { field: 'motivo_demora', id: 'motivo_demora', label: 'Motivo Demora', required: false, type: 'textarea', master: false },
    { field: 'responsable', id: 'responsable', label: 'Responsable', required: false, type: 'text', master: false },
    { field: 'observaciones', id: 'observaciones', label: 'Observaciones', required: false, type: 'textarea', master: false },

    //boque
     { field: 'bloque', id: 'bloque', label: 'Núm. Bloque', required: false, type: 'text', master: false },
    { field: 'bloque_hora_i', id: 'bloque_hora_i', label: 'Hora Inicio', required: true, type: 'time', master: false },
    { field: 'bloque_hora_f', id: 'bloque_hora_f', label: 'Hora Fin', required: true, type: 'time', master: false }
];



function getClientes(clienteId,subclienteid) {
    $.ajax({
        type: 'GET',
        url: '/subclientes/' + clienteId,
        success: function(data) {
            $('#id_subcliente').empty();
            $('#id_subcliente').append('<option value="">Seleccionar subcliente</option>');
            $.each(data, function(key, subcliente) {
                if(subclienteid && subclienteid == subcliente.id) {
                    $('#id_subcliente').append('<option value="' + subcliente.id + '" selected>' + subcliente.nombre + '</option>');
                }else{
                    $('#id_subcliente').append('<option value="' + subcliente.id + '">' + subcliente.nombre + '</option>');
                }

            });
            $('#id_subcliente').select2();
        }
    });
}



function validateFormFields() {
    for (const f of formFields) {

        // 📌 1️⃣ Si el campo NO es requerido → saltar
        if (!f.required) continue;

        const el = document.getElementById(f.id);

        // 📌 2️⃣ Si el campo NO existe → saltar
        if (!el) continue;

        // 📌 3️⃣ Si está deshabilitado por permisos → NO validar
        if (el.disabled || el.readOnly) continue;

        let value = el.value?.trim() ?? "";

        // 📌 4️⃣ Validación por tipo
        let invalid = false;

        switch (f.type) {
            case "select":
                if (value === "" || value === "0" || value === null) invalid = true;
                break;

            case "numeric":
                if (value === "" || isNaN(value)) invalid = true;
                break;

            case "time":
                if (!/^\d{2}:\d{2}(:\d{2})?$/.test(value)) invalid = true;
                break;

            default:
                if (value === "") invalid = true;
        }

        // 📌 5️⃣ Si es inválido → lanzar alerta
        if (invalid) {
            Swal.fire({
                icon: 'warning',
                title: 'Campo requerido',
                html: `El campo <strong>${f.label}</strong> es obligatorio.`,
                confirmButtonText: "Entendido"
            });

            // Enfocar campo
            setTimeout(() => el.focus(), 300);

            return false;
        }
    }

    return true;
}


function getFormData() {
    const data = {};

    formFields.forEach(f => {
        const el = document.getElementById(f.id);
        if (!el) return;

        let value;

        if (f.type === 'select') {
            value = el.options[el.selectedIndex]?.value || null;
        } else {
            value = el.value || null;
        }

        // ---- LIMPIEZA AUTOMÁTICA DE NÚMEROS ----
        if (value && typeof value === 'string') {
            // Si contiene dígitos, puntos o comas, lo consideramos número
            if (/[\d.,]+/.test(value)) {
                value = value
                    .replace(/,/g, '')     // quitar comas
                    .replace(/\$/g, '')    // quitar moneda
                    .trim();

                // Si después de limpiar es numérico, conviértelo a número real
                if (!isNaN(value)) {
                    value = parseFloat(value);
                }
            }
        }
        // -----------------------------------------

        data[f.field] = value;
    });

        data["_token"] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    return data;
}


$('#solicitarservicio').on('click', function(e) {
    if(validateFormFields()){
        if(localStorage.getItem('cotizacionId')){
        const cotizacionId = localStorage.getItem('cotizacionId');
        updateCotizacion(cotizacionId);

    }else{
        guardarCotizacionLocal();
    }


    }

});

$('#btnContinuar').on('click', function () {
      if(validateFormFields()){
        if(localStorage.getItem('cotizacionId')){
        const cotizacionId = localStorage.getItem('cotizacionId');
        updateCotizacion(cotizacionId);

    }else{
        guardarCotizacionLocal();
    }


    }
});


function guardarCotizacionLocal() {

    let formData = new FormData();

    const data = getFormData();
    for (const campo in data) {
        formData.append(campo, data[campo]);
    }

    return $.ajax({
        url: COTIZACION_URL,
        method: "POST",
        data: formData,
        processData: false,
        contentType: false,
        success: function (res) {

            if (!res.success) {
                Swal.fire("Error", res.message, "error");
                return;
            }

            // Guardamos ID para subir archivos
            window.cotLocalId = res.id;

            $('#noticeFileUploader').addClass('d-none');
            $('#fileUploaderContainer').removeClass('d-none');

            Swal.fire({
                icon: 'success',
                title: 'Guardado correctamente',
                text: 'Ahora puede adjuntar los documentos.'
            });
            localStorage.setItem('cotizacionId',res. cotizacion_id);
            localStorage.setItem('numContenedor', res. num_contenedor);

            setTimeout(() => {
                    initFileUploader();
                }, 300);
        },
        error: function (err) {
            console.error(err);
            Swal.fire("Error", "Error al guardar la solicitud local", "error");
        }
    });
}


function updateCotizacion(id) {
   let formData = new FormData();
const data = getFormData();

// Convertir datos a FormData
for (const campo in data) {
    formData.append(campo, data[campo]);
}


const url =  `/cotizaciones/single/update-local/${id}`;

$.ajax({
    url: url,
    method: "POST",
    data: formData,
    processData: false,
    contentType: false,
    success: function (res) {

        // Guardar ID de la cotización
        const id = res.id ;
        window.cotLocalId = id;
        localStorage.setItem('cotizacionId', res.cotizacion_id);
        localStorage.setItem('numContenedor', res.num_contenedor);

        // Mostrar file uploader
        $('#noticeFileUploader').addClass('d-none');
        $('#fileUploaderContainer').removeClass('d-none');

        Swal.fire({
            icon: 'success',
            title: 'Actualizado correctamente',
            text: 'Ahora puede adjuntar los documentos.'
        });

        setTimeout(() => initFileUploader(), 300);
    },
    error: function (err) {
        console.error(err);
        Swal.fire("Error", "Error al guardar la solicitud local", "error");
    }
});
}







