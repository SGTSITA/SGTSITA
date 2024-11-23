const tasa_iva = 0.16;
const tasa_retencion = 0.04;
const catalogo_clientes = document.querySelector("#txtClientes");

$(".moneyformat").on("focus",(e)=>{
var val = e.target.value;
e.target.value = reverseMoneyFormat(val);
})

$(".moneyformat").on("blur",(e) =>{
var val = e.target.value;
e.target.value =  moneyFormat(val);
})

function calcularTotal() {
    const precio_viaje = parseFloat(reverseMoneyFormat(document.getElementById('precio_viaje').value)) || 0;
    const burreo = parseFloat(reverseMoneyFormat(document.getElementById('burreo').value)) || 0;
    const otro = parseFloat(reverseMoneyFormat(document.getElementById('otro').value)) || 0;
    const estadia = parseFloat(reverseMoneyFormat(document.getElementById('estadia').value)) || 0;
    const maniobra = parseFloat(reverseMoneyFormat(document.getElementById('maniobra').value)) || 0;

    const subTotal = precio_viaje + burreo + maniobra + estadia + otro;
    
    //calcularImpuestos(subTotal);
    const baseFactura = parseFloat(reverseMoneyFormat(document.getElementById('base_factura').value)) || 0;
    const iva = (baseFactura * tasa_iva);
    const retencion = (baseFactura * tasa_retencion);

    document.getElementById('iva').value = moneyFormat(iva);
    document.getElementById('retencion').value = moneyFormat(retencion);

    const baseTaref = (subTotal - baseFactura - iva) + retencion;

    // Mostrar el resultado en el input de base_taref
    document.getElementById('base_taref').value = moneyFormat(baseTaref);

    // Restar el valor de Retención del total
    const totalSinRetencion = precio_viaje + burreo + iva + otro + estadia + maniobra;
    const totalConRetencion = totalSinRetencion - retencion;

    // Obtener el valor de Precio Tonelada
    const precioTonelada = parseFloat(reverseMoneyFormat(document.getElementById('precio_tonelada').value)) || 0;

    // Sumar el valor de Precio Tonelada al total
    const totalFinal = totalConRetencion + precioTonelada;

    // Formatear el total con comas
    const totalFormateado = moneyFormat(totalFinal);

    document.getElementById('total').value = totalFormateado;

}

document.addEventListener('DOMContentLoaded', function () {
    // Obtener elementos del DOM
    var pesoReglamentarioInput = document.getElementById('peso_reglamentario');
    var pesoContenedorInput = document.getElementById('peso_contenedor');
    var sobrepesoInput = document.getElementById('sobrepeso');

    var precioSobrePesoInput = document.getElementById('precio_sobre_peso');
    var precioToneladaInput = document.getElementById('precio_tonelada');

    // Agregar evento de cambio a los inputs
    pesoReglamentarioInput.addEventListener('input', calcularSobrepeso);
    pesoContenedorInput.addEventListener('input', calcularSobrepeso);
    pesoContenedorInput.addEventListener('input', calcularSobrepeso);
    pesoContenedorInput.addEventListener('input', valorSobrePrecio);


    function valorSobrePrecio(){
        // Obtener el valor de Sobrepeso
        var sobrepeso = parseFloat(sobrepesoInput.value.replace(/,/g, '')) || 0;
   
        // Obtener el valor de Precio Sobre Peso
        var precioSobrePeso = parseFloat(precioSobrePesoInput.value.replace(/,/g, '')) || 0;
   
        // Calcular el resultado de la multiplicación
        var resultado = sobrepeso * precioSobrePeso;
   
        // Mostrar el resultado en el campo "Precio Tonelada"
        precioToneladaInput.value = moneyFormat(resultado); 
   
        // Calcular el total
        calcularTotal();
   }
    // Función para calcular el sobrepeso
    function calcularSobrepeso() {
        var pesoReglamentario = parseFloat(pesoReglamentarioInput.value) || 0;
        var pesoContenedor = parseFloat(pesoContenedorInput.value) || 0;

        // Calcular sobrepeso
        var sobrepeso = Math.max(pesoContenedor - pesoReglamentario, 0);

        // Mostrar sobrepeso en el input correspondiente con dos decimales
        sobrepesoInput.value = sobrepeso.toFixed(2);
        // Calcular el total
        calcularTotal();
    }

    // Agregar evento de entrada al campo "Precio Sobre Peso"
    precioSobrePesoInput.addEventListener('input', function () {
        valorSobrePrecio();
    });

    // Calcular sobrepeso inicialmente al cargar la página
    calcularSobrepeso();

    // Agregar eventos de cambio a los inputs para calcular automáticamente
    document.getElementById('base_factura').addEventListener('input', calcularTotal);
    var inputMoneyFormat = $('.moneyformat');
    inputMoneyFormat.on('input',calcularTotal)

});


