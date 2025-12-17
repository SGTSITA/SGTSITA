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
        { texto: '1) ¿Carga en patio?', campo: 'cargado_patio' },
        { texto: '2) ¿Inicio ruta?', campo: 'en_destino' },
        { texto: '3)¿Inicia carga?', campo: 'inicio_descarga' },
        { texto: '4)¿Fin descarga?', campo: 'fin_descarga' },
        { texto: '5 ¿Recepción Doctos Firmados?', campo: 'recepcion_doc_firmados' },
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
function cargarinicial() {
    const params = new URLSearchParams({
        idCliente: idCliente,
    });

    fetch(`/coordenadas/contenedor/search?${params.toString()}`)
        .then((response) => response.json())
        .then((data) => {
            contenedoresDisponibles = data.datos;
        })
        .catch((error) => {
            console.error('Error al traer coordenadas:', error);
        });
}

document.addEventListener('DOMContentLoaded', function () {
    let gridApi;

    cargarinicial();

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
        { headerName: 'Cliente', field: 'cliente', sortable: true, filter: true },
        { headerName: '# Contenedor', field: 'contenedor', sortable: true, filter: true },
        { headerName: 'Origen', field: 'origen', sortable: true, filter: true },
        { headerName: 'Destino', field: 'destino', sortable: true, filter: true },

        {
            headerName: 'E Burrero',
            field: 'Estatus_Burrero',
            minWidth: 130,
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
            minWidth: 150,
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
            minWidth: 150,
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

    //load primero cojn el cliente
    const params = new URLSearchParams({
        idCliente: idCliente,
    });

    const clienteSelect = document.getElementById('cliente');
    clienteSelect.addEventListener('change', function () {
        const clienteId = this.value;
        const subclienteSelect = document.getElementById('subcliente');

        // Limpia las opciones anteriores
        subclienteSelect.innerHTML = '<option value="">Seleccione un subcliente</option>';

        if (clienteId) {
            // Puedes mostrar un "cargando..." si quieres
            const loadingOption = document.createElement('option');
            loadingOption.textContent = 'Cargando subclientes...';
            loadingOption.disabled = true;
            loadingOption.selected = true;
            subclienteSelect.appendChild(loadingOption);

            fetch(`/api/coordenadas/subclientes/${clienteId}`)
                .then((response) => response.json())
                .then((subclientes) => {
                    subclienteSelect.innerHTML = '<option value="">Seleccione un subcliente</option>'; // Resetea

                    if (subclientes.length > 0) {
                        subclientes.forEach((subcliente) => {
                            const option = document.createElement('option');
                            option.value = subcliente.id;
                            option.textContent = subcliente.nombre;
                            subclienteSelect.appendChild(option);
                        });
                    } else {
                        const option = document.createElement('option');
                        option.textContent = 'No hay subclientes disponibles';
                        option.disabled = true;
                        subclienteSelect.appendChild(option);
                    }
                })
                .catch((error) => {
                    console.error('Error al cargar subclientes:', error);
                    subclienteSelect.innerHTML = '<option value="">Error al cargar subclientes</option>';
                });
        }
    });

    getEntidadesPC();
    getCoordenadasList(params);

    function getCoordenadasList(parametros) {
        const overlay = document.getElementById('gridLoadingOverlay');
        overlay.style.display = 'flex';

        gridApi.setGridOption('rowData', []);

        fetch('/coordenadas/contenedor/search?' + parametros.toString())
            .then((response) => response.json())
            .then((data) => {
                PreguntaA = data.preguntas;
                gridApi.setGridOption('rowData', data.datos);
            })
            .catch((error) => {
                console.error('❌ Error al obtener la lista de coordenadas:', error);
            })
            .finally(() => {
                overlay.style.display = 'none';
            });
    }

    function getEntidadesPC() {
        fetch('/api/coordenadas/entidadesPC')
            .then((response) => response.json())
            .then((data) => {
                //const proveedorSelect = document.getElementById('proveedor');
                const clienteSelect = document.getElementById('cliente');

                // Añadir una opción predeterminada
                //  proveedorSelect.innerHTML = '<option value="">Seleccione un proveedor</option>';
                clienteSelect.innerHTML = '<option value="">Seleccione un cliente</option>';

                // Cargar proveedores
                // data.proveedor.forEach(proveedor => {
                //     const option = document.createElement('option');
                //     option.value = proveedor.id;
                //     option.textContent = proveedor.nombre;
                //     proveedorSelect.appendChild(option);
                // });

                // Cargar clientes
                data.client.forEach((cliente) => {
                    const option = document.createElement('option');
                    option.value = cliente.id;
                    option.textContent = cliente.nombre;
                    if (cliente.id == idCliente) {
                        option.selected = true;
                        clienteSelect.disabled = true;
                    }
                    clienteSelect.appendChild(option);
                });

                clienteSelect.dispatchEvent(new Event('change'));
            })
            .catch((error) => console.error('Error al cargar proveedores y clientes:', error));
    }

    document.getElementById('formFiltros').addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        const queryParams = new URLSearchParams();

        formData.forEach((valor, clave) => {
            if (valor.trim() !== '') {
                queryParams.append(clave, valor);
            }
        });

        queryParams.append('idCliente', idCliente);

        getCoordenadasList(queryParams.toString());
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
    let id_cotizacion = parametersW['id_cotizacion'];

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
            let msjNo = 'Sin responder';
            if (p.texto === '7) Toma Foto de Boleta de Patio' && valor === '1') {
                // ya se cargo foto y puede verse
                //tendria q buscar la direccion para la foto

                fetch("{{ url('coordenadas/coordenadas/extsearchDoctos') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    body: JSON.stringify({
                        id_cotizacion: id_cotizacion,
                        contenedor: contenedor,
                    }),
                })
                    .then((response) => response.json())
                    .then((data2) => {
                        contenido += `
                                <div class="d-flex flex-column justify-content-between border rounded p-2 mb-2" style="min-height: 100px;">
                                    <div class="mb-2"><strong>${p.texto}</strong></div>
                                    <div class="mt-auto">
                                        <a href="/cotizaciones/cotizacion${data2.inCotizacion}/${data2.filePath}" target="_blank" class="btn btn-active-primary btn-sm">
                                            Ver Archivo
                                    </a>
                                    </div>
                                </div>
                            `;

                        console.log('Éxito:', data);
                    })
                    .catch((error) => {
                        console.error('Error en la petición:', error);
                    });

                //end fetch buscar archivo
            } else {
                msjNo = 'NO';
            }

            contenido += `<div><strong>${p.texto}</strong> <span>${msjNo}</span></div>`;
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
    const modal = document.getElementById('formFiltros');
    const inputs = modal.querySelectorAll('input, select, textarea');

    inputs.forEach((element) => {
        if (element.tagName === 'SELECT') {
            if (element.disabled == false) {
                element.selectedIndex = 0;
            }
        } else {
            element.value = '';
        }
    });
    seleccionados.length = 0;
    document.getElementById('contenedores-seleccionados').innerHTML = '';
    const inputContenedores = document.getElementById('contenedores');
    if (inputContenedores) {
        inputContenedores.value = '';
    }
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
