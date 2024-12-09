var containerMultiple = document.getElementById('cotizacion-multiple');
var FileName = 'tableroCotizacionMultiple';
var data = [];
var config = {
  data: data,
  minRows: 0,
  width: '100%',
  height: 400,
  rowHeaders: true,
  minSpareRows: 1,
  autoWrapRow: true,
  colHeaders: ['# CONTENEDOR','CLIENTE','ORIGEN',  'DESTINO', 'TAMAÑO CONTENEDOR','PESO CONTENEDOR', 'NÚM BLOQUE','HORA INICIO',"HORA FIN","ID"],
  fixedColumnsLeft: 1,
  columns:[{readOnly:false},{readOnly:false },{readOnly:false},{readOnly:false},
    {readOnly:false},
    {readOnly:false},
    {readOnly:false},
    {readOnly:false},
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
  }],
  hiddenColumns: {columns: [9], indicators: true },
  filters: false,
  //dropdownMenu: ['filter_by_value','filter_action_bar'],
  licenseKey: 'non-commercial-and-evaluation',
  copyPaste: false,
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


var hotTable = new Handsontable(containerMultiple, config);
var TableroActivo = 0;
var TMenu = 0;

hotTable.updateSettings({
    cells: function(row, col) {
        var cellProperties = {};
       // var data = this.instance.getData();
        var cellTotalPayment = hotTable.getDataAtCell(row,6) + hotTable.getDataAtCell(row,7);
        if(col >= 1 && cellTotalPayment > hotTable.getDataAtCell(row,4) ){
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
      const filteredData = hotTable.getData(); // Obtén los datos de la tabla después de filtrar
      // Aquí puedes recorrer los datos filtrados y hacer cualquier actualización necesaria
      filteredData.forEach((row, index) => {
        // Ejemplo: actualizando una celda específica
        hotTable.setDataAtRowProp(index, 8, 1);
        totalPayment = hotTable.getDataAtCell(index,6) + hotTable.getDataAtCell(index,7);
                var rowSaldoOriginal = hotTable.getDataAtCell(index,4);
                var rowSaldoActual =  rowSaldoOriginal - totalPayment;
                hotTable.setDataAtCell(index,5,rowSaldoActual);
                hotTable.setDataAtCell(index,8,totalPayment);
      });

    },
    afterChange: (changes) => {
        if (changes != null) {
            Fila = changes[0][0];
   
            Columna = changes[0][1];
            ValAnterior = changes[0][2];
            ValNuevo = changes[0][3];
            if (Columna == 6 || Columna == 7) {
                sumPayment(6,7);
               /* totalPayment = hotTable.getDataAtCell(Fila,6) + hotTable.getDataAtCell(Fila,7);
                var rowSaldoOriginal = hotTable.getDataAtCell(Fila,4);
                var rowSaldoActual =  rowSaldoOriginal - totalPayment;
                hotTable.setDataAtCell(Fila,5,rowSaldoActual);
                hotTable.setDataAtCell(Fila,8,totalPayment);*/
                const filteredData = hotTable.getData(); // Obtén los datos de la tabla después de filtrar
                // Aquí puedes recorrer los datos filtrados y hacer cualquier actualización necesaria
                filteredData.forEach((row, index) => {
                  // Ejemplo: actualizando una celda específica
                  hotTable.setDataAtRowProp(index, 8, 1);
                  totalPayment = hotTable.getDataAtCell(index,6) + hotTable.getDataAtCell(index,7);
                          var rowSaldoOriginal = hotTable.getDataAtCell(index,4);
                          var rowSaldoActual =  rowSaldoOriginal - totalPayment;
                          hotTable.setDataAtCell(index,5,rowSaldoActual);
                          hotTable.setDataAtCell(index,8,totalPayment);
                });
            } 

        }
    }
});