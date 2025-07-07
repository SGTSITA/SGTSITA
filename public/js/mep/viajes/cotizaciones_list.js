const localeText = {
    page: 'Página',
    more: 'Más',
    to: 'a',
    of: 'de',
    next: 'Siguiente',
    last: 'Último',
    first: 'Primero',
    previous: 'Anterior',
    loadingOoo: 'Cargando...',
    selectAll: 'Seleccionar todo',
    searchOoo: 'Buscar...',
    blanks: 'Vacíos',
    filterOoo: 'Filtrar...',
    applyFilter: 'Aplicar filtro...',
    equals: 'Igual',
    notEqual: 'Distinto',
    lessThan: 'Menor que',
    greaterThan: 'Mayor que',
    contains: 'Contiene',
    notContains: 'No contiene',
    startsWith: 'Empieza con',
    endsWith: 'Termina con',
    andCondition: 'Y',
    orCondition: 'O',
    group: 'Grupo',
    columns: 'Columnas',
    filters: 'Filtros',
    pivotMode: 'Modo Pivote',
    groups: 'Grupos',
    values: 'Valores',
    noRowsToShow: 'Sin filas para mostrar',
    pinColumn: 'Fijar columna',
    autosizeThiscolumn: 'Ajustar columna',
    copy: 'Copiar',
    resetColumns: 'Restablecer columnas',
    blank: 'Vacíos',
    notBlank: 'No Vacíos',
    paginationPageSize: 'Registros por página'
  };

  const btnFull = document.querySelector('#btnFull')

