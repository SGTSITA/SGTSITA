/**
 * Requiere HandsOnTable version 8
 */
var currentBalance = 0;
var applyPayments = 0;
var finalBalance = 0;
var totalPayment = 0;
 var data = [

];

var containerPagos = document.getElementById('pendientes');
var FileName = 'tableroPendientes';

var config = {
  data: data,
  minRows: 0,
  width: '100%',
  height: 400,
  rowHeaders: true,
  minSpareRows: 0,
  autoWrapRow: true,

  colHeaders: ['# CONTENEDOR','SUBCLIENTE','TIPO VIAJE',  'ESTATUS', 'SALDO ORIGINAL','SALDO ACTUAL', 'PAGO 1','PAGO 2',"TOTAL PAGADO"],
  fixedColumnsLeft: 1,
  columns:[{readOnly:true},{readOnly:true },{readOnly:true},{readOnly:true},
    {
      readOnly:true,
      type: 'numeric',
      numericFormat: {
        pattern: '$ 0,0.00',
        culture: 'en-US'
      }
    },
    {
      type: 'numeric',
      numericFormat: {
        pattern: '$ 0,0.00',
        culture: 'en-US'
      }
    },
    {
      type: 'numeric',
      numericFormat: {
        pattern: '$ 0,0.00',
        culture: 'en-US'
      }
    },
    {
      readOnly:false,
      type: 'numeric',
      numericFormat: {
        pattern: '$ 0,0.00',
        culture: 'en-US'
      },
      render:errorRenderer
    },
    {
      readOnly:true,
      type: 'numeric',
      numericFormat: {
        pattern: '$ 0,0.00',
        culture: 'en-US'
      }
    }],
  //hiddenColumns: {columns: [7], indicators: true },
  filters: true,
  dropdownMenu: ['filter_by_value','filter_action_bar'],
  licenseKey: 'non-commercial-and-evaluation',
  copyPaste: true,
  language: 'es-MX',
  columnSorting: true, // Habilita la ordenación de columnas
  sortIndicator: true 
}

function negativeValueRenderer(instance, td, row, col, prop, value, cellProperties) {
  Handsontable.renderers.TextRenderer.apply(this, arguments);
    if (value === 'Finalizado') {
      td.style.fontStyle = 'italic';
      td.style.background = '#98e1e6fe';
    }else if (value==='En Curso'){
      td.style.background = '#F8BC30';
    }else if (value==="Cancelado") {
      td.style.background = "#C21A1A";
      td.style.color = "#FFFFFF";
    }
    
}

var colorRenderer = function (instance, td, row, col, prop, value, cellProperties) {
  Handsontable.renderers.TextRenderer.apply(this, arguments);
  //td.style.background = "#C21A1A";
    td.style.color = "#C21A1A";
};

var errorRenderer = function (instance, td, row, col, prop, value, cellProperties) {
    Handsontable.renderers.NumericRenderer.apply(this, arguments); // Usamos el NumericRenderer base
    td.style.color = "#C21A1A"; // Cambia el color del texto a rojo
    td.style.fontWeight = "bold";
};

Handsontable.renderers.registerRenderer('negativeValueRenderer', negativeValueRenderer);
Handsontable.renderers.registerRenderer('colorRenderer', colorRenderer);
Handsontable.renderers.registerRenderer('errorRenderer', errorRenderer);


var hotTable = new Handsontable(containerPagos, config);
var TableroActivo = 0;
var TMenu = 0;

hotTable.updateSettings({
    cells: function(row, col) {
        var cellProperties = {};
       // var data = this.instance.getData();
        totalPayment = hotTable.getDataAtCell(row,6) + hotTable.getDataAtCell(row,7);
        if(col >= 1 && totalPayment > hotTable.getDataAtCell(row,4) ){
          this.renderer = errorRenderer;  
        }else{
          this.renderer = undefined;
        }
        return cellProperties;
    },
    afterSelection: (FilaSelect) => {
        Fila = FilaSelect;
    },
    afterFilter:()=>{
      //getDataFiltered
      sumPayment(6,7);
    },
    afterChange: (changes) => {
        if (changes != null) {
            Fila = changes[0][0];
            Columna = changes[0][1];
            ValAnterior = changes[0][2];
            ValNuevo = changes[0][3];
            if (Columna == 6 || Columna == 7) {
                sumPayment(6,7);
                totalPayment = hotTable.getDataAtCell(Fila,6) + hotTable.getDataAtCell(Fila,7);
                var rowSaldoOriginal = hotTable.getDataAtCell(Fila,4);
                var rowSaldoActual =  rowSaldoOriginal - totalPayment;
                hotTable.setDataAtCell(Fila,5,rowSaldoActual);
                hotTable.setDataAtCell(Fila,8,totalPayment);
            } 

        }
    }
});

