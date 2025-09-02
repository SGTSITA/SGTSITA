const gastosFormFields = [
    {'field':'motivo', 'id':'motivo','label':'Descripción','required': true, "type":"text"},
    {'field':'monto1', 'id':'monto1','label':'Monto','required': true, "type":"money"},
    {'field':'categoria_movimiento', 'id':'categoria_movimiento','label':'Categoría','required': true, "type":"text"},
    {'field':'fecha_movimiento', 'id':'fecha_movimiento','label':'Fecha movimiento','required': true, "type":"text"},
    {'field':'fecha_aplicacion', 'id':'fecha_aplicacion','label':'Fecha aplicación','required': false, "type":"text"},
    {'field':'id_banco1', 'id':'id_banco1','label':'Fecha aplicación','required': true, "type":"text"},
    {'field':'tipoPago', 'id':'tipoPago','label':'Tipo de Pago','required': true, "type":"select"},
];

const formFieldsDiferir = [
  {'field':'txtDiferirFechaInicia','id':'txtDiferirFechaInicia','label':'Fecha inicio Pago Diferido','required': false, "type":"text", "trigger":"tipoPago"},
  {'field':'txtDiferirFechaTermina','id':'txtDiferirFechaTermina','label':'Fecha Finalización Pago Diferido','required': false, "type":"text", "trigger":"tipoPago"},
  
]

function handleSelection(input) {
    
  document.querySelectorAll('.custom-option').forEach(opt =>{
      opt.classList.remove('selected') 
  });
  
  input.parentElement.classList.add('selected');

  document.querySelectorAll(".aplicacion-gastos").forEach( opt => {
      opt.classList.add('d-none')
  })

  if(input.parentElement.innerText == "Viaje") document.querySelector("#aplicacion-viaje").classList.remove('d-none');
  if(input.parentElement.innerText == "Equipo") document.querySelector("#aplicacion-equipo").classList.remove('d-none');
  
  
}

document.getElementById("tipoPago").addEventListener("change", function () {
  const seccion = document.getElementById("seccionDiferido");
  if (this.value === "1") {
    const bsCollapse = new bootstrap.Collapse(seccion, { show: true });
  } else {
    const bsCollapse = bootstrap.Collapse.getInstance(seccion);
    if (bsCollapse) bsCollapse.hide();
  }
});


  const currencyFormatter = (value) => {
    return new Intl.NumberFormat("es-MX", { style: "currency", currency: "MXN" }).format(value);
  };

  const formatFecha = params => {
    if (!params) return "";
    const [year, month, day] = params.split("-"); // Divide YYYY-MM-DD
    return `${day}/${month}/${year}`; // Retorna en formato d/m/Y
  };

  

  
  
  let monto1 = document.querySelector('#monto1')
  let labelMontoGasto = document.querySelector('#labelMontoGasto')
  let labelGastoDiario = document.querySelector('#labelGastoDiario')
  let labelDiasPeriodo = document.querySelector('#labelDiasPeriodo')
    let btnConfirmacion = document.querySelector('#btnConfirmacion')

  let fromDate = null;
  let toDate = null;

  monto1.addEventListener('input', async (e) => labelMontoGasto.textContent = await moneyFormat(e.target.value), calcDays())

  
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

  

  /*btnDiferir.addEventListener('click',()=>{
    let gasto = apiGrid.getSelectedRows();

    if(gasto.length <= 0 || gasto.length > 1){
      Swal.fire('Seleccionar UN Gasto','Debe seleccionar el gasto que desea diferir','warning');
      return;
    } 

    if(gasto[0].GastoAplicado == true){
      Swal.fire('Gasto Previamente aplicado','Lo sentimos, el gastos que está intentado APLICAR, ha sido utilizado previamente','warning');
      return;
    }

    labelMontoGasto.textContent = moneyFormat(gasto[0].Monto)
    labelDescripcionGasto.textContent = gasto[0].Descripcion
    IdGasto = gasto[0].IdGasto
    calcDays()
    const modalElement = document.getElementById('modalDiferir');
    const bootstrapModal = new bootstrap.Modal(modalElement);
    bootstrapModal.show();

  })*/
   


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

   
   passValidation = formFieldsDiferir.every((item) => {
      let trigger = item.trigger;
      let field = document.getElementById(item.field);

      if(trigger != "none"){
          let primaryField = document.getElementById(trigger);
          if(primaryField.value == 1 && field.value.length == 0){
              Swal.fire("El campo "+item.label+" es obligatorio","Parece que no ha proporcionado información en el campo "+item.label,"warning");
              return false;
          }
      }
      
      if(field){
          if(item.required === true && field.value.length == 0){
              Swal.fire("El campo "+item.label+" es obligatorio","Parece que no ha proporcionado información en el campo "+item.label,"warning");
              return false;
          }
      }
      
      formData[item.field] = field.value;
      return true;

  });

if(!passValidation) return passValidation;

   formData["_token"] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
   formData["gastoDiario"] = labelGastoDiario.textContent
   formData["numPeriodos"] = labelDiasPeriodo.textContent

   const select = document.getElementById("selectUnidades");
   const unidades = Array.from(select.selectedOptions).map(option => option.value);
   formData["unidades"] = unidades

   const selectViaje = document.getElementById("selectViajes");
   const viajes = Array.from(selectViaje.selectedOptions).map(option => option.value);
   formData["viajes"] = viajes

   let input = document.querySelector('input[name="formasAplicar"]:checked');
   formData["formasAplicar"] = input.value

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

                      gastosFormFields.forEach((item) =>{
                          var input = item.field;
                          var inputValue = document.getElementById(input);
                          if(inputValue && item.type != 'select'){
                            inputValue.value = "";
                          }
                       });

                       formFieldsDiferir.forEach((item) =>{
                          var input = item.field;
                          var inputValue = document.getElementById(input);
                          if(inputValue){
                            inputValue.value = "";
                          }
                       });
                    
                    }
                });
        },
        error:function(){       
        Swal.fire("Error","Ha ocurrido un error, intentelo nuevamente","error");
        }
    })
  })

  function diferirGasto(){
    /*let fechaDesde = document.getElementById('txtDiferirFechaInicia')
    let fechaHasta = document.getElementById('txtDiferirFechaTermina')

    let gastoDiario = labelGastoDiario.textContent
    let diasContados = labelDiasPeriodo.textContent*/

    let _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    /*if(fechaDesde.value.length == 0 || fechaHasta.value.length == 0){
      Swal.fire('Definir periodo','Por favor seleccione la fecha inicial y final del periodo','warning');
      return false;
    }*/

    const select = document.getElementById("selectUnidades");
    const unidades = Array.from(select.selectedOptions).map(option => option.value);

    const selectViaje = document.getElementById("selectViajes");
    const viajes = Array.from(selectViaje.selectedOptions).map(option => option.value);


    let input = document.querySelector('input[name="formasAplicar"]:checked');
    let formasAplicar = input.value

    let _IdGasto = IdGasto

    $.ajax({
      url: '/gastos/diferir',
      type:'post',
      data:{_token,_IdGasto,unidades,viajes, formasAplicar},
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

  /*
  btnConfirmacion.addEventListener('click',()=>{
    diferirGasto()
  })*/

   $(".moneyformat").on("focus",(e)=>{
    var val = e.target.value;
    e.target.value = reverseMoneyFormat(val);
    })
    
    $(".moneyformat").on("blur",(e) =>{
    var val = e.target.value;
    e.target.value =  moneyFormat(val);
    })