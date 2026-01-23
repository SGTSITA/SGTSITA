let operadores = [];
let unidades = [];

const formFieldsMep = [
    {
        field: 'txtOperador',
        id: 'txtOperador',
        label: 'Nombre operador',
        required: true,
        type: 'text',
        trigger: 'none',
    },
    { field: 'txtTelefono', id: 'txtTelefono', label: 'Teléfono', required: true, type: 'text', trigger: 'none' },
    {
        field: 'txtNumUnidad',
        id: 'txtNumUnidad',
        label: 'Núm Eco/ Núm Unidad / Identificador',
        required: true,
        type: 'text',
        trigger: 'none',
    },
    { field: 'txtPlacas', id: 'txtPlacas', label: 'Placas', required: true, type: 'text', trigger: 'none' },
    { field: 'txtSerie', id: 'txtSerie', label: 'Núm Serie / VIN', required: true, type: 'text', trigger: 'none' },
    { field: 'selectGPS', id: 'selectGPS', label: 'Compañia GPS', required: true, type: 'text', trigger: 'none' },
    { field: 'txtImei', id: 'txtImei', label: 'IMEI', required: true, type: 'text', trigger: 'none' },
    { field: 'txtTipoViaje', id: 'txtTipoViaje', label: 'Tipo Viaje', required: false, type: 'text', trigger: 'none' },
    {
        field: 'txtFechaInicio',
        id: 'txtFechaInicio',
        label: 'Fecha Salida',
        required: false,
        type: 'text',
        trigger: 'none',
    },
    {
        field: 'txtFechaFinal',
        id: 'txtFechaFinal',
        label: 'Fecha Entrega',
        required: false,
        type: 'text',
        trigger: 'none',
    },

    {
        field: 'txtNumChasisA',
        id: 'txtNumChasisA',
        label: 'Núm Eco/ Núm Chasis / Identificador',
        required: true,
        type: 'text',
        trigger: 'none',
    },
    { field: 'txtPlacasA', id: 'txtPlacasA', label: 'Placas Chasis A', required: true, type: 'text', trigger: 'none' },
    {
        field: 'selectChasisAGPS',
        id: 'selectChasisAGPS',
        label: 'Compañia GPS Chasis A',
        required: true,
        type: 'text',
        trigger: 'none',
    },
    {
        field: 'txtImeiChasisA',
        id: 'txtImeiChasisA',
        label: 'IMEI Chasis A',
        required: true,
        type: 'text',
        trigger: 'none',
    },

    {
        field: 'txtNumChasisB',
        id: 'txtNumChasisB',
        label: 'Núm Eco/ Núm Chasis B / Identificador',
        required: false,
        type: 'text',
        trigger: 'txtTipoViaje',
        expectedValue: 'Full',
        labelTrigger: 'Tipo Viaje',
    },
    {
        field: 'txtPlacasB',
        id: 'txtPlacasB',
        label: 'Placas Chasis B',
        required: false,
        type: 'text',
        trigger: 'txtNumChasisB',
    },
    {
        field: 'selectChasisBGPS',
        id: 'selectChasisBGPS',
        label: 'Compañia GPS Chasis B',
        required: false,
        type: 'text',
        trigger: 'txtNumChasisB',
    },
    {
        field: 'txtImeiChasisB',
        id: 'txtImeiChasisB',
        label: 'IMEI Chasis B',
        required: false,
        type: 'text',
        trigger: 'txtNumChasisB',
    },
    { field: 'cmbProveedor', id: 'cmbProveedor', label: 'Proveedor', required: false, type: 'hidden', trigger: 'none' },
];

const normalizarFecha = (valueFecha) => {
    if (!valueFecha) return null;

    if (valueFecha.includes('/')) {
        const [d, m, y] = valueFecha.split('/');
        return `${y}-${m}-${d}`;
    }

    return valueFecha.substring(0, 10);
};

const btnAsignaOperador = document.querySelector('#btnAsignaOperador');
const btnPlanearViaje = document.querySelector('#btnPlanearViaje');

