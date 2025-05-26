const gastosFormFields = [
    {'field':'motivo', 'id':'motivo','label':'Descripción','required': true, "type":"text"},
    {'field':'monto1', 'id':'monto1','label':'Monto','required': true, "type":"money"},
    {'field':'categoria_movimiento', 'id':'categoria_movimiento','label':'Categoría','required': true, "type":"text"},
    {'field':'fecha_movimiento', 'id':'fecha_movimiento','label':'Fecha movimiento','required': true, "type":"text"},
    {'field':'fecha_aplicacion', 'id':'fecha_aplicacion','label':'Fecha aplicación','required': true, "type":"text"},
    {'field':'id_banco1', 'id':'id_banco1','label':'Fecha aplicación','required': true, "type":"text"},
];

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
      'rag-green': params => params.data.Diferido === "Diferido",
  },
   rowData: [
  
   ],

   columnDefs: [
     { field: "IdGasto", hide: true},
     { field: "NumContenedor" ,filter: true,floatingFilter: true},
     { field: "Descripcion" },
     { field: "Monto",width: 150, valueFormatter: params => currencyFormatter(params.value), cellStyle: { textAlign: "right" }},
     { field: "FechaGasto",filter: true, floatingFilter: true,valueFormatter: params => formatFecha(params.value)},
  
   ],
  
   localeText: localeText,
   onRowSelected:(event)=>{
    seleccionGastosContenedor()
   },
  };
  
  const myGridElement = document.querySelector('#myGrid');
  let apiGrid = agGrid.createGrid(myGridElement, gridOptions);
  
  
  
  var paginationTitle = document.querySelector("#ag-32-label");
  paginationTitle.textContent = 'Registros por página';
  
  document.querySelectorAll(".fechasDiferir").forEach(elemento => {
  elemento.addEventListener("focus", () => calcDays());
  elemento.addEventListener("blur", () => calcDays());
  elemento.addEventListener("change", () => calcDays());
});

  let IdGasto = null;

    function seleccionGastosContenedor(){
        let seleccion = apiGrid.getSelectedRows();
        let totalPago = 0;
        seleccion.forEach((contenedor) =>{
            totalPago += parseFloat(contenedor.Monto);
        })

        let totalPagoLabel = document.querySelectorAll('.totalPago') 
        totalPagoLabel.forEach(t=> t.textContent = moneyFormat(totalPago))
      //  totalPagoLabel.textContent = moneyFormat(totalPago)
    }  

    function makePayment(){
        const modalElement = document.getElementById('modal-pagar-gastos');
        const bootstrapModal = new bootstrap.Modal(modalElement);
        bootstrapModal.show();
    }

    function applyPaymentGastos(){
        let totalPagoGastos = document.querySelector('#totalPago1')

        let totalPago = reverseMoneyFormat(totalPagoGastos.textContent)
    
        let bancosPagoGastos = document.querySelector('#bancosPagoGastos')
        let bank = bancosPagoGastos.value;
    
        
    
        let gastosPagar = apiGrid.getSelectedRows();
        let _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    
        $.ajax({
          url:'/gastos/payGxp',
          type:'post',
          data:{totalPago, bank, gastosPagar,_token},
          beforeSend:()=>{
            mostrarLoading('Aplicando pago...')
          },
          success:(response)=>{
            Swal.fire(response.Titulo,response.Mensaje,response.TMensaje)
                ocultarLoading()
            if(response.TMensaje == "success"){
                getGxp();
              $('#modalPagar').modal('hide')
            }
          },
          error:()=>{
            ocultarLoading()
            Swal.fire("Error inesperado","Ocurrio un error mientras procesamos su solicitud","error")
    
          }
        })
    }
   
   function getGxp(){

    var _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    $.ajax({
        url: '/gastos/getGxp',
        type: 'post',
        data: {_token},
        beforeSend:()=>{
            mostrarLoading('Cargando gastos... espere un momento')
        },
        success:(response)=>{
            apiGrid.setGridOption("rowData", response.contenedores)
            ocultarLoading();
        },
        error:()=>{
            ocultarLoading();
        }
    });
   }

  





   $(".moneyformat").on("focus",(e)=>{
    var val = e.target.value;
    e.target.value = reverseMoneyFormat(val);
    })
    
    $(".moneyformat").on("blur",(e) =>{
    var val = e.target.value;
    e.target.value =  moneyFormat(val);
    })