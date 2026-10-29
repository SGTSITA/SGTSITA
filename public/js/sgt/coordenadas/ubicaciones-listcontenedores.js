let contenedoresDisponibles;
let contenedoresAsignadosAntes;
let gridApi;
let mapaComparacion = null;
document.addEventListener('DOMContentLoaded', function () {
    definirTable();

    const modal = new bootstrap.Modal(document.getElementById('modalBuscarConvoy'), {
        backdrop: 'static',
        keyboard: false,
    });

    // Mostrar el modal al cargar la vista
    modal.show();

    // Mostrar el modal manualmente si el usuario da clic en el botón
    document.getElementById('btnNuevoconboy').addEventListener('click', function () {
        modal.show();
    });

    document.getElementById('formBuscarConvoy').addEventListener('submit', function (e) {
        e.preventDefault();

        const numero = document.getElementById('numero_convoy').value;

        fetch(`/coordenadas/conboys/getconvoy/${numero}`)
            .then((res) => res.json())
            .then((data) => {
                if (data.success) {
                    const fechaInicio = new Date(data.data.fecha_inicio);
                    const fechaFin = new Date(data.data.fecha_fin);

                    const formatoFecha = (fecha) => {
                        return `${fecha.getDate().toString().padStart(2, '0')}/${(fecha.getMonth() + 1).toString().padStart(2, '0')}/${fecha.getFullYear()}`;
                    };
                    document.getElementById('descripcionConvoy').textContent = data.data.nombre;
                    document.getElementById('fechaInicioConvoy').textContent = formatoFecha(fechaInicio);
                    document.getElementById('fechaFinConvoy').textContent = formatoFecha(fechaFin);
                    document.getElementById('id_convoy').value = data.data.idconvoy;
                    contenedoresDisponibles = data.data.contenedoresPropios;
                    contenedoresAsignadosAntes = data.data.contenedoresPropiosAsignados;
                    contenedoresAsignadosAntes.forEach((contenedor, index) => {
                        seleccionarContenedor(contenedor.num_contenedor);
                    });

                    document.getElementById('resultadoConvoy').style.display = 'block';
                } else {
                    alert('Convoy no encontrado.');
                }
            });
    });

    document.getElementById('btnGuardarContenedores').addEventListener('click', function () {
        const numeroConvoy = document.getElementById('numero_convoy').value;
        let idconvoy = document.getElementById('id_convoy').value;
        document.getElementById('ItemsSelects').value = ItemsSelects.join(';');

        if (!ItemsSelects || ItemsSelects.length === 0) {
            alert('Por favor, seleccione al menos un contenedor.');
            return;
        }
        let datap = {
            items_selects: ItemsSelects,
            idconvoy: idconvoy,
            numero_convoy: numeroConvoy,
        };

        fetch(`/coordenadas/conboys/agregar`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify(datap),
        })
            .then(async (res) => {
                if (!res.ok) {
                    // Intentamos extraer el mensaje del error (por si Laravel lo devuelve)
                    const errorText = await res.text();
                    throw new Error(errorText || 'Error desconocido del servidor');
                }
                return res.json();
            })
            .then((data) => {
                if (data.success) {
                    document.getElementById('modalBuscarConvoy').style.display = 'none';

                    Swal.fire({
                        title: 'Guardado correctamente',
                        text: data.message + ' ' + data.no_conboy,
                        icon: 'success',
                        confirmButtonText: 'Aceptar',
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: data.message || 'No se pudo guardar.',
                        icon: 'error',
                        confirmButtonText: 'Cerrar',
                    });
                }
            })
            .catch((error) => {
                console.error('Error en la petición:', error);

                Swal.fire({
                    title: 'Error inesperado',
                    text: error.message,
                    icon: 'error',
                    confirmButtonText: 'Cerrar',
                });
            });
    });
});
function abrirMapaEnNuevaPestana(latitud, longitud, latitud_seguimiento, longitud_seguimiento, contenedor) {
    const url = `/mapa-comparacion?latitud=${latitud}&longitud=${longitud}&latitud_seguimiento=${latitud_seguimiento}&longitud_seguimiento=${longitud_seguimiento}&contenedor=${contenedor}`;
    window.open(url, '_blank');
}

