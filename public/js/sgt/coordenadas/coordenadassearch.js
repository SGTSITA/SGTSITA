const preguntasPorTipo = {
    b: [
        { texto: '1)¿ Registro en Puerto ?', campo: 'registro_puerto' },
        { texto: '2)¿ Dentro de Puerto ?', campo: 'dentro_puerto' },
        { texto: '3)¿ Cargado Contenedor ?', campo: 'cargado_contenedor' },
        { texto: '4)¿ En Fila Fiscal ?', campo: 'fila_fiscal' },
        {
            texto: '5)¿ Modulado ?',
            campo: 'modulado_tipo',
            opciones: ['5.1) Verde', '5.2) Amarillo', '5.3) Rojo', '5.4) OVT'],
        },
        { texto: '6)¿ Descarga en patio ?', campo: 'descarga_patio' },
        { texto: '7) Toma Foto de Boleta de Patio', campo: 'toma_foto_patio' },
    ],
    f: [
        { texto: '1)¿Carga en patio?', campo: 'cargado_patio' },
        { texto: '2)¿Inicio ruta?', campo: 'en_destino' },
        { texto: '3)¿Inicia carga?', campo: 'inicio_descarga' },
        { texto: '4)¿Fin descarga?', campo: 'fin_descarga' },
        { texto: '5)¿Recepción Doctos Firmados?', campo: 'recepcion_doc_firmados' },
    ],
    c: [
        { texto: '¿1) Registro en Puerto ?', campo: 'registro_puerto' },
        { texto: '¿2) Dentro de Puerto ?', campo: 'dentro_puerto' },
        { texto: '¿3) Cargado Contenedor?', campo: 'cargado_contenedor' },
        { texto: '¿4) En Fila Fiscal?', campo: 'fila_fiscal' },
        {
            texto: '¿5) Modulado?',
            campo: 'modulado_tipo',
            opciones: ['5.1) Verde', '5.2) Amarillo', '5.3) Rojo', '5.4) OVT'],
        },
        { texto: '¿6) En Destino?', campo: 'en_destino' },
        { texto: '¿7) Inicio Descarga?', campo: 'inicio_descarga' },
        { texto: '¿8) Fin Descarga?', campo: 'fin_descarga' },
        { texto: '¿9) Recepción Doctos Firmados?', campo: 'recepcion_doc_firmados' },
    ],
};

let contenedoresDisponibles = [];

