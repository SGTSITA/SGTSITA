const tasa_iva = 0.16;
const tasa_retencion = 0.04;
const catalogo_clientes = document.querySelector("#txtClientes");

const formFields = [
    {'field':'origen', 'id':'origen','label':'Origen','required': true, "type":"text"},
    {'field':'destino', 'id':'destino','label':'Destino','required': true, "type":"text"},
    {'field':'num_contenedor', 'id':'num_contenedor','label':'Núm. Contenedor','required': true, "type":"text"},
    {'field':'tamano', 'id':'tamano','label':'Tamaño Contenedor','required': true, "type":"numeric"},
    {'field':'peso_reglamentario', 'id':'peso_reglamentario','label':'Peso Reglamentario','required': true, "type":"numeric"},
    {'field':'peso_contenedor', 'id':'peso_contenedor','label':'Peso Contenedor','required': true, "type":"numeric"},
    {'field':'precio_viaje', 'id':'precio_viaje','label':'Precio Viaje','required': true, "type":"money"},
    {'field':'base_factura', 'id':'base_factura','label':'Base 1','required': true, "type":"money"},
    {'field':'fecha_modulacion', 'id':'fecha_modulacion','label':'Fecha Modulación','required': false, "type":"text"},
    {'field':'fecha_entrega', 'id':'fecha_entrega','label':'Fecha Entrega','required': false, "type":"text"},
    {'field':'sobrepeso', 'id':'sobrepeso','label':'Sobrepeso','required': false, "type":"numeric"},
    {'field':'precio_sobre_peso', 'id':'precio_sobre_peso','label':'Precio Sobre Peso','required': false, "type":"money"},
    {'field':'precio_tonelada', 'id':'precio_tonelada','label':'Precio Tonelada','required': false, "type":"money"},
    {'field':'burreo', 'id':'burreo','label':'Burreo','required': false, "type":"money"},
    {'field':'maniobra', 'id':'maniobra','label':'Maniobra','required': false, "type":"money"},
    {'field':'estadia', 'id':'estadia','label':'Estadía','required': false, "type":"money"},
    {'field':'otro', 'id':'otro','label':'Otros','required': false, "type":"money"},
    {'field':'iva', 'id':'iva','label':'IVA','required': false, "type":"money"},
    {'field':'retencion', 'id':'retencion','label':'Retención','required': false, "type":"money"},
    {'field':'base_taref', 'id':'base_taref','label':'Base 2','required': false, "type":"money"},
    {'field':'total', 'id':'total','label':'Total','required': false, "type":"money"},   
];

const formFieldsBloque = [
    {'field':'bloque','label':'Block','required': false, "type":"text"},
    {'field':'bloque_hora_i','label':'Hora Inicio','required': false, "type":"text"},
    {'field':'bloque_hora_f','label':'Hora Fin','required': false, "type":"text"},
]

const formFieldsProveedor = [
    {'field':'precio_viaje','id':'precio_proveedor','label':'Costo Viaje','required': false, "type":"money"},
    {'field':'burreo','id':'burreo_proveedor','label':'Burreo Proveedor','required': false, "type":"money"},
    {'field':'maniobra','id':'maniobra_proveedor','label':'Maniobra Proveedor','required': false, "type":"money"},
    {'field':'estadia','id':'estadia_proveedor','label':'Estadía','required': false, "type":"money"},
    {'field':'sobrepeso','id':'cantidad_sobrepeso_proveedor','label':'Sobre Peso','required': false, "type":"text"},
    {'field':'precio_sobre_peso','id':'sobrepeso_proveedor','label':'Precio Sobre Peso','required': false, "type":"money"},
    {'field':'precio_tonelada','id':'total_tonelada','label':'Total Tonelada','required': false, "type":"money"},
    {'field':'base_factura','id':'base1_proveedor','label':'Base 1','required': false, "type":"money"},
    {'field':'base_taref','id':'base2_proveedor','label':'Base 2','required': false, "type":"money"},
    {'field':'otro','id':'otro_proveedor','label':'Otros','required': false, "type":"money"},
    {'field':'iva','id':'iva_proveedor','label':'IVA','required': false, "type":"money"},
    {'field':'retencion','id':'retencion_proveedor','label':'Retención','required': false, "type":"money"},
    {'field':'total','id':'total_proveedor','label':'Total','required': false, "type":"money"},
]

