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
 

    <script>
        let map;
  let markers = [];
 function googleMapsReady() {
    
            initMap();
        }
  function initMap() {
   
    map = new google.maps.Map(document.getElementById("mapaComparacion"), {
      center: { lat: 0, lng: 0 },
      zoom: 2,
    });

    

     const marker = new google.maps.Marker({
    position: { lat: 0, lng: 0 },
    map: map,
  });

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

        async function compararDirecciones() {
   let direcccionEsperada = await getAddressFromLatLng(lat1, lon1);
    procesarDireccion(direcccionEsperada);
    
}
compararDirecciones();
function procesarDireccion(direccion) {
    console.log("Procesando:", direccion);
     const contentC = `
  <div style="
    background-color: #e3f2fd;
    padding: 5px;
    border-radius: 8px;
    font-family: Arial, sans-serif;
    box-shadow: 0 2px 6px rgba(0,0,0,0.3);
    max-width: 240px;
  ">
    <div style="font-weight: bold; font-size: 17px; margin-bottom: 6px;">
      Ubicación esperada
    </div>
    <div style="font-size: 17px; line-height: 1.5;">
      <strong>Direccion:</strong> ${direccion}<br>
     
    </div>
  </div>
`;

const infoWindowUbiEsperada = new google.maps.InfoWindow({
  content: contentC,
});

const markerUbiEsperada = new google.maps.Marker({
  position: { lat: lat1, lng: lon1 },
  map: map,
  icon: {
    path: google.maps.SymbolPath.CIRCLE,
    scale: 8,
    fillColor: "#00ff00",
    fillOpacity: 0.8,
    strokeWeight: 1,
    strokeColor: "#3366CC",
  },
  title: "Ubicación esperada",
});

markerUbiEsperada.addListener("click", () => {
  infoWindowUbiEsperada.open(map, markerUbiEsperada);
});

 infoWindowUbiEsperada.open(map, markerUbiEsperada);
}
   
    

      

   
    async function compararDirecciones2() {
  let direcccionGps  = await getAddressFromLatLng(lat2,lon2);
     procesarDireccion2(direcccionGps);
}
compararDirecciones2();
function procesarDireccion2(direccion) {

const contentC2 = `
  <div style="
    background-color: #e3f2fd;
    padding: 5px;
    border-radius: 8px;
    font-family: Arial, sans-serif;
    box-shadow: 0 2px 6px rgba(0,0,0,0.3);
    max-width: 240px;
  ">
    <div style="font-weight: bold; font-size: 17px; margin-bottom: 6px;">
      Ubicación GPS
    </div>
    <div style="font-size: 17px; line-height: 1.5;">
      <strong>Direccion:</strong> ${direccion}<br>
     
    </div>
  </div>
`;
 

    const infoWindowgps = new google.maps.InfoWindow({
  content: contentC2,
});
   const markergps = new google.maps.Marker({
  position: { lat: lat2, lng: lon2 },
  map: map,
  icon: {
    path: google.maps.SymbolPath.CIRCLE,
    scale: 8,
    fillColor: "#4A90E2",
    fillOpacity: 0.8,
    strokeWeight: 1,
    strokeColor: "#3366CC",
  },
  title: "Ubicación GPS",
});

markergps.addListener("click", () => {
  infoWindowgps.open(map, markergps);
});
    

  

    infoWindowgps.open(map, markergps);
}
     

    // Línea entre ambos puntos
    const flightPath = new google.maps.Polyline({
      path: [
        { lat: lat1, lng: lon1 },
        { lat: lat2, lng: lon2 },
      ],
      geodesic: true,
      strokeColor: "#FF0000",
      strokeOpacity: 1.0,
      strokeWeight: 2,
    });

    flightPath.setMap(map);


  // Espera que cargue la API con callback
  window.initMap = initMap;
  }


  function getAddressFromLatLng(lat, lng) {
  const geocoder = new google.maps.Geocoder();

  return new Promise((resolve, reject) => {
    geocoder.geocode({ location: { lat, lng } }, (results, status) => {
      if (status === "OK") {
        if (results[0]) {
          resolve(results[0].formatted_address);
        } else {
          reject("No se encontró dirección para estas coordenadas");
        }
      } else {
        reject("Error de geocodificación: " + status);
      }
    });
  });
}
       
    </script>

      <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAtAO2AZBgzC7QaBxnMnPoa-DAq8vaEvUc" async defer onload="googleMapsReady()"></script>
</body>
</html>