function moneyFormat(moneyValue){
  const $formatMoneda = new Intl.NumberFormat('es-MX', {
    style: 'currency',
    currency: 'MXN',
    minimumFractionDigits: 2
  }).format(moneyValue);

  return $formatMoneda;
}


  /*================================================================ */

  function getViajesSinLiquidar(client){
    var _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
   // console.log(_token.getAttribute('content'));
    $.ajax({
      url:'/cuentas/cobrar/por_liquidar',
      type:'post',
      data: {_token, client},
      beforeSend:function(){
        
      },
      success:function(data){
        hotTable.loadData(data.handsOnTableData);
        getCurrentBalance(4);
        var formatCurrentBalance = moneyFormat(currentBalance);
        $("#currentBalance").text(formatCurrentBalance);
        $("#finalBalance").text(formatCurrentBalance);
        sumPayment(6,7);
      },
      error:function(data){
        swal('Error 500','Ha ocurrido un error y no se pudo procesar su solicitud, por favor intentelo nuevamente','error');
      }
    })
  }

function getCurrentBalance(colBalance = 4){
  var data = hotTable.getDataAtCol(colBalance); // Obtiene los datos de la columna específica
  currentBalance = data.reduce(function (accumulator, currentValue) {
    // Verifica si el valor es un número válido antes de sumarlo
    if (typeof currentValue === 'number' && !isNaN(currentValue)) {
      return accumulator + currentValue;
    }
    return accumulator;
  }, 0); // Inicia la sumPayOne desde 0

  return true;
 }
  
 function sumPayment(colPayOne, colPayTwo) {
  var data = hotTable.getDataAtCol(colPayOne); // Obtiene los datos de la columna específica
  var sumPayOne = data.reduce(function (accumulator, currentValue) {
    // Verifica si el valor es un número válido antes de sumarlo
    if (typeof currentValue === 'number' && !isNaN(currentValue)) {
      return accumulator + currentValue;
    }
    return accumulator;
  }, 0); // Inicia la sumPayOne desde 0

  data = hotTable.getDataAtCol(colPayTwo);
  var sumPayTwo = data.reduce(function (accumulator, currentValue) {
    // Verifica si el valor es un número válido antes de sumarlo
    if (typeof currentValue === 'number' && !isNaN(currentValue)) {
      return accumulator + currentValue;
    }
    return accumulator;
  }, 0); // Inicia la sumPayOne desde 0

  var sumPay = sumPayOne + sumPayTwo;
  finalBalance = (currentBalance - sumPay);
  $("#finalBalance").text(moneyFormat(finalBalance));  
  $("#sumPago1").text(moneyFormat(sumPayOne));  
  $("#sumPago2").text(moneyFormat(sumPayTwo));  
  if(finalBalance < 0) {
    $("#finalBalance").addClass('text-danger');
    $("#borderBalance").removeClass("border-success").addClass("border-danger")
  }else{
    $("#finalBalance").removeClass('text-danger');
    $("#borderBalance").addClass("border-success").removeClass("border-danger")
  }
 
  sumPay = moneyFormat(sumPay);
  $("#payment").text( sumPay);
  
}
 /*================================================================ */

 var btnAplicarPago = document.querySelector("#btnAplicarPago");
 $(btnAplicarPago).on("click", ()=>{
  applyPayment();
 });
 
 function applyPayment(){
      var _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
      var bankOne = $("#cmbBankOne").val();
      var bankTwo = $("#cmbBankOne").val();

      if(bankOne == "null" || bankTwo == "null"){
        Swal.fire({
          customClass: {
            confirmButton: "btn btn-success",
            cancelButton: "btn btn-danger"
          },
          buttonsStyling: true,
          showConfirmButton:true,
          confirmButtonText: "Entendido!",
          title:"Seleccione bancos",
          text:"Falta seleccionar al menos uno de los bancos",
          icon:"warning"
        }); 
        return false;
      }

      if(finalBalance < 0){
        Swal.fire("No se puede aplicar el pago","Al menos uno de los pagos ingresados superan el monto de la deuda, por favor corrobore su información","warning"); 
        return false;
      }

      var button = document.querySelector("#kt_apply_payment_button");
      var datahotTable = hotTable.getData();

      $.ajax({
        url:'/credits/manage/apply-payment',
        type:'post',
        data:{_token, bankOne, bankTwo, datahotTable},
        beforeSend:function(){
          button.setAttribute("data-kt-indicator", "on");          
        },
        success:function(response){
          button.removeAttribute("data-kt-indicator");
          Swal.fire(response.Titulo,response.Mensaje,response.TMensaje);
          if(response.TMensaje == "success") getActiveCredits(client) ;
        },
        error:function(){
          Swal.fire('Error 500','Error inesperado, por favor intentelo nuevamente','error');
          button.removeAttribute("data-kt-indicator");
        }
      })
    }

    