$(".moneyformat").on("focus",(e)=>{
var val = e.target.value;
e.target.value = reverseMoneyFormat(val);
})

$(".moneyformat").on("blur",(e) =>{
var val = e.target.value;
e.target.value =  moneyFormat(val);
})

function calcularTotal(modulo = 'crear') {
    const fields = (modulo == 'crear') ? formFields : formFieldsProveedor;

    const field_precio_viaje = fields.find( i => i.field == "precio_viaje");
    const field_burreo = fields.find( i => i.field == "burreo");
    const field_otro = fields.find( i => i.field == "otro");
    const field_estadia = fields.find( i => i.field == "estadia");
    const field_maniobra = fields.find( i => i.field == "maniobra");

    const precio_viaje = parseFloat(reverseMoneyFormat(document.getElementById(field_precio_viaje.id).value)) || 0;
    const burreo = parseFloat(reverseMoneyFormat(document.getElementById(field_burreo.id).value)) || 0;
    const otro = parseFloat(reverseMoneyFormat(document.getElementById(field_otro.id).value)) || 0;
    const estadia = parseFloat(reverseMoneyFormat(document.getElementById(field_estadia.id).value)) || 0;
    const maniobra = parseFloat(reverseMoneyFormat(document.getElementById(field_maniobra.id).value)) || 0;

    const subTotal = precio_viaje + burreo + maniobra + estadia + otro;

    const field_base_factura = fields.find( i => i.field == "base_factura");

    const baseFactura = parseFloat(reverseMoneyFormat(document.getElementById(field_base_factura.id).value)) || 0;
    const iva = (baseFactura * tasa_iva);
    const retencion = (baseFactura * tasa_retencion);

    const field_iva = fields.find( i => i.field == "iva");
    const field_retencion = fields.find( i => i.field == "retencion");

    document.getElementById(field_iva.id).value = moneyFormat(iva);
    document.getElementById(field_retencion.id).value = moneyFormat(retencion);

    // Restar el valor de Retención del total
    const totalSinRetencion = precio_viaje + burreo + iva + otro + estadia + maniobra;
    const totalConRetencion = totalSinRetencion - retencion;

    // Obtener el valor de Precio Tonelada
    const field_precio_tonelada = fields.find( i => i.field == "precio_tonelada");
    const precioTonelada = parseFloat(reverseMoneyFormat(document.getElementById(field_precio_tonelada.id).value)) || 0;

    // Sumar el valor de Precio Tonelada al total
    const totalFinal = totalConRetencion + precioTonelada;
    
    //baseTaref Corresponde a Base 2
    const baseTaref = (totalFinal - baseFactura - iva) + retencion;

    // Mostrar el resultado en el input de base_taref
    const field_base_taref = fields.find( i => i.field == "base_taref");
    document.getElementById(field_base_taref.id).value = moneyFormat(baseTaref);

    
    // Formatear el total con comas
    const totalFormateado = moneyFormat(totalFinal);
    const field_total = fields.find( i => i.field == "total");
    document.getElementById(field_total.id).value = totalFormateado;

}

