window.markers = [];
var map = L.map('map').setView([0, 0], 2);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
}).addTo(map);


let marker = null;

  
      


let detalleConvoys;
let contenedoresDisponibles = [];
function cargarConvoysEnSelect(convoys) {
  const select = document.getElementById('convoys');

  // Limpiar opciones actuales excepto la primera
  select.innerHTML = '<option value="">Seleccione un convoy</option>';

  convoys.forEach(convoy => {
    const option = document.createElement('option');
    option.value = convoy.id; 
    option.textContent = `${convoy.no_conboy} - ${convoy.nombre}`;
    select.appendChild(option);
  });
}
function cargarEquiposEnSelect(dataequipos) {
  const select = document.getElementById('Equipo');

  // Limpiar opciones actuales excepto la primera
  select.innerHTML = '<option value="">Seleccione un equipo</option>';

  dataequipos.forEach(equipo => {
    const option = document.createElement('option');
    option.value = `${equipo.id_equipo}|${equipo.imei}|${equipo.id}`;
    const textoPlaca = equipo.placas?.trim() ? equipo.placas : 'SIN PLACA';
    option.textContent = `${equipo.id_equipo } - ${equipo.marca}- ${equipo.tipo}- ${textoPlaca}`;
    select.appendChild(option);
  });
}
function cargarinicial()
{
       fetch(`/coordenadas/contenedor/searchEquGps?`)
      .then(response => response.json())
    .then(data => {
        
         contenedoresDisponibles   = data.datos;
      cargarConvoysEnSelect( data.conboys);
      cargarEquiposEnSelect( data.equipos);
    detalleConvoys =   data.dataConten;
          
      })
  .catch(error => {
          console.error('Error al traer coordenadas:', error);
    });
}


function obtenerImeisPorConvoyId(convoyId) {
    return detalleConvoys
    .filter(item => item.conboy_id == convoyId && item.imei && item.id_contenedor)
    .map(item => item.num_contenedor + "|" + item.imei + "|" + item.id_contenedor);
}

function obtenerTabActivo() {
    const tabActivo = document.querySelector('#filtroTabs .nav-link.active');
    return tabActivo ? tabActivo.getAttribute('data-bs-target') : null;
}

let intervalId = null;
let idConvoyOContenedor=0;
document.getElementById('filtroModal').addEventListener('submit', function(event) {
  event.preventDefault();
let tipoBusqueda ='IMEIS';

 // const convoy = document.getElementById("convoys").value;
  //const contenedores = document.getElementById("contenedores").value;
  //const equipoC = equipoCdocument.getElementById("Equipo").value;

   const tab = obtenerTabActivo();

    switch (tab) {
        case '#filtro-convoy':
            const convoy = document.getElementById('convoys').value;
            if (!convoy) {
                alert('Seleccione un convoy');
                return false;
            }
            ItemsSelects = obtenerImeisPorConvoyId(convoy);
            break;

        case '#filtro-contenedor':
            const contenedores = document.getElementById('contenedores').value;
            if (!contenedores) {
                alert('Agregue al menos un contenedor');
                return false;
            }
            break;

        case '#filtro-Equipo':
            const equipo = document.getElementById('Equipo').value;
            ItemsSelects.push(equipo);
            if (!equipo) {
                alert('Seleccione un equipo');
                return false;
            }
            break;

        default:
            alert('No se detectó un filtro válido');
            return false;
    }








  // Set hidden input por si necesitas backend
  document.getElementById('ItemsSelects').value = ItemsSelects.join(';');

  


  actualizarUbicacion(ItemsSelects,tab);
  document.getElementById('btnDetener').style.display = 'inline-block';
  document.getElementById('btnDetener2').style.display = 'inline-block';
  if (intervalId) clearInterval(intervalId);
  
  intervalId = setInterval(() => {
    actualizarUbicacion(ItemsSelects,tab);
  }, 5000);
});
  
