const gridOptionsOperador = {
   pagination: true,
      paginationPageSize: 100,
      paginationPageSizeSelector: [100, 200, 500],
      rowSelection: {
        mode: "multiRow",
        headerCheckbox: true,
      },
      rowClassRules: {
        'bg-gradient-warning': params => params.data.Estatus === "Pago Pendiente",
      },
      rowData: [
        
      ],

      columnDefs: [
        { field: "IdCotizacion", hide: true},
        { field: "IdGasto", hide: true},
        { field: "Gasto",width: 210 },
        { field: "Monto",width: 110,valueFormatter: params => currencyFormatter(params.value), cellStyle: { textAlign: "right" }  },
        { field: "Estatus",width: 150 },
        { field: "Fecha",filter: true, floatingFilter: true},
        { field: "FechaPago",filter: true, floatingFilter: true},
        { field: "BancoPago",filter: true, floatingFilter: true},    
              
      ],
      
      localeText: localeText
  };
  
  const gridElementGastosOperador = document.querySelector('#gridGastosOperador');
  let apiGridGastosOperador = agGrid.createGrid(gridElementGastosOperador, gridOptionsOperador);
 // const gridInstance = new agGrid.Grid(gridElementGastosOperador, gridOptions);
  
  var paginationTitle = document.querySelector("#ag-32-label");
  paginationTitle.textContent = 'Registros por página';
  
  let IdContenedorViaje = null;
   
   function getGastosOperador(){
    var _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    let spanContenedor = document.querySelector("#spanContenedor");
    let numContenedor = spanContenedor.textContent;
    $.ajax({
        url: '/cotizaciones/gastos-operador/get',
        type: 'post',
        data: {_token, numContenedor},
        beforeSend:()=>{},
        success:(response)=>{
            apiGridGastosOperador.setGridOption("rowData", response)

            let totalGastos = 0;
            response.forEach((d)=>{
              totalGastos += parseFloat(d.Monto)
            });

            let totalGastosOperador = document.querySelector('#totalGastosOperador')
            totalGastosOperador.textContent = moneyFormat(totalGastos)

        },
        error:()=>{

        }
    });
   }

   function putGastosOperador(){
    let spanContenedor = document.querySelector("#spanContenedor");
    let numContenedor = spanContenedor.textContent;

    let textDescripcion = document.querySelector("#txtDescripcionGastoOperador")
    let textMonto = document.querySelector("#txtMontoGastoOperador")
    let checkPagoInmediato = document.querySelector("#checkPagoInmediato")

    let bancosGastos = document.querySelector('#bancosGastos')

    let pagoInmediato = checkPagoInmediato.checked;


    if(textMonto.value.length == 0 || textDescripcion.value.length == 0){
        Swal.fire('Complete información','La información del descuento está incompleta, por favor llene todos los campos','warning')
        return false;
    }

    let montoGasto = reverseMoneyFormat(textMonto.value)
    let descripcion = textDescripcion.value
    let _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    let bancoPago = bancosGastos.value

    $.ajax({
        url:'/cotizaciones/gastos-operador/registrar',
        type:'post',
        data:{numContenedor,descripcion,montoGasto,pagoInmediato,bancoPago,_token},
        beforeSend:()=>{},
        success:(response)=>{
            Swal.fire(response.Titulo,response.Mensaje,response.TMensaje)
            $('#modal-form').modal('hide')
            textDescripcion.value = '';
            textMonto.value = '';
        },
        error:(err)=>{
            Swal.fire('Ocurrio un error','Error','error')
        }
    })
   }



   