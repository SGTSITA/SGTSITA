# MEMORY.md — SGTSITA
Memoria viva del proyecto entre sesiones (máximo ~60 líneas).

## Estado actual
- Plataforma ERP SGTSITA (Gologi Pro) 100% operativa (Metronic UI, Laravel 9, MySQL 8, Docker).
- **Spec 019 (Thin Controllers & Reusable Services) — COMPLETADA AL 100% (Fases 1 a 6):**
  * **Fase 1 (Catálogos):** `ClienteService`, `ProveedorService`, `OperadorService`, `PrestamoService`, `EquipoService` + FormRequests.
  * **Fase 2 (Tesorería & CxC/CxP):** `BancosService`, `GastosService`, `CuentasCobrarService`, `CuentasPagarService`.
  * **Fase 3 (Liquidaciones & Ajustes):** `LiquidacionesService` centraliza balance real, amortizaciones, bancos y `OperadorGastosContabilidadService`.
  * **Fase 4 (Operaciones & Planeación):** `CotizacionesService` y `PlaneacionService` (`PlaneacionController` y `CotizacionesLocalesController` delgados).
  * **Fase 5 (Portales & Monitoreo):** `CostosViajeMepService`, `MepService`, `CoordenadasService`.
  * **Fase 6 (Validación):** Sintaxis verificada (`php -l`), cachés limpias y rutas resueltas. API móvil protegida sin cambios de contrato.
- **Spec 025 (Validación de Peso de Contenedor y Sobrepeso):** En `CotizacionesService::validarYCalcularPesoSobrepeso()` y `CotizacionesController` (`> 0` y `<= 60` ton, recalcula `sobrepeso = max(peso - peso_reglamentario, 0)`), homologado en `cotizaciones.js` y `cotizaciones-local.js`.
- **Pantalla Completa en Mis Viajes (`/viajes/mis-viajes`):** Botón Pantalla completa (`toggleFullscreenMisViajes()`) y selector de densidad (`Auto (Captura)` | `Compacta` | `Normal`).
- **Cierre de Sesión Automático y Rastreo de Mapas (`CheckUserSessionTimeout.php` y `sessionAutoLogout.js`):** Sincronización multi-pestaña en `localStorage`, keep-alive cada 4 min y pausa automática en pantallas de rastreo/mapas.
- **Spec 024 (Devengo de sueldos, anticipos y deducciones):** `OperadorGastosContabilidadService` deduplica comprobaciones y registra "Dinero para viaje - [Operador]". En `/reporteria/utilidad`, `pagoOperacion = min($sueldo_viaje, $total_pagado)`. Balance General Empresa 8 cuadrado con Diferencia $0.00.
- **Spec 020 & 011 (CONAC & Cierre Contable):** Acumulación real en `3.2.1`, formatos ESF-01, ERA-02, BC-03, AC-07, LD-06 y selector `initSgtDateRange`.
- **Specs operativas activas:** Spec 002–018 (`/liquidaciones/ajustes`, `#filtro_unidad`, Reporte Personalizado CXC, Naanix GPS, Multi-equipo `requiere_unidad_gasto == 1`).
- **Respaldo de Sistema Completo:** `php artisan backup:sistema-completo` (`--sin-gps`, `--listar`, `--eliminar`).

## Decisiones arquitectónicas (y por qué)
- `PlaneacionService` y `LiquidacionesService`: centralizan la lógica de negocio e integran `OperadorGastosContabilidadService` para mantener sincronizadas las pólizas contables de sueldos, anticipos, comprobaciones y liquidaciones.
- `CotizacionesService`: unifica creación individual, en lote (`storeMultipleCotizaciones`, `storeMultipleLocal`), locales (`storeLocal`, `singleUpdateLocal`), validación de peso (`<= 60` ton) y conversiones Full / Local-Foráneo.
- Deduplicación de Viáticos y Aislamiento de Sueldos: `tipo_gasto = 'operador'` con categoría `'Sueldos de Operadores'` y `origen_legacy = 'asignacion_sueldo_operador'` se filtran fuera de otros gastos de planeación.
- Multi-equipo H00 Global: activado estrictamente con `requiere_unidad_gasto == 1`.
- Transacciones `DB::transaction()` obligatorias en todo cálculo y mutación de saldo.

## Errores a evitar
- En `storeLocal`, crear primero `Cotizaciones`, después su bitácora y luego `DocumCotizacion` con el ID real; devolver `id`, `cotizacion_id`, `docum_cotizacion_id` y `num_contenedor`.
- En ediciones de viajes locales, persistir transportista en `id_proveedor` y `transportista_local`, y empresa en `id_empresa`/`empresa_local` (sincronizando `docum_cotizacion.id_empresa`).
- Para adjuntos en FileUploader, obtener archivo con `getInstance(input).getChoosedFiles()`.
- Al renderizar PDFs con `Pdf::loadView`, asegurar compatibilidad dual de variables (`cotizacion`/`cotizaciones`) y blindar blades con operadores null-safe (`?->`) y `try-catch`.
- NUNCA alterar la firma JSON de rutas en `routes/api.php` consumidas por la app Flutter `operador_appsgt`.
- Prohibido `migrate:fresh` o `db:wipe` en cualquier entorno.

## Próximos pasos
- [ ] Ejecutar en producción `php artisan contabilidad:sync-historico 8 --force` tras desplegar cambios.
- [ ] Validar en producción telemetría continua de Naanix y retirar `SISGPSTrait.php`.
- [ ] Spec 001: Módulo de control y reportes de combustible por unidad de transporte.
