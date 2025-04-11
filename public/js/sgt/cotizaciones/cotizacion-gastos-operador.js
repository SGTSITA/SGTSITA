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
      onRowSelected:(event)=>{
        btnPaymentStatus()
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
  const btnElminar = document.querySelector('#btnDelete2')
  let apiGridGastosOperador = (gridElementGastosOperador) ? agGrid.createGrid(gridElementGastosOperador, gridOptionsOperador) : null;
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
          if(gridElementGastosOperador){
            apiGridGastosOperador.setGridOption("rowData", response)

            let totalGastos = 0;
            response.forEach((d)=>{
              totalGastos += parseFloat(d.Monto)
            });

            let totalGastosOperador = document.querySelector('#totalGastosOperador')
            totalGastosOperador.textContent = moneyFormat(totalGastos)
          }
            

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
            
            if(response.TMensaje == "success"){
              getGastosOperador();
              $('#modal-gastos-operador').modal('hide')
              textDescripcion.value = '';
              textMonto.value = '';
            }
        },
        error:(err)=>{
            Swal.fire('Ocurrio un error','Error','error')
        }
    })
   }

  let btnPayment = document.querySelector('#btnPayment')
  if(btnPayment){
    btnPayment.addEventListener('click',()=>{
      paymentGastosOperador()
    })
  }

  function btnPaymentStatus(){
    if(gridElementGastosOperador){
      let seleccion = apiGridGastosOperador.getSelectedRows();
      btnPayment.disabled = (seleccion.length == 0) ? true : false;
      btnElminar.disabled = (seleccion.length == 0) ? true : false;
    }

  }

   function paymentGastosOperador(){
    if(gridElementGastosOperador){
      let seleccionPago = apiGridGastosOperador.getSelectedRows();
      let totalPago = 0
    }
    

    let validarGastos = seleccionPago.every((gasto)=>{
      if (gasto.Estatus != 'Pago Pendiente') return false;
      totalPago += parseFloat(gasto.Monto)
      return true;
    })

    if(!validarGastos) {
      Swal.fire('No es posible pagar','Solo se admiten Gastos con estatus "Pago Pendiente"','warning')
      return false;
    }
    
    let totalPagoGastosOperador = document.querySelector('#totalPagoGastosOperador')

    if(totalPagoGastosOperador) totalPagoGastosOperador.textContent = moneyFormat(totalPago)

    const modalElement = document.getElementById('modal-pagar-gastos-operador');
    const bootstrapModal = new bootstrap.Modal(modalElement);
    bootstrapModal.show();

    
   }

   function applyPaymentGastosOperador(){
    
    let totalPagoGastosOperador = document.querySelector('#totalPagoGastosOperador')

    let totalPago = reverseMoneyFormat(totalPagoGastosOperador.textContent)

    let bancosPagoGastos = document.querySelector('#bancosPagoGastos')
    let bank = bancosPagoGastos.value;

    let spanContenedor = document.querySelector("#spanContenedor");
    let numContenedor = spanContenedor.textContent;

    let gastosPagar = apiGridGastosOperador.getSelectedRows();
    let _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');


    $.ajax({
      url:'/cotizaciones/gastos-operador/pagar',
      type:'post',
      data:{totalPago, numContenedor, bank, gastosPagar,_token},
      beforeSend:()=>{

      },
      success:(response)=>{
        Swal.fire(response.Titulo,response.Mensaje,response.TMensaje)
            
        if(response.TMensaje == "success"){
          getGastosOperador();
          $('#modal-pagar-gastos-operador').modal('hide')
        }
      },
      error:()=>{
        Swal.fire("Error inesperado","Ocurrio un error mientras procesamos su solicitud","error")

      }
    })
   }

   function eliminarGastoOperador(){
    Swal.fire({
      title: "¿Desea eliminar el gasto seleccionado?",
      text: 'Estas a punto de eliminar un gasto, si se encuentra seguro haga click en "Si, Eliminar"',
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Si, Eliminar!",
      cancelButtonText: 'Cancelar'
    }).then((result) => {
      if (result.isConfirmed) {
       //
       let seleccionEliminarPago = apiGridGastosOperador.getSelectedRows();
       let _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

       let spanContenedor = document.querySelector("#spanContenedor");
       let numContenedor = spanContenedor.textContent;

       $.ajax({
        url:'/cotizaciones/gastos-operador/eliminar',
        type:'post',
      data:{seleccionEliminarPago,numContenedor,_token},
      beforeSend:()=>{

      },
      success:(response)=>{
        Swal.fire(response.Titulo,response.Mensaje,response.TMensaje)
            
        if(response.TMensaje == "success"){
          getGastosOperador();
         
        }
      },
      error:()=>{
        Swal.fire("Error inesperado","Ocurrio un error mientras procesamos su solicitud","error")

      }
       })
      }
    });
   }
   
   btnElminar.addEventListener('click',()=>{
    eliminarGastoOperador();
   })


   