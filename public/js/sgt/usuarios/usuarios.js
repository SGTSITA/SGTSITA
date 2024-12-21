const formFieldsUsuario = [
    {'field':'name', 'id':'name','label':'Nombre','required': true, "type":"text"},
    {'field':'email', 'id':'email','label':'Correo Electrónico','required': true, "type":"email"},
    {'field':'password', 'id':'password','label':'Contraseña','required': true, "type":"text"},
    {'field':'confirm-password', 'id':'confirm-password','label':'Confirmar Contraseña','required': true, "type":"text"},
];


$("#usuarioCreate").on("submit", function(e){
    e.preventDefault();

    var passValidation = formFieldsUsuario.every((item) => {
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

    formFieldsUsuario.forEach((item) =>{
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
    formData["roles"] = [1]
    formData["id_empresa"] = 1

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
                            location.reload();
                        }
                    
                    }
                });
        },
        error:function(){       
        Swal.fire("Error","Ha ocurrido un error, intentelo nuevamente","error");
        }
    })

});

