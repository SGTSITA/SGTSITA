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


document.getElementById('btnCopiarMapa').addEventListener('click', function() {
  const link = document.getElementById('linkMapa').href;
  
  if (link === '#' || !link.trim()) {
    alert('No hay enlace disponible para copiar.');
    return;
  }

  navigator.clipboard.writeText(link)
    .then(() => {
      this.innerHTML = '<i class="fas fa-check"></i> Copiado';
      this.classList.remove('btn-outline-primary');
      this.classList.add('btn-success');
      setTimeout(() => {
        this.innerHTML = '<i class="fas fa-copy"></i> Copiar';
        this.classList.remove('btn-success');
        this.classList.add('btn-outline-primary');
      }, 2000);
    })
    .catch(() => alert('Error al copiar el enlace.'));
});



