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

  const currencyFormatter = (value) => {
    return new Intl.NumberFormat("es-MX", { style: "currency", currency: "MXN" }).format(value);
  };

  const formatFecha = params => {
    if (!params) return "";
    const [year, month, day] = params.split("-"); // Divide YYYY-MM-DD
    return `${day}/${month}/${year}`; // Retorna en formato d/m/Y
  };



  const gridOptions = {
   pagination: true,
   paginationPageSize: 10,
   paginationPageSizeSelector: [10, 20, 50,100],
   rowSelection: {
    mode: "multiRow",
    headerCheckbox: true,
   },
   rowClassRules: {
    'bg-gradient-danger': params => params.data.utilidad < 0,
   },
   rowData: [
  
   ],

   columnDefs: [
   
     { field: "numContenedor",filter: true, floatingFilter: true},
     { field: "cliente",filter: true, floatingFilter: true },
     { field: "precioViaje",width: 150, valueFormatter: params => currencyFormatter(params.value), cellStyle: { textAlign: "right" }},
     { field: "pagoOperacion",width: 150, valueFormatter: params => currencyFormatter(params.value), cellStyle: { textAlign: "right" } },
     { field: "gastosExtra", valueFormatter: params => currencyFormatter(params.value), cellStyle: { textAlign: "right" }},
     { field: "gastosViaje", valueFormatter: params => currencyFormatter(params.value), cellStyle: { textAlign: "right" }},
     { field: "gastosDiferidos", valueFormatter: params => currencyFormatter(params.value), cellStyle: { textAlign: "right" }},
     { field: "utilidad", valueFormatter: params => currencyFormatter(params.value), cellStyle: { textAlign: "right" } },
     { field: "transportadoPor",width: 150,filter: true, floatingFilter: true},
     { field: "operadorOrProveedor",filter: true, floatingFilter: true},
     { field: "estatusViaje",filter: true, floatingFilter: true},
     { field: "estatusPago",filter: true, floatingFilter: true},
     { field: "detalleGastos", hide:true},
   ],
  
   localeText: localeText
  };
  
  const myGridElement = document.querySelector('#myGrid');
  let apiGrid = agGrid.createGrid(myGridElement, gridOptions);
 // const gridInstance = new agGrid.Grid(myGridElement, gridOptions);
  
  var paginationTitle = document.querySelector("#ag-32-label");
  paginationTitle.textContent = 'Registros por página';
  
  let IdContenedor = null;
  let btnVerDetalle = document.querySelector('#btnVerDetalle')

  function exportUtilidades(){

    var _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const rowData = JSON.stringify( apiGrid.getSelectedRows());
    const totalRows = apiGrid.paginationGetRowCount();
    let fechaInicio = $('#daterange').attr('data-start');
    let fechaFin = $('#daterange').attr('data-end');

    $.ajax({
      url: '/reporteria/utilidad/export',
      method: 'POST',
      data: {
          _token: _token,
          rowData: rowData,
          totalRows: totalRows,
          fechaInicio: fechaInicio,
          fechaFin: fechaFin,
          fileType: 'pdf'
      },
      xhrFields: {
        responseType: "blob" // Asegura que el tipo de respuesta sea un Blob
      },
      success: function(response) {
        console.log(response)
        if (response instanceof Blob) {
          var blob = new Blob([response], { type: 'application/pdf'  });
          var url = URL.createObjectURL(blob);

          var a = document.createElement('a');
          a.style.display = 'none';
          a.href = url;
          a.target = "_blank"
         // a.download = 'Cuentas_por_pagar_{{ date('d-m-Y') }}.' + fileType;
          document.body.appendChild(a);

          // Inicia la descarga
          a.click();

          // Limpiar después de la descarga
          window.URL.revokeObjectURL(url);
          document.body.removeChild(a);
        }else {
          console.error("La respuesta no es un Blob válido:", response);
      }

         // alert('El archivo se ha descargado correctamente.');
      },
      error: function(xhr, status, error) {
          console.error(error);
          alert('Ocurrió un error al exportar los datos.');
      }
  });
  }
   
   function getUtilidadesViajes(startDate, endDate){
    var _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    $.ajax({
        url: '/reporteria/utilidad/ver-utilidad',
        type: 'post',
        data: {_token,startDate, endDate},
        beforeSend:()=>{},
        success:(response)=>{
            let data = JSON.parse(response)
            apiGrid.setGridOption("rowData", data.Info)
        },
        error:()=>{

        }
    });
   }

   function verDetalleGastos(){

    let contenedor = apiGrid.getSelectedRows();
    if(contenedor.length <= 0){
        Swal.fire('Seleccione un contenedor','Debe seleccionar un contenedor de la lista','warning');
        return;
    } 
    document.getElementById('labelContenedor').textContent = contenedor[0].numContenedor
    let elementos =contenedor[0].detalleGastos;
    const ul = document.getElementById('infoGastos');
    while (ul.firstChild) {
          ul.removeChild(ul.firstChild);
    }

    elementos.forEach(item => {

      let liTemplate = `<li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
      <div class="d-flex flex-column">
        <h6 class="mb-1 text-dark font-weight-bold text-sm" style="color:#333335 !important">${item.motivo_gasto}</h6>
        <span class="text-xs">${obtenerFechaEnLetra(item.fecha_gasto)}</span>
        <span class="badge bg-purple-transparent">${item.tipo_gasto}</span>
      </div>
      <div class="d-flex fw-semibold align-items-right" style="color:#333335; font-size:16px;">
       ${moneyFormat(item.monto_gasto)}
      </div>
    </li>`

      ul.innerHTML += liTemplate;

    });

    mostrarModal()
   }

   btnVerDetalle.addEventListener('click',()=> {
    verDetalleGastos()
   });

   $(".moneyformat").on("focus",(e)=>{
    var val = e.target.value;
    e.target.value = reverseMoneyFormat(val);
    })
    
    $(".moneyformat").on("blur",(e) =>{
    var val = e.target.value;
    e.target.value =  moneyFormat(val);
    })