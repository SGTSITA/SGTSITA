window.markers = [];
var map = L.map('map').setView([0, 0], 2);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
}).addTo(map);


let marker = null;

  
      



let contenedoresDisponibles = [];


function cargarinicial()
{
       fetch(`/coordenadas/contenedor/searchEquGps?`)
      .then(response => response.json())
    .then(data => {
        
         contenedoresDisponibles   = data.datos;
        
          
      })
  .catch(error => {
          console.error('Error al traer coordenadas:', error);
    });
}

let intervalId = null;

document.getElementById('formFiltros').addEventListener('submit', function(event) {
  event.preventDefault();

    document.getElementById('ItemsSelects').value = ItemsSelects.join(';');
if (!ItemsSelects || ItemsSelects.length === 0) {
    alert('Por favor, seleccione al menos un contenedor.');
    return;
  }


  actualizarUbicacion(ItemsSelects);
  document.getElementById('btnDetener').style.display = 'inline-block';
  document.getElementById('btnDetener2').style.display = 'inline-block';
  if (intervalId) clearInterval(intervalId);
  
  intervalId = setInterval(() => {
    actualizarUbicacion(ItemsSelects);
  }, 5000);
});
  limpiarMarcadores();

function actualizarUbicacion(imeis) {
    let responseOk = false;
  fetch("/coordenadas/ubicacion-vehiculo", {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    },
    body: JSON.stringify({ imeis: imeis })
  })
  .then(res => res.json())
  .then(data => {
    //console.log('Ubicaciones recibidas:', data);
    const dataUbi= data;

    
responseOk = true;
    if (Array.isArray(dataUbi)) {
      dataUbi.forEach((item, index) => {
        let latlocal = item.ubicacion.data.lat;
        let lnglocal = item.ubicacion.data.lng;
        if (latlocal && lnglocal) {
          const newMarker = L.marker([latlocal, lnglocal]).addTo(map).bindPopup(item.contenedor).openPopup();
        if (index === 0) map.setView([latlocal, lnglocal], 15);
        
        }
      });
    } else {
      console.warn('La respuesta no es un array de ubicaciones:', data);
    }
    if (responseOk){
         const modalElement = document.getElementById('filtroModal'); 
      const filtroModal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
      filtroModal.hide();
    }
  
  })
  .catch(error => {
    console.error('Error al obtener ubicaciones:', error);
  });
}

// Para detener la actualización con un botón
document.getElementById('btnDetener').addEventListener('click', function() {
  detener();
});
document.getElementById('btnDetener2').addEventListener('click', function() {
  detener();
});

function detener(){
    if(intervalId) {
    clearInterval(intervalId);
    intervalId = null;
    document.getElementById('btnDetener').style.display = 'none';
    document.getElementById('btnDetener2').style.display = 'none';
  }
}



// Función para limpiar marcadores
function limpiarMarcadores() {
    if (window.markers && window.markers.length > 0) {
        window.markers.forEach(marker => {
            map.removeLayer(marker);
        });
        window.markers = [];
    }
}


document.addEventListener('DOMContentLoaded', function () {
     cargarinicial();
    const btnEditarFiltros = document.getElementById('btnEditarFiltros');
    const btnCerrarModal = document.getElementById('btnCerrarModal');

 var filtroModal = new bootstrap.Modal(document.getElementById('filtroModal'));
    if (filtroModal && btnEditarFiltros && btnCerrarModal) {
       
        btnEditarFiltros.addEventListener('click', function () {
           
            filtroModal.show();
        });

        setTimeout(function () {
            if (btnCerrarModal) {
                // Cerrar modal
                btnCerrarModal.addEventListener('click', function () {
                    filtroModal.hide();
                });
            } else {
                console.error("Botón de cierre no encontrado.");
            }
        }, 100);

        filtroModal.show();

        } else {
            console.error("No se encontraron los elementos necesarios.");
        }
});

function limpiarFiltros() {
  
    const modal = document.getElementById('filtroModal'); 
    const inputs = modal.querySelectorAll('input, select, textarea');

    inputs.forEach(element => {
        if (element.tagName === 'SELECT') {
            element.selectedIndex = 0; 
        } else {
            element.value = ''; 
        }
    });

     seleccionados.length = 0; 
  ItemsSelects.length = 0;
 const div = document.getElementById('contenedores-seleccionados');
        div.innerHTML = '';

  document.getElementById('ItemsSelects').value = '';

}

const seleccionados = [];
const ItemsSelects = [];

    function mostrarSugerencias() {
        const input = document.getElementById('contenedor-input');
        const filtro = input.value.trim().toUpperCase();
        const sugerenciasDiv = document.getElementById('sugerencias');
        sugerenciasDiv.innerHTML = '';

        if (filtro.length === 0) {
            sugerenciasDiv.style.display = 'none';
            return;
        }

        const filtrados = contenedoresDisponibles.filter(c =>
            
            (c.contenedor || '').toUpperCase().includes(filtro) &&
    !seleccionados.includes(c.contenedor)


        );

        filtrados.forEach(c => {
            const item = document.createElement('div');
            item.textContent = c.contenedor;
            item.style.padding = '5px';
            item.style.cursor = 'pointer';
            item.onclick = () => seleccionarContenedor(c.contenedor);
            sugerenciasDiv.appendChild(item);
        });

        sugerenciasDiv.style.display = filtrados.length ? 'block' : 'none';
    }

    function seleccionarContenedor(valor) {
        seleccionados.push(valor);
         const contenedorData = contenedoresDisponibles.find(c => c.contenedor === valor);

         ItemsSelects.push(valor +"-" +contenedorData.imei);
        document.getElementById('contenedor-input').value = '';
        document.getElementById('sugerencias').style.display = 'none';
        actualizarVista();
    }

    function agregarContenedor() {
        const input = document.getElementById('contenedor-input');
        const valor = input.value.trim().toUpperCase();
        if (valor && contenedoresDisponibles.includes(valor) && !seleccionados.includes(valor)) {
            seleccionados.push(valor);
           
            input.value = '';
            actualizarVista();
        }
    }

    function eliminarContenedor(idx) {
        seleccionados.splice(idx, 1);
          ItemsSelects.splice(idx, 1);
        actualizarVista();
    }

    function actualizarVista() {
        const div = document.getElementById('contenedores-seleccionados');
        div.innerHTML = '';

        seleccionados.forEach((cont, i) => {
            div.innerHTML += `
                 <span class="badge bg-secondary me-1">
            ${cont}
            <button type="button" 
                onclick="eliminarContenedor(${i})" 
                style="background:none; border:none; color:red; margin-left:5px; font-weight:bold;" 
                title="Eliminar">
                &times;
            </button>
        </span>
                
            `;
        });

        document.getElementById('contenedores').value = seleccionados.join(';');
        document.getElementById('ItemsSelects').value = ItemsSelects.join(';');
    }


  

// document.getElementById('ubicacion-toggle').addEventListener('change', function() {
//     const ubicacionTexto = document.getElementById('ubicacion-texto');
    
//     if (this.checked) {
//         ubicacionTexto.textContent = 'Última ubicación';
//     } else {
//         ubicacionTexto.textContent = 'Todas las ubicaciones';
//     }
// });


