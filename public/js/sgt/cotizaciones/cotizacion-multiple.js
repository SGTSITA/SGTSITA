var containerMultiple = document.getElementById('cotizacion-multiple');
var FileName = 'tableroCotizacionMultiple';

var data = [];
var proveedoresFormateados = [];
var transportistasFormateados = [];

// NO BORRAR
async function getClientes(clienteId){ 
  let dataGetClientes = $.ajax({ 
    type: 'GET', url: '/subclientes/'+clienteId , 
    success: function(data) { 
      let dataClientes = []; 
      $.each(data, function(key, subcliente) {
         dataClientes.push(formatoConsecutivo(subcliente.id) + ' - ' + subcliente.nombre); 
      }); 
      dataDropDown = dataClientes; 
      return dataClientes;
    } 
  });


proveedoresLista.forEach(p => {
    proveedoresFormateados.push(formatoConsecutivo(p.id) + ' - ' + p.nombre);
});


transportistasLista.forEach(t => {
    transportistasFormateados.push(formatoConsecutivo(t.id) + ' - ' + t.nombre);
});




  
  return dataGetClientes; 
}





// CAMPOS OBLIGATORIOS
const formFieldsContenedores = [
    {'field':'id_subcliente', 'index':0,'label':'Sub Cliente','required': true},
    {'field':'numContenedor', 'index':3,'label':'Núm. Contenedor','required': true},
    {'field':'origen', 'index':4,'label':'Origen','required': true},
    {'field':'destino', 'index':5,'label':'Destino','required': true},
    {'field':'tamContenedor', 'index':6,'label':'Tamaño Contenedor','required': true},
    {'field':'pesoContenedor', 'index':7,'label':'Peso Contenedor','required': canElegirProveedor ? true : false},
];

function buildHandsOntable(){

    //-------------------------------------------------------
    // 1) LAS COLUMNAS SON FIJAS, SIEMPRE MISMO ORDEN
    //-------------------------------------------------------
    let columnas = [
        { data: 0, type:'dropdown', source:dataDropDown, strict:true, width: 150  },   // 0 SUBCLIENTE
        { data: 1, type:'dropdown', source:proveedoresFormateados, strict:true, width: 150  }, // 1 PROVEEDOR
        { data: 2, type:'dropdown', source:transportistasFormateados, strict:true, width: 150  }, // 2 TRANSPORTISTA
        { data: 3 , width: 150 },               // 3 CONTENEDOR
        { data: 4 , width: 150 },               // 4 ORIGEN
        { data: 5 , width: 150 },               // 5 DESTINO
        { data: 6, type:'numeric' , width: 150 }, // 6 TAMAÑO
        { data: 7, type:'numeric' , width: 150 }, // 7 PESO

        { data: 8 , width: 100   },  // 8 peso reglamentario
        { data: 9, width: 100  },  // 9 sobrepeso
        { data:10 , width: 100 },  // 10 precio sobrepeso
        { data:11 , width: 100 },  // 11 precio tonelada

        { data:12, type:'date', dateFormat:'YYYY-MM-DD', correctFormat:true, width: 155  },
        { data:13, type:'date', dateFormat:'YYYY-MM-DD', correctFormat:true, width: 140  },

        { data:14 , width: 130  },  // 14 num bloque

        // 15 HORA INICIO
        {
            data:15,
            type:'time',
            timeFormat:'HH:mm:ss',
            correctFormat:true, width: 100 
        },

        // 16 HORA FIN
        {
            data:16,
            type:'time',
            timeFormat:'HH:mm:ss',
            correctFormat:true, width: 100 
        },

        { data:17, width: 200  },  // 17 direccion
        { data:18, readOnly:true } // 18 ID
    ];


    let headers = [
        "SUBCLIENTE",
        "PROVEEDOR",
        "TRANSPORTISTA",
        "# CONTENEDOR",
        "ORIGEN",
        "DESTINO",
        "TAMAÑO",
        "PESO",
        "PESO REGLAMENTARIO",
        "SOBREPESO",
        "PRECIO_SOBREPESO",
        "PRECIO_TONELADA",
        "FECHA MODULACIÓN",
        "FECHA ENTREGA",
        "NÚM BLOQUE",
        "HORA INICIO",
        "HORA FIN",
        "DIRECCIÓN",
        "ID"
    ];


    let columnasOcultas = [];
let fixetcolimns = 4;
    if (!canElegirProveedor) {
        columnasOcultas.push(1); 
        columnasOcultas.push(2); 
        fixetcolimns = 2;
    }


    columnasOcultas.push(8,9,10,11,18);


    var config = {
        data: data,
        colHeaders: headers,
        columns: columnas,
        rowHeaders: true,

        fixedColumnsLeft: fixetcolimns, 

        height: 450,
        minSpareRows: 1,
        licenseKey: 'non-commercial-and-evaluation',

        hiddenColumns: {
            columns: columnasOcultas,
            indicators: false
        }
    };

    var hotTable = new Handsontable(containerMultiple, config);

  
    function validateMultiple(){
        let rows = hotTable.getData();
 let filasValidas = [];


        for(let i=0; i<rows.length; i++){
            let r = rows[i];


            
          let filaVacia = r.every(v => v === null || v === "");

          
          if (filaVacia) continue;

            if(r.every(v => v===null || v==="")) continue;

            for(let campo of formFieldsContenedores){
                let val = r[campo.index];
                if(campo.required && (!val || val==="")){
                    Swal.fire("Campo faltante",
                        `Falta ${campo.label} en la fila ${i+1}`,
                        "warning");
                    return false;
                }
            }
             filasValidas.push(r);
        }


      if (filasValidas.length === 0) {
        Swal.fire("Sin datos", "No hay información para guardar.", "info");
        return false;
     }

        createContenedoresMultiple(filasValidas);
        return true;
    }


  function createContenedoresMultiple(contenedores){
      var _token = document.querySelector('meta[name="csrf-token"]').content;
      var uuid = localStorage.getItem('uuid');
      var permiso_proveedor = canElegirProveedor ? 1 : 0;

      $.post('/viajes/solicitud/multiple',
          {_token, contenedores, uuid, permiso_proveedor},
          function(response){

              Swal.fire(response.Titulo, response.Mensaje, response.TMensaje)
              .then(() => {
                  // SOLO recargar si el servidor indica éxito
                  if (response.TMensaje === "success" || response.TMensaje === "ok") {
                      location.reload();
                  }
                  // Si es warning o error, NO recargar
              });

          }
      );
  }

    return {
        validarSolicitud: validateMultiple
    };
}