document.addEventListener('DOMContentLoaded', function () {
    let gridApi;

    let mapModal = document.getElementById('modalMapa');
    if (mapModal) {
        makeDraggable(mapModal);
    }

    let PreguntaA;

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
        { headerName: '# Contenedor', field: 'contenedor', sortable: true, filter: true },
        { headerName: 'Origen', field: 'origen', sortable: true, filter: true },
        { headerName: 'Destino', field: 'destino', sortable: true, filter: true },

        {
            headerName: 'E Burrero',
            field: 'Estatus_Burrero',
            minWidth: 180,
            cellRenderer: function (params) {
                let color = 'secondary';
                let clasIcon = 'fa fa-hourglass-half me-1';
                if (String(params.data.tipo_b_estado) === '2') {
                    color = 'success';
                    clasIcon = 'fa fa-check-circle me-1 text-success';
                } else if (String(params.data.tipo_b_estado) === '1') {
                    color = 'primary';
                    clasIcon = ' fa fa-play-circle me-1';
                } else if (String(params.data.tipo_b_estado) === '0') {
                    color = 'warning';
                    clasIcon = 'fa fa-hourglass-half me-1';
                }
                return `
                        <button class="btn btn-sm btn-outline-${color} ver-mapa-btn" 
                            data-tipo="b"
                            data-info='${JSON.stringify(params.data).replace(/'/g, '&#39;')}' 
                            title="Ver progreso...">
                            <i class="${clasIcon}"></i> ${params.data.Estatus_Burrero}
                        </button>
                `;
            },
        },
        {
            headerName: 'E Foraneo',
            field: 'Estatus_Foraneo',
            minWidth: 180,
            cellRenderer: function (params) {
                let color = 'secondary';
                let clasIcon = 'fa fa-hourglass-half me-1';
                if (String(params.data.tipo_f_estado) === '2') {
                    color = 'success';
                    clasIcon = 'fa fa-check-circle me-1 text-success';
                } else if (String(params.data.tipo_f_estado) === '1') {
                    color = 'primary';
                    clasIcon = ' fa fa-play-circle me-1';
                } else if (String(params.data.tipo_f_estado) === '0') {
                    color = 'warning';
                    clasIcon = 'fa fa-hourglass-half me-1';
                }
                return `
                  
                    <button class="btn btn-sm btn-outline-${color} ver-mapa-btn" 
                    data-tipo="f"
                    data-info='${JSON.stringify(params.data).replace(/'/g, '&#39;')}' 
                    title="Ver progreso...">
                    <i class="${clasIcon}"></i> ${params.data.Estatus_Foraneo}
                </button>
                `;
            },
        },
        {
            headerName: 'E Completo',
            field: 'Estatus_Completo',
            minWidth: 180,
            cellRenderer: function (params) {
                let color = 'secondary';
                let clasIcon = 'fa fa-hourglass-half me-1';
                if (String(params.data.tipo_c_estado) === '2') {
                    color = 'success';
                    clasIcon = 'fa fa-check-circle me-1 text-success';
                } else if (String(params.data.tipo_c_estado) === '1') {
                    color = 'primary';
                    clasIcon = ' fa fa-play-circle me-1';
                } else if (String(params.data.tipo_c_estado) === '0') {
                    color = 'warning';
                    clasIcon = 'fa fa-hourglass-half me-1';
                }

                return `
                     <button class="btn btn-sm btn-outline-${color} ver-mapa-btn" 
                    data-tipo="c"
                    data-info='${JSON.stringify(params.data).replace(/'/g, '&#39;')}' 
                    title="Ver progreso...">
                <i class="${clasIcon}"></i> ${params.data.Estatus_Completo}
                     </button>
                `;
            },
        },
    ];

    const gridOptions = {
        columnDefs: columnDefs,
        pagination: true,
        paginationPageSize: 100,
        rowSelection: 'multiple',
        defaultColDef: {
            resizable: true,
            flex: 1,
        },
    };

    const myGridElement = document.querySelector('#myGrid');
    gridApi = agGrid.createGrid(myGridElement, gridOptions);

    getCoordenadasList('');

    function getCoordenadasList(parametros) {
        const overlay = document.getElementById('gridLoadingOverlay');
        overlay.style.display = 'flex';

        gridApi.setGridOption('rowData', []);

        fetch('/coordenadas/contenedor/search?' + parametros.toString())
            .then((response) => response.json())
            .then((data) => {
                PreguntaA = data.preguntas;
                contenedoresDisponibles = data.datos;
                gridApi.setGridOption('rowData', data.datos);
            })
            .catch((error) => {
                console.error('❌ Error al obtener la lista de coordenadas:', error);
            })
            .finally(() => {
                overlay.style.display = 'none';
            });
    }

    document.getElementById('formFiltros').addEventListener('submit', function (e) {
        e.preventDefault();

        const form = e.target;
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);

        getCoordenadasList(params.toString());
    });
});

document.addEventListener('click', function (e) {
    if (e.target.closest('.ver-mapa-btn')) {
        const btn = e.target.closest('.ver-mapa-btn');
        const tipo = btn.dataset.tipo;
        const info = JSON.parse(btn.dataset.info);
        abrirModalCuestionario(tipo, info);
    }
});

