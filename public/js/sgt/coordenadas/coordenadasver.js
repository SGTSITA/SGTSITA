window.markers = [];
var map = L.map('map').setView([0, 0], 2);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
}).addTo(map);

// Mostrar el modal de Bootstrap al cargar la página
window.onload = function() {
  var filtroModal = new bootstrap.Modal(document.getElementById('filtroModal'));
  filtroModal.show();

  // Centrar mapa en ubicación actual
  if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(function (position) {
          const lat = position.coords.latitude;
          const lng = position.coords.longitude;
          map.setView([lat, lng], 9);

          const marker = L.marker([lat, lng]).addTo(map)
              .bindPopup('Buscar Contenedores').openPopup();

          window.markers.push(marker);
      }, function (error) {
          console.warn('No se pudo obtener la ubicación:', error.message);
      });
  } else {
      console.warn('Geolocalización no soportada por este navegador.');
  }
};

// Enviar filtros al backend
document.getElementById('formFiltros').addEventListener('submit', function(event) {
  event.preventDefault();

  const formData = new FormData(this);
  const queryParams = new URLSearchParams();

  formData.forEach((valor, clave) => {
    if (valor.trim() !== '') {
      queryParams.append(clave, valor);
    }
  });

  limpiarMarcadores(); // Limpia los anteriores antes de buscar nuevos

  fetch(`/coordenadas/contenedor/search?${queryParams.toString()}`)
      .then(response => response.json())
      .then(data => {
          const preguntas = data.preguntas;
          const datos = data.datos;

          datos.forEach(contenedor => {
            preguntas.forEach(pregunta => {
                const campo = pregunta.campo;
                const valor = contenedor[campo];
        
                if (valor) {
                    const coords = valor.split(',');
                    if (coords.length === 2) {
                        const lat = parseFloat(coords[0]);
                        const lng = parseFloat(coords[1]);
        
                        const marker = L.marker([lat, lng])
                            .addTo(map)
                            .bindTooltip(`${pregunta.tooltip} <br> Contenedor: <strong>${contenedor.contenedor}</strong>`)
                            .openTooltip();
        
                        window.markers.push(marker);
                    }
                }
            });
        });

          // Cierra el modal después de aplicar los filtros
          var modal = bootstrap.Modal.getInstance(document.getElementById('filtroModal'));
          modal.hide();
      })
      .catch(error => {
          console.error('Error al traer coordenadas:', error);
      });
});

// Función para limpiar marcadores
function limpiarMarcadores() {
    if (window.markers && window.markers.length > 0) {
        window.markers.forEach(marker => {
            map.removeLayer(marker);
        });
        window.markers = [];
    }
}

document.getElementById('cliente').addEventListener('change', function () {
    const clienteId = this.value;
    const subclienteSelect = document.getElementById('subcliente');

    // Limpia las opciones anteriores
    subclienteSelect.innerHTML = '<option value="">Seleccione un subcliente</option>';

    if (clienteId) {
        // Puedes mostrar un "cargando..." si quieres
        const loadingOption = document.createElement('option');
        loadingOption.textContent = 'Cargando subclientes...';
        loadingOption.disabled = true;
        loadingOption.selected = true;
        subclienteSelect.appendChild(loadingOption);

        fetch(`/api/coordenadas/subclientes/${clienteId}`)
            .then(response => response.json())
            .then(subclientes => {
                subclienteSelect.innerHTML = '<option value="">Seleccione un subcliente</option>'; // Resetea

                if (subclientes.length > 0) {
                    subclientes.forEach(subcliente => {
                        const option = document.createElement('option');
                        option.value = subcliente.id;
                        option.textContent = subcliente.nombre;
                        subclienteSelect.appendChild(option);
                    });

                    
                } else {
                    const option = document.createElement('option');
                    option.textContent = 'No hay subclientes disponibles';
                    option.disabled = true;
                    subclienteSelect.appendChild(option);
                }
            })
            .catch(error => {
                console.error('Error al cargar subclientes:', error);
                subclienteSelect.innerHTML = '<option value="">Error al cargar subclientes</option>';
            });
    }
});
document.addEventListener('DOMContentLoaded', function () {
    
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

            fetch('/api/coordenadas/entidadesPC')
            .then(response => response.json())
            .then(data => {
                const proveedorSelect = document.getElementById('proveedor');
                const clienteSelect = document.getElementById('cliente');

                // Añadir una opción predeterminada
                proveedorSelect.innerHTML = '<option value="">Seleccione un proveedor</option>';
                clienteSelect.innerHTML = '<option value="">Seleccione un cliente</option>';

                // Cargar proveedores
                data.proveedor.forEach(proveedor => {
                    const option = document.createElement('option');
                    option.value = proveedor.id;
                    option.textContent = proveedor.nombre;
                    proveedorSelect.appendChild(option);
                });

                // Cargar clientes
                data.client.forEach(cliente => {
                    const option = document.createElement('option');
                    option.value = cliente.id;
                    option.textContent = cliente.nombre;
                    clienteSelect.appendChild(option);
                });
            })
            .catch(error => console.error('Error al cargar proveedores y clientes:', error))




        }, 100);

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

}