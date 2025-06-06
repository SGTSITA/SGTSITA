const gastosFormFields = [
    {'field':'motivo', 'id':'motivo','label':'Descripción','required': true, "type":"text"},
    {'field':'monto1', 'id':'monto1','label':'Monto','required': true, "type":"money"},
    {'field':'categoria_movimiento', 'id':'categoria_movimiento','label':'Categoría','required': true, "type":"text"},
    {'field':'fecha_movimiento', 'id':'fecha_movimiento','label':'Fecha movimiento','required': true, "type":"text"},
    {'field':'fecha_aplicacion', 'id':'fecha_aplicacion','label':'Fecha aplicación','required': false, "type":"text"},
    {'field':'id_banco1', 'id':'id_banco1','label':'Fecha aplicación','required': true, "type":"text"},
];

document.getElementById("tipoPago").addEventListener("change", function () {
  const seccion = document.getElementById("seccionDiferido");
  if (this.value === "1") {
    const bsCollapse = new bootstrap.Collapse(seccion, { show: true });
  } else {
    const bsCollapse = bootstrap.Collapse.getInstance(seccion);
    if (bsCollapse) bsCollapse.hide();
  }
});

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
     { field: "Descripcion" },
     { field: "Monto",width: 150, valueFormatter: params => currencyFormatter(params.value), cellStyle: { textAlign: "right" }},
     { field: "Categoria",width: 150 },
     { field: "CuentaOrigen",filter: true, floatingFilter: true},
     { field: "FechaGasto",filter: true, floatingFilter: true,valueFormatter: params => formatFecha(params.value)},
     { field: "FechaContabilizado",filter: true, floatingFilter: true,valueFormatter: params => formatFecha(params.value)},    
     { field: "Estatus",cellRenderer: MissionResultRenderer},
     { field: "Diferido"}
   ],
  
   localeText: localeText
  };
  
  const myGridElement = document.querySelector('#myGrid');
  let apiGrid = agGrid.createGrid(myGridElement, gridOptions);
  let btnDiferir = document.querySelector('#btnDiferir');
  let monto1 = document.querySelector('#monto1')
  let labelMontoGasto = document.querySelector('#labelMontoGasto')
  let labelGastoDiario = document.querySelector('#labelGastoDiario')
  let labelDiasPeriodo = document.querySelector('#labelDiasPeriodo')
  let labelDescripcionGasto = document.querySelector("#labelDescripcionGasto")
  let btnConfirmacion = document.querySelector('#btnConfirmacion')

  let fromDate = null;
  let toDate = null;

  monto1.addEventListener('input', async (e) => labelMontoGasto.textContent = await moneyFormat(e.target.value), calcDays())

  
  var paginationTitle = document.querySelector("#ag-32-label");
  paginationTitle.textContent = 'Registros por página';
  
  document.querySelectorAll(".fechasDiferir").forEach(elemento => {
    elemento.addEventListener("focus", () => calcDays());
    elemento.addEventListener("blur", () => calcDays());
    elemento.addEventListener("change", () => calcDays());
});

  let IdGasto = null;

  function diferenciaEnDias(fecha1, fecha2) {
    fecha1ToTime = new Date(fecha1)
    fecha2Totme = new Date(fecha2)
    const unDia = 1000 * 60 * 60 * 24; // Milisegundos en un día
    const diferenciaMs = Math.abs(fecha2Totme.getTime() - fecha1ToTime.getTime());
    return Math.floor(diferenciaMs / unDia);
 }

 function diferenciaEnMeses(fecha1, fecha2) {
  let inicio = new Date(fecha1+ "T00:00:00");
  let fin = new Date(fecha2+ "T00:00:00");
  
    let periodos = 1; // Siempre hay al menos un periodo

    if (inicio.getFullYear() === fin.getFullYear() && inicio.getMonth() === fin.getMonth()) {
      return periodos;
    }

    // Mientras no lleguemos al mes y año de la fecha final
    while (inicio.getFullYear() < fin.getFullYear() || inicio.getMonth() < fin.getMonth()) {
      periodos++;
      inicio.setMonth(inicio.getMonth() + 1);
    }

    return periodos;
}

  function calcDays(){
    let fechaI = document.getElementById('txtDiferirFechaInicia');
    let fechaF = document.getElementById('txtDiferirFechaTermina');

    if(fechaI.value.length > 0 && fechaF.value.length > 0){
     let diasContados = diferenciaEnMeses(fechaI.value, fechaF.value)
     labelDiasPeriodo.textContent = diasContados

     let amount = (monto1.value.length > 0) ? reverseMoneyFormat(monto1.value) : 0
     let dailyAmount = parseFloat(amount) / diasContados
     labelGastoDiario.textContent = moneyFormat(dailyAmount)
    }
  }

  btnDiferir.addEventListener('click',()=>{
    let gasto = apiGrid.getSelectedRows();

    if(gasto.length <= 0 || gasto.length > 1){
      Swal.fire('Seleccionar UN Gasto','Debe seleccionar el gasto que desea diferir','warning');
      return;
    } 

    if(gasto[0].Diferido == "Diferido"){
      Swal.fire('Previamente diferido','Este gasto ya fue diferido previtamente','warning');
      return;
    }

    labelMontoGasto.textContent = moneyFormat(gasto[0].Monto)
    labelDescripcionGasto.textContent = gasto[0].Descripcion
    IdGasto = gasto[0].IdGasto
    calcDays()
    const modalElement = document.getElementById('modalDiferir');
    const bootstrapModal = new bootstrap.Modal(modalElement);
    bootstrapModal.show();

  })
   
   function getGastos(from,to){
    fromDate = from
    toDate = to
    var _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    $.ajax({
        url: '/gastos/generales/get',
        type: 'post',
        data: {_token,from,to},
        beforeSend:()=>{},
        success:(response)=>{
            apiGrid.setGridOption("rowData", response)
        },
        error:()=>{

        }
    });
   }

  $("#frmCrearGasto").on('submit',(e)=>{
    e.preventDefault();
    var form = $(this);
   
    var passValidation = gastosFormFields.every((item) => {
        var field = document.getElementById(item.field);
        if(field){
            if(item.required === true && field.value.length == 0){
                Swal.fire("El campo "+item.label+" es obligatorio","Parece que no ha proporcionado información en el campo "+item.label,"warning");
                return false;
            }
        }
        return true;
    })

   if(!passValidation) return passValidation;

   const formData = {};

   gastosFormFields.forEach((item) =>{
    var input = item.field;
    var inputValue = document.getElementById(input);
    if(inputValue){
        if(item.type == "money"){
            formData[input] = (inputValue.value.length > 0) ? parseFloat(reverseMoneyFormat(inputValue.value)) : 0;
        }else{
            formData[input] = inputValue.value;
        }
    }
   });

   formData["_token"] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
   $.ajax({
        url: '/gastos/generales/create',
        type: "post",
        data: formData,
        beforeSend:function(){
        
        },
        success:function(data){
                Swal.fire(data.Titulo,data.Mensaje,data.TMensaje).then(function() {
                    if(data.TMensaje == "success"){
                      $('#exampleModal').modal('hide')
                      getGastos(fromDate,toDate);
                    
                    }
                });
        },
        error:function(){       
        Swal.fire("Error","Ha ocurrido un error, intentelo nuevamente","error");
        }
    })
  })

  function diferirGasto(){
    let fechaDesde = document.getElementById('txtDiferirFechaInicia')
    let fechaHasta = document.getElementById('txtDiferirFechaTermina')

    let gastoDiario = labelGastoDiario.textContent
    let diasContados = labelDiasPeriodo.textContent
    let _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    if(fechaDesde.value.length == 0 || fechaHasta.value.length == 0){
      Swal.fire('Definir periodo','Por favor seleccione la fecha inicial y final del periodo','warning');
      return false;
    }

    const select = document.getElementById("selectUnidades");
    const unidades = Array.from(select.selectedOptions).map(option => option.value);

    let _IdGasto = IdGasto

    $.ajax({
      url: '/gastos/diferir',
      type:'post',
      data:{_token,_IdGasto,gastoDiario,diasContados,unidades,fechaDesde: fechaDesde.value, fechaHasta: fechaHasta.value},
      beforeSend:()=>{},
      success:(response)=>{
        if(response.TMensaje == 'success'){
    
          $('#modalDiferir').modal('hide')
          getGastos(fromDate,toDate);

        }
        Swal.fire(response.Titulo,response.Mensaje,response.TMensaje)
      },
      error:()=>{
        Swal.fire('Error','Ocurrió un error','error')
      }
    });
  }

  btnConfirmacion.addEventListener('click',()=>{
    diferirGasto()
  })

   $(".moneyformat").on("focus",(e)=>{
    var val = e.target.value;
    e.target.value = reverseMoneyFormat(val);
    })
    
    $(".moneyformat").on("blur",(e) =>{
    var val = e.target.value;
    e.target.value =  moneyFormat(val);
    })