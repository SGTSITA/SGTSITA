const btnActivar = document.querySelectorAll(".btn-config-gps")
const modal = new bootstrap.Modal(document.getElementById('modal-gps-form'));
const titelModal = document.querySelector('#gpsCompany')
const userName = document.querySelector('#txtUserName')
const accessKey = document.querySelector('#txtPassword')

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
            
            titelModal.dataset.gpsCompany = response.data.id
            titelModal.textContent = response.data.nombre

            if(response.data.servicios_gps.length != 0 ){
                let account = JSON.parse(response.data.servicios_gps[0].account_info)    
                userName.value = account.appId
                accessKey.value = account.accessKey
            }else{
                userName.value = ''
                accessKey.value = ''
            }
            

            modal.show();
        },
        error:()=>{

        }
    })
}

function guardarConfigGps(){
    let gps = titelModal.dataset.gpsCompany
    
    let _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    $.ajax({
        url:'/gps/config/store',
        type:'post',
        data:{_token, gps, userName: userName.value, accessKey: accessKey.value},
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