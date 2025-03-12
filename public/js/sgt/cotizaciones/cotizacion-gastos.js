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

   const currencyFormatter = (value) => {
      return new Intl.NumberFormat("es-MX", { style: "currency", currency: "MXN" }).format(value);
    };

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
    noRowsToShow: 'Aún no hay gastos registrados',
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
      onRowSelected:(event)=>{
        btnDeleteStatus()
      },
      rowData: [
      
      ],
      columnDefs: [
        { field: "IdContenedor", hide: true},
        { field: "IdGasto", hide: true},
        { field: "Gasto",width: 210 },
        { field: "Monto",width: 110 ,valueFormatter: params => currencyFormatter(params.value), cellStyle: { textAlign: "right" } },
        { field: "Fecha",filter: true, floatingFilter: true}
              
      ],
      
      localeText: localeText
  };
  
  const myGridElement = document.querySelector('#gridGastos');
  let apiGrid = agGrid.createGrid(myGridElement, gridOptions);

  let btnDelete = document.querySelector('#btnDelete')
 btnDelete.addEventListener('click',()=>{
  deleteGastos()
 })
  
  var paginationTitle = document.querySelector("#ag-32-label");
  paginationTitle.textContent = 'Registros por página';
  
  let IdContenedor = null;

  function btnDeleteStatus(){
    let seleccion = apiGrid.getSelectedRows();
    btnDelete.disabled = (seleccion.length == 0) ? true : false;
  }
   
   function getGastosContenedor(){
    var _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    let spanContenedor = document.querySelector("#spanContenedor");
    let numContenedor = spanContenedor.textContent;
    $.ajax({
        url: '/cotizaciones/gastos/get',
        type: 'post',
        data: {_token, numContenedor},
        beforeSend:()=>{},
        success:(response)=>{
            apiGrid.setGridOption("rowData", response)
            let total = 0;
            response.forEach((d)=>{
              total += d.Monto
            });

            let txtSumGastos = document.querySelectorAll(".txtSumGastos");
            let txtTotalCotizacion = document.querySelector("#txtTotalCotizacion");
            let txtResultGastos  = document.querySelectorAll(".txtResultGastos");
            let valorCotizacion = reverseMoneyFormat(txtTotalCotizacion.value);
            valorCotizacion = parseFloat(valorCotizacion)

            txtSumGastos.forEach(i => i.value = moneyFormat(total))

            txtTotalCotizacion.value = moneyFormat(valorCotizacion)
            txtResultGastos.forEach((r) => r.value = moneyFormat(valorCotizacion + total))
        },
        error:()=>{

        }
    });
   }

   function putGastosContenedor(){
    let spanContenedor = document.querySelector("#spanContenedor");
    let numContenedor = spanContenedor.textContent;

    let textDescripcion = document.querySelector("#txtDescripcion")
    let textMonto = document.querySelector("#txtMonto")

    if(textMonto.value.length == 0 || textDescripcion.value.length == 0){
        Swal.fire('Complete información','La información del descuento está incompleta, por favor llene todos los campos','warning')
        return false;
    }

    let montoGasto = reverseMoneyFormat(textMonto.value)
    let descripcion = textDescripcion.value
   let _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    $.ajax({
        url:'/cotizaciones/gastos/registrar',
        type:'post',
        data:{numContenedor,descripcion,montoGasto,_token},
        beforeSend:()=>{},
        success:(response)=>{
            Swal.fire(response.Titulo,response.Mensaje,response.TMensaje)
            $('#modal-form').modal('hide')
            textDescripcion.value = '';
            textMonto.value = '';
            getGastosContenedor()
        },
        error:(err)=>{
            Swal.fire('Ocurrio un error','Error','error')
        }
    })
   }

   function deleteGastos(){
    let spanContenedor = document.querySelector("#spanContenedor");
    let numContenedor = spanContenedor.textContent;
    let _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    let seleccionGastos = apiGrid.getSelectedRows();

    $.ajax({
      url:'/cotizaciones/gastos/eliminar',
      type:'post',
      data:{numContenedor,_token,seleccionGastos},
      beforeSend:()=>{},
      success:(response)=>{
          Swal.fire(response.Titulo,response.Mensaje,response.TMensaje)
          
          getGastosContenedor()
      },
      error:(err)=>{
          console.error(err)
          Swal.fire('Ocurrio un error','Error','error')
      }
  })
   }



   