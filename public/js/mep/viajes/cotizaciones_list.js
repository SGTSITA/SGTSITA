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

  let operadores = [];
  let unidades = [];

  const formFieldsMep = [
    {'field':'txtOperador','id':'txtOperador','label':'Nombre operador','required': true, "type":"text", "trigger":"none"},
    {'field':'txtTelefono','id':'txtTelefono','label':'Teléfono','required': true, "type":"text", "trigger":"none"},
    {'field':'txtNumUnidad','id':'txtNumUnidad','label':'Núm Eco/ Núm Unidad / Identificador','required': true, "type":"text", "trigger":"none"},
    {'field':'txtPlacas','id':'txtPlacas','label':'Placas','required': true, "type":"text", "trigger":"none"},
    {'field':'txtSerie','id':'txtSerie','label':'Núm Serie / VIN','required': true, "type":"text", "trigger":"none"},
    {'field':'selectGPS','id':'selectGPS','label':'Compañia GPS','required': true, "type":"text", "trigger":"none"},
    {'field':'txtImei','id':'txtImei','label':'IMEI','required': true, "type":"text", "trigger":"none"},

]


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
        const formData = {};

        //formFieldsMep
    let passValidation = formFieldsMep.every((item) => {

        let field = document.getElementById(item.field);
        if(field){
            if(item.required === true && field.value.length == 0){
                Swal.fire("El campo "+item.label+" es obligatorio","Parece que no ha proporcionado información en el campo "+item.label,"warning");
                return false;
            }
        }

        if (field.dataset.mepUnidad) {
            formData['mepUnidad'] = field.dataset.mepUnidad
        }

        if (field.dataset.mepOperador) {
            formData['mepOperador'] = field.dataset.mepOperador
        }

        formData[item.field] = field.value;
        return true;

    });

    if(!passValidation) return passValidation;

        

        let data = {"contenenedor":seleccion[0],"formData":formData};
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


});      

function buscarOperador(nombre){
    
    let operador = operadores.find(op =>{
        return (op.nombre === nombre) ? op : false
    })

    let txtTelefono = document.querySelector('#txtTelefono')

    toastr.options.positionClass = 'toast-middle-center';
    let txtOperador = document.querySelector("#txtOperador")
    

    if(operador){
        txtTelefono.value = operador.telefono
        txtOperador.dataset.mepOperador = operador.id
        toastr.success('Operador identificado');
    }else{
        txtTelefono.value = ''
        txtOperador.dataset.mepOperador = 0
        toastr.warning('Operador no encontrado');
    }
    
}

function buscarUnidad(numUnidad){
    
    let unidad = unidades.find(u =>{
        return (u.id_equipo === numUnidad.toUpperCase()) ? u : false
    })

    let txtPlacas = document.querySelector('#txtPlacas')
    let txtSerie = document.querySelector('#txtSerie')
    let txtImei = document.querySelector('#txtImei')
    let selectGPS = document.querySelector('#selectGPS')

    let txtNumUnidad = document.querySelector("#txtNumUnidad")

    toastr.options.positionClass = 'toast-middle-center';
    if(unidad){
        txtPlacas.value = unidad.placas
        txtSerie.value = unidad.num_serie
        txtImei.value = unidad.imei
        
        txtNumUnidad.dataset.mepUnidad = unidad.id
        for (let i = 0; i < selectGPS.options.length; i++) {
            if (selectGPS.options[i].value === String(unidad.gps_company_id)) {
              selectGPS.selectedIndex = i;
              break;
            }
        }
        toastr.success('Unidad identificado');
    }else{
        txtPlacas.value = ''
        txtSerie.value = ''
        txtImei.value = ''
        txtNumUnidad.dataset.mepUnidad = 0
        toastr.warning('No se encontró unidad');
    }
    
}

function getCatalogoOperadorUnidad(){
    let _token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
    $.ajax({
        url:'/mep/catalogos/operador-unidad',
        type:'post',
        data:{_token},
        beforeSend:()=>{},
        success:(response)=>{
            operadores = response.operadores
            unidades = response.unidades
        },
        error:()=>{
            console.error('No pudimos obtener los datos de operadores y unidades de la empresa.')
        }
    })
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