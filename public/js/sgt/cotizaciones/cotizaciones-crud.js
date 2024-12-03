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
        getClientes(clienteId);
    } else {
        $('#id_subcliente').empty();
        $('#id_subcliente').append('<option value="">Seleccionar subcliente</option>');
    }
   
});

function getClientes(clienteId){
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
}

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
        if(field){
            if(item.required === true && field.value.length == 0){
                Swal.fire("El campo "+item.label+" es obligatorio","Parece que no ha proporcionado información en el campo "+item.label,"warning");
                return false;
            }
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