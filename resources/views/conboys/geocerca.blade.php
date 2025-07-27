<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Mapa Geocerca Fullscreen</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- Leaflet CSS -->
  <link
    rel="stylesheet"
    href="https://unpkg.com/leaflet/dist/leaflet.css"
  />
  <link
    rel="stylesheet"
    href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css"
  />

  <style>
    html, body {
      height: 100%;
      margin: 0;
      padding: 0;
      overflow: hidden;
      font-family: Arial, sans-serif;
    }
    #map {
      position: fixed;
      top: 0; left: 0; right: 0; bottom: 0;
      z-index: 1;
    }
    .panel {
      position: fixed;
      top: 10px;
      left: 10px;
      background: rgba(255,255,255,0.9);
      padding: 15px 20px;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0,0,0,0.3);
      z-index: 1000;
      max-width: 320px;
      user-select: none;
    }
    .panel label {
      font-weight: 600;
      margin-right: 8px;
    }
    .panel input[type="number"] {
      width: 100px;
      padding: 6px 10px;
      border-radius: 5px;
      border: 1.5px solid #ccc;
      font-size: 1rem;
      transition: border-color 0.3s ease;
    }
    .panel input[type="number"]:focus {
      border-color: #007bff;
      outline: none;
    }
    .panel button {
      margin-top: 10px;
      width: 100%;
      background-color: #28a745;
      color: white;
      border: none;
      padding: 10px 0;
      border-radius: 6px;
      font-weight: 600;
      font-size: 1rem;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }
    .panel button:hover {
      background-color: #218838;
    }
  </style>
</head>
<body>

  <div id="map"></div>

  <div class="panel">
    <div>
      <label for="radiusInput">Radio (m):</label>
      <input type="number" id="radiusInput" value="500" min="10" step="10" />
    </div>
    <button id="guardarBtn">Guardar y cerrar</button>
  </div>

  <!-- Leaflet JS -->
  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
  <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>

  <script>
    let map, marker, circle;
    let geofenceLatLng = null;

    function initMap() {
      const defaultCenter = [19.4326, -99.1332]; // CDMX

      map = L.map("map").setView(defaultCenter, 13);

      L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        maxZoom: 19,
        attribution: '© OpenStreetMap contributors',
      }).addTo(map);

      const geocoder = L.Control.geocoder({
        defaultMarkGeocode: false
      })
      .on("markgeocode", function(e) {
        const center = e.geocode.center;
        map.setView(center, 15);
        placeMarker(center);
      })
      .addTo(map);

      map.on("click", function(e) {
        placeMarker(e.latlng);
      });

      function placeMarker(latlng) {
        geofenceLatLng = latlng;
        const radius = parseInt(document.getElementById("radiusInput").value) || 100;

        if (marker) {
          map.removeLayer(marker);
        }
        if (circle) {
          map.removeLayer(circle);
        }

        marker = L.marker(latlng).addTo(map);
        circle = L.circle(latlng, {
          radius: radius,
          color: "#ff0000",
          fillColor: "#ff0000",
          fillOpacity: 0.3,
        }).addTo(map);
      }

      document.getElementById("radiusInput").addEventListener("input", () => {
        if (geofenceLatLng && circle) {
          const newRadius = parseInt(document.getElementById("radiusInput").value) || 100;
          circle.setRadius(newRadius);
        }
      });

      document.getElementById("guardarBtn").addEventListener("click", () => {
        if (!geofenceLatLng) {
          alert("Debes seleccionar una ubicación en el mapa.");
          return;
        }
        const lat = geofenceLatLng.lat;
        const lng = geofenceLatLng.lng;
        const radius = parseInt(document.getElementById("radiusInput").value) || 100;

        if (window.opener && typeof window.opener.setGeocercaData === "function") {
          window.opener.setGeocercaData(lat, lng, radius);
          window.close();
        } else {
          alert(`Latitud: ${lat}\nLongitud: ${lng}\nRadio: ${radius} metros`);
        }
      });
    }

    window.onload = initMap;
  </script>

</body>
</html>
