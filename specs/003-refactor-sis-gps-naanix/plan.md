# Plan Técnico — Spec 003: Refactorización e Integración de Servicio GPS Naanix / SIS Technologies

Cubre: RF-1 a RF-6 detallados en `spec.md`.

## 1. Archivos y Responsabilidades

### Backend / Configuración
- `config/services.php`:
  - Modernizar la clave `GPS_SIS_URL` para estructurarla con `url_base`, `client_id`, `timeout`, `verify_ssl`.
- `.env`:
  - Actualizar `GPS_SIS_URL=https://3.90.229.208:8058/MClientesExternosJSPY` y definir `GPS_SIS_CLIENT_ID=937184269`.

### Capa de Integración GPS
- `app/Traits/SISGPSTrait.php`:
  - Eliminar dependencia heredada de `\SoapClient` y llamadas SOAP.
  - Implementar métodos de la especificación Naanix Web Services:
    - `sisCalcularLlave(int|string $idCliente): int`: Llave = Hour UTC + Day UTC + IdCliente.
    - `sisObtenerToken(int|string $idCliente): ApiResponse`: Solicitud POST a `/General/ObtenerToken`. Almacenamiento en `Cache::remember('gps:sis:token:' . $idCliente, 3000, ...)`.
    - `sisValidarCredenciales(string $idCliente, ?string $llave = null): ApiResponse`: Validación de acceso.
    - `sisObtenerUnidades(int|string $idCliente): ApiResponse`: Solicitud POST a `/General/ObtenerUnidades`. Mapea catálogo de unidades y las indexa por `DispositivoAsignado` (IMEI) y por `IdUnidad`.
    - `sisGetPosicionesActuales(int|string $idCliente, int $idPosicion = 0): ApiResponse`: Solicitud POST a `/General/ObtenerPosicionActualGeneral`.
    - `sisGetLastPosition(string $idCliente, ?string $key, string $deviceId): ApiResponse`: Consulta de telemetría de una unidad específica con fallback inteligente sobre `sisGetPosicionesActuales`.

### Orquestación de Monitoreo
- `app/Services/UbicacionService.php`:
  - En `consultarSisGpsGrupo(array $items, array $credenciales)`:
    - Realizar una sola llamada agrupada a `sisGetPosicionesActuales` con las credenciales del grupo.
    - Emparejar cada item solicitado con su posición correspondiente utilizando el mapeo de unidades.
    - Formatear el resultado en la estructura estándar de SGTSITA (`lat`, `lng`, `velocidad`, `imei`, `deviceName`, `status`, etc.).
  - En `obtenerUbicacionByDispositivosIndividual` (o bloque switch case):
    - Corregir el typo `$ $ubicacionApi` en la línea 1704 y adaptar el bloque a la respuesta normalizada de `sisGetLastPosition`.

### Controladores y Endpoints de Prueba
- `app/Http/Controllers/GpsCompanyController.php`:
  - Ajustar el validador `case 7` en `setupGps` y `validarCredencialesAjax` para que valide el `account` (IdCliente) mediante `sisValidarCredenciales()`.
- `app/Http/Controllers/GpsController.php`:
  - Actualizar `loginSisGps` y `getlocationSIS` para devolver las respuestas JSON del nuevo servicio.

---

## 2. Decisiones Técnicas y de Rendimiento

1. **Protocolo HTTP / REST con Laravel Client (`Http::fake` / `Http::timeout`):**
   - Utilizar el cliente nativo `Illuminate\Support\Facades\Http` con `withoutVerifying()` (por el certificado autofirmado en la IP y puerto 8058) y un timeout de 8 segundos para evitar bloqueos del worker.
2. **Estrategia de Caché Multinivel:**
   - **Token:** Almacenado por 50 minutos bajo `gps:sis:token:{idCliente}`. Si expira o la API devuelve `-1000` (Error de validación de acceso), se fuerza la regeneración del token y se reintenta una vez.
   - **Catálogo de Unidades:** Almacenado por 6 horas bajo `gps:sis:unidades:{idCliente}`. Permite resolver `IMEI / DispositivoAsignado <-> IdUnidad` sin consultar `/General/ObtenerUnidades` en cada ciclo de rastreo.
3. **Optimización por Lote en el Scheduler:**
   - En lugar de ejecutar 43 peticiones individuales en bucle cada 5 minutos (lo cual demoraba ~40s en SOAP), se ejecuta 1 sola petición POST a `ObtenerPosicionActualGeneral`, reduciendo el tiempo de sincronización a menos de 1 segundo.
4. **Preservación Inmutable de Contratos Externos:**
   - La respuesta enviada a la vista web de mapas (`/coordenadas/rastreoTab`) y a la API consumida por `operador_appsgt` mantiene los mismos campos: `lat`, `lng`, `velocidad`, `status`, etc. Cero impacto en la app móvil.
