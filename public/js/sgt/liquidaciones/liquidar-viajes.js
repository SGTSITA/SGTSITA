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
   rowData: [
  
   ],

   columnDefs: [
     { field: "IdAsignacion", hide: true},
     { field: "IdOperador", hide: true},
     { field: "IdContenedor", hide: true},
     { field: "ContenedorPrincipal", hide: true},
     { field: "Contenedores" },
     { field: "SueldoViaje",width: 150, valueFormatter: params => currencyFormatter(params.value), cellStyle: { textAlign: "right" }},
     { field: "DineroViaje",width: 150, valueFormatter: params => currencyFormatter(params.value), cellStyle: { textAlign: "right" }},
     { field: "GastosJustificados",width: 150, valueFormatter: params => currencyFormatter(params.value), cellStyle: { textAlign: "right" }},
     { field: "MontoPago",width: 150, valueFormatter: params => currencyFormatter(params.value), cellStyle: { textAlign: "right" }},
     { field: "FechaInicia",width: 150 },
     { field: "FechaTermina",width: 150 },
    
   ],
  
   localeText: localeText
  };
  
  const myGridElement = document.querySelector('#myGrid');
  let apiGrid = agGrid.createGrid(myGridElement, gridOptions);
 // const gridInstance = new agGrid.Grid(myGridElement, gridOptions);
  
  var paginationTitle = document.querySelector("#ag-32-label");
  paginationTitle.textContent = 'Registros por página';
  
  let IdContenedor = null;
  let IdOperador = document.querySelector('#IdOperador');
  let dTotalPago = document.querySelector('#totalPago')
  let dNumViajes = document.querySelector('#numViajes')
  let btnSummaryPayment = document.querySelector('#btnSummaryPayment')
  let sumaSalario = document.querySelector('#sumaSalario')
  let sumaDineroViaje = document.querySelector('#sumaDineroViaje')
  let sumaJustificados = document.querySelector('#sumaJustificados')
  let sumaPago = document.querySelector('#sumaPago')
  let contadorContenedores = document.querySelector("#contadorContenedores")
  let btnConfirmaPago = document.querySelector("#btnConfirmaPago")
  let btnJustificar = document.querySelector("#btnJustificar")
  let totalMontoPago = 0;
   
   function mostrarViajesOperador(operador){
    var _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    $.ajax({
        url: '/liquidaciones/viajes/operador',
        type: 'post',
        data: {_token, operador},
        beforeSend:()=>{
          mostrarLoading('Obteniendo viajes')
        },
        success:(response)=>{
            apiGrid.setGridOption("rowData", response.viajes)
            dTotalPago.textContent = moneyFormat(response.totalPago)
            dNumViajes.textContent = response.numViajes
            ocultarLoading()
        },
        error:()=>{
          ocultarLoading()
        }
    });
   }

   function summaryPay(){

    let pagoContenedores = apiGrid.getSelectedRows();
    if(pagoContenedores.length <= 0){
        Swal.fire('Seleccione contenedores','Debe seleccionar al menos un contenedor de la lista','warning');
        return;
    } 

    let suma = 0;
    let sumSalario = 0;
    let sumJustificado = 0;
    let sumDineroViaje = 0;

    pagoContenedores.forEach((c)=>{
       suma = suma + parseFloat(c.MontoPago);
       sumSalario = sumSalario + parseFloat(c.SueldoViaje);
       sumDineroViaje = parseFloat(sumDineroViaje ?? 0)  + parseFloat(c.DineroViaje ?? 0);
       sumJustificado = sumJustificado + parseFloat(c.GastosJustificados);

    });

    totalMontoPago = suma;

    sumaPago.textContent = moneyFormat(suma)
    sumaSalario.textContent = moneyFormat(sumSalario)
    sumaDineroViaje.textContent = `- ${moneyFormat(sumDineroViaje)}`
    sumaJustificados.textContent = `+ ${moneyFormat(sumJustificado)}`
    contadorContenedores.textContent = `${pagoContenedores.length} de ${dNumViajes.textContent}`

    const modalElement = document.getElementById('exampleModal');
    const bootstrapModal = new bootstrap.Modal(modalElement);
    bootstrapModal.show();

   }

   function aplicarPago(){
    let banco = document.getElementById('cmbBankOne');
    if(banco.value == "null"){
        Swal.fire('Seleccione cuenta de retiro','Por favor seleccione la cuenta de retiro','warning');
        return;
    }

    let pagoContenedores = apiGrid.getSelectedRows();
    let bancoId = banco.value;
    var _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    let _IdOperador = IdOperador.value;

    $.ajax({
        url:'/liquidaciones/viajes/aplicar-pago',
        type:'post',
        data:{_token,_IdOperador,pagoContenedores,bancoId,totalMontoPago},
        beforeSend:()=>{

        },
        success:(response)=>{
            Swal.fire(response.Titulo, response.Mensaje, response.TMensaje);
            if(response.TMensaje == "success"){
              location.reload()
            }
        },
        error:()=>{
            Swal.fire("Error", "Ha ocurrido un error", "danger");
        }
    })

   }

   btnSummaryPayment.addEventListener('click',()=>{
    summaryPay()
   });

   btnConfirmaPago.addEventListener('click',()=>{
    aplicarPago()
   });

   btnJustificar.addEventListener('click',()=>{
    openModalJustificar()
   });

  function openModalJustificar(){
    let justificaContenedores = apiGrid.getSelectedRows();
      if(justificaContenedores.length != 1){
          Swal.fire('Seleccione un contenedor','Debe seleccionar solo un contenedor de la lista','warning');
          return false;
      } 

    const modalElement = document.getElementById('modal-justificar');
      const bootstrapModal = new bootstrap.Modal(modalElement);
      bootstrapModal.show();
  }

  function justificarGasto(){

    let monto = document.getElementById("txtMonto").value
    if(monto.length == 0){
      Swal.fire('Ingrese Monto','Por favor introduzca el monto del gasto que está justificando','warning')
      return false
    }

    let txtDescripcion = document.getElementById('txtDescripcion').value;

    if(txtDescripcion.length == 0){
      Swal.fire('Ingrese descripción','Por favor introduzca la descripción del gasto que está justificando','warning')
      return false
    }

    let justificaContenedores = apiGrid.getSelectedRows();
    let DineroViaje = 0;
    let GastosJustificados = 0;
    let numContenedor;
    let IdOperador = null;
    justificaContenedores.forEach((cn)=>{
      DineroViaje = cn.DineroViaje
      GastosJustificados = cn.GastosJustificados || 0
      numContenedor = cn.ContenedorPrincipal
      IdOperador = cn.IdOperador
    })

    let sinJustificar = DineroViaje - GastosJustificados;
    let montoJustificacion = reverseMoneyFormat( document.getElementById("txtMonto").value);
    
    /*if(montoJustificacion > 0 && montoJustificacion > sinJustificar){
      Swal.fire("Monto a justificar incorrecto",`El monto a justificar debe ser mayor a cero y no debe superar el monto pendiente de Dinero de Viaje. Monto pendiente por justificar ${moneyFormat(sinJustificar)}`,"warning");
      return false;
    }*/

    let _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    if(txtDescripcion.length == 0){
      Swal.fire('Ingrese descripcion','Por favor introduzca la descripción del gasto que está justificando','warning')
      return false
    }

    $.ajax({
      url:'/liquidaciones/viajes/gastos/justificar',
      type:'post',
      data:{_token, montoJustificacion,numContenedor, txtDescripcion},
      beforeSend:()=>{

      },
      success:(response)=>{
        Swal.fire(response.Titulo,response.Mensaje,response.TMensaje)
        $('#exampleModal').modal('hide')
        if(response.TMensaje == "success"){
         // setTimeout(()=>{location.reload()},350)
         mostrarViajesOperador(IdOperador)
          
        }
      },
      error:(error)=>{
        Swal.fire('Error','Ocurre un problema','error')
      }
    })
  }

   $(".moneyformat").on("focus",(e)=>{
    var val = e.target.value;
    e.target.value = reverseMoneyFormat(val);
    })
    
    $(".moneyformat").on("blur",(e) =>{
    var val = e.target.value;
    e.target.value =  moneyFormat(val);
    })