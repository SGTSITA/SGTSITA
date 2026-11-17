# Tareas — Spec 003: Refactorización e Integración de Servicio GPS Naanix / SIS Technologies

- [x] **T1. Configurar variables de entorno y `config/services.php`.** RF-1
  - Hecho cuando: Existan `GPS_NAANIX_URL` (apuntando a `https://3.90.229.208:8058/MClientesExternosJSPY`), `GPS_NAANIX_CLIENT_ID` (por defecto `937184269`) y su configuración cargada limpiamente en `config/services.php` manteniendo compatibilidad con la configuración previa.
- [x] **T2. Implementar nuevo trait `app/Traits/NaanixGPSTrait.php` conservando `SISGPSTrait.php` intacto.** RF-1, RF-2, RF-4, RF-5
  - Hecho cuando: Se cree `NaanixGPSTrait.php` con el algoritmo de cálculo de Llave UTC (`sisCalcularLlave`), obtención y caché de Token (`sisObtenerToken`), catálogo de unidades (`sisObtenerUnidades`), consulta de posiciones (`sisGetPosicionesActuales`), posición individual (`sisGetLastPosition`) y validación de credenciales (`sisValidarCredenciales`), manteniendo `SISGPSTrait.php` disponible como respaldo para reversión en caliente si se requiriera.
- [x] **T3. Adaptar `UbicacionService.php` para consultas en lote y resolución por dispositivo.** RF-3, RF-4
  - Hecho cuando: `consultarSisGpsGrupo` aproveche la consulta general masiva de Naanix resolviendo por IMEI/IdUnidad, reduciendo la latencia de rastreo con fallback automático a SOAP en caso de error, y se corrija el typo de sintaxis `$ $ubicacionApi` en el flujo individual.
- [x] **T4. Actualizar controladores `GpsCompanyController.php` y `GpsController.php`.** RF-2, RF-6
  - Hecho cuando: La prueba de credenciales en `setupGps` y `setConfigEquipo` (case 7) invoque el validador REST de `NaanixGPSTrait` y los endpoints de prueba devuelvan respuestas JSON estructuradas.
- [x] **T5. Pruebas de integración, verificación con la API real y ejecución de `rastreo:intervalConfig`.** DoD
  - Hecho cuando: Se ejecuten pruebas de integración contra el servicio real de Naanix, validando obtención de token, respuesta de posiciones y ejecución de `rastreo:intervalConfig` con éxito en Docker.
- [x] **T6. Limpieza de caché con `artisan optimize:clear` y actualización de `MEMORY.md`.** DoD
  - Hecho cuando: Se ejecute `optimize:clear`, se verifique la estabilidad del sistema y se documente la nueva integración en `MEMORY.md`.