function asignarOperador2(planear = 0) {
    const formData = {};

    //formFieldsMep
    let passValidation = formFieldsMep.every((item) => {
        let field = document.getElementById(item.field);
        let trigger = item.trigger;

        if (trigger != 'none') {
            let primaryField = document.getElementById(trigger);

            if (field.value.length <= 0 && primaryField.value == item.expectedValue) {
                if (field.value.length === 0) {
                    Swal.fire(
                        `El campo "${item.label}" es condicional y está vacío`,
                        `El campo "${item.label}" es obligatorio cuando el valor de ${item.labelTrigger} es ${item.expectedValue}. Por favor proporcione está información.`,
                        'warning',
                    );
                    return false;
                }
            }
        }

        if (field) {
            if (item.required === true && field.value.length == 0) {
                Swal.fire(
                    'El campo ' + item.label + ' es obligatorio',
                    'Parece que no ha proporcionado información en el campo ' + item.label,
                    'warning',
                );
                return false;
            }

            //validar fechas de inicio y fin si se planea el viaje.
            if (planear === 1 && (item.field === 'txtFechaInicio' || item.field === 'txtFechaFinal')) {
                let valueFecha = field.value;

                if (!valueFecha) {
                    Swal.fire(
                        'El campo ' + item.label + ' es obligatorio al planear el viaje',
                        'Parece que no ha proporcionado información en el campo ' + item.label,
                        'warning',
                    );
                    return false;
                }
                //formatear fecha para yyy-mm-dd hh:mm:ss
                let fechaObj = normalizarFecha(valueFecha);

                formData[item.field] = fechaObj;
                return true;
            }
        }

        if (field.dataset.mepUnidad) {
            formData['mepUnidad'] = field.dataset.mepUnidad;
        }

        if (field.dataset.mepOperador) {
            formData['mepOperador'] = field.dataset.mepOperador;
        }

        formData[item.field] = field.value;

        return true;
    });
    formData['planear'] = planear;

    if (!passValidation) return passValidation;

    let idContenedor = localStorage.getItem('idContenedor');

    let data = { idContenedor: idContenedor, formData: formData };
    fetch('/mep/viajes/operador/asignar', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
        },
        body: JSON.stringify(data),
    })
        .then((response) => {
            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor');
            }
            return response.json();
        })
        .then((data) => {
            console.log('Respuesta del backend:', data);

            Swal.fire(data.Titulo, data.Mensaje, data.TMensaje);
        })
        .catch((error) => {
            console.error('Error al enviar los datos:', error);
            alert('Ocurrió un error al asignar el operador.');
        });
}

btnAsignaOperador.addEventListener('click', () => asignarOperador2(0));

btnPlanearViaje.addEventListener('click', () => asignarOperador2(1));
// async function buscarRecurso(params = {}) {
//     const query = new URLSearchParams(params).toString();

//     try {
//         const response = await fetch(`/api/buscar-recursos?${query}`, {
//             headers: {
//                 Accept: 'application/json',
//             },
//         });

//         if (!response.ok) {
//             throw new Error('Error en la búsqueda');
//         }

//         return await response.json();
//     } catch (error) {
//         console.error(error);
//         return [];
//     }
// }

function buscarOperador(nombre) {
    let operador = operadores.find((op) => {
        return op.nombre === nombre ? op : false;
    });

    let txtTelefono = document.querySelector('#txtTelefono');

    toastr.options.positionClass = 'toast-middle-center';
    let txtOperador = document.querySelector('#txtOperador');

    if (operador) {
        txtTelefono.value = operador.telefono;
        txtOperador.dataset.mepOperador = operador.id;
        toastr.success('Operador identificado');
    } else {
        txtTelefono.value = '';
        txtOperador.dataset.mepOperador = 0;
        toastr.warning('Operador no encontrado');
    }
}