$('#id_cliente').change(function() {
    var clienteId = $(this).val();
    if (clienteId) {
        var dataClientes = JSON.parse(catalogo_clientes.value);
        dataClientes.forEach((i)=>{
            if(i.id == clienteId){
                $("#telClient").text(i.telefono)
                $("#mailClient").text(i.correo.toLowerCase())

            }
        })
        $.ajax({
            type: 'GET',
            url: '/subclientes/' + clienteId,
            success: function(data) {
                $('#id_subcliente').empty();
                $('#id_subcliente').append('<option value="">Seleccionar subcliente</option>');
                $.each(data, function(key, subcliente) {
                    $('#id_subcliente').append('<option value="' + subcliente.id + '">' + subcliente.nombre + '</option>');
                });
                $('#id_subcliente').select2();
            }
        });
    } else {
        $('#id_subcliente').empty();
        $('#id_subcliente').append('<option value="">Seleccionar subcliente</option>');
    }
});

$("#cotizacionCreate").on("submit", function(e){
    e.preventDefault();
    var form = $(this);
    var url = form.attr('action');

    //Validaciones a campos obligatorios
    if($("#id_cliente").val() == ""){
        Swal.fire("Seleccione Cliente","Aún no ha seleccionado Cliente, este es un campo requerido","warning");
        return false;
    }

    const selectSubClient = document.getElementById("id_subcliente");
    const subClientQty = selectSubClient.options.length;

    if(subClientQty > 1 && $("#id_subcliente").val() == ""){
        Swal.fire("Seleccione SubCliente","Aún no ha seleccionado SubCliente, este es un campo requerido","warning");
        return false;
    }

    const formFields = [
        {'field':'origen','label':'Origen','required': true, "type":"text"},
        {'field':'destino','label':'Destino','required': true, "type":"text"},
        {'field':'num_contenedor','label':'Núm. Contenedor','required': true, "type":"text"},
        {'field':'tamano','label':'Tamaño Contenedor','required': true, "type":"numeric"},
        {'field':'peso_reglamentario','label':'Peso Reglamentario','required': true, "type":"numeric"},
        {'field':'peso_contenedor','label':'Peso Contenedor','required': true, "type":"numeric"},
        {'field':'precio_viaje','label':'Precio Viaje','required': true, "type":"money"},
        {'field':'base_factura','label':'Base 1','required': true, "type":"money"},
        {'field':'fecha_modulacion','label':'Fecha Modulación','required': true, "type":"text"},
        {'field':'fecha_entrega','label':'Fecha Entrega','required': true, "type":"text"},
        {'field':'sobrepeso','label':'Sobrepeso','required': false, "type":"numeric"},
        {'field':'precio_sobre_peso','label':'Precio Sobre Peso','required': false, "type":"money"},
        {'field':'precio_tonelada','label':'Precio Tonelada','required': false, "type":"money"},
        {'field':'burreo','label':'Burreo','required': false, "type":"money"},
        {'field':'maniobra','label':'Maniobra','required': false, "type":"money"},
        {'field':'estadia','label':'Estadía','required': false, "type":"money"},
        {'field':'otro','label':'Otros','required': false, "type":"money"},
        {'field':'iva','label':'IVA','required': false, "type":"money"},
        {'field':'retencion','label':'Retención','required': false, "type":"money"},
        {'field':'base_taref','label':'Base 2','required': false, "type":"money"},
        {'field':'total','label':'Total','required': false, "type":"money"},
       
    ];

    var passValidation = formFields.every((item) => {
        var field = document.getElementById(item.field);
        
        if(item.required === true && field.value.length == 0){
            Swal.fire("El campo "+item.label+" es obligatorio","Parece que no ha proporcionado información en el campo "+item.label,"warning");
            return false;
        }

        return true;
    })

   if(!passValidation) return passValidation;

   //Validaciones con condicionantes
   let sobrePeso = document.getElementById('sobrepeso').value;
   let precioSobrePeso = document.getElementById('precio_sobre_peso').value;
   if(sobrePeso > 0 && precioSobrePeso.length <= 0){
        Swal.fire("El campo Precio Sobre Peso es obligatorio","Parece que no ha proporcionado información en el campo Precio Sobre Peso","warning");
        return false;
   }

   const formData = {};

   formFields.forEach((item) =>{
    var input = item.field;
    var inputValue = document.getElementById(input);
    if(item.type == "money"){
        formData[input] = (inputValue.value.length > 0) ? parseFloat(reverseMoneyFormat(inputValue.value)) : 0;
    }else{
        formData[input] = inputValue.value;
    }
   });

   formData["_token"] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
   formData["id_cliente"] = $("#id_cliente").val();
   formData["id_subcliente"] = selectSubClient.value;

   $.ajax({
        url: url,
        type: "post",
        data: formData,
        beforeSend:function(){
        
        },
        success:function(data){
                Swal.fire(data.Titulo,data.Mensaje,data.TMensaje).then(function() {
                    if(data.TMensaje == "success"){
                    location.reload();
                    }
                });
        },
        error:function(){       
        Swal.fire("Error","Ha ocurrido un error, intentelo nuevamente","error");
        }
    })

  
});


