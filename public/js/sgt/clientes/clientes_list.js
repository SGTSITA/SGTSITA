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
        mode: "singleRow",
        headerCheckbox: false,
      },
   rowData: [
  
   ],

   columnDefs: [
     { field: "IdCliente", hide: true},
     { field: "Nombre",filter: true, floatingFilter: true},
     { field: "Correo",filter: true, floatingFilter: true},
     { field: "Telefono",filter: true, floatingFilter: true },
     { field: "RFC",width: 100 },
     { field: "RegimenFiscal",width: 200 },
     { field: "Empresa",width: 110 },
     { field: "Direccion",width: 110 },
   ],
  
   localeText: localeText
  };
  
  const myGridElement = document.querySelector('#myGrid');
  let apiGrid = agGrid.createGrid(myGridElement, gridOptions);
 // const gridInstance = new agGrid.Grid(myGridElement, gridOptions);
  
  var paginationTitle = document.querySelector("#ag-32-label");
  paginationTitle.textContent = 'Registros por página';
  
  let IdContenedor = null;
   
   function getClientesList(){
    var _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    $.ajax({
        url: '/clients/get-list',
        type: 'post',
        data: {_token},
        beforeSend:()=>{},
        success:(response)=>{
  
          apiGrid.setGridOption("rowData", response.list)
        },
        error:()=>{

        }
    });
   }

   function goToClientEdit(){
    var _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    let client = apiGrid.getSelectedRows();
    
    if(client.length == 0){
      Swal.fire('Seleccione cliente','Debe seleccionar un cliente','warning');
      return false;
    }
    let IdCliente = null;
    
    client.forEach(c => IdCliente = c.IdCliente)
    

    var url = '/clients/edit';

    var form =
    $('<form action="' + url + '" method="post">' +
        '<input type="hidden" name="id_client" value="'+IdCliente+'" />' +
        '<input type="hidden" name="_token" value="' + _token + '" />' +
    '</form>');

    $('body').append(form);
    form.submit();
    setTimeout(()=>{
        if (form) {
            form.remove();
        }
    },1000)
    
   }

   function goToSubClients(){
    var _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    let client = apiGrid.getSelectedRows();
    
    if(client.length == 0){
      Swal.fire('Seleccione cliente','Debe seleccionar un cliente','warning');
      return false;
    }
    let IdCliente = null;
    
    client.forEach(c => IdCliente = c.IdCliente)
    
    var url = '/subclientes/list';

    var form =
    $('<form action="' + url + '" method="post">' +
        '<input type="hidden" name="id_client" value="'+IdCliente+'" />' +
        '<input type="hidden" name="_token" value="' + _token + '" />' +
    '</form>');

    $('body').append(form);
    form.submit();
    setTimeout(()=>{
        if (form) {
            form.remove();
        }
    },1000)
   }