document.addEventListener("DOMContentLoaded", function () {
    let gridApi;
    let currentTab = "planeadas";

    const tabs = document.querySelectorAll('#cotTabs .nav-link');
    
    tabs.forEach(tab => {
        tab.addEventListener("click", function () {
            tabs.forEach(t => t.classList.remove("active"));
            this.classList.add("active");
            currentTab = this.getAttribute("data-status");
            btnFull.disabled = (currentTab == 'en_espera' || currentTab == 'aprobadas') ? false : true
            getCotizacionesList();
        });
    });

    const columnDefs = [
        { headerCheckboxSelection: true, checkboxSelection: true, width: 30 },
        { headerName: "No", field: "id", sortable: true, filter: true , hide: true},
        { headerName: "Tipo Viaje", field: "tipo", sortable: true, filter: true , hide: true},
        { headerName: "Cliente", field: "cliente", sortable: true, filter: true, minWidth: 150 },
        {   headerName: "# Contenedor", 
            field: "contenedor", 
            sortable: true, 
            filter: true, 
            minWidth: 150 ,
            autoHeight: true, // Permite que la fila se ajuste en altura
            cellStyle:params => {
                const styles = {
                  'white-space': 'normal',
                  'line-height': '1.5',
                };
            
                // Si la cotización es tipo "Full", aplicar fondo 
                if (params.data.tipo === 'Full') {
                  styles['background-color'] = '#ffe5b4'; 
                }
            
                return styles;
              },
        },
        { headerName: "Origen", field: "origen", sortable: true, filter: true, minWidth: 150  },
        { headerName: "Destino", field: "destino", sortable: true, filter: true, minWidth: 150  },
        
        {
            headerName: "Estatus",
            field: "estatus",
            minWidth: 180,
            cellRenderer: function (params) {
                let color = "secondary";
                if (params.data.estatus === "Aprobada") color = "success";
                else if (params.data.estatus === "Cancelada") color = "danger";
                else if (params.data.estatus === "Pendiente") color = "warning";
        
                return `
                    <button class="btn btn-sm btn-outline-${color}"  title="Estatus">
                        <i class="fa fa-sync-alt me-1"></i> ${params.data.estatus}
                    </button>
                `;
            }
        },        
        {
            headerName: "Coordenadas",
            field: "coordenadas",
            minWidth: 180,
            sortable: false,
            filter: false,
            cellRenderer: function (params) {
                
                    return `
                    <button class="btn btn-sm btn-outline-info" 
                    onclick="abrirModalCoordenadas(${params.data.id},${params.data.id_asignacion})" 
                     title="Compartir coordenadas">
                     <i class="fa fa-map-marker-alt"></i> Compartir
                     </button>
                                               
                    `;
                
               
            }
        },
        {
            headerName: "Acciones",
            field: "acciones",
            minWidth: 500,
            cellRenderer: function (params) {
                let acciones = "";

                if (currentTab === "planeadas") {
                    acciones = `
                        
                        <button class="btn btn-sm btn-outline-warning" onclick="abrirDocumentos(${params.data.id})" title="Ver Documentos">
                            <i class="fa fa-folder"></i>
                        </button>
                        ${params.data.tipo_asignacion === "Propio" ? `
                            <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#cambioModal${params.data.id}" title="Asignación: Propio">
                                Propio
                            </button>
                        ` : `
                            <button class="btn btn-sm btn-outline-dark" data-bs-toggle="modal" data-bs-target="#cambioModal${params.data.id}" title="Asignación: Subcontratado">
                                Sub.
                            </button>
                        `}`;
                } else if (currentTab === "finalizadas") {
                    acciones = `
              
                        <button class="btn btn-sm btn-outline-info" onclick="abrirDocumentos(${params.data.id})" title="Ver Documentos">
                            <i class="fa fa-folder"></i>
                        </button>
                    `;
                } else if (currentTab === "en_espera") {
                    acciones = `
                   
                        <button class="btn btn-sm btn-outline-info" onclick="abrirDocumentos(${params.data.id})" title="Ver Documentos">
                            <i class="fa fa-folder"></i>
                        </button>
                     
                    `;
                } else if (currentTab === "aprobadas") {
                    acciones = `
                  
                        <button class="btn btn-sm btn-outline-info" onclick="abrirDocumentos(${params.data.id})" title="Ver Documentos">
                            <i class="fa fa-folder"></i>
                        </button>
                       
                    `;
                } else if (currentTab === "canceladas") {
                    acciones = `<button class="btn btn-sm btn-outline-warning" onclick="abrirDocumentos(${params.data.id})" title="Ver Documentos"><i class="fa fa-folder"></i></button>`;
                }

                return acciones;
            }
        }
    ];

    const gridOptions = {
        columnDefs: columnDefs,
        domLayout: 'autoHeight', 
        pagination: true,
        paginationPageSize: 10,
        paginationPageSizeSelector: [10, 50, 100],
        rowSelection: "multiple",
        defaultColDef: {
            resizable: true,
            flex: 1
        },
        localeText: localeText,
        onRowSelected:(event)=>{
            seleccionarContenedor()
        },
    };

    const myGridElement = document.querySelector("#myGrid");
    gridApi = agGrid.createGrid(myGridElement, gridOptions);

    getCotizacionesList();

    function getCotizacionesList() {
        const overlay = document.getElementById("gridLoadingOverlay");
        overlay.style.display = "flex";
    
        let url = "/mep/viajes/list";
        if (currentTab === "finalizadas") url = "/mep/viajes/finalizadas";
        if (currentTab === "en_espera") url = "/mep/viajes/espera";
        if (currentTab === "aprobadas") url = "/mep/viajes/aprobadas";
        if (currentTab === "canceladas") url = "/mep/viajes/canceladas";
    
        gridApi.setGridOption("rowData", []); 
    
        fetch(url)
            .then(response => response.json())
            .then(data => {
                gridApi.setGridOption("rowData", data.list);
            })
            .catch(error => {
                console.error("❌ Error al obtener la lista de cotizaciones:", error);
            })
            .finally(() => {
                overlay.style.display = "none"; 
            });
    }

    btnFull.addEventListener('click',()=>{
        let seleccion = gridApi.getSelectedRows();
        let validarCliente = seleccion.every(element => 
            element.cliente === seleccion[0].cliente
        );

        if(seleccion.length > 2){
            Swal.fire('Maximo 2 contenedores','Lo sentimos, solo puede seleccionar maximo 2 contenedores, estos deben ser de un mismo cliente','warning')
            return false
        }

        if(!validarCliente){
            Swal.fire('Cliente distinto','Lo sentimos, los contenedores deben ser de un mismo cliente','warning')
            return false
        }

        Swal.fire({
            title: '¿Estás seguro?',
            text: "Quiere unir los contenedores seleccionados en un viaje Full.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, continuar',
            cancelButtonText: 'No, cancelar',
            reverseButtons: true // Opcional: invierte el orden de los botones
          }).then((result) => {
            if (result.isConfirmed) {
                let _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
              $.ajax({
                url:'/cotizaciones/transformar/full',
                type:'post',
                data:{_token, seleccion},
                beforeSend:()=>{
                    mostrarLoading('Fusionando contenedores... espere un momento')
                },
                success:(response)=>{
                    Swal.fire(response.Titulo, response.Mensaje, response.TMensaje)
                    if(response.TMensaje == "success"){
                        getCotizacionesList();
                    }
                    ocultarLoading();
                },
                error:()=>{
                    ocultarLoading();
                }
              })
            } else if (result.dismiss === Swal.DismissReason.cancel) {
              // Acción si el usuario canceló
              console.log("El usuario canceló");
            }
          });
          
    })

    const botonAbrirModal = document.getElementById('abrirModalBtn');

    botonAbrirModal.addEventListener('click', () => {
     // llenarModalViaje();
      let seleccion = gridApi.getSelectedRows();

      if(seleccion.length == 1){
        document.getElementById('numeroContenedor').textContent = seleccion[0].contenedor;
        //document.getElementById('fechaViaje').textContent = seleccion[0].;
        document.getElementById('origenViaje').textContent = seleccion[0].origen;
        document.getElementById('destinoViaje').textContent = seleccion[0].destino;
        document.getElementById('estatusViaje').textContent = seleccion[0].estatus;
      }
      

      let modalElement = (seleccion.length != 1) ? 'noSeleccionModal' : 'viajeModal'
      const modal1 = new bootstrap.Modal(document.getElementById(modalElement));
      modal1.show();
    });

    const btnAsignaOperador = document.querySelector('#btnAsignaOperador')

    

    function asignarOperador2() {
        let seleccion = gridApi.getSelectedRows();
        let operador = document.getElementById('operadorSelect')
        let unidad = document.getElementById('unidadSelect')
        let data = {"contenenedor":seleccion[0],"operador": operador.value,"unidad": unidad.value};
        fetch('/mep/viajes/operador/asignar', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
          },
          body: JSON.stringify(data)
        })
        .then(response => {
          if (!response.ok) {
            throw new Error('Error en la respuesta del servidor');
          }
          return response.json();
        })
        .then(data => {
          console.log('Respuesta del backend:', data);
          
          Swal.fire(data.Titulo, data.Mensaje, data.TMensaje)
        })
        .catch(error => {
          console.error('Error al enviar los datos:', error);
          alert('Ocurrió un error al asignar el operador.');
        });
      }

      btnAsignaOperador.addEventListener('click',asignarOperador2)
       

    function seleccionarContenedor(){
        if (currentTab != 'en_espera' && currentTab != 'aprobadas') return false
        let seleccion = gridApi.getSelectedRows();
        if(seleccion.length > 2){
            Swal.fire('Maximo 2 contenedores','Lo sentimos, solo puede seleccionar maximo 2 contenedores, estos deben ser de un mismo cliente','warning')
            return false
        }

        let validarCliente = seleccion.every(element => 
            element.cliente === seleccion[0].cliente
        );

        if(!validarCliente){
            Swal.fire('Cliente distinto','Lo sentimos, los contenedores deben ser de un mismo cliente','warning')
            return false
        }

        localStorage.setItem('numContenedor',seleccion[0].contenedor); 
    }

    // Abrir el modal
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

    // Función para buscar los datos de la cotización
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
    // Función para limpiar los datos
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
});      

