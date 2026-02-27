var containerMultipleLocal = document.getElementById('cotizacion-multiple');
var FileName = 'tableroCotizacionMultiple';

var dataLocal = [];
var proveedoresFormateadosLocal = [];
var transportistasFormateadosLocal = [];

// Obtener subclientes
async function getClientesLocal(clienteId) {
    let dataGetClientes = $.ajax({
        type: 'GET',
        url: '/subclientes/' + clienteId,
        success: function (data) {
            let dataClientes = [];
            $.each(data, function (key, subcliente) {
                dataClientes.push(formatoConsecutivo(subcliente.id) + ' - ' + subcliente.nombre);
            });
            dataDropDownLocal = dataClientes;
            return dataClientes;
        },
    });

    return dataGetClientes;
}

// Formatear proveedores
proveedoresLista.forEach((p) => {
    proveedoresFormateadosLocal.push(formatoConsecutivo(p.id) + ' - ' + p.nombre);
});

// Formatear transportistas
transportistasLista.forEach((t) => {
    transportistasFormateadosLocal.push(formatoConsecutivo(t.id) + ' - ' + t.nombre);
});

const formFieldsContenedoresLocal = [
    { field: 'id_subcliente', index: 0, label: 'Sub Cliente', required: true },
    { field: 'numContenedor', index: 3, label: 'Núm. Contenedor', required: true },
    { field: 'origen', index: 4, label: 'Origen', required: true },
    { field: 'destino', index: 5, label: 'Destino', required: true },
    { field: 'tamContenedor', index: 6, label: 'Tamaño Contenedor', required: true },
    { field: 'pesoContenedor', index: 7, label: 'Peso Contenedor', required: true },
];

function buildHandsOntableLocal() {
    //-------------------------------------------------------
    // 1) COLUMNAS FIJAS (MISMO ORDEN)
    //    SIMPLE: solo removimos 8–11
    //-------------------------------------------------------
    let columnas = [
        { data: 0, type: 'dropdown', source: dataDropDownLocal, strict: true, width: 150 }, // 0
        { data: 1, type: 'dropdown', source: proveedoresFormateadosLocal, strict: true, width: 150 }, // 1
        { data: 2, type: 'dropdown', source: transportistasFormateadosLocal, strict: true, width: 150 }, // 2
        { data: 3, width: 150 }, // CONTENEDOR
        { data: 4, width: 150 }, // ORIGEN
        { data: 5, width: 150 }, // DESTINO
        { data: 6, type: 'numeric', width: 150 }, // TAMAÑO
        { data: 7, type: 'numeric', width: 150 }, // PESO

        // OMITIDAS 8,9,10,11

        { data: 12, type: 'date', dateFormat: 'YYYY-MM-DD', correctFormat: true, width: 155 },
        { data: 13, type: 'date', dateFormat: 'YYYY-MM-DD', correctFormat: true, width: 140 },

        { data: 14, width: 130 }, // BLOQUE

        {
            data: 15,
            type: 'time',
            timeFormat: 'HH:mm:ss',
            correctFormat: true,
            width: 100,
        },

        {
            data: 16,
            type: 'time',
            timeFormat: 'HH:mm:ss',
            correctFormat: true,
            width: 100,
        },

        { data: 17, width: 200 }, // DIRECCIÓN
        { data: 18, readOnly: true }, // ID
    ];

    let headers = [
        'SUBCLIENTE',
        'PROVEEDOR',
        'TRANSPORTISTA',
        '# CONTENEDOR',
        'ORIGEN',
        'DESTINO',
        'TAMAÑO',
        'PESO',
        'FECHA MODULACIÓN',
        'FECHA ENTREGA',
        'NÚM BLOQUE',
        'HORA INICIO',
        'HORA FIN',
        'DIRECCIÓN',
        'ID',
    ];

    // columnas ocultas dinámicas
    let columnasOcultas = [];
    let fixetcolimns = 4;

    if (!canElegirProveedor) {
        columnasOcultas.push(1);
        columnasOcultas.push(2);
        fixetcolimns = 2;
    }

    // Se quitan las columnas 8–11 porque NO EXISTEN aquí
    columnasOcultas.push(18);

    var config = {
        data: dataLocal,
        colHeaders: headers,
        columns: columnas,
        rowHeaders: true,
        fixedColumnsLeft: fixetcolimns,
        height: 450,
        minSpareRows: 1,
        licenseKey: 'non-commercial-and-evaluation',

        hiddenColumns: {
            columns: columnasOcultas,
            indicators: false,
        },
    };

    var hotLocal = new Handsontable(containerMultipleLocal, config);

    function validateMultipleLocal() {
        let rows = hotLocal.getData();
        let filasValidas = [];

        for (let i = 0; i < rows.length; i++) {
            let r = rows[i];

            let filaVacia = r.every((v) => v === null || v === '');
            if (filaVacia) continue;

            for (let campo of formFieldsContenedoresLocal) {
                let val = r[campo.index];
                if (campo.required && (!val || val === '')) {
                    Swal.fire('Campo faltante', `Falta ${campo.label} en la fila ${i + 1}`, 'warning');
                    return false;
                }
            }

            filasValidas.push(r);
        }

        if (filasValidas.length === 0) {
            Swal.fire('Sin datos', 'No hay información para guardar.', 'info');
            return false;
        }

        createContenedoresMultipleLocal(filasValidas);
        return true;
    }

    // POST LOCAL
    function createContenedoresMultipleLocal(contenedores) {
        var _token = document.querySelector('meta[name="csrf-token"]').content;
        var uuid = localStorage.getItem('uuid');
        var permiso_proveedor = canElegirProveedor ? 1 : 0;
        var origen_captura = document.getElementById('origen_captura').value;

        $.post(
            '/viajes/solicitud/multiple-local',
            { _token, contenedores, uuid, permiso_proveedor, origen_captura },
            function (response) {
                Swal.fire(response.Titulo, response.Mensaje, response.TMensaje).then(() => {
                    if (response.TMensaje === 'success' || response.TMensaje === 'ok') {
                        location.reload();
                    }
                });
            },
        );
    }

    return {
        validarSolicitudLocal: validateMultipleLocal,
    };
}