function calcularDistanciaKm(lat1, lon1, lat2, lon2) {
    const R = 6371; // Radio de la tierra en km
    const dLat = toRad(lat2 - lat1);
    const dLon = toRad(lon2 - lon1);
    const a =
        Math.sin(dLat / 2) * Math.sin(dLat / 2) +
        Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.sin(dLon / 2) * Math.sin(dLon / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return (R * c).toFixed(2); // Devuelve con 2 decimales
}

function toRad(value) {
    return (value * Math.PI) / 180;
}

function definirTable() {
    const columnDefs = [
        {
            headerCheckboxSelection: true,
            checkboxSelection: true,
            width: 40,
            pinned: 'left',
            suppressSizeToFit: true,
            resizable: false,
        },
        // { headerName: "No Coti", field: "id_cotizacion", sortable: true, filter: true },
        // { headerName: "No Asig", field: "id_asignacion", sortable: true, filter: true },
        // { headerName: "No Coor", field: "id_coordenada", sortable: true, filter: true },
        { headerName: 'Cliente', field: 'cliente', sortable: true, filter: true },
        { headerName: 'Origen', field: 'origen', sortable: true, filter: true },
        { headerName: 'Destino', field: 'destino', sortable: true, filter: true },
        { headerName: 'Contenedor', field: 'contenedor', sortable: true, filter: true },
        { headerName: 'Ultima Ubicación', field: 'direccion', sortable: true, filter: true },

        {
            headerName: 'Acciones',
            field: 'acciones',
            cellRenderer: function (params) {
                const container = document.createElement('div');
                const data = params.data; // aquí vienen lat1/lng1 y lat2/lng2
                const btnRastrear = document.createElement('button');

                btnRastrear.innerText = 'Comparar Ubicaciones';
                btnRastrear.classList.add('btn', 'btn-sm', 'btn-info');

                btnRastrear.onclick = function () {
                    const latitud = parseFloat(data.latitud);
                    const longitud = parseFloat(data.longitud);
                    if (isNaN(latitud) || isNaN(longitud)) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Ubicación no disponible',
                            text: 'Este registro aún no tiene direccion en mapa asignadas en cotizacion.',
                            confirmButtonText: 'Aceptar',
                        });
                        return; // Detener ejecución si no hay coordenadas
                    }

                    const latitud_seguimiento = parseFloat(data.latitud_seguimiento);
                    const longitud_seguimiento = parseFloat(data.longitud_seguimiento);

                    abrirMapaEnNuevaPestana(
                        latitud,
                        longitud,
                        latitud_seguimiento,
                        longitud_seguimiento,
                        data.contenedor,
                    );
                };

                container.appendChild(btnRastrear);
                return container;
            },
        },
    ];
    function formatDateForInput(dateString) {
        const date = new Date(dateString);
        const year = date.getFullYear();
        const month = `0${date.getMonth() + 1}`.slice(-2);
        const day = `0${date.getDate()}`.slice(-2);
        return `${year}-${month}-${day}`;
    }

    const gridOptions = {
        columnDefs: columnDefs,
        pagination: true,
        paginationPageSize: 100,
        // rowSelection: "multiple",
        defaultColDef: {
            resizable: true,
            flex: 1,
        },
    };

    const myGridElement = document.querySelector('#myGrid');
    gridApi = agGrid.createGrid(myGridElement, gridOptions);

    cargaConboys();

    function cargaConboys() {
        const overlay = document.getElementById('gridLoadingOverlay');
        overlay.style.display = 'flex';

        gridApi.setGridOption('rowData', []);

        fetch('/coordenadas/conboys/getHistorialUbicaciones')
            .then((response) => response.json())
            .then(async (data) => {
                const enriched = await Promise.all(
                    data.map(async (row) => {
                        const { latitud_seguimiento, longitud_seguimiento } = row;
                        try {
                            const resp = await fetch(
                                `https://nominatim.openstreetmap.org/reverse?format=json&lat=${latitud_seguimiento}&lon=${longitud_seguimiento}`,
                            );
                            const geo = await resp.json();
                            return {
                                ...row,
                                direccion: geo.display_name,
                            };
                        } catch (e) {
                            console.warn('No pude obtener dirección para', row, e);
                            return {
                                ...row,
                                direccion: null,
                            };
                        }
                    }),
                );
                gridApi.setGridOption('rowData', enriched);
                //contenedoresGuardados = data.dataConten;
            })
            .catch((error) => {
                console.error('❌ Error al obtener la lista de convoys:', error);
            })
            .finally(() => {
                overlay.style.display = 'none';
            });
    }
}

