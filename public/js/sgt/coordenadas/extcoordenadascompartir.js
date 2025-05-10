const preguntasPorTipo = {
    b: [
        { texto: "1)¿ Registro en Puerto ?", campo: "registro_puerto" },
        { texto: "2)¿ Dentro de Puerto ?", campo: "dentro_puerto" },
        { texto: "3)¿ Descarga Vacío ?", campo: "descarga_vacio" },
        { texto: "4)¿ Cargado Contenedor ?", campo: "cargado_contenedor" },
        { texto: "5)¿ En Fila Fiscal ?", campo: "fila_fiscal" },
        { texto: "6)¿ Modulado ?", campo: "modulado_tipo", opciones: ["5.1) Verde","5.2) Amarillo","5.3) Rojo", "5.4) OVT"] },
        { texto: "7)¿ Descarga en patio ?", campo: "descarga_patio" },
    ],
    f: [
        { texto: "1) ¿Carga en patio?", campo: "cargado_patio" },
        { texto: "2) ¿Inicio ruta?", campo: "en_destino" },
        { texto: "3)¿Inicia carga?", campo: "inicio_descarga" },
        { texto: "4)¿Fin descarga?", campo: "fin_descarga" },
        { texto: "5 ¿Recepción Doctos Firmados?", campo: "recepcion_doc_firmados" },
    ],
    c: [
        { texto: "¿1) Registro en Puerto ?", campo: "registro_puerto" },
        { texto: "¿2) Dentro de Puerto ?", campo: "dentro_puerto" },
        { texto: "¿3) Descarga Vacío?", campo: "descarga_vacio" },
        { texto: "¿4) Cargado Contenedor?", campo: "cargado_contenedor" },
        { texto: "¿5) En Fila Fiscal?", campo: "fila_fiscal" },
        { texto: "¿6) Modulado?", campo: "modulado_tipo", opciones: ["5.1) Verde","5.2) Amarillo","5.3) Rojo", "5.4) OVT"] },
        { texto: "¿7) En Destino?", campo: "en_destino" },
        { texto: "¿8) Inicio Descarga?", campo: "inicio_descarga" },
        { texto: "¿9) Fin Descarga?", campo: "fin_descarga" },
        { texto: "¿10) Recepción Doctos Firmados?", campo: "recepcion_doc_firmados" },
    ],
};




