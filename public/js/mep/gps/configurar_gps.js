const btnActivar = document.querySelectorAll(".btn-config-gps")
const modal = new bootstrap.Modal(document.getElementById('modal-gps-form'));
const titelModal = document.querySelector('#gpsCompany')
let formFields = [];

btnActivar.forEach((btn) =>{
    
    btn.addEventListener('click',()=>{
        configuracionGps(btn.dataset.gps)    
    })
})

function configuracionGps(gps){
    $.ajax({
        url:'/gps/config',
        type:'get',
        data:{gps},
        beforeSend:()=>{

        },
        success:(response)=>{
            
            titelModal.dataset.gpsCompany = gps
            titelModal.textContent = response.data[0].nombre

            formFields = JSON.parse( response.data[0].account_fields)
            const contenedor = document.getElementById('form-account');
            contenedor.innerHTML = '';

            formFields.forEach((f)=>{
               
                const input = document.createElement('input');

                const label = document.createElement('label');
                label.htmlFor = f.field; 
                label.textContent = f.label; 

                input.type = f.type;
                input.name = f.field;
                input.id = f.field;
                input.classList.add('form-control', 'mb-3');
                input.placeholder = `Escribe ${f.field}...`;

                
                contenedor.appendChild(label);
                contenedor.appendChild(input);
            })

            let accoutGps = (response.account)
            if(accoutGps.length != 0 ){
                accoutGps.forEach((a) =>{
                    let input = document.getElementById(a.field)
                    if(input){
                        input.value = a.valor
                    }
                })
            }
            

            modal.show();
        },
        error:()=>{

        }
    })
}

function guardarConfigGps(){
    let gps = titelModal.dataset.gpsCompany

    let account = [];
    let isValidForm = formFields.every((field)=>{
        let input = document.getElementById(field.field);
        if(input.value.length == 0){
            Swal.fire(`Falta campo ${field.label}`,`Proporcione la información del campo ${field.label}`,'warning')
            return false;
        }

        let dato = {field: field.field, valor: input.value}

        account = [...account,dato]

        return true;
   })

   if(!isValidForm) return isValidForm;

    
    let _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    $.ajax({
        url:'/gps/config/store',
        type:'post',
        data:{_token, gps, account},
        beforeSend:()=>{

        },
        success:(response)=>{
            Swal.fire(response.Titulo, response.Mensaje, response.TMensaje)
            modal.hide();
        },
        error:()=>{

        }
    })
}