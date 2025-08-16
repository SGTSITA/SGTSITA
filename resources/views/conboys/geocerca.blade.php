<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Geocerca configuracion</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="viewport" content="width=device-width, initial-scale=1" />
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
      width: 100%;
      height: 100%;
    }
    .panel {
      position: fixed;
      top: 10px;
      left: 10px;
      background: rgba(255, 255, 255, 0.95);
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
      z-index: 1000;
      max-width: 650px;
    }
    .panel label {
      font-weight: 600;
      margin-right: 8px;
    }
    .panel input[type="number"] {
      width: 300px;
      padding: 6px 10px;
      border-radius: 5px;
      border: 1.5px solid #ccc;
      font-size: 1rem;
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
    }
    .panel button:hover {
      background-color: #218838;
    }
  </style>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">


</head>
<body>

    <!-- Mapa -->
    <div id="map" style="height: 100%; width: 100%;"></div>



<div class="position-absolute bg-white p-3 rounded shadow"
     style="top: 45px; left: 10px; z-index: 1000; min-width: 250px;">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <strong class="text-dark">Crear Geocerca</strong>
        
    </div>

    <div class="mb-3">
        <label class="form-label">Dirección:</label>
        <input type="text" id="direccionInput" class="form-control" placeholder="Buscar dirección...">
    </div>

    <div class="mb-3">
        <label class="form-label">Radio (m):</label>
        <input type="number" id="radioInput" class="form-control" value="500">
    </div>

    <button class="btn btn-success w-100"  id="guardarBtn">Guardar y cerrar</button>
</div>


<script>
  let map, marker = null, circle = null;
  let geofenceLatLng = null;
  function googleMapsReady() {
    
            initMap();
        }
function initMap() {
  const defaultCenter = { lat: 19.4326, lng: -99.1332 }; // CDMX
  map = new google.maps.Map(document.getElementById("map"), {
    zoom: 13,
    center: defaultCenter,
  });

  const input = document.getElementById("direccionInput");
  const radioGeo = document.getElementById("radioInput");
  const autocomplete = new google.maps.places.Autocomplete(input);
  autocomplete.bindTo("bounds", map);

  autocomplete.addListener("place_changed", async () => {
    const value = input.value.trim();

    if (value.startsWith("http://") || value.startsWith("https://")) {
      // Si es una URL acortada
      const _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
      try {
        const response = await fetch('/coordenadas/resolver-link-google', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': _token,
            'Accept': 'application/json',
          },
          body: JSON.stringify({ shortUrl: value })
        });

        const data = await response.json();

        if (data.lat && data.lng) {
          map.setCenter({ lat: data.lat, lng: data.lng });
          map.setZoom(15);

          new google.maps.Marker({
            position: { lat: data.lat, lng: data.lng },
            map: map,
          });
const location = { lat: data.lat, lng: data.lng };
          placeMarkerAndCircle(location);
        } else {
          alert("No se pudo obtener la ubicación desde la URL.");
        }
      } catch (error) {
        alert("Error al procesar la URL.");
      }

    } else {
      // Si es una dirección escrita
      const place = autocomplete.getPlace();
      if (!place.geometry || !place.geometry.location) {
        alert("No se encontró la ubicación.");
        return;
      }

      map.setCenter(place.geometry.location);
      map.setZoom(15);

      placeMarkerAndCircle(place.geometry.location);
    }
  });

  // También puedes permitir pegar una URL y presionar Enter para resolverla
  input.addEventListener("keydown", async (e) => {
    if (e.key === "Enter") {
      const value = input.value.trim();
     
      if (value.startsWith("http://") || value.startsWith("https://") || esShortUrlGoogleMaps(value)) {
        const _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            fetch('/coordenadas/resolver-link-google', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': _token,
          'Accept': 'application/json',
        },
        body: JSON.stringify({ shortUrl: value })
      })
        .then(response => response.json())
        .then(data => {
          if (data.lat && data.lng) {
            map.setCenter({ lat: data.lat, lng: data.lng });
            map.setZoom(15);

            new google.maps.Marker({
              position: { lat: data.lat, lng: data.lng },
              map: map,
            });
const location = { lat: data.lat, lng: data.lng };
          placeMarkerAndCircle(location);
          } else {
            alert("No se pudo obtener la ubicación desde la URL.");
          }
        })
        .catch(error => {
          alert("Error al procesar la URL.");
          console.error(error);
        });
      }
    }
  });

  map.addListener("click", function (e) {
    placeMarkerAndCircle(e.latLng);
  });

  radioGeo.addEventListener("input", () => {
    if (circle) {
      const radius = parseInt(radioGeo.value) || 500;
      circle.setRadius(radius);
    }
  });
}

  function placeMarkerAndCircle(latLng) {
    geofenceLatLng = latLng;

    if (marker) marker.setMap(null);
    if (circle) circle.setMap(null);

    marker = new google.maps.Marker({
      position: latLng,
      map: map,
    });

    const radius = parseInt(document.getElementById("radioInput").value) || 100;

    circle = new google.maps.Circle({
      strokeColor: "#FF0000",
      strokeOpacity: 0.8,
      strokeWeight: 2,
      fillColor: "#FF0000",
      fillOpacity: 0.35,
      map: map,
      center: latLng,
      radius: radius,
    });
  }
function esShortUrlGoogleMaps(url) {
    const regex = /^https?:\/\/maps\.app\.goo\.gl\/.+$/i;
    return regex.test(url);
}

      document.getElementById("guardarBtn").addEventListener("click", () => {
        if (!geofenceLatLng) {
          alert("Debes seleccionar una ubicación en el mapa.");
          return;
        }
        const lat = geofenceLatLng.lat();
        const lng = geofenceLatLng.lng();
        const radius = parseInt(document.getElementById("radioInput").value) || 100;

        if (window.opener && typeof window.opener.setGeocercaData === "function") {
          window.opener.setGeocercaData(lat, lng, radius);
          window.close();
        } else {
         // alert(Latitud: ${lat}\nLongitud: ${lng}\nRadio: ${radius} metros);
        }
      });
    

</script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAtAO2AZBgzC7QaBxnMnPoa-DAq8vaEvUc&libraries=places" async defer onload="googleMapsReady()"></script>
<!-- Bootstrap 5 JS (Requiere Popper para algunos componentes) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