function asignarOperador(){
    const viajeData = {
        operadores: ['Juan Pérez', 'Ana López', 'Carlos Ortega'],
        unidades: ['Tracto 101', 'Tracto 202', 'Tracto 303'],
        numeroContenedor: 'TCNU8868160',
        fecha: '04/07/2025',
        origen: 'Veracruz, MX',
        destino: 'Guadalajara, MX',
        estatus: 'En tránsito'
      };
    
      // Función para llenar el modal
      
        // Llenar selects
        const operadorSelect = document.getElementById('operadorSelect');
        const unidadSelect = document.getElementById('unidadSelect');
        
        operadorSelect.innerHTML = '<option disabled selected>Seleccione un operador</option>';
        unidadSelect.innerHTML = '<option disabled selected>Seleccione una unidad</option>';
        
        data.operadores.forEach(op => {
          operadorSelect.innerHTML += `<option value="${op}">${op}</option>`;
        });
    
        data.unidades.forEach(u => {
          unidadSelect.innerHTML += `<option value="${u}">${u}</option>`;
        });
    
        // Llenar etiquetas informativas
        document.getElementById('numeroContenedor').textContent = data.numeroContenedor;
        document.getElementById('fechaViaje').textContent = data.fecha;
        document.getElementById('origenViaje').textContent = data.origen;
        document.getElementById('destinoViaje').textContent = data.destino;
        document.getElementById('estatusViaje').textContent = data.estatus;
     
}



function abrirDocumentos(idCotizacion) {
    $(`#estatusDoc${idCotizacion}`).modal("show");
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


function aprobarCotizacion(idCotizacion) {
    Swal.fire({
        title: "¿Aprobar cotización?",
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Sí, aprobar"
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/cotizaciones/update/estatus/${idCotizacion}`, {
                method: "PATCH",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
                },
                body: JSON.stringify({ estatus: "Aprobada" })
            })
            .then(() => Swal.fire("Aprobada", "Cotización aprobada", "success"))
            .then(() => getCotizacionesList());
        }
    });
}
function abrirCambioEmpresa(idCotizacion) {
    const form = document.getElementById("formCambioEmpresa");
    const route = `/cotizaciones/cambiar/empresa/${idCotizacion}`;
    form.setAttribute("action", route);

    const modal = new bootstrap.Modal(document.getElementById("modalCambioEmpresa"));
    modal.show();
}


function abrirCambioEstatus(idCotizacion) {
    const form = document.getElementById("formCambioEstatus");

    if (!form) {
        console.error("❌ No se encontró el formulario #formCambioEstatus");
        return;
    }

    // Setear la acción del formulario
    form.action = `/cotizaciones/update/estatus/${idCotizacion}`;

    // Mostrar el modal
    const modal = new bootstrap.Modal(document.getElementById("modalCambioEstatus"));
    modal.show();
}
function abrirDocumentos(idCotizacion) {
    fetch(`/cotizaciones/documentos/${idCotizacion}`)
        .then(response => response.json())
        .then(data => {
            const modal = new bootstrap.Modal(document.getElementById("modalEstatusDocumentos"));
            const titulo = document.getElementById("tituloContenedor");
            const cuerpo = document.getElementById("estatusDocumentosBody");

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

            campos.forEach(item => {
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
        .catch(error => {
            console.error('Error al obtener documentos:', error);
            Swal.fire('Error', 'No se pudieron obtener los documentos', 'error');
        });
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