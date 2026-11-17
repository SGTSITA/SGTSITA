# [SDD-003] Refactorización e Integración de Servicio GPS Naanix / SIS Technologies (MClientesExternosJSPY)

Estado: completado

## 1. Taxonomía de la Especificación
- **ID:** SDD-003
- **Módulo:** Monitoreo Satelital / Integración GPS / Flota y Viajes
- **Tipo:** Refactor / Integración
- **Prioridad:** Alta
- **Autor / Solicitante:** JoseMXN / Antigravity

---

## 2. Descripción y Contexto
El proveedor de rastreo satelital **SIS Technologies / Naanix** ha actualizado su infraestructura, reemplazando el servicio heredado basado en SOAP/WSDL (`http://3.90.229.208:8080/external/tracking3?wsdl` y `SoapClient` en `app/Traits/SISGPSTrait.php`) por una interfaz moderna de Web Services **POST / JSON** (`Web Service MClientesExternosJSPY`).

El servicio expone tres operaciones principales:
1. **`ObtenerToken`:** Autenticación mediante `Cliente` y `Llave` dinámica (suma en UTC de `Hour + Day + IdCliente`). Retorna un token con vigencia de 1 hora.
2. **`ObtenerUnidades`:** Catálogo de unidades asociadas al cliente con identificador de telemetría (`DispositivoAsignado`/IMEI), placas, número económico, tipo de unidad, odómetro y tiempo sin reportar.
3. **`ObtenerPosicionActualGeneral`:** Consulta masiva o delta (vía `IdPosicion`) de las últimas coordenadas registradas (latitud, longitud, velocidad, fecha UTC, geocodificación textual, ID y descripción de evento).

Actualmente en SGTSITA existen 43 equipos y proveedores vinculados a SIS GPS (ID 7 en `gps_company`). Esta refactorización moderniza el trait y el servicio de rastreo para consumir la nueva API REST/JSON con alto rendimiento, gestión de caché de tokens/unidades y resiliencia ante fallos.

---

## 3. Criterios de Aceptación (Definition of Done - DoD)

- [ ] **Escenario 1 (Generación de Llave y Obtención de Token):**
  - **Dado:** Las credenciales configuradas (`Cliente: 937184269` u obtenidas dinámicamente de `gps_company_proveedores`).
  - **Cuando:** El sistema requiere consultar telemetría o validar credenciales.
  - **Entonces:** Calcula la llave matemática `(int) gmdate('G') + (int) gmdate('j') + (int) $idCliente`, ejecuta la petición POST a `/General/ObtenerToken` y almacena el token en caché (`Cache::put`) por 50 minutos para evitar peticiones redundantes.

- [ ] **Escenario 2 (Validación de Credenciales en Panel de Configuración):**
  - **Dado:** Un administrador configurando o probando SIS GPS en `GpsCompanyController`.
  - **Cuando:** Se invoca la validación de acceso.
  - **Entonces:** Si la API retorna `Resultado: 1`, se marca como exitosa; si retorna códigos negativos (`-1000`, `-1001`, etc.), se devuelve un `ApiResponse` con `success: false` y el mensaje de error correspondiente.

- [ ] **Escenario 3 (Rastreo Automático y Consulta por Lote):**
  - **Dado:** El comando programado `rastreo:intervalConfig` (`cmdRastreoInterval.php`) o el mapa de monitoreo en tiempo real solicitando la posición de contenedores o equipos vinculados a SIS GPS.
  - **Cuando:** `UbicacionService::consultarSisGpsGrupo()` consulta las posiciones.
  - **Entonces:** Llama a `/General/ObtenerPosicionActualGeneral`, cruza las posiciones devueltas contra los IMEIs o `IdUnidad` de las unidades solicitadas y retorna las coordenadas normalizadas (`lat`, `lng`, `velocidad`, `fecha`, etc.) para su guardado en `coordenadas_historial` y despliegue en mapas sin interrupciones.

- [ ] **Escenario 4 (Mapeo Inteligente de Unidades):**
  - **Dado:** Que la consulta de posición actual devuelve `IdUnidad` y la base de datos de SGTSITA almacena el IMEI / `DispositivoAsignado`.
  - **Cuando:** Se procesan las posiciones de SIS GPS.
  - **Entonces:** El sistema mantiene un mapa en caché de `IdUnidad <-> DispositivoAsignado (IMEI) / Placas` consumiendo `/General/ObtenerUnidades` (con TTL de 6 a 12 horas o bajo demanda) para asociar la telemetría con el registro exacto de `equipos`.

