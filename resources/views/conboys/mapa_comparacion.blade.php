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

#mapaComparacion {
    height: 100vh; /* Ocupa toda la altura visible del navegador */
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
    font-weight: 500;
}

#info tr:nth-child(even) {
    background-color: #f8f9fa;
}

  
    </style>
</head>
<body>

    <div id="info" class="container mt-3">
    <div class="table-responsive">
        <table class="table table-bordered align-middle text-center shadow-sm">
            <thead class="table-primary">
                <tr>
                    <th>Contenedor</th>
                    <th>Ubicación esperada</th>
                    <th>Ubicación GPS</th>
                    <th>Distancia aprox.</th>
                    <th>Tiempo llegada</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><span id="contenedorSpan" class="fw-semibold"></span></td>
                    <td><span id="esperadaSpan"></span></td>
                    <td><span id="gpsSpan"></span></td>
                    <td><span id="distanciaSpan">km</span></td>
                    <td><span id="TiempoLLegadaSpan">00 hrs - 00 min : 00 seg</span></td>
                </tr>
            </tbody>
        </table>
    </div>
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

        setTimeout(() => {
    mapa.invalidateSize();
}, 300);
    </script>
</body>
</html>