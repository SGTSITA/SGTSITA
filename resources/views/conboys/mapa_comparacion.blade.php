<!DOCTYPE html>
<html>
<head>
    <title>Comparación de Ubicaciones</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" />

    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
        }

        #info {
            background-color: white;
            padding: 1rem;
            border-bottom: 1px solid #ccc;
            z-index: 1000;
            position: fixed;
            width: 100%;
        }

        #mapaComparacion {
            position: absolute;
            top: 130px; /* altura del panel superior */
            bottom: 0;
            width: 100%;
        }

        .info-item {
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body>

    <div id="info">
        <div class="info-item"><strong>Contenedor:</strong> <span id="contenedorSpan"></span></div>
        <div class="info-item"><strong>Ubicación esperada:</strong> <span id="esperadaSpan"></span></div>
        <div class="info-item"><strong>Ubicación GPS:</strong> <span id="gpsSpan"></span></div>
        <div class="info-item"><strong>Distancia aproximada:</strong> <span id="distanciaSpan"></span> km</div>
    </div>

    <div id="mapaComparacion"></div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>

    <script>
        // Obtener parámetros desde la URL
        const params = new URLSearchParams(window.location.search);
        const lat1 = parseFloat(params.get('latitud'));
        const lon1 = parseFloat(params.get('longitud'));
        const lat2 = parseFloat(params.get('latitud_seguimiento'));
        const lon2 = parseFloat(params.get('longitud_seguimiento'));
        const contenedor = params.get('contenedor') ?? 'No definido';

        // Mostrar datos en texto
        document.getElementById('contenedorSpan').textContent = contenedor;
        document.getElementById('esperadaSpan').textContent = `${lat1.toFixed(5)}, ${lon1.toFixed(5)}`;
        document.getElementById('gpsSpan').textContent = `${lat2.toFixed(5)}, ${lon2.toFixed(5)}`;

        // Calcular distancia usando fórmula Haversine
        function calcularDistancia(lat1, lon1, lat2, lon2) {
            const R = 6371; // km
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a =
                Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                Math.sin(dLon / 2) * Math.sin(dLon / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            return (R * c).toFixed(2); // km
        }

        const distancia = calcularDistancia(lat1, lon1, lat2, lon2);
        document.getElementById('distanciaSpan').textContent = distancia;

        // Mostrar mapa
        const mapa = L.map('mapaComparacion').setView([lat1, lon1], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(mapa);

        L.marker([lat1, lon1]).addTo(mapa).bindPopup("Ubicación esperada").openPopup();
        L.marker([lat2, lon2]).addTo(mapa).bindPopup("Ubicación GPS final");

        L.polyline([[lat1, lon1], [lat2, lon2]], { color: 'red' }).addTo(mapa);
    </script>
</body>
</html>