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

      const IdSubClienteValue = params.data.IdSubCliente;

      this.eventListener = () => goToEdit(IdSubClienteValue);
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
   rowData: [
  
   ],

   columnDefs: [
     { field: "",width: 60, cellRenderer: CustomButtonComponent},
     { field: "SubCliente",filter: true, floatingFilter: true},
     { field: "RFC",filter: true, floatingFilter: true},
     { field: "NombreComercial" },
     { field: "CorreoElectronico",width: 100 },
     { field: "Telefono",width: 110 },
   ],
  
   localeText: localeText
  };
  
  const myGridElement = document.querySelector('#agGridSubClientes');
  let apiGrid = agGrid.createGrid(myGridElement, gridOptions);
  
  var paginationTitle = document.querySelector("#ag-32-label");
  paginationTitle.textContent = 'Registros por página';
   
   function getSubClientes(){
    var _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    var _cliente = document.querySelector('meta[name="id-cliente"]').getAttribute('content');
    $.ajax({
        url: '/clientes/list',
        type: 'post',
        data: {_token, _cliente},
        beforeSend:()=>{},
        success:(response)=>{
          apiGrid.setGridOption("rowData", response.data)
          let counter = document.querySelector("#countSubclientes");
          if(counter){
            counter.textContent = response.data.length
          }
        },
        error:()=>{

        }
    });
   }

   function goToEdit(IdSubCliente){
    var _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    var _cliente = document.querySelector('meta[name="id-cliente"]').getAttribute('content');

    var url = '/clientes/edit';

    var form =
    $('<form action="' + url + '" method="post">' +
        '<input type="hidden" name="id_subcliente" value="'+IdSubCliente+'" />' +
        '<input type="hidden" name="idClient" value="'+_cliente+'" />' +
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

 