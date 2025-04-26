window.markers = [];
var map = L.map('map').setView([0, 0], 2); 
document.getElementById('buscadorContenedor').addEventListener('keypress', function (e) {
    if (e.key === 'Enter') {
        const contenedor = e.target.value.trim();
        if (!contenedor) return;

        fetch(`/coordenadas/contenedor/search?query=${encodeURIComponent(contenedor)}`)
        .then(response => response.json())
        .then(data => {
            const preguntas = data.preguntas;
            const datos = data.datos;
    
            preguntas.forEach(pregunta => {
                const campo = pregunta.campo;
                const valor = datos[campo];
    
                if (valor) {
                    const coords = valor.split(','); // Asumimos que están como "lat,lng"
                    if (coords.length === 2) {
                        const lat = parseFloat(coords[0]);
                        const lng = parseFloat(coords[1]);
    
                        L.marker([lat, lng])
                            .addTo(map)
                            .bindTooltip(pregunta.tooltip)
                            .openTooltip(); // opcional
                    }
                }
            });
        });
    }
});

function mostrarEnMapa(datos) {
    // Suponiendo que tienes acceso a `L` de Leaflet y un mapa llamado `map`
    const lat = parseFloat(datos.lat);
    const lng = parseFloat(datos.lng);

    if (isNaN(lat) || isNaN(lng)) return;

    // Limpia los marcadores anteriores si es necesario
    if (window.marcadorActual) {
        window.map.removeLayer(window.marcadorActual);
    }

    window.marcadorActual = L.marker([lat, lng]).addTo(map)
        .bindPopup(`Contenedor: ${datos.contenedor}`).openPopup();

    map.setView([lat, lng], 15);
}


L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
}).addTo(map);

document.addEventListener('DOMContentLoaded', function () {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function (position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;

            map.setView([lat, lng], 9); 
            L.marker([lat, lng]).addTo(map)
                .bindPopup('Buscar Contenedores').openPopup();
        }, function (error) {
            console.warn('No se pudo obtener la ubicación:', error.message);
        });
    } else {
        console.warn('Geolocalización no soportada por este navegador.');
    }
});


function limpiarMarcadores() {
    if (window.markers && window.markers.length > 0) {
        window.markers.forEach(marker => {
            marker.remove(); // O también: map.removeLayer(marker);
        });
        window.markers = [];
    }
}