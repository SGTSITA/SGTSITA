const formFieldsClientes = [
    {'field':'nombre', 'id':'nombre','label':'Nombre ó Razón Social','required': true, "type":"text"},
    {'field':'rfc', 'id':'rfc','label':'RFC','required': false, "type":"text"},
    {'field':'regimen_fiscal', 'id':'regimen_fiscal','label':'Régimen Fiscal','required': false, "type":"text"},
    {'field':'nombre_empresa', 'id':'nombre_empresa','label':'Nombre Comercial','required': true, "type":"text"},
    {'field':'correo', 'id':'correo','label':'Correo Electrónico','required': true, "type":"email"},
    {'field':'telefono', 'id':'telefono','label':'Teléfono','required': true, "type":"text"},
    {'field':'direccion', 'id':'direccion','label':'Dirección','required': false, "type":"text"},
];

$("#sublienteCreate").on("submit", function(e){
    e.preventDefault();

    var passValidation = formFieldsClientes.every((item) => {
        var field = document.getElementById(item.field);
        if(field){
            if(item.required === true && field.value.length == 0){
                Swal.fire("El campo "+item.label+" es obligatorio","Parece que no ha proporcionado información en el campo "+item.label,"warning");
                return false;
            }

            if(item.required === true && item.type === "email"){
                var isValid = validarEmail(field.value);
                if(!isValid){
                    Swal.fire("El campo "+item.label+" esperaba una direccion de correo electrónico","Parece el dato proporcionado en el campo "+item.label+" no es un correo electrónico valido","warning");
                    return false;
                }
            }
        }
        return true;
    });

    if(!passValidation) return passValidation;

    const formData = {};

    formFieldsClientes.forEach((item) =>{
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

    formData["_token"] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    formData["id_cliente"] = $("#id_cliente").val();

    var subCliente = document.querySelector("#id_subcliente")
    if(subCliente){
        formData["id_subcliente"] = subCliente.value;
    }
    

    var uuid = localStorage.getItem('uuid');
   if(uuid != null){
    formData["uuid"] = uuid
    }

    var form = $(this);
    var url = form.attr('action');

    $.ajax({
        url: url,
        type: "post",
        data: formData,
        beforeSend:function(){
        
        },
        success:function(data){
                Swal.fire(data.Titulo,data.Mensaje,data.TMensaje).then(function() {
                    if(data.TMensaje == "success"){
                        var uuid = localStorage.getItem('uuid');
                        if(uuid){
                            window.location.replace("/clientes/list");
                        }else{
                            window.location.replace("/clients");
                        }
                    
                    }
                });
        },
        error:function(){       
        Swal.fire("Error","Ha ocurrido un error, intentelo nuevamente","error");
        }
    })

});

