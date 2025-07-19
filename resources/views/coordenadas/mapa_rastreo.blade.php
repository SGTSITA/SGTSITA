<!DOCTYPE html>
<html>
<head>
    <title>Comparación de Ubicaciones</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" />

    <style>
        html, body {
    height: 100%;
    margin: 0;
    padding: 0;
}

#mapaRastreo {
    height: 100%; /* Ocupa toda la altura visible del navegador */
    width: 100%;
    margin: 0;
    border: none;
    border-radius: 0;
}
     #info table {
    width: 100%;
    border-collapse: collapse;
    box-shadow: 0 0 10px rgba(0,0,0,0.05);
    font-family: 'Segoe UI', sans-serif;
    font-size: 14px; /* Más pequeña */
}

#info th, #info td {
    border: 1px solid #dee2e6;
    padding: 6px 8px; /* Menos padding = menos altura */
    text-align: center;
    line-height: 1.2; /* Más compacto verticalmente */
}

#info thead {
    background-color: #0d6efd;
    color: white;
}

#info tbody td span {
    font-weight: 400;
}

#info tr:nth-child(even) {
    background-color: #f8f9fa;
}
   .btn-toggle {
      background-color: #f44366;
      color: white;
      font-weight: bold;
      padding: 10px 20px;
      border: none;
      border-radius: 50px;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      font-size: 14px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
      transition: background-color 0.2s ease;
    }

    .btn-toggle:hover {
      background-color: #d63455;
    }

    .btn-toggle i {
      font-size: 16px;
    }
  
    </style>
     <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" 
     
      crossorigin="anonymous">
</head>
<body>

    <div id="info" class="container mt-3">
    <div class="table-responsive">
        <table class="table table-bordered align-middle text-center shadow-sm">
            <thead class="table-primary">
                <tr>
                   <th>Tipo</th>
                    <th>Contenedor</th>
                    <th>Acciones</th>
                
                </tr>
            </thead>
            <tbody>
                <tr>
                  <td><span id="tipoSpan" class="fw-semibold"></span></td>
                    <td><span id="contenedorSpan" class="fw-semibold"></span></td>
                    <td> <button id="btnDetener" class="btn-toggle" style="display: none;">
                        <i class="bi bi-pause-circle"></i> Detener actualización
                      </button>
                    <a href="{{ route('HistorialUbicaciones') }}" class="btn btn-warning" target="_blank" rel="noopener noreferrer">
    <i class="bi bi-clock-history me-1"></i> Historial de Ubicaciones
</a>
                    
                    </td>
                
                </tr>
            </tbody>
        </table>
    </div>
</div>

    <div id="mapaRastreo"></div>

    <div class="modal fade" id="modalInfoViaje" tabindex="-1" aria-labelledby="modalInfoViajeLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content shadow-lg rounded-4">
      <div class="modal-header bg-primary text-white rounded-top-4">
        <h5 class="modal-title" id="modalInfoViajeLabel">
          <i class="bi bi-truck-front-fill me-2"></i> Información del Viaje
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body" id="contenidoModalViaje">
        <!-- Aquí se insertará el contenido dinámico -->
      </div>
      <div class="modal-footer bg-light rounded-bottom-4">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="bi bi-x-circle"></i> Cerrar
        </button>
      </div>
    </div>
  </div>
</div>

    <!-- Leaflet JS -->
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
      rel="stylesheet"
      
      crossorigin="anonymous">
  <script src="{{ asset('assets/js/core/bootstrap.min.js')}}"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        
        crossorigin="anonymous"></script>
    <script>

      window.markers = [];
var map = L.map('mapaRastreo').setView([0, 0], 2);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
}).addTo(map);


let marker = null;

       const params = new URLSearchParams(window.location.search);
      let detalleConvoys;