function abrirModalCuestionario(tipoCuestionario, parametersW) {
    const preguntas = preguntasPorTipo[tipoCuestionario];
    let contenido = '';
    let contenedor = parametersW['contenedor'];
    document.getElementById('numeroContenedor').textContent = '# Contenedor:  ' + contenedor;
    preguntas.forEach((p) => {
        const valor = parametersW[p.campo];

        if (valor && typeof valor === 'string' && valor.includes(',')) {
            const [lat, lng] = valor.split(',').map((v) => parseFloat(v.trim()));

            if (!isNaN(lat) && !isNaN(lng)) {
                contenido += `
                        <div class="d-flex flex-column justify-content-between border rounded p-2 mb-2" style="min-height: 100px;">
                            <div class="mb-2"><strong>${p.texto}</strong></div>
                            <div class="mt-auto">
                                <button onclick="verMapa(${lat}, ${lng})" class="btn btn-sm btn-primary ms-2" id="btnVerMapa">Ver Mapa</button>
                            </div>
                        </div>
                    `;
            } else {
                contenido += `<div><strong>${p.texto}</strong> </div>`;
            }
        } else {
            contenido += `<div><strong>${p.texto}</strong> <span>Sin responder</span></div>`;
        }

        document.getElementById('modal-body-cuestionario').innerHTML = contenido;

        document.getElementById('myModal').style.display = 'block';
    });
}

function closeModal() {
    document.getElementById('myModal').style.display = 'none';
}

window.onclick = function (event) {
    if (event.target === document.getElementById('myModal')) {
        closeModal();
    }
};

function limpiarFiltros() {
    const modal = document.getElementById('filtroModal');
    const inputs = modal.querySelectorAll('input, select, textarea');

    inputs.forEach((element) => {
        if (element.tagName === 'SELECT') {
            element.selectedIndex = 0;
        } else {
            element.value = '';
        }
    });
}

function verMapa(lat, lng) {
    const url = `https://maps.google.com/maps?q=${lat},${lng}&z=15&output=embed`;
    document.getElementById('iframeMapa').src = url;
    document.getElementById('modalMapa').style.display = 'block';
}

function cerrarModalMapa() {
    document.getElementById('modalMapa').style.display = 'none';
    document.getElementById('iframeMapa').src = '';
}

function makeDraggable(element) {
    let isMouseDown = false;
    let offsetX, offsetY;

    const modalHeader = element.querySelector('.modal-header');
    if (modalHeader) {
        modalHeader.addEventListener('mousedown', function (e) {
            isMouseDown = true;
            offsetX = e.clientX - element.offsetLeft;
            offsetY = e.clientY - element.offsetTop;
        });

        window.addEventListener('mousemove', function (e) {
            if (isMouseDown) {
                element.style.left = e.clientX - offsetX + 'px';
                element.style.top = e.clientY - offsetY + 'px';
            }
        });

        window.addEventListener('mouseup', function () {
            isMouseDown = false;
        });
    }
}

const seleccionados = [];

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
        (c) => (c.contenedor || '').toUpperCase().includes(filtro) && !seleccionados.includes(c.contenedor),
    );

    filtrados.forEach((c) => {
        const item = document.createElement('div');
        item.textContent = c.contenedor;
        item.style.padding = '5px';
        item.style.cursor = 'pointer';
        item.onclick = () => seleccionarContenedor(c.contenedor);
        sugerenciasDiv.appendChild(item);
    });

    sugerenciasDiv.style.display = filtrados.length ? 'block' : 'none';
}

function seleccionarContenedor(valor) {
    seleccionados.push(valor);
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
    actualizarVista();
}

function actualizarVista() {
    const div = document.getElementById('contenedores-seleccionados');
    div.innerHTML = '';

    seleccionados.forEach((cont, i) => {
        div.innerHTML += `
             <span class="badge bg-secondary me-1">
        ${cont}
        <button type="button" 
            onclick="eliminarContenedor(${i})" 
            style="background:none; border:none; color:red; margin-left:5px; font-weight:bold;" 
            title="Eliminar">
            &times;
        </button>
    </span>
            
        `;
    });

    document.getElementById('contenedores').value = seleccionados.join(';');
}
