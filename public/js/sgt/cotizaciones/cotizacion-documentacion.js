class MissionResultRenderer {
    eGui;
  
    // Optional: Params for rendering. The same params that are passed to the cellRenderer function.
    init(params) {
      let icon = document.createElement("img");
      icon.src = `https://www.ag-grid.com/example-assets/icons/${params.value ? "tick-in-circle" : "cross-in-circle"}.png`;
      icon.setAttribute("style", "width: auto; height: auto;");
  
      this.eGui = document.createElement("span");
      this.eGui.setAttribute(
        "style",
        "display: flex; justify-content: center; height: 100%; align-items: center",
      );
      this.eGui.appendChild(icon);
    }
  
    // Required: Return the DOM element of the component, this is what the grid puts into the cell
    getGui() {
      return this.eGui;
    }
  
    // Required: Get the cell to refresh.
    refresh(params) {
      return false;
    }
  }

  class CustomButtonComponent {
    eGui;
    eButton;
    eventListener;
   
    init(params) {
      this.eGui = document.createElement("div");
      let button = document.createElement("button");
      button.innerHTML = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path opacity="0.3" d="M5 16C3.3 16 2 14.7 2 13C2 11.3 3.3 10 5 10H5.1C5 9.7 5 9.3 5 9C5 6.2 7.2 4 10 4C11.9 4 13.5 5 14.3 6.5C14.8 6.2 15.4 6 16 6C17.7 6 19 7.3 19 9C19 9.4 18.9 9.7 18.8 10C18.9 10 18.9 10 19 10C20.7 10 22 11.3 22 13C22 14.7 20.7 16 19 16H5ZM8 13.6H16L12.7 10.3C12.3 9.89999 11.7 9.89999 11.3 10.3L8 13.6Z" fill="currentColor" /><path d="M11 13.6V19C11 19.6 11.4 20 12 20C12.6 20 13 19.6 13 19V13.6H11Z" fill="currentColor" /></svg>';
      button.className = "btn btn-sm btn-primary";
      button.style.fontSize = "10px";
      button.style.padding = "2px 6px";
      button.style.lineHeight = "1";

      const NumContenedorValue = params.data.NumContenedor;

      this.eventListener = () => goToUploadDocuments(NumContenedorValue);
      button.addEventListener("click", this.eventListener);
      this.eGui.appendChild(button);
    }
   
    getGui() {
      return this.eGui;
    }
   
    refresh(params) {
      return true;
    }
   
    destroy() {
      if (button) {
        button.removeEventListener("click", this.eventListener);
      }
    }
   }

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
   
  const gridOptions = {
   pagination: true,
      paginationPageSize: 500,
      paginationPageSizeSelector: [200, 500, 1000],
      rowSelection: {
        mode: "singleRow",
        headerCheckbox: false,
      },
   rowData: [
  
   ],

   columnDefs: [

     { field: "NumContenedor",filter: true, floatingFilter: true},
     { field: "Origen",filter: true, floatingFilter: true},
     { field: "Destino" },
     { field: "Peso",width: 100 },
     { field: "BoletaLiberacion",width: 110,cellRenderer: MissionResultRenderer },
     { field: "DODA",width: 110,cellRenderer: MissionResultRenderer },
     { field: "FormatoCartaPorte",width: 150,cellRenderer: MissionResultRenderer },
     { field: "PreAlta",width: 110,cellRenderer: MissionResultRenderer },
   ],
  
   localeText: localeText
  };
  
  const myGridElement = document.querySelector('#myGrid');
  let apiGrid = agGrid.createGrid(myGridElement, gridOptions);
 // const gridInstance = createGrid(myGridElement, gridOptions)//new agGrid.Grid(myGridElement, gridOptions);
  
  var paginationTitle = document.querySelector("#ag-32-label");
  paginationTitle.textContent = 'Registros por página';
  
  const btnDocumets = document.querySelector('#btnDocs');
  //const api = createGrid(gridDiv, gridOptions)
   
   function getContenedoresPendientes(estatus = 'En espera'){
    var _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    $.ajax({
        url: '/viajes/documents/pending',
        type: 'post',
        data: {_token,estatus},
        beforeSend:()=>{},
        success:(response)=>{
            
          if(response.length > 0){
            btnDocumets.disabled = false;
          }
          apiGrid.setGridOption("rowData", response)
        },
        error:()=>{

        }
    });
   }

   function goToUploadDocuments(){
        let contenedor = apiGrid.getSelectedRows();

        let numContenedor = null;
        contenedor.forEach(c => numContenedor = c.NumContenedor)

        let titleFileUploader = document.querySelector("#titleFileUploader");
        titleFileUploader.textContent = numContenedor.toUpperCase();
        localStorage.setItem('numContenedor',numContenedor);
        const modalElement = document.getElementById('kt_modal_fileuploader');
        const bootstrapModal = new bootstrap.Modal(modalElement);
        bootstrapModal.show();
   }

   function cancelarViajeQuestion(){
    Swal.fire({
      title: '¿Desea cancelar el viaje seleccionado?',
      text:'Está a punto de cancelar el viaje, si está seguro haga click en "Si, Cancelar"',
      icon: 'question',
      confirmButtonText: 'Si, Cancelar',
      cancelButtonText: 'No quiero cancelarlo',
      showCancelButton: true
    }).then((respuesta) =>{
      if(respuesta.isConfirmed){
        cancelarViajeConfirm();
      }
    })
   }

   function cancelarViajeConfirm(){
    let contenedor = apiGrid.getSelectedRows();

    let numContenedor = null;
    contenedor.forEach(c => numContenedor = c.NumContenedor)

    let _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    $.ajax({
      url:'/viajes/cancelar',
      type:'post',
      data:{_token, numContenedor},
      beforeSend:()=>{

      },
      success:(response)=>{
        Swal.fire(response.Titulo,response.Mensaje, response.TMensaje);
      },
      error:(err)=>{
        Swal.fire("Ocurrio un error","No se pudo procesar la solicitud", "error");
      }
    });
   }

   btnDocumets.addEventListener('click',goToUploadDocuments)