document.addEventListener('DOMContentLoaded', function () {
    // Obtener elementos del DOM
    var pesoReglamentarioInput = document.getElementById('peso_reglamentario');
    var pesoContenedorInput = document.getElementById('peso_contenedor');
    var sobrepesoInput = document.getElementById('sobrepeso');

    var precioSobrePesoInput = document.getElementById('precio_sobre_peso');
    var precioToneladaInput = document.getElementById('precio_tonelada');

    var precioSobrePesoProveedor = document.getElementById('sobrepeso_proveedor')
    var sobrePesoProveedor = document.getElementById('cantidad_sobrepeso_proveedor');
    var precioToneladaProveedor = document.getElementById('total_tonelada');

    // Agregar evento de cambio a los inputs
    pesoReglamentarioInput.addEventListener('input', calcularSobrepeso);
    pesoContenedorInput.addEventListener('input', calcularSobrepeso);
    pesoContenedorInput.addEventListener('input', calcularSobrepeso);
    pesoContenedorInput.addEventListener('input', valorSobrePrecio);


    function valorSobrePrecio(){
        // Obtener el valor de Sobrepeso
        var sobrepeso = parseFloat(sobrepesoInput.value.replace(/,/g, '')) || 0;
   
        // Obtener el valor de Precio Sobre Peso
        var precioSobrePeso = parseFloat(reverseMoneyFormat(precioSobrePesoInput.value)) || 0;
   
        // Calcular el resultado de la multiplicación
        var resultado = sobrepeso * precioSobrePeso;
   
        // Mostrar el resultado en el campo "Precio Tonelada"
        precioToneladaInput.value = moneyFormat(resultado); 
   
        // Calcular el total
        calcularTotal();
   }

   function valorSobrePrecioProveedor(){
    // Obtener el valor de Sobrepeso
    var sobrepeso = parseFloat(sobrePesoProveedor.value.replace(/,/g, '')) || 0;

    // Obtener el valor de Precio Sobre Peso
    var precioSobrePeso = parseFloat(reverseMoneyFormat(precioSobrePesoProveedor.value)) || 0;

    // Calcular el resultado de la multiplicación
    var resultado = sobrepeso * precioSobrePeso;

    // Mostrar el resultado en el campo "Precio Tonelada"
    precioToneladaProveedor.value = moneyFormat(resultado); 

    // Calcular el total
    calcularTotal('proveedores');
}
    // Función para calcular el sobrepeso
    function calcularSobrepeso() {
        var pesoReglamentario = parseFloat(pesoReglamentarioInput.value) || 0;
        var pesoContenedor = parseFloat(pesoContenedorInput.value) || 0;

        // Calcular sobrepeso
        var sobrepeso = Math.max(pesoContenedor - pesoReglamentario, 0);

        // Mostrar sobrepeso en el input correspondiente con dos decimales
        sobrepesoInput.value = sobrepeso.toFixed(4);
        var sobrePesoProveedor = document.getElementById('cantidad_sobrepeso_proveedor');
        if(sobrePesoProveedor){
            sobrePesoProveedor.value = sobrepeso.toFixed(4);
            
        }
        // Calcular el total
        calcularTotal();
    }

    // Agregar evento de entrada al campo "Precio Sobre Peso"
    precioSobrePesoInput.addEventListener('input', ()=> {
        valorSobrePrecio();
    });

    if(precioSobrePesoProveedor){
        precioSobrePesoProveedor.addEventListener('input', ()=> {
            valorSobrePrecioProveedor();
        });
    }
    

    // Calcular sobrepeso inicialmente al cargar la página
    calcularSobrepeso();

    // Agregar eventos de cambio a los inputs para calcular automáticamente
    document.getElementById('base_factura').addEventListener('input', ()=>{calcularTotal()});
    var inputMoneyFormat = $('.calculo-cotizacion');
    inputMoneyFormat.on('input',()=>{calcularTotal()})
    var inputMoneyFormatProveedores = $('.calculo-proveedor');
    inputMoneyFormatProveedores.on('input',()=>{calcularTotal('proveedores')})
    

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


$("#cotizacionesUpdate").on("submit",(e)=>{
    e.preventDefault();
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

   //Eliminamos el formato moneda de todos los campos antes de enviar al backend
   formFields.forEach((item) =>{
    if(item.type == "money") {
        var field = document.getElementById(item.field);
        field.value = reverseMoneyFormat(field.value);
    }
   });

   formFieldsProveedor.forEach((item) =>{
    if(item.type == "money") {
        var field = document.getElementById(item.id);
        if(field){
          field.value = reverseMoneyFormat(field.value);
        }
    }
   });

   e.target.submit();
})