//sugerencias propias

const seleccionados = [];
const ItemsSelects = [];

function mostrarSugerencias() {
    const input = document.getElementById('contenedor-input');
    const filtro = input.value.trim().toUpperCase();
    const sugerenciasDiv = document.getElementById('sugerencias');
    sugerenciasDiv.innerHTML = '';

    if (filtro.length === 0) {
        sugerenciasDiv.style.display = 'none';
        return;
    }

    const filtrados = contenedoresDisponibles.filter(
        (c) => (c.num_contenedor || '').toUpperCase().includes(filtro) && !seleccionados.includes(c.num_contenedor),
    );

    filtrados.forEach((c) => {
        const item = document.createElement('div');
        item.textContent = c.num_contenedor;
        item.style.padding = '5px';
        item.style.cursor = 'pointer';
        item.onclick = () => seleccionarContenedor(c.num_contenedor);
        sugerenciasDiv.appendChild(item);
    });

    sugerenciasDiv.style.display = filtrados.length ? 'block' : 'none';
}

function seleccionarContenedor(valor) {
    seleccionados.push(valor);
    const contenedorData = contenedoresDisponibles.find((c) => c.num_contenedor === valor);

    ItemsSelects.push(valor + '-' + contenedorData.id_contenedor);
    document.getElementById('contenedor-input').value = '';
    document.getElementById('sugerencias').style.display = 'none';
    actualizarVista();
}

function agregarContenedor() {
    const input = document.getElementById('contenedor-input');
    const valor = input.value.trim().toUpperCase();
    if (valor && contenedoresDisponibles.includes(valor) && !seleccionados.includes(valor)) {
        seleccionados.push(valor);

        input.value = '';
        actualizarVista();
    }
}

function eliminarContenedor(idx) {
    seleccionados.splice(idx, 1);
    ItemsSelects.splice(idx, 1);
    actualizarVista();
}

function actualizarVista() {
    const tbody = document.querySelector('#tablaContenedores tbody');
    tbody.innerHTML = '';
    seleccionados.forEach((cont, i) => {
        const row = document.createElement('tr');
        row.innerHTML = `
          
            <td>${cont}</td>
            <td>
                <button type="button" 
                        class="btn btn-sm btn-danger"
                        onclick="eliminarContenedor(${i})">
                     <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });

    document.getElementById('contenedores').value = seleccionados.join(';');
    document.getElementById('ItemsSelects').value = ItemsSelects.join(';');
}

function limpiarFormularioConvoy() {
    // Limpiar tabla de contenedores
    const tbody = document.getElementById('tablaContenedores');
    tbody.innerHTML = '';

    // Limpiar inputs
    document.getElementById('numero_convoy').value = '';
    document.getElementById('id_convoy').value = '';

    // Limpiar selects ocultos o arrays usados
    ItemsSelects.length = 0; // Si es global, la reinicias
    document.getElementById('ItemsSelects').value = '';

    // Ocultar modal si es necesario
    document.getElementById('modalBuscarConvoy').style.display = 'none';

    // Limpiar también posibles mensajes o alertas
    // document.getElementById('resultadoBusquedaConvoy')?.innerHTML = '';
}