function actualizarUbicacionReal(coordenadaData){
    fetch('/coordenadas/rastrear/savehistori', {
        method: 'POST',
         headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    },
    body: JSON.stringify(coordenadaData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Coordenada guardada:', data.data);
        } else {
            console.warn('Error al guardar coordenada', data);
        }
    })
    .catch(error => {
        console.error('Error en la solicitud:', error);
    });

}
function actualizarUbicacion(imeis,t) {
  const selectElement = document.getElementById('tipo');

let tipo = selectElement.value;

    let responseOk = false;
  fetch("/coordenadas/ubicacion-vehiculo", {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    },
    body: JSON.stringify({ imeis: imeis , tipo: tipo})
  })
  .then(res => res.json())
  .then(data => {
    //console.log('Ubicaciones recibidas:', data);
    const dataUbi= data;

limpiarMarcadores();
responseOk = true;
    if (Array.isArray(dataUbi)) {
      dataUbi.forEach((item, index) => {
         let latlocal ='';
        let lnglocal='';
        let nEconomico='';
        let id_contenConvoy ='';
        if(tipo ==='skyGps'){
        let arrayData = item.ubicacion.data;
        if (Array.isArray(arrayData) && arrayData.length === 0) {
            console.warn('La respuesta fue exitosa pero `data` está vacío.');
            return
          }
          arrayData.forEach((item2, index) => {
              nEconomico =' No Economico:' +item2.economico +' imei:' + item2.imei ;
              latlocal= item2.tracks[0].position.latitude;
              lnglocal= item2.tracks[0].position.longitude;
                 id_contenConvoy=  item2.id_contenendor;
              if (latlocal && lnglocal) {

                 const newMarker = L.marker([latlocal, lnglocal]).addTo(map).bindPopup(nEconomico).openPopup();
                window.markers.push(newMarker);
                     newMarker.on('click', () => {
            const contenedor = item.contenedor;
             const info = contenedoresDisponibles.find(d => d.contenedor === contenedor);
                if (!info) {
                  if(t='#filtro-Equipo'){
                      if(t='#filtro-Equipo'){
                    const ahora = new Date();
                       info = contenedoresDisponibles.find(d => {
                            const inicio = new Date(d.fecha_inicio);
                            const fin = new Date(d.fecha_fin);

                            return ahora >= inicio && ahora <= fin;
                        });
                         console.log(info);
                         if (!info) {
                          alert("Información del viaje no encontrada.");
                          return;
                         }
                  }
                }else {
                    alert("Información del viaje no disponible.");
                  return;
                  }

                  
                }

                const contenido = `
                  <div class="p-3">
                    <h5 class="mb-2">🚚 Información del Viaje</h5>
                    <p><strong>Cliente:</strong> ${info.cliente}</p>
                    <p><strong>Contenedor:</strong> ${info.contenedor}</p>
                    <p><strong>Origen:</strong> ${info.origen}</p>
                    <p><strong>Destino:</strong> ${info.destino}</p>
                    <p><strong>Contrato:</strong> ${info.tipo_contrato}</p>
                    <p><strong>IMEI:</strong> ${info.imei}</p>
                    <p><strong>Fecha Inicio:</strong> ${info.fecha_inicio}</p>
                  </div>
                `;

              document.getElementById('contenidoModalViaje').innerHTML = contenido;

              // Mostrar el modal con Bootstrap 5
              const modal = new bootstrap.Modal(document.getElementById('modalInfoViaje'));
              modal.show();
            });
                
              }
          })


        }else {
             latlocal = item.ubicacion.data.lat;
         lnglocal = item.ubicacion.data.lng;
         id_contenConvoy=  item.id_contenendor;
        if (latlocal && lnglocal) {
    
          const newMarker = L.marker([latlocal, lnglocal]).addTo(map).bindPopup(item.contenedor).openPopup();
            window.markers.push(newMarker);

          newMarker.on('click', () => {
            const contenedor = item.contenedor;
             let info = contenedoresDisponibles.find(d => d.contenedor === contenedor);
                if (!info) {
                   if(t='#filtro-Equipo'){
                    const ahora = new Date();
                       info = contenedoresDisponibles.find(d => {
                            const inicio = new Date(d.fecha_inicio);
                            const fin = new Date(d.fecha_fin);

                            return ahora >= inicio && ahora <= fin;
                        });
                         console.log(info);
                         if (!info) {
                          alert("Información del viaje no encontrada.");
                          return;
                         }
                  }else {
                    alert("Información del viaje no encontrada.");
                  return;
                  }
                }

                const contenido = `
                  <div class="p-3">
                    <h5 class="mb-2">🚚 Información del Viaje</h5>
                    <p><strong>Cliente:</strong> ${info.cliente}</p>
                    <p><strong>Contenedor:</strong> ${info.contenedor}</p>
                    <p><strong>Origen:</strong> ${info.origen}</p>
                    <p><strong>Destino:</strong> ${info.destino}</p>
                    <p><strong>Contrato:</strong> ${info.tipo_contrato}</p>
                    <p><strong>IMEI:</strong> ${info.imei}</p>
                    <p><strong>Fecha Inicio:</strong> ${info.fecha_inicio}</p>
                  </div>
                `;

              document.getElementById('contenidoModalViaje').innerHTML = contenido;

              // Mostrar el modal con Bootstrap 5
              const modal = new bootstrap.Modal(document.getElementById('modalInfoViaje'));
              modal.show();
            });
        if (index === 0) map.setView([latlocal, lnglocal], 15);
        
        }
        }
      const datasave = {
          latitud: latlocal,
          longitud: lnglocal,
          ubicacionable_id: idConvoyOContenedor,
          tipo: tipo
      };
        if (idConvoyOContenedor!= ""){
      actualizarUbicacionReal(datasave)
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
    detener();
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
   if (!window.markers) window.markers = [];

    window.markers.forEach(marker => {
        if (map.hasLayer(marker)) {
            map.removeLayer(marker);
        }
    });

    window.markers = [];
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

       
            if (btnCerrarModal) {
                // Cerrar modal
                btnCerrarModal.addEventListener('click', function () {
                    filtroModal.hide();
                });
            } else {
                console.error("Botón de cierre no encontrado.");
            }
       

        filtroModal.show();

        } else {
            console.error("No se encontraron los elementos necesarios.");
        }




         const tabLinks = document.querySelectorAll('#filtroTabs button[data-bs-toggle="tab"]');
          const tituloFiltroDiv = document.getElementById('tituloSeguimiento');
            tabLinks.forEach(function (tab) {
              tab.addEventListener('shown.bs.tab', function (event) {
               
                console.log('Tab actual:', event.target.id);
                console.log('Tab anterior:', event.relatedTarget?.id);
                 const dataId = event.target.getAttribute('data-id');
                  if (dataId && tituloFiltroDiv) {
                      tituloFiltroDiv.textContent = 'Seguimiento  ' + dataId;
                    }
               
              });
            });
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
  limpiarMarcadores();

}

const seleccionados = [];
let ItemsSelects = [];

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

         ItemsSelects.push(valor +"|" +contenedorData.imei+"|"+ contenedorData.id_contenedor);
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