document.addEventListener("DOMContentLoaded", function () {
    let gridApi;

    

  let PreguntaA;



  

    const columnDefs = [
        { headerCheckboxSelection: true, checkboxSelection: true, width: 50 },
       
        { headerName: "Cliente", field: "cliente", sortable: true, filter: true },
        { headerName: "Origen", field: "origen", sortable: true, filter: true },
        { headerName: "Destino", field: "destino", sortable: true, filter: true },
        { headerName: "# Contenedor", field: "contenedor", sortable: true, filter: true },
        {
            headerName: "Compartir",
            field: "tipo_b_estado",
            minWidth: 180,
            cellRenderer: function (params) {
                      
                return `
                        <button class="btn btn-sm btn-outline-success ms-2" 
        onclick='abrirModalCoordenadas(${params.data.id_cotizacion},${params.data.id_asignacion})' 
        title="Compartir coordenadas">
        <i class="fa fa-share-alt me-1"></i> Compartir
    </button>
                `;
            }
        }       
       
    ];
    

    const gridOptions = {
        columnDefs: columnDefs,
        pagination: true,
        paginationPageSize: 100,
        rowSelection: "multiple",
        defaultColDef: {
            resizable: true,
            flex: 1
        },
    };

    const myGridElement = document.querySelector("#myGrid");
    gridApi = agGrid.createGrid(myGridElement, gridOptions);
    

    window.abrirModalCoordenadas = function(id_cotizacion,idAsignacion) {
        const modal = document.getElementById('modalCoordenadas');

        if (modal) {
            modal.style.display = 'block';
            limpiarDatos();
            // Backdrop
            if (!document.querySelector('.modal-backdrop')) {
                const backdrop = document.createElement('div');
                backdrop.classList.add('modal-backdrop');
                backdrop.style.position = 'fixed';
                backdrop.style.top = '0';
                backdrop.style.left = '0';
                backdrop.style.width = '100%';
                backdrop.style.height = '100%';
                backdrop.style.backgroundColor = 'rgba(0,0,0,0.5)';
                backdrop.style.zIndex = '1040';
                document.body.appendChild(backdrop);
            }
        
            document.body.classList.add('modal-open');
            document.body.style.overflow = 'hidden';
        
            document.getElementById('idCotizacionCompartir').value =id_cotizacion;
            document.getElementById('idAsignacionCompartir').value =idAsignacion;
    
            // Activar tab Mail por defecto
            cambiarTab('mail');
        }

        // Asegúrate de que el select para tipo de cuestionario exista
        const tipoSelect = document.getElementById('optipoCuestionario');
        if (tipoSelect) {
            tipoSelect.addEventListener('change', function() {
                // Verifica si se seleccionó una opción válida
                const tipoSeleccionado = tipoSelect.value;

                if (tipoSeleccionado) {
                    //limpiarDatos("");
                    // Aquí haces el fetch solo si hay una opción seleccionada
                    fetchCotizacion(id_cotizacion, tipoSeleccionado);
                } else {
                    // Si no hay selección válida, limpiamos los datos
                    limpiarDatos();
                }
            });
        }
    };

    function fetchCotizacion(id_cotizacion, tipoCuestionario) {
        const link = `${window.location.origin}/coordenadas/questions/${id_cotizacion}/${tipoCuestionario}`;
        let _url = `/coordenadas/cotizaciones/get/${id_cotizacion}`;
       
        fetch(_url)
            .then(response => response.json())
            .then(data => {
                if (!data.list || data.list.length === 0) {
                    let messageNoData = "No se encontró información para esta cotización.";
                    // Limpiar la información cuando no se encuentra
                    limpiarDatos(messageNoData);
                    return;
                }

                let bPasaValidacion = 0;

                const item = data.list[0];
                const mensaje = ` ${item.contenedor}`;
                if (data.coordenada) {
                  
                    document.getElementById('estadoC').value = data.coordenada.tipo_c_estado ?? 0;
                    document.getElementById('estadoB').value = data.coordenada.tipo_b_estado ?? 0;
                    document.getElementById('estadoF').value = data.coordenada.tipo_f_estado ?? 0;
                    
                    let tVslidacion = '';

                    const estadoC = parseInt(document.getElementById('estadoC').value);//completo solo 1 vez, si ya esta finalizado y no se puede elegir otro tipo
                    const estadoB = parseInt(document.getElementById('estadoB').value);
                    const estadoF = parseInt(document.getElementById('estadoF').value);
                 
                    if ([estadoC, estadoB, estadoF].includes(2)) {
                        if (estadoC ===2){
                            alert('El cuestionario "Completo" ya ha sido finalizado, no se puede compartir.');

                        }else if(estadoB ===2  ){

                            alert('El cuestionario "Burrero" ya ha sido finalizado, no se puede compartir.');

                        }else if (estadoF===2){
                            alert('El cuestionario "Foraneo" ya ha sido finalizado, no se puede compartir.');
                        }

                    }
                    else {
                         // Un contenedor puede tener, burrero y foraneo, pero si es completo ya no se podrá elegir, validar compartir coordenadas.
                         bPasaValidacion=  validarselectTipoCuentio(estadoC,estadoB,estadoF)
                    }
                                     
                    

                } else {
                    bPasaValidacion=1;
                    // Primera vez compartiendo, todo en 0
                    document.getElementById('estadoC').value = 0;
                    document.getElementById('estadoB').value = 0;
                    document.getElementById('estadoF').value = 0;
                    validarselectTipoCuentio(estadoC,estadoB,estadoF)
                }



                if (bPasaValidacion===1) {
                    // mail
                    document.getElementById('linkMail').innerText = link;
                    document.getElementById('mensajeText').innerText = mensaje;
                    // whatsapp
                    document.getElementById("wmensajeText").innerText = mensaje;
                    document.getElementById("linkWhatsapp").value = link;
                    // 🟢 Armamos el link para WhatsApp
                    const textoWhatsapp = `Contenedor: ${mensaje}\n\n${link}`;
                    document.getElementById("whatsappLink").href = `https://wa.me/?text=${encodeURIComponent(textoWhatsapp)}`;
                }else {
                    console.error("❌ Validacion de estatus de cuestionario");
                }
               

                
            })
            .catch(error => {
                console.error("❌ Error al obtener info de cotizaciones:", error);
            });
    }

    function validarselectTipoCuentio(estadoC,estadoB,estadoF){

        const selectTipoCuestionario = document.getElementById('optipoCuestionario');
let selecvalueuser = selectTipoCuestionario.value;
    // Deshabilitar las opciones basadas en el estado de los cuestionarios
    if(selecvalueuser==='b' || selecvalueuser==='f'){
        if (estadoC === 1 ) {
                // Si tipo C ya se compartió, deshabilitar opción b y f
            //  selectTipoCuestionario.querySelector('option[value="b"]').disabled = true;
            // selectTipoCuestionario.querySelector('option[value="f"]').disabled = true;
            alert('El cuestionario "completo" ya ha sido compartido, no se puede compartir otro tipo.');
            return 0;
            }else if (estadoC ===0) {
                return 1;
        }
    }
    
    if (selecvalueuser==='c' ){
        if ((estadoB === 1 || estadoF===1) ) {
            // Si tipo B o f ya se compartió, deshabilitar opción c
            //selectTipoCuestionario.querySelector('option[value="c"]').disabled = true;
           alert('El cuestionario "Burrero/Foraneo" ya ha sido compartido, no se puede compartir otro tipo.');
    
           return 0;
        }else if ((estadoB === 0 || estadoF===0)){
            return 1;

        }

    }
    
   
    
   
    // Si ninguno está compartido, asegúrate de que todas las opciones estén habilitadas
    if (estadoC === 0 && estadoB === 0 && estadoF === 0) {
        // selectTipoCuestionario.querySelector('option[value="c"]').disabled = false;
        // selectTipoCuestionario.querySelector('option[value="b"]').disabled = false;
        // selectTipoCuestionario.querySelector('option[value="f"]').disabled = false;
        retLocal=1;
    }
    return 1
    }

    function limpiarDatos(message = "") {
        // Limpiar los valores cuando no se selecciona una opción válida
        document.getElementById('linkMail').innerText = message;
       
        document.getElementById('mensajeText').innerText = "";
        document.getElementById('correoDestino').value="";
        document.getElementById("wmensajeText").innerText = "";
        document.getElementById("linkWhatsapp").value = "";
        document.getElementById("whatsappLink").href = "#";
        document.getElementById("idAsignacionCompartir").value = "";
        const select = document.getElementById('optipoCuestionario');
        select.selectedIndex = 0;
        document.getElementById('idCotizacionCompartir').value ="";
        document.getElementById('idAsignacionCompartir').value ="";
    }

    const params = new URLSearchParams({
        idCliente: idCliente,
        
    });


    const clienteSelect = document.getElementById('cliente');
clienteSelect.addEventListener('change', function () {
    const clienteId = this.value;
    const subclienteSelect = document.getElementById('subcliente');

  
    subclienteSelect.innerHTML = '<option value="">Seleccione un subcliente</option>';

    if (clienteId) {
        // Puedes mostrar un "cargando..." si quieres
        const loadingOption = document.createElement('option');
        loadingOption.textContent = 'Cargando subclientes...';
        loadingOption.disabled = true;
        loadingOption.selected = true;
        subclienteSelect.appendChild(loadingOption);

        fetch(`/api/coordenadas/subclientes/${clienteId}`)
            .then(response => response.json())
            .then(subclientes => {
                subclienteSelect.innerHTML = '<option value="">Seleccione un subcliente</option>'; // Resetea

                if (subclientes.length > 0) {
                    subclientes.forEach(subcliente => {
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
            .catch(error => {
                console.error('Error al cargar subclientes:', error);
                subclienteSelect.innerHTML = '<option value="">Error al cargar subclientes</option>';
            });
    }
});

    getEntidadesPC();
        getCoordenadasList(params);
   
function getCoordenadasList(parametros) {
        const overlay = document.getElementById("gridLoadingOverlay");
        overlay.style.display = "flex";
    
              
        gridApi.setGridOption("rowData", []); 
    
        fetch("/coordenadas/contenedor/search?" + parametros.toString())
            .then(response => response.json())
            .then(data => {
                PreguntaA= data.preguntas;
                gridApi.setGridOption("rowData", data.datos);
            })
            .catch(error => {
                console.error("❌ Error al obtener la lista de coordenadas:", error);
            })
            .finally(() => {
                overlay.style.display = "none"; 
            });
    }


    function getEntidadesPC(){
        fetch('/api/coordenadas/entidadesPC')
        .then(response => response.json())
        .then(data => {
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
            data.client.forEach(cliente => {
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
        .catch(error => console.error('Error al cargar proveedores y clientes:', error))

    }

    document.getElementById("formFiltros").addEventListener("submit", function (e) {
        e.preventDefault();
    
       
    
        const form = e.target;
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);
    
        params.append("idCliente", idCliente);

        getCoordenadasList(params.toString()); 
        
        

    });

    
});




    document.addEventListener('click', function(e) {
        if (e.target.closest('.ver-mapa-btn')) {
            const btn = e.target.closest('.ver-mapa-btn');
            const tipo = btn.dataset.tipo;
            const info = JSON.parse(btn.dataset.info);
            abrirModalCuestionario(tipo, info);
        }
    });

   
    
    function abrirModalCuestionario(tipoCuestionario, parametersW){
        const preguntas = preguntasPorTipo[tipoCuestionario];
        let contenido = "";
    let contenedor =  parametersW["contenedor"];
    document.getElementById("numeroContenedor").textContent = "# Contenedor:  " +  contenedor;
        preguntas.forEach(p => {
            const valor = parametersW[p.campo];
    
            if (valor && typeof valor === 'string' && valor.includes(',')) {
                const [lat, lng] = valor.split(',').map(v => parseFloat(v.trim()));
            
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
    
       
    
        document.getElementById("modal-body-cuestionario").innerHTML = contenido;
        

        document.getElementById('myModal').style.display = 'block';

    })
}
   
    
      function closeModal() {
        document.getElementById('myModal').style.display = 'none';
      }
    
      window.onclick = function(event) {
        if (event.target === document.getElementById('myModal')) {
          closeModal();
        }
      }
   
function limpiarFiltros() {
  
    const modal = document.getElementById('filtroModal'); 
    const inputs = modal.querySelectorAll('input, select, textarea');

    inputs.forEach(element => {
        if (element.tagName === 'SELECT') {
            if (element.disabled == false ){
                element.selectedIndex = 0; 
            }
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
                element.style.left = (e.clientX - offsetX) + 'px';
                element.style.top = (e.clientY - offsetY) + 'px';
            }
        });

        window.addEventListener('mouseup', function () {
            isMouseDown = false;
        });
    }
}


function cambiarTab(tabId) {
    // Ocultamos todos los divs con clase 'tab-content'
    const tabs = document.querySelectorAll('.tab-content');
    tabs.forEach(tab => {
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

function mostrarTab(tab) {
    // Ocultar ambos
    document.getElementById('tab-mail').style.display = 'none';
    document.getElementById('tab-whatsapp').style.display = 'none';

    // Quitar clase activa
    const tabs = document.querySelectorAll('.nav-link');
    tabs.forEach(el => el.classList.remove('active'));

    // Mostrar el tab seleccionado
    document.getElementById(`tab-${tab}`).style.display = 'block';

    // Activar tab
    document.querySelector(`.nav-link[href="#"][onclick*="${tab}"]`).classList.add('active');
}

function cerrarModal() {
    const modal = document.getElementById('modalCoordenadas');
    limpCampos();
    if (modal) {
        modal.style.display = 'none';
    }

    // Eliminar el fondo oscuro si existe
    const backdrop = document.querySelector('.modal-backdrop');
    if (backdrop) {
        backdrop.remove();
    }
    
    // Quitar la clase modal-open del body
    document.body.classList.remove('modal-open');
    document.body.style.overflow = ''; // restaurar scroll
}

function limpCampos(){
    document.getElementById('linkMail').innerText = "";
       
    document.getElementById('mensajeText').innerText = "";
    document.getElementById('correoDestino').value="";
    document.getElementById("wmensajeText").innerText = "";
    document.getElementById("linkWhatsapp").value = "";
    document.getElementById("whatsappLink").href = "#";
    document.getElementById("idAsignacionCompartir").value = "";
    const select = document.getElementById('optipoCuestionario');
    select.selectedIndex = 0;
    document.getElementById('idCotizacionCompartir').value ="";
    document.getElementById('idAsignacionCompartir').value ="";
}
function copiarDesdeInput(inputId) {
    const input = document.getElementById(inputId);
    input.select();
    input.setSelectionRange(0, 99999);
    document.execCommand("copy");
    alert("¡Enlace copiado!");
}


function enviarMailCoordenadas() {

    const mensaje = document.getElementById('mensajeText').innerText;
    const asunto = 'No. contenedor: ' + mensaje;

    const link = document.getElementById('linkMail').innerText;
    const correo = document.getElementById('correoDestino').value;
 
    fetch('/coordenadas/cotizaciones/mail-coordenadas', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({
            correo: correo,
            asunto: asunto,
            mensaje: mensaje,
            link:  link
        })
    })
    .then(res => res.json())
    .then(data => alert('Correo enviado ✅'))
    .catch(err => console.error('Error:', err));
    saveCoordenadas();
    
}
function guardarYAbrirWhatsApp(event) {
    event.preventDefault(); // Evita que el enlace se abra inmediatamente
    saveCoordenadas();
    window.open(document.getElementById('whatsappLink').href, '_blank');
       
}

function saveCoordenadas() {

    let idAsignacionSave= document.getElementById("idAsignacionCompartir").value;
    let idCotizacionSave= document.getElementById("idCotizacionCompartir").value;
    let typeQuestion= document.getElementById("optipoCuestionario").value;
    
    if (idAsignacionSave != null)
    {

        let tipoFlujo = null;
        let registroPuerto = null;
        let dentroPuerto = null;
        let descargaVacio = null;
        let cargadoContenedor = null;
        let filaFiscal = null;
        let moduladoTipo = null;
        let moduladoCoordenada = null;
        let enDestino = null;
        let inicioDescarga = null;
        let finDescarga = null;
        let recepcionDocFirmados = null;
        
        let tipoFlujoDatetime = null;
        let registroPuertoDatetime = null;
        let dentroPuertoDatetime = null;
        let descargaVacioDatetime = null;
        let cargadoContenedorDatetime = null;
        let filaFiscalDatetime = null;
        let moduladoTipoDatetime = null;
        let moduladoCoordenadaDatetime = null;
        let enDestinoDatetime = null;
        let inicioDescargaDatetime = null;
        let finDescargaDatetime = null;
        let recepcionDocFirmadosDatetime = null;
        let tipo_c_estado = document.getElementById('estadoC').value;
        let tipo_b_estado =document.getElementById('estadoB').value;
        let tipo_f_estado = document.getElementById('estadoF').value;

             
        if (typeQuestion =='b'){
            tipo_b_estado=1;
        }else if (typeQuestion =='c'){
            tipo_c_estado=1;
        }else if (typeQuestion =='f'){
            tipo_f_estado=1;
        }
     
        const data = {
         idAsig: idAsignacionSave,
         idCotSave: idCotizacionSave,
     
         tipo_flujo: tipoFlujo ?? null,
         registro_puerto: registroPuerto ?? null,
         dentro_puerto: dentroPuerto ?? null,
         descarga_vacio: descargaVacio ?? null,
         cargado_contenedor: cargadoContenedor ?? null,
         fila_fiscal: filaFiscal ?? null,
         modulado_tipo: moduladoTipo ?? null,
         modulado_coordenada: moduladoCoordenada ?? null,
         en_destino: enDestino ?? null,
         inicio_descarga: inicioDescarga ?? null,
         fin_descarga: finDescarga ?? null,
         recepcion_doc_firmados: recepcionDocFirmados ?? null,
     
         tipo_flujo_datatime: tipoFlujoDatetime ?? null,
         registro_puerto_datatime: registroPuertoDatetime ?? null,
         dentro_puerto_datatime: dentroPuertoDatetime ?? null,
         descarga_vacio_datatime: descargaVacioDatetime ?? null,
         cargado_contenedor_datatime: cargadoContenedorDatetime ?? null,
         fila_fiscal_datatime: filaFiscalDatetime ?? null,
         modulado_tipo_datatime: moduladoTipoDatetime ?? null,
         modulado_coordenada_datatime: moduladoCoordenadaDatetime ?? null,
         en_destino_datatime: enDestinoDatetime ?? null,
         inicio_descarga_datatime: inicioDescargaDatetime ?? null,
         fin_descarga_datatime: finDescargaDatetime ?? null,
         recepcion_doc_firmados_datatime: recepcionDocFirmadosDatetime ?? null,

          tipo_c_estado :tipo_c_estado,
             tipo_b_estado :tipo_b_estado,
        tipo_f_estado :tipo_f_estado
     
        }  ;
     
         fetch('/coordenadas/compartir/save', {
             method: 'POST',
             headers: {
                 'Content-Type': 'application/json',
                 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
             },
             body: JSON.stringify(data)
         })
         .then(res => {
            if (res.status === 204) {
                console.log('Coordenadas Actualizadas ✅');
            } else {
                return res.json().then(data => {
                    console.log('Coordenadas Actualizadas ✅');
                });
            }
        })
        .catch(err => console.error('Error:', err));
    
    }
    
}