- [ ] **Escenario 5 (Manejo Seguro de Excepciones y Tiempos de Espera):**
  - **Dado:** Una caída de red, tiempo de respuesta excesivo (> 8s) o error interno del proveedor (`Resultado: -1`, `-2`, `-3`).
  - **Cuando:** Se ejecuta la petición HTTP en `SISGPSTrait`.
  - **Entonces:** Se atrapa la excepción, se registra en `Log::error`, y se devuelve una respuesta estandarizada de falla sin interrumpir el procesamiento del resto de proveedores de GPS.

- [ ] **Escenario 6 (Compatibilidad con la API Móvil Flutter y Vistas Web):**
  - **Dado:** La app móvil `operador_appsgt` y las vistas web de mapas (`/coordenadas/rastreoTab`, etc.).
  - **Cuando:** Consultan la ubicación de viajes y unidades.
  - **Entonces:** La estructura de respuesta y el contrato JSON permanecen inalterados, preservando compatibilidad absoluta.

---

## 4. Impacto Técnico y Cambios Previstos

- **Configuración (`config/services.php` y `.env`):**
  - Actualizar `config('services.GPS_SIS_URL')` para soportar:
    - `url_base`: `env('GPS_SIS_URL', 'https://3.90.229.208:8058/MClientesExternosJSPY')`
    - `client_id`: `env('GPS_SIS_CLIENT_ID', '937184269')`
    - `timeout`: `env('GPS_SIS_TIMEOUT', 10)`
    - `verify_ssl`: `env('GPS_SIS_VERIFY_SSL', false)`

- **Capa de Servicios y Traits:**
  - `app/Traits/SISGPSTrait.php`:
    - Reemplazar completamente la lógica de `SoapClient` por peticiones HTTP JSON (`Http::withHeaders()->withoutVerifying()`).
    - Implementar:
      - `sisCalcularLlave(int|string $idCliente): int`
      - `sisObtenerToken(int|string $idCliente): ApiResponse` (con caché automática de 50 min)
      - `sisValidarCredenciales(string $idCliente, ?string $llave = null): ApiResponse`
      - `sisObtenerUnidades(int|string $idCliente): ApiResponse` (con caché de catálogo de unidades)
      - `sisGetPosicionesActuales(int|string $idCliente, int $idPosicion = 0): ApiResponse`
      - `sisGetLastPosition(int|string $idCliente, ?string $key, string $deviceId): ApiResponse` (compatibilidad hacia atrás para llamadas puntuales por dispositivo/IMEI)
  - `app/Services/UbicacionService.php`:
    - Optimizar `consultarSisGpsGrupo(array $items, array $credenciales)` para consultar las posiciones de SIS GPS en un único llamado masivo (`sisGetPosicionesActuales`) y resolver por IMEI o `IdUnidad`, reduciendo la latencia de N peticiones secuenciales a 1 sola.
    - Corregir el typo detectado en la línea 1704 (`$ $ubicacionApi`).

- **Controladores Afectados:**
  - `app/Http/Controllers/GpsCompanyController.php`:
    - Ajustar `case 7` para la validación de credenciales con la nueva firma de `SISGPSTrait::sisValidarCredenciales()`.
  - `app/Http/Controllers/GpsController.php`:
    - Actualizar `loginSisGps` y `getlocationSIS` para usar la nueva API REST en lugar de SOAP.

---

## 5. Decisiones Técnicas Justificadas
1. **Desacoplamiento y Caché de Tokens:** Dado que el token emitido por Naanix tiene 1 hora de vigencia y la llave depende de la hora y día UTC, cachear el token por 50 minutos ahorra cientos de peticiones de login por hora durante la ejecución del cron `rastreo:intervalConfig`.
2. **Consulta Masiva vs Unitaria:** En el sistema anterior SOAP, se ejecutaba `getLastPosition` por cada equipo en bucle. En el nuevo Web Service, `ObtenerPosicionActualGeneral` devuelve todas las posiciones activas en una sola llamada POST. Esto acelera el cron de 40+ segundos a menos de 500 ms.
3. **Mapeo Automático de Unidades:** `ObtenerPosicionActualGeneral` devuelve `IdUnidad`, mientras que SGTSITA registra `imei` (`DispositivoAsignado`). Al indexar en memoria/caché `IdUnidad <-> DispositivoAsignado` usando `ObtenerUnidades`, se logra un emparejamiento 100% confiable y tolerante a cambios.
