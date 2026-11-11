# MEMORY.md — SGTSITA
Memoria viva del proyecto entre sesiones (máximo ~50 líneas).

## Estado actual
- **Unicidad de `id_viaje` por Cotización y Rastreo GPS (`CotizacionesService::resolverViajeDesdeRequest` y `CoordenadasService::baseQuery`):**
  - En `CotizacionesService::resolverViajeDesdeRequest` (y delegado desde `CotizacionesController::resolverViajeDesdeRequest`): si una cotización ya cuenta con un registro en `viajes` (`viajes_cotizacion`), se reutiliza y actualiza su `tipo` (`sencillo` o `full`) y `estado = 'activo'` en lugar de crear un nuevo `Viajes::create`, limpiando registros secundarios huérfanos en `viajes_cotizacion`.
  - En `CoordenadasService::baseQuery`: el `leftJoin` a `viajes_cotizacion` / `viajes` se realiza mediante `leftJoinSub` agrupado por `cotizacion_id` y filtrado por `v.estado = 'activo'`, evitando filas duplicadas en `/coordenadas/rastrearTabs` y `/coordenadas/coordenadas/exrastrear`.
- **Spec 019 (Thin Controllers & Reusable Services) — COMPLETADA Y VALIDADA EN DEMO (SGT, MEC y MEP):**
  - Validación interactiva CRUD en vivo en sistema central (Admin `oliva@gmail.com`): Clientes, Subclientes, Proveedores, Operadores, Equipos, Préstamos, Bancos, Cuentas Bancarias y Cotizaciones (Crear, Editar, Listar, Desactivar/Reactivar y Cambios de Estatus).
  - Correcciones aplicadas y probadas en vivo:
    - Normalización de IDs (`int` vs `array`) en `CotizacionesService` para `convertirFull`, `cancelarFull`, `convertirLocalForaneo` y `revertirForaneo`.
    - Creación defensiva de carpetas en `OperadorService` (`@mkdir` solo con archivos) y validación limpia de CURP en `StoreOperadorRequest`.
    - Desacople de `password_temporal` en WhatsApp (`boardCarpos.js`, `modal_info_planeacion.blade.php`), separación limpia de número y referencia en `/viajes/mis-viajes-local`, y bypass de validación destino-ubicación en viajes locales.
    - Soporte completo de edición y guardado de subclientes en panel central (`ClientController::edit`, `routes/web.php`).
    - Blindaje en `ExternosController::getFilesProperties` con fallback y null-safety (`?->`) al cargar archivos de contenedores.
  - Reporte consolidado en `reporte_pruebas_sgt_demo.md` y 39 reportes PDF/XLSX exportados en escritorio `PRUEBAS SGT DEMO`.
- **Cambio de Proveedor y Transportista en Viaje Planeado (`CotizacionesController::singleUpdate` y `CotizacionesService::deshacerPlaneacionPorCambioProveedor`):**
  - Cancela movimientos bancarios de dinero para viaje, anula gastos unificados (`origen_legacy like 'asignacion_planeacion%'`), limpia `GastosOperadores::withoutGlobalScope('no_eliminados')`, `Coordenadas`, `DineroContenedor` y `ViaticosOperador` antes de eliminar `Asignaciones`.
- **Spec 025 (Validación de Peso de Contenedor y Sobrepeso):** En `CotizacionesService::validarYCalcularPesoSobrepeso()` y `CotizacionesController` (`> 0` y `<= 60` ton, recalcula `sobrepeso = max(peso - peso_reglamentario, 0)`), homologado en `cotizaciones.js` y `cotizaciones-local.js`.
- **Pantalla Completa en Mis Viajes (`/viajes/mis-viajes`):** Botón Pantalla completa (`toggleFullscreenMisViajes()`) y selector de densidad (`Auto (Captura)` | `Compacta` | `Normal`).
- **Cierre de Sesión Automático y Rastreo de Mapas (`CheckUserSessionTimeout.php` y `sessionAutoLogout.js`):** Sincronización multi-pestaña en `localStorage`, keep-alive cada 4 min y pausa automática en pantallas de rastreo/mapas.
- **Spec 024 (Devengo de sueldos, anticipos y deducciones):** `OperadorGastosContabilidadService` deduplica comprobaciones y registra "Dinero para viaje - [Operador]". En `/reporteria/utilidad`, `pagoOperacion = min($sueldo_viaje, $total_pagado)`. Balance General Empresa 8 cuadrado con Diferencia $0.00.
- **Spec 020 & 011 (CONAC & Cierre Contable):** Acumulación real en `3.2.1`, formatos ESF-01, ERA-02, BC-03, AC-07, LD-06 y selector `initSgtDateRange`.
- **Specs operativas activas:** Spec 002–018 (`/liquidaciones/ajustes`, `#filtro_unidad`, Reporte Personalizado CXC, Naanix GPS, Multi-equipo `requiere_unidad_gasto == 1`).
- **Respaldo de Sistema Completo:** `php artisan backup:sistema-completo` (`--sin-gps`, `--listar`, `--eliminar`).

## Decisiones arquitectónicas (y por qué)
- `PlaneacionService` y `LiquidacionesService`: centralizan la lógica de negocio e integran `OperadorGastosContabilidadService` para mantener sincronizadas las pólizas contables de sueldos, anticipos, comprobaciones y liquidaciones.
- `CotizacionesService`: unifica creación individual, en lote (`storeMultipleCotizaciones`, `storeMultipleLocal`), locales (`storeLocal`, `singleUpdateLocal`), validación de peso (`<= 60` ton), resolución única de `Viajes` (`resolverViajeDesdeRequest`) y conversiones Full / Local-Foráneo.
- Deduplicación de Viáticos y Aislamiento de Sueldos: `tipo_gasto = 'operador'` con categoría `'Sueldos de Operadores'` y `origen_legacy = 'asignacion_sueldo_operador'` se filtran fuera de otros gastos de planeación.
- Multi-equipo H00 Global: activado estrictamente con `requiere_unidad_gasto == 1`.
- Transacciones `DB::transaction()` obligatorias en todo cálculo y mutación de saldo.

## Errores a evitar
- Nunca crear un nuevo `Viajes::create` si la cotización ya tiene un `viaje_id` en `viajes_cotizacion`; reutilizar el existente y actualizar su `tipo`/`estado`.
- En `GastosService::registrar`, no enviar valores fuera del `ENUM('manual','combustibles','mantenimiento','impuestos','migracion_legacy')` en `origen_modulo`.
- En `storeLocal`, crear primero `Cotizaciones`, después su bitácora y luego `DocumCotizacion` con el ID real.
- Al renderizar PDFs con `Pdf::loadView`, asegurar compatibilidad dual de variables (`cotizacion`/`cotizaciones`) y blindar blades con operadores null-safe (`?->`) y `try-catch`.
- NUNCA alterar la firma JSON de rutas en `routes/api.php` consumidas por la app Flutter `operador_appsgt`.
- Prohibido `migrate:fresh` o `db:wipe` en cualquier entorno.

## Próximos pasos
- [ ] Ejecutar en producción `php artisan contabilidad:sync-historico 8 --force` tras desplegar cambios.
- [ ] Validar en producción telemetría continua de Naanix y retirar `SISGPSTrait.php`.
- [ ] Spec 001: Módulo de control y reportes de combustible por unidad de transporte.
