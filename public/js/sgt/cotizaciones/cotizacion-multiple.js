var containerMultiple = document.getElementById('cotizacion-multiple');
var FileName = 'tableroCotizacionMultiple';

var data = [];
var dataDropDown = null;

const formFieldsContenedores = [
  {'field':'subCliente', 'index':0,'label':'Sub Cliente','required': true, "trigger":"none","type":"text"},
  {'field':'numContenedor', 'index':1,'label':'Núm. Contenedor','required': true, "trigger":"none","type":"text"},
  {'field':'origen', 'index':2,'label':'Origen','required': true, "trigger":"none","type":"text"},
  {'field':'destino', 'index':3,'label':'Destino','required': true, "trigger":"none","type":"text"},
  {'field':'tamContenedor', 'index':4,'label':'Tamaño Contenedor','required': true, "trigger":"none","type":"numeric"},
  {'field':'pesoContenedor', 'index':5,'label':'Peso Contenedor','required': true, "trigger":"none","type":"numeric"},
  {'field':'numBloque', 'index':6,'label':'Núm. Bloque','required': false, "trigger":"none","type":"text"},
  {'field':'horaInicio', 'index':7,'label':'Hora Inicio','required': false, "trigger":"none","type":"text"},
  {'field':'horaFinal', 'index':8,'label':'Hora Final','required': false, "trigger":"none","type":"text"},
];

async function getClientes(){
  let dataGetClientes =
  $.ajax({
    type: 'GET',
    url: '/subclientes/11' ,
    success: function(data) {
        let dataClientes = [];
        $.each(data, function(key, subcliente) {
          dataClientes.push(formatoConsecutivo(subcliente.id) + ' - ' + subcliente.nombre);
          
        });
       dataDropDown = dataClientes;
       return dataClientes;
    }
});
return dataGetClientes;
}


function buildHandsOntable(){
  var config = {
    data: data,
    minRows: 0,
    width: '100%',
    height: 400,
    rowHeaders: true,
    minSpareRows: 1,
    autoWrapRow: true,
    colWidths: [230, 180, 200, 200, 150, 150, 150, 150, 150,1],
    colHeaders: ['SUB CLIENTE','# CONTENEDOR','ORIGEN',  'DESTINO', 'TAMAÑO CONTENEDOR','PESO CONTENEDOR', 'NÚM BLOQUE','HORA INICIO',"HORA FIN","ID"],
    fixedColumnsLeft: 2,
    columns:[
      {type: 'dropdown',source: dataDropDown, strict: true},
      {readOnly:false },
      {readOnly:false},
      {readOnly:false},
      {readOnly:false, type: 'numeric',numericFormat: {pattern: '0,0.00',culture: 'en-US'}},
      {readOnly:false, type: 'numeric',numericFormat: {pattern: '0,0.00',culture: 'en-US'}},
      {readOnly:false},
      {readOnly:false,type: 'time',timeFormat: 'h:mm a',correctFormat: true},
      {readOnly:false,type: 'time',timeFormat: 'h:mm a',correctFormat: true},
      {readOnly:true,}
    ],
    hiddenColumns: {columns: [9], indicators: true },
    filters: false,
    //dropdownMenu: ['filter_by_value','filter_action_bar'],
    licenseKey: 'non-commercial-and-evaluation',
    copyPaste: false,
    language: 'es-MX',
    contextMenu: false,
      outsideClickDeselects :  true
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
  
  
  var hotTable = new Handsontable(containerMultiple, config);
  var TableroActivo = 0;
  var TMenu = 0;
  
  hotTable.updateSettings({
      cells: function(row, col) {
         /* var cellProperties = {};
         // var data = this.instance.getData();
          var cellTotalPayment = hotTable.getDataAtCell(row,6) + hotTable.getDataAtCell(row,7);
          if(col >= 1 && cellTotalPayment > hotTable.getDataAtCell(row,4) ){
            this.renderer = errorRenderer;  
          }else{
            this.renderer = undefined;
          }
          return cellProperties;*/
      },
      afterSelection: (FilaSelect) => {
          Fila = FilaSelect;
      },
      afterFilter:()=>{
 
      },
      afterChange: (changes) => {
          if (changes != null) {
              Fila = changes[0][0];
     
              Columna = changes[0][1];
              ValAnterior = changes[0][2];
              ValNuevo = changes[0][3];
           
  
          }
      }
  });

  function validateMultiple(){
    const dataMultiple = (hotTable.getData());

    let solicitudContenedores = [];
    var passValidation =
    dataMultiple.every((i,indiceFila) =>{

      let columnsNotNull = 0;

      i.forEach((element) => {
        if (element != null) columnsNotNull++;}
      );

      //Si todas las columnas de una fila estan vacias, saltaremos las validaciones y continuamos con la proxima fila
      if(columnsNotNull == 0)  return true;

      var validations = formFieldsContenedores.every((item,index) => {
        let field = i;
        let campo = field[index];
        if(item.required === true && campo == null){
            Swal.fire(`Falta ${item.label} en la fila ${(indiceFila + 1)}`,"Parece que aún no ha proporcionado información en el campo "+item.label,"warning");
            return false;
        }else{

          return true;
        }
      })

      solicitudContenedores.push(i);
      return  validations ;

    });

    if(!passValidation) return passValidation;

   // console.table(solicitudContenedores);

    createContenedoresMultiple(solicitudContenedores);
  }

  function createContenedoresMultiple(contenedores){
    var _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    var uuid = localStorage.getItem('uuid');
    $.ajax({
      url: '/viajes/solicitud/multiple',
      type:'post',
      data:{_token, contenedores, uuid},
      beforeSend:()=>{},
      success:(response)=>{
        Swal.fire(response.Titulo,response.Mensaje,response.TMensaje).then(function() {
          if(response.TMensaje == "success"){
              var uuid = localStorage.getItem('uuid');
              if(uuid){
                  window.location.replace("/viajes/documents");
              }else{
                  location.reload();
              }
          
          }
      });
      },
      error:(x,error)=>{
        console.warn(x.responseText)
      }
    });
  }

  return {
    validarSolicitud: validateMultiple // Exponiendo la función interna
};
  
}

