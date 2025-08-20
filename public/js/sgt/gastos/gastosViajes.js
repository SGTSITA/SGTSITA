var bancosList = null;
var data = [];

async function getBancos(){
  let dataGetBancos =
  $.ajax({
    type: 'GET',
    url: '/bancos/list' ,
    success: function(data) {
        let dataBancos = [];
        $.each(data, function(key, banco) {
          dataBancos.push(formatoConsecutivo(banco.id) + ' - ' + banco.nombre_beneficiario + ' - '+banco.nombre_banco);
        });
       bancosList = dataBancos;
       return dataBancos;
    }
});
return dataGetBancos;
}

function buildGastosHandsOnTable(){


var containerPagosPendientes = document.getElementById('pagosPendientes');

var config = {
  data: data,
  width: '100%',
  height: 400,
  rowHeaders: true,
  minRows: 0,
  width: '100%',
  height: 400,
  rowHeaders: true,
  minSpareRows: 0,
  autoWrapRow: true,
 // colHeaders: ['CONTENEDOR', 'COMISION', 'DIESEL','CASETAS', 'G. DIFERIDOS','VARIOS',"TOTAL GASTOS","ID"],
 nestedHeaders: [
  ['',{ label: 'Gastos por viaje', colspan: 3 },{ label: 'Pago Inmediato', colspan: 4 }],
  ['Contenedor', 'Comision', 'Diesel','Casetas','Pago Comision','Pago Diesel','Pago Casetas','Banco']
],
  fixedColumnsLeft: 1,
  columns:[
    {readOnly:true, width:350},
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
      type: 'numeric',
      numericFormat: {
        pattern: '$ 0,0.00',
        culture: 'en-US'
      }
    },
    {className: "htCenter",type: 'checkbox', checkedTemplate: '1', uncheckedTemplate: '0'},
    {className: "htCenter",type: 'checkbox', checkedTemplate: '1', uncheckedTemplate: '0'},
    {className: "htCenter",type: 'checkbox', checkedTemplate: '1', uncheckedTemplate: '0'},
    {type: 'dropdown',source: bancosList, strict: true, width:375},
    {
      readOnly:true,
    }
],
  hiddenColumns: {columns: [8], indicators: false },
  filters: true,
  dropdownMenu: ['filter_by_value','filter_action_bar'],
  licenseKey: 'non-commercial-and-evaluation',
  copyPaste: true,
  language: 'es-MX'
}

var handsOnTableGastos = new Handsontable(containerPagosPendientes, config);


handsOnTableGastos.updateSettings({
    cells: function(row, col) {
        var cellProperties = {};
     
        return cellProperties;
    },
    afterSelection: (FilaSelect) => {
        Fila = FilaSelect;
    },
    afterFilter:()=>{
     

    },
    afterChange: (changes) => {

    }
});


  /*================================================================ */

  function getViajes(from, to){
    var _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    $.ajax({
      url:'/gastos/viajes/list',
      type:'post',
      data: {_token:_token, from, to},
      beforeSend:function(){
        
      },
      success:function(data){
        
        handsOnTableGastos.loadData(data.handsOnTableData);

      },
      error:function(data){
        swal('Error 500','Ha ocurrido un error y no se pudo procesar su solicitud, por favor intentelo nuevamente','error');
      }
    })
  }

  function guardarGastos(){
    var _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    var datahandsOnTableGastos = JSON.stringify(handsOnTableGastos.getData());
    $.ajax({
      url:'/gastos/viajes/confirmar-gastos',
      type:'post',
      data:{_token, datahandsOnTableGastos},
      beforeSend:function(){
          
      },
      success:function(response){
        
        Swal.fire(response.Titulo,response.Mensaje,response.TMensaje);
       // if(response.TMensaje == "success") btnAplicarPago.disabled = true;
      },
      error:function(){
        Swal.fire('Error 500','Error inesperado, por favor intentelo nuevamente','error');
        
      }
    })
  }

  return {
    storeDataHTGastos: guardarGastos,
    fillDataHTGastos : getViajes
  };

}