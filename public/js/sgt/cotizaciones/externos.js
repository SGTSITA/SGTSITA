let selectProveedor = document.querySelector('#id_proveedor')
let selectTransport = document.querySelector('#id_transportista')

selectProveedor.addEventListener('change',()=>{
 getTranspotistas()
})

function getTranspotistas(){
    let _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    let proveedor = selectProveedor.value;
 $.ajax({
    type: 'post',
    url: '/mec/transportistas/list',
    data:{proveedor, _token},
    beforeSend:()=>{

    },
    success:(response)=>{
        
        let opciones = response;
        selectTransport.innerHTML = "";

        opciones.forEach(opcion => {
            selectTransport.add(new Option(opcion.nombre, opcion.id));
        });
    },
    error:()=>{

    }
 })
}