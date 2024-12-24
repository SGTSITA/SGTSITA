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
      button.innerHTML = '<span class="svg-icon svg-icon-muted svg-icon-2hx"><svg width="23" height="24" viewBox="0 0 23 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M21 13V13.5C21 16 19 18 16.5 18H5.6V16H16.5C17.9 16 19 14.9 19 13.5V13C19 12.4 19.4 12 20 12C20.6 12 21 12.4 21 13ZM18.4 6H7.5C5 6 3 8 3 10.5V11C3 11.6 3.4 12 4 12C4.6 12 5 11.6 5 11V10.5C5 9.1 6.1 8 7.5 8H18.4V6Z" fill="currentColor"/><path opacity="0.3" d="M21.7 6.29999C22.1 6.69999 22.1 7.30001 21.7 7.70001L18.4 11V3L21.7 6.29999ZM2.3 16.3C1.9 16.7 1.9 17.3 2.3 17.7L5.6 21V13L2.3 16.3Z" fill="currentColor"/></svg></span></span>';
      button.className = "btn btn-sm bg-gradient-success";
      button.style.fontSize = "10px";
      button.style.padding = "2px 6px";
      button.style.lineHeight = "1";

      const NumContenedorValue = params.data.NumContenedor;

      this.eventListener = () => assignEmpresa(NumContenedorValue);
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
      paginationPageSize: 100,
      paginationPageSizeSelector: [100, 200, 500],
      rowSelection: {
        mode: "multiRow",
        headerCheckbox: true,
      },
   rowData: [
  
   ],

   columnDefs: [
     { field: "IdContenedor", hide: true},
     { field: "NumContenedor",filter: true, floatingFilter: true},
     { field: "Origen",filter: true, floatingFilter: true},
     { field: "Destino" },
     { field: "Peso",width: 100 },
     { field: "BoletaLiberacion",width: 110,cellRenderer: MissionResultRenderer },
     { field: "DODA",width: 110,cellRenderer: MissionResultRenderer },
     { field: "CartaPorte",width: 110,cellRenderer: MissionResultRenderer },
   ],
  
   localeText: localeText
  };
  
  const myGridElement = document.querySelector('#myGrid');
  let apiGrid = agGrid.createGrid(myGridElement, gridOptions);
 // const gridInstance = new agGrid.Grid(myGridElement, gridOptions);
  
  var paginationTitle = document.querySelector("#ag-32-label");
  paginationTitle.textContent = 'Registros por página';
  
  let IdContenedor = null;
   
   function getContenedoresPorAsignar(){
    var _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    $.ajax({
        url: '/viajes/get-asignables',
        type: 'post',
        data: {_token},
        beforeSend:()=>{},
        success:(response)=>{
            console.log(response)
          apiGrid.setGridOption("rowData", response)
        },
        error:()=>{

        }
    });
   }

   function assignEmpresa(_IdContenedor){
        const modalElement = document.getElementById('cambioEmpresa');
        const bootstrapModal = new bootstrap.Modal(modalElement);
        bootstrapModal.show();
        IdContenedor = _IdContenedor
   }

   function asignarContenedores(){
    let contenedores = apiGrid.getSelectedRows();

    if($("#cmbEmpresa").val() == ""){
      Swal.fire("Seleccione Empresa","Aún no ha seleccionado Empresa, este es un campo requerido","warning");
      return false;
    }
    
    if(contenedores.length == 0){
      Swal.fire('Seleccione contenedores','Para realizar la asignación debe seleccionar al menos un contenedor','warning');
      return false;
    }

    Swal.fire({
      title: '¿Asignar seleccionados?',
      icon: 'question',
      confirmButtonText: 'Si, asignar',
      cancelButtonText: 'Cancelar',
      showCancelButton: true
    }).then((respuesta) =>{
      if(respuesta.isConfirmed){
        confirmarAsignarContenedores(contenedores);
      }
    })
   }

   function confirmarAsignarContenedores(contenedores){
    let _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    let empresa = $("#cmbEmpresa").val();
    let seleccionContenedores = JSON.stringify(contenedores)
    $.ajax({
      url: '/cotizaciones/asignar/empresa',
      type: 'post',
      data: {_token, seleccionContenedores,empresa},
      beforeSend:()=>{

      },
      success:(response)=>{
        if(response.TMensaje == "success") getContenedoresPorAsignar();
        Swal.fire(response.Titulo,response.Mensaje,response.TMensaje);
      },
      error:(x, error)=>{
        Swal.fire('Ocurrio un error',x.getMessage(),'error');
      }
    });
    
   }