let contenedoresDisponibles = [];
let contenedoresDisponiblesAll =[];
let intervalId = null;
let ItemsSelects = [];
let idConvoyOContenedor=0;
 const contenedor = params.get('contenedor')
 let tipoSpans = params.get('tipoS')
 function actualizarUbicacion(imeis,t) {
 
 document.getElementById('tipoSpan').textContent = tipoSpans;
 document.getElementById('contenedorSpan').textContent = contenedor;
let tipo = "";

    let responseOk = false;
  fetch("/coordenadas/ubicacion-vehiculo", {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    },
    body: JSON.stringify({ imeis: imeis})
  })
  .then(res => res.json())
  .then(data => {
    //console.log('Ubicaciones recibidas:', data);
    const dataUbi= data;

limpiarMarcadores();
responseOk = true;
    if (Array.isArray(dataUbi)) {
      dataUbi.forEach((item, index) => {
        tipo= item.tipogps;
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
                  if (tipoSpans.toLowerCase().includes('convoy')) {
                     info = contenedoresDisponiblesAll.find(d => d.contenedor === contenedor);
                  }else if(t==='#filtro-Equipo'){
                      if(t==='#filtro-Equipo'){
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
         idConvoyOContenedor=  item.id_contenendor;
        if (latlocal && lnglocal) {
    
          const newMarker = L.marker([latlocal, lnglocal]).addTo(map).bindPopup(tipoSpans + ' '+ item.contenedor).openPopup();
            window.markers.push(newMarker);
          tipo= tipo + ' '+ item.contenedor;
          newMarker.on('click', () => {
            const contenedor = item.contenedor;
             let info = contenedoresDisponibles.find(d => d.contenedor === contenedor);
                if (!info) {
                  if (tipoSpans.toLowerCase().includes('convoy')) {
                     info = contenedoresDisponiblesAll.find(d => d.contenedor === contenedor);
                  }else if(t==='#filtro-Equipo'){
                   
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
                }
                let extraInfo = '';

                if (t === '#filtro-Equipo') {
                  extraInfo = `
                    <p><strong>IMEI CHASIS:</strong> ${info.imei_chasis}</p>
                           `;
                }

                const contenido = `
                  <div class="p-3">
                    <h5 class="mb-2">🚚 Información del Viaje</h5>
                    <p><strong>Cliente:</strong> ${info.cliente}</p>
                    <p><strong>Contenedor:</strong> ${info.contenedor}</p>
                    <p><strong>Origen:</strong> ${info.origen}</p>
                    <p><strong>Destino:</strong> ${info.destino}</p>
                    <p><strong>Contrato:</strong> ${info.tipo_contrato}</p>
                    <p><strong>Fecha Inicio:</strong> ${info.fecha_inicio}</p>
                    <p><strong>IMEI:</strong> ${info.imei}</p>
                   
                                   ${extraInfo}
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
    
  
  })
  .catch(error => {
    console.error('Error al obtener ubicaciones:', error);
    detener();
  });
}
cargarinicial();

function limpiarMarcadores() {
   if (!window.markers) window.markers = [];

    window.markers.forEach(marker => {
        if (map.hasLayer(marker)) {
            map.removeLayer(marker);
        }
    });

    window.markers = [];
}


function cargarinicial() {
    fetch(`/coordenadas/contenedor/searchEquGps?`)
        .then(response => response.json())
        .then(data => {
            contenedoresDisponibles = data.datos;
            detalleConvoys = data.dataConten;
contenedoresDisponiblesAll=     data.       datosAll;

            if (!contenedoresDisponibles) {
                alert('No se encontró información del contenedor.');
            }

            const contenedores = contenedor.trim().replace(/\s*\/\s*/g, ' ').split(/\s+/); 
            contenedores.forEach(cod => {
                const infoc = contenedoresDisponibles.find(d => d.contenedor === cod);

                if (infoc) {
                    let conponerStrin = cod + '|' + infoc.imei + '|' + infoc.id_contenedor + '|' + infoc.tipoGps;
                    ItemsSelects.push(conponerStrin);
                } else {



                  //buscamos en todos pero se valida si es convoy para saber si tenemos que buscar aunq no le pertenece el contenedor al user
                  if (tipoSpans.toLowerCase().includes('convoy')) {
                const infoc2 = contenedoresDisponiblesAll.find(d => d.contenedor === cod);
                                  if (infoc2) {
                                    let conponerStrin = cod + '|' + infoc2.imei + '|' + infoc2.id_contenedor + '|' + infoc2.tipoGps;
                                    ItemsSelects.push(conponerStrin);
                                } else {
                                  console.warn(`Contenedor ${cod} no encontrado en contenedoresDisponibles.`);
                                }

                  } else {
                  console.warn(`Contenedor ${cod} no encontrado en contenedoresDisponibles.`);
                }
                  
                  
                }                     
                
            });

            if (ItemsSelects.length > 0) {
                actualizarUbicacion(ItemsSelects, '');
                document.getElementById('btnDetener').style.display = 'inline-block';

                if (intervalId) clearInterval(intervalId);

                intervalId = setInterval(() => {
                    actualizarUbicacion(ItemsSelects, '');
                }, 5000);
            } else {
                Swal.fire('Atención', 'Ningún contenedor válido fue encontrado.', 'warning');
            }
        }); // <-- cierre del .then()
}




document.getElementById('btnDetener').addEventListener('click', function() {
   const icon = this.querySelector('i');

      if (intervalId) {
        clearInterval(intervalId);
        intervalId = null;

        this.innerHTML = '<i class="bi bi-play-circle"></i> Reanudar actualización';
        console.log('⛔ Actualización detenida.');
      } else {
          actualizarUbicacion(ItemsSelects,'');

          if (intervalId) clearInterval(intervalId);
          
          intervalId = setInterval(() => {
            actualizarUbicacion(ItemsSelects,'');
          }, 5000);

        this.innerHTML = '<i class="bi bi-pause-circle"></i> Detener actualización';
        console.log('✅ Reanudando actualización...');
      }
});


function detener(){
    if(intervalId) {
    clearInterval(intervalId);
    intervalId = null;
    document.getElementById('btnDetener').style.display = 'none';
   
  }
}



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

    </script>
</body>
</html>