function buscarUnidad(numUnidad, sTipo) {
    let unidad = unidades.find((u) => {
        return u.id_equipo === numUnidad.toUpperCase() ? u : false;
    });

    // let txtPlacas = document.querySelector('#txtPlacas')
    // let txtSerie = document.querySelector('#txtSerie')
    // let txtImei = document.querySelector('#txtImei')
    // let selectGPS = document.querySelector('#selectGPS')

    // let txtNumUnidad = document.querySelector("#txtNumUnidad")

    let mapInputs = {
        Unidad: {
            placas: 'txtPlacas',
            serie: 'txtSerie',
            imei: 'txtImei',
            gps: 'selectGPS',
            dataset: 'txtNumUnidad',
        },
        ChasisA: {
            placas: 'txtPlacasA',
            serie: null,
            imei: 'txtImeiChasisA',
            gps: 'selectChasisAGPS',
            dataset: 'txtNumChasisA',
        },
        ChasisB: {
            placas: 'txtPlacasB',
            serie: null,
            imei: 'txtImeiChasisB',
            gps: 'selectChasisBGPS',
            dataset: 'txtNumChasisB',
        },
    };
    let config = mapInputs[sTipo];

    toastr.options.positionClass = 'toast-middle-center';
    if (unidad) {
        if (config.placas) document.getElementById(config.placas).value = unidad.placas ?? '';

        if (config.serie) document.getElementById(config.serie).value = unidad.num_serie ?? '';

        if (config.imei) document.getElementById(config.imei).value = unidad.imei ?? '';

        if (config.gps) {
            let select = document.getElementById(config.gps);
            for (let i = 0; i < select.options.length; i++) {
                if (String(select.options[i].value) === String(unidad.gps_company_id)) {
                    select.selectedIndex = i;
                    break;
                }
            }
        }

        document.getElementById(config.dataset).dataset.mepUnidad = unidad.id;

        toastr.success('Unidad identificado');
    } else {
        if (config.placas) document.getElementById(config.placas).value = '';

        if (config.serie) document.getElementById(config.serie).value = '';

        if (config.imei) document.getElementById(config.imei).value = '';

        if (config.gps) document.getElementById(config.gps).selectedIndex = 0;

        document.getElementById(config.dataset).dataset.mepUnidad = 0;
        toastr.warning('No se encontró unidad');
    }
}

function getCatalogoOperadorUnidad() {
    let _token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    $.ajax({
        url: '/mep/catalogos/operador-unidad',
        type: 'post',
        data: { _token },
        beforeSend: () => {},
        success: (response) => {
            operadores = response.operadores;
            unidades = response.unidades;
        },
        error: () => {
            console.error('No pudimos obtener los datos de operadores y unidades de la empresa.');
        },
    });
}

function abrirDocumentos(idCotizacion) {
    $(`#estatusDoc${idCotizacion}`).modal('show');
}

function descargarPDF(idCotizacion) {
    const fecha = new Date().toISOString().slice(0, 10); // formato: YYYY-MM-DD
    const link = document.createElement('a');
    link.href = `/cotizaciones/pdf/${idCotizacion}`;
    link.download = `cotizacion_${idCotizacion}_${fecha}.pdf`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function abrirDocumentos(idCotizacion) {
    fetch(`/cotizaciones/documentos/${idCotizacion}`)
        .then((response) => response.json())
        .then((data) => {
            const modal = new bootstrap.Modal(document.getElementById('modalEstatusDocumentos'));
            const titulo = document.getElementById('tituloContenedor');
            const cuerpo = document.getElementById('estatusDocumentosBody');

            titulo.innerText = `#${data.num_contenedor ?? 'N/A'}`;
            cuerpo.innerHTML = '';

            const campos = [
                { label: 'Num contenedor', valor: data.num_contenedor },
                { label: 'Documento CCP', valor: data.doc_ccp },
                { label: 'Boleta de Liberación', valor: data.boleta_liberacion },
                { label: 'Doda', valor: data.doda },
                { label: 'Carta Porte', valor: data.carta_porte },
                { label: 'Boleta Vacio', valor: data.boleta_vacio === 'si' },
                { label: 'EIR', valor: data.doc_eir },
                // { label: 'Foto Patio', valor: data.foto_patio },
            ];

            campos.forEach((item) => {
                const col = document.createElement('div');
                col.className = 'col-6';
                col.innerHTML = `
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid ${item.valor ? 'fa-check-circle text-success' : 'fa-times-circle text-muted'}"></i>
                        <span class="fw-semibold">${item.label}</span>
                    </div>
                `;
                cuerpo.appendChild(col);
            });

            modal.show();
        })
        .catch((error) => {
            console.error('Error al obtener documentos:', error);
            Swal.fire('Error', 'No se pudieron obtener los documentos', 'error');
        });
}

function cambiarTab(tabId) {
    // Ocultamos todos los divs con clase 'tab-content'
    const tabs = document.querySelectorAll('.tab-content');
    tabs.forEach((tab) => {
        tab.style.display = 'none';
    });

    // Mostramos solo el que corresponde
    const tabToShow = document.getElementById('tab-' + tabId);
    if (tabToShow) {
        tabToShow.style.display = 'block';
    } else {
        console.error(`No se encontró el tab: tab-${tabId}`);
    }
}
