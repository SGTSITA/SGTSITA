/**
 * Requiere HandsOnTable version 8
 */
var currentBalance = 0;
var applyPayments = 0;
var finalBalance = 0;
var totalPayment = 0;
var sumPayOne = 0;
var sumPayTwo = 0;
var proveedor = -1;

 var data = [

];

var containerPagosPendientes = document.getElementById('pagosPendientes');
var FileName = 'tableroPendientes';

var config = {
  data: data,
  minRows: 0,
  width: '100%',
  height: 400,
  rowHeaders: true,
  minSpareRows: 0,
  autoWrapRow: true,
  colHeaders: ['# CONTENEDOR', 'ESTATUS', 'SALDO ORIGINAL','SALDO ACTUAL', 'PAGO 1','PAGO 2',"TOTAL PAGADO","ID"],
  fixedColumnsLeft: 1,
  columns:[{readOnly:true},{readOnly:true},
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
    },
    {
        readOnly:true,
    }
],
  hiddenColumns: {columns: [7], indicators: false },
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


var hotTableCXP = new Handsontable(containerPagosPendientes, config);
var TableroActivo = 0;
var TMenu = 0;

hotTableCXP.updateSettings({
    cells: function(row, col) {
        var cellProperties = {};
       // var data = this.instance.getData();
        var cellTotalPayment = hotTableCXP.getDataAtCell(row,4) + hotTableCXP.getDataAtCell(row,5);
        if(col >= 1 && cellTotalPayment > hotTableCXP.getDataAtCell(row,2) ){
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
      sumPayment(4,5);
      const filteredData = hotTableCXP.getData(); // Obtén los datos de la tabla después de filtrar
      // Aquí puedes recorrer los datos filtrados y hacer cualquier actualización necesaria
      filteredData.forEach((row, index) => {
        
      //  hotTableCXP.setDataAtRowProp(index, 6, totalPayment);
        totalPayment = hotTableCXP.getDataAtCell(index,4) + hotTableCXP.getDataAtCell(index,5);
                var rowSaldoOriginal = hotTableCXP.getDataAtCell(index,2);
                var rowSaldoActual =  rowSaldoOriginal - totalPayment;
                hotTableCXP.setDataAtCell(index,3,rowSaldoActual);
                hotTableCXP.setDataAtCell(index,6,totalPayment);
      });

    },
    afterChange: (changes) => {
        if (changes != null) {
            Fila = changes[0][0];
   
            Columna = changes[0][1];
            ValAnterior = changes[0][2];
            ValNuevo = changes[0][3];
            if (Columna == 4 || Columna == 5) {
                sumPayment(4,5);
               /* totalPayment = hotTableCXP.getDataAtCell(Fila,4) + hotTableCXP.getDataAtCell(Fila,5);
                var rowSaldoOriginal = hotTableCXP.getDataAtCell(Fila,2);
                var rowSaldoActual =  rowSaldoOriginal - totalPayment;
                hotTableCXP.setDataAtCell(Fila,5,rowSaldoActual);
                hotTableCXP.setDataAtCell(Fila,8,totalPayment);*/
                const filteredData = hotTableCXP.getData(); // Obtén los datos de la tabla después de filtrar
                // Aquí puedes recorrer los datos filtrados y hacer cualquier actualización necesaria
                filteredData.forEach((row, index) => {
                  // Ejemplo: actualizando una celda específica
                 // hotTableCXP.setDataAtRowProp(index, 8, 1);
                  totalPayment = hotTableCXP.getDataAtCell(index,4) + hotTableCXP.getDataAtCell(index,5);
                          var rowSaldoOriginal = hotTableCXP.getDataAtCell(index,2);
                          var rowSaldoActual =  rowSaldoOriginal - totalPayment;
                          hotTableCXP.setDataAtCell(index,3,rowSaldoActual);
                          hotTableCXP.setDataAtCell(index,6,totalPayment);
                });
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

  function getViajesPorPagar(provee){
    var _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    proveedor = provee;

    $.ajax({
      url:'/cuentas/pagar/por_liquidar',
      type:'post',
      data: {_token:_token, proveedor:provee},
      beforeSend:function(){
        
      },
      success:function(data){
        hotTableCXP.loadData(data.handsOnTableData);
        getCurrentBalance(2);
        var formatCurrentBalance = moneyFormat(currentBalance);
        $("#currentBalance").text(formatCurrentBalance);
        $("#finalBalance").text(formatCurrentBalance);
        $("#countViajes").text(data.handsOnTableData.length);
        sumPayment(4,5);
      },
      error:function(data){
        swal('Error 500','Ha ocurrido un error y no se pudo procesar su solicitud, por favor intentelo nuevamente','error');
      }
    })
  }

function getCurrentBalance(colBalance = 2){
  var data = hotTableCXP.getDataAtCol(colBalance); // Obtiene los datos de la columna específica
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
  var data = hotTableCXP.getDataAtCol(colPayOne); // Obtiene los datos de la columna específica
  sumPayOne = data.reduce(function (accumulator, currentValue) {
    // Verifica si el valor es un número válido antes de sumarlo
    if (typeof currentValue === 'number' && !isNaN(currentValue)) {
      return accumulator + currentValue;
    }
    return accumulator;
  }, 0); // Inicia la sumPayOne desde 0

  data = hotTableCXP.getDataAtCol(colPayTwo);
  sumPayTwo = data.reduce(function (accumulator, currentValue) {
    // Verifica si el valor es un número válido antes de sumarlo
    if (typeof currentValue === 'number' && !isNaN(currentValue)) {
      return accumulator + currentValue;
    }
    return accumulator;
  }, 0); // Inicia la sumPayOne desde 0

  var sumPay = sumPayOne + sumPayTwo;
  finalBalance = (currentBalance - sumPay);
  applyPayments = sumPay;
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

      if(finalBalance < 0){
        Swal.fire("No se puede aplicar el pago","Al menos uno de los pagos ingresados superan el monto de la deuda, por favor corrobore su información","warning"); 
        return false;
      }

      if(applyPayments == 0){
        Swal.fire("Debe ingresar pagos","Aún no ha ingresado pagos, por favor ingrese al menos uno e intentelo nuevamente","warning"); 
        return false;
      }

      var bankOne = $("#cmbBankOne").val();
      var bankTwo = $("#cmbBankTwo").val();

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

      var datahotTableCXP = hotTableCXP.getData();
      var amountPayOne = sumPayOne;
      var amountPayTwo = sumPayTwo;
      var theClient = proveedor;

      $.ajax({
        url:'/cuentas/pagar/confirmar_pagos',
        type:'post',
        data:{_token, theClient, bankOne, bankTwo, amountPayOne, amountPayTwo, applyPayments,datahotTableCXP},
        beforeSend:function(){
            
        },
        success:function(response){
          
          Swal.fire(response.Titulo,response.Mensaje,response.TMensaje);
          if(response.TMensaje == "success") btnAplicarPago.disabled = true;
        },
        error:function(){
          Swal.fire('Error 500','Error inesperado, por favor intentelo nuevamente','error');
          
        }
      })
    }

    
