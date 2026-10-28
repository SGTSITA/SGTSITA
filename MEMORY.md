# MEMORY.md — SGTSITA
Memoria viva del proyecto entre sesiones (máximo ~100 líneas).

## Estado actual
- Plataforma ERP de Transporte y Logística (Gologi Pro / SGTSITA) operativa.
- Módulos activos: Cotizaciones, Viajes, Contenedores, Planeación, Monitoreo, Gastos y Liquidaciones.
- Spec 020 (Cierre Contable de Ejercicios, Acumulación Real 3.2.1, SGT DateRangePicker y Reorganización de Navegación):
  - En `/contabilidad/balance-general`: botones "Matriz y Cuentas CONAC" y "Guía Contabilizadora" removidos; botón de acceso a "Guía Contabilizadora" integrado en `/contabilidad/configuracion`.
  - Selector de fechas del Balance General migrado a `initSgtDateRange` con accesos rápidos: Ejercicio 2025, Ejercicio 2026, Este mes, Mes anterior y Personalizado.
  - Acumulación real en `3.2.1`: Si no existen pólizas de cierre contable formal (`cierre_ejercicio`), `3.2.2` permanece estrictamente en $0.00 y `3.2.1` acumula el resultado operativo integral desde inicio de operaciones (2025 a corte) con Balance General perfectamente cuadrado ($A = P + C$, Diferencia $0.00).
  - Motor de Cierre Contable: Modal interactivo con previsualización de partidas, validación transaccional (`CierreContableRequest`), cancelación de cuentas de resultados Clase 4 (Debe) y Clase 5 (Haber), creación automática de subcuentas secuenciales en catálogo (`3.2.2.01`, `3.2.2.02`, etc.) con nombre descriptivo del ejercicio, historial de cierres y función de reversión limpia.
- Spec 011 (CONAC Pólizas & Verdad Operativa): Balance General al corte 2026-09-30 conciliado y cuadrado con Diferencia $0.00:
  - Cuentas por Cobrar (CXC `1.1.2.1%`): Al corte del 30 de septiembre 2026 muestra exactamente $297,650.00 (los 5 viajes entregados en septiembre que se cobraron el 01/10/2026); al corte actual muestra exactamente $0.00 tras la entrada de los depósitos bancarios de octubre.
  - Cuentas por Pagar (CXP `2.1.1%`): $288,857.96 exactos (reflejo fiel de los 71 gastos operativos pendientes de pago).
  - Bancos (`1.1.1.2%`): $432,750.25 (Azteca $12,775.96 + Banorte $419,974.29).
  - Activo ($1,328,195.92) = Pasivo ($288,857.96) + Patrimonio ($1,039,337.96). Cuadrado: SI (Diferencia: $0.00).
- `ContabilidadSyncHistoricoCommand`: acepta argumento posicional `{empresa?}` y opción `--empresa=`. En `syncOperacionesHistoricasPreBanco`, sincroniza la amortización de CxP y CxC al corte 30/09 contra el saldo pendiente real de gastos y cotizaciones, asegurando que Cuentas por Pagar (2.1.1) refleje con exactitud la deuda operativa pendiente ($186,857.96 en producción), manteniendo el Balance General estrictamente cuadrado con Diferencia $0.00.
- Conciliación de Ingresos por Fletes y Reporte de Utilidad:
  - En `/reporteria/utilidad`: `precioViaje` toma `$d->total` sin sumar gastos operativos extraordinarios, cuadrando al centavo con la cuenta `4.1.1.1` (Ingresos por Fletes y Servicios) del Estado de Resultados en $3,810,200.00 (Diferencia: $0.00).
- Desglose Contable de Cobros Multipoliza y Contenedores:
  - En `PolizaService::contabilizarMovimientoBancario`: depósitos bancarios originados en `CobroPago` desglosan una partida individual de abono a Clientes (`1.1.2.1.001`) por cada cotización/contenedor cobrado, evitando asociar montos globales a un solo contenedor.
  - En el Auxiliar Contable de CXC, el contenedor `MSKU1782954` muestra Cargo por $62,530.00 y Abonos por $21,280.00 (Azteca) y $41,250.00 (Banorte), saldo neto: $0.00.
- Regla Contable para Viajes Full:
  - En `CotizacionesService::contabilizarProvisionViaje`: solo se provisiona la cotización `Principal`, concatenando ambos contenedores (`CONT1 / CONT2`) en referencia y concepto. La cotización `Secundaria` no se provisiona para evitar duplicidad de ingresos y saldos falsos en CXC.
- Provisión y Cobro Operativo en tiempo real:
  - Al capturar costos y crear viaje (`CotizacionesController::storeV2` y `update`): provisión automática `PROVISION_VIAJE` (Cargo CXC `1.1.2.1.001`, Abono Ingresos `4.1.1.1`).
  - Al cobrar en CXC (`CuentasCobrarController::aplicar_pagos` -> `BancosService::registrarMovimiento`): generación automática de póliza bancaria (Cargo Bancos `1.1.1.2.xxx`, Abono CXC `1.1.2.1.001`), cancelando de inmediato la provisión.
- `/reporteria/viajes`: columna `peso_contenedor` configurada con `agNumberColumnFilter` y `comparator` numérico (filtros de Mayor que, Menor que, Igual a, En rango en español) y habilitada en exportaciones de Excel y PDF.
- Formatos Oficiales CONAC & Exportación: Vistas previas dinámicas en `/contabilidad/balance-general` para Balance General (ESF-01), Estado de Resultados (ERA-02), Balanza de Comprobación (BC-03), Auxiliar Contable / Libro Mayor por Cuentas (AC-07) con desglose de pólizas por cuenta afectable y saldos acumulados, y Libro Diario de Pólizas (LD-06) con partidas detalladas para conciliación. Soporte completo de exportación a PDF (DomPDF institucional apaisado con firmas) y Excel (Maatwebsite\Excel con estilos y sumas iguales).
- Spec 002: Integración de movimientos y transferencias bancarias en gastos (Draft Autosave).
- Spec 003: Refactorización GPS SIS hacia API REST Naanix (`MClientesExternosJSPY`).
- Spec 004: Sincronización integral Gastos de viaje, Pagos bancarios y App Móvil Admin.
- Spec 005 (demo): Multi-equipo en H00 Global (`id_empresa=28`). Filtro universal de Unidad en reportes y precarga al editar gastos.
- Spec 005 (móvil): Restauración automática de gastos soft-deleted al re-sincronizar desde App Móvil Admin.
- Spec 011 (CONAC Pólizas & Verdad Operativa): Bancos en Balance General CONAC sincronizados directamente de `cat_banco_cuentas_movimientos` (/bancos/bancosv2) alcanzando exactamente $721,400.25 (Azteca $119,175.96 + Banorte $602,224.29). Subcuenta Banorte `1.1.1.2.005` agregada. Préstamos a operadores (`1.1.2.3.001`) conciliados en exactamente $36,000.00 (saldo insoluto real de Oscar de Jesús Moreno) tras incorporar amortizaciones de liquidaciones (`PagoPrestamo`) y contrapartida en `1.1.2.3.004` (Otros Deudores Diversos). Subcuenta `1.1.2.3.004` evita sobregiro en Gastos por Comprobar y corrige duplicidad en el Activo. Balance General perfectamente cuadrado a 0.00.
- Balance General Drill-down: Modal interactivo auxiliar por concepto (`balance-general/detalle-concepto`) con resolución automática y masiva del número de contenedor (`DocumCotizacion` -> `num_contenedor`) cruzando cotizaciones, asignaciones, gastos operativos y amortizaciones; badge visual distintivo y búsqueda por contenedor en tiempo real.
- Formatos Oficiales CONAC & Exportación: Vistas previas dinámicas en `/contabilidad/balance-general` para Balance General (ESF-01), Estado de Resultados (ERA-02) y Balanza de Comprobación (BC-03). Botón "Imprimir" eliminado; incorporadas exportaciones nativas en PDF (DomPDF institucional con CSS2.1, orientación automática, 3 firmas y protesta de ley) y Excel (Maatwebsite\Excel con estilos y auto-ajuste). Balanza de comprobación en estricto equilibrio en Sumas Iguales con filtro reactivo de cuentas.
- Spec 006: Resumen mensual de utilidades, desglose drill-down y selector rápido en `/socios`.
- Spec 007: Visualización de gastos indirectos de periodo sin contenedor en `/reporteria/utilidad`.
- Spec 010: Control universal de calendario DateRangePicker dinámico en módulo central SGT (`window.initSgtDateRange`, años delimitados por BD de cotizaciones y año actual + 1).
- Spec 015: Gasto Burrero Vacío (GBV01) opcional al finalizar viaje (`PlaneacionController::finalizarViaje`) para evitar bloqueos por viajes cancelados o truncados tempranamente por daños físicos.
- Fallback Fecha Aplicación Dinero Viaje: En `/planeaciones/viajes/editar/{idCotizacion}`, si `dinero_contenedor` no tiene registro previo, precarga automáticamente `$asignacion->fecha_pago_salida` o `$asignacion->fecha_inicio`.
- API REST activa para Flutter `operador_appsgt` y microservicio WhatsApp (`whatsapp-bot/`).
- Stack: Laravel 9 (PHP 8.1/8.2) + MySQL 8 + Docker + Metronic UI.

## Decisiones (y por qué)
- Empresa 8 (H10-OLIVA): egresos operativos bancarios cancelan pasivo en `2.1.1.2.001` (Proveedores CXP) y cobros acreditan `1.1.2.1.001` (Clientes CXC), eliminando duplicidades contra cuentas de resultados (5.1.x) o deudores diversos.
- Póliza de Ajuste Histórico Pre-Bancario (`syncOperacionesHistoricasPreBanco`): amortiza los saldos devengados en 2025 concluidos antes de la entrada en vigor del registro bancario (/bancos/bancosv2), equilibrando contra Patrimonio / Resultados de Ejercicios Anteriores (`3.2.2.001`).
- `ContabilidadService`: el concepto `3.2.2` suma acumulativamente los resultados netos de ejercicios previos (`ingresosPrevios - gastosPrevios`) sin condicionarse a saldo cero, garantizando que el patrimonio refleje la utilidad histórica no cerrada.
- `BancosService::registrarMovimiento`: genera póliza contable automática para cualquier movimiento operativo bancario en tiempo real.
- `/reporteria/viajes`: `peso_contenedor` concatena el peso del secundario en viajes tipo Full (`27.23 / 25.10`) para mantener la misma convención de visualización que `num_contenedor`.
- Burrero Vacío (GBV01) opcional en Planeación Elemental: Se retiró la validación obligatoria de `GBV01` en `PlaneacionController::finalizarViaje` para permitir finalizar viajes propios interrumpidos por siniestros, fallas o daños físicos antes de llegar a destino. La opción permanece en catálogos y formularios de gastos para viajes que sí incurran en ella.
- Fallback en `FechaAplicacionDinero` de `edit.blade.php`: Viajes creados por planeación elemental o app móvil no insertan registro en la tabla auxiliar `dinero_contenedor`. Al editar, el formulario precarga `$asignacion->fecha_pago_salida` o `$asignacion->fecha_inicio` en lugar de dejar el campo en blanco y bloquear el guardado.
- Multi-equipo H00 Global (`id_empresa=28`): coexisten tractocamiones activos (AB-100, AP-002, etc.). Cada movimiento se imputa a su unidad y los reportes filtran por `asignaciones.id_camion` o `gastos.id_equipo`.
- Transacciones `DB::transaction`: obligatorias en cotizaciones, viajes, gastos y movimientos bancarios para evitar inconsistencias.
- Trait Naanix independiente (`app/Traits/NaanixGPSTrait.php`): preserva `SISGPSTrait.php` intacto como respaldo/fallback.
- Caché de Tokens Naanix (50 min) y Llave UTC dinámica: previene saturación de peticiones de autenticación en el cron `rastreo:intervalConfig`.
- Consulta masiva por lote (`ObtenerPosicionActualGeneral`): reduce latencia de sincronización de ~40s a < 1s.
- `sincronizarGastoConBancos()` en `GastosService`: unifica propagación en cascada de importes hacia `gasto_pagos`, `cat_bancos_cuentas_movimientos` y saldo en `bancos`.
- `meses_resumen` en `SociosService`: agrupa viajes (bruta) y gastos indirectos imputados por mes, cuadrando Utilidad Mensual.
- `linkedCalendars: false` y `showDropdowns: true` en `daterangepicker`: desacopla calendarios y permite seleccionar mes/año sin desbordar.
- `window.initSgtDateRange()` y `/sgt/fechas/periodos-cotizaciones`: calcula dinámicamente `ejercicio_ini_bd` (BD `cotizaciones`) y `ejercicio_fin_bd` (`max(max_bd, anio_actual + 1)`). Elimina años inválidos (2001) y previene mantenimiento anual. En acciones rápidas solo muestra: Últimos 7 días, Últimos 30 días, Este mes, Mes anterior y Personalizado (excluyendo Hoy, Ayer y años estáticos del menú rápido). Soportado tanto en `layouts.app` como en `layouts.usuario_externo` para vistas de clientes (`/viajes/mis-viajes`, `/viajes/documents`, reportería externa).
- `/reporteria/utilidad`: botón "Ver Gastos" muestra gastos indirectos del periodo si no hay viajes o contenedor seleccionado.
- `EquipoService` y accesor `$equipo->texto_select`: unifican consultas (`getTractocamiones()`) y estandarizan el formato visual de unidades a `id_equipo - marca` en todos los selectores de gastos y reportería (Principio 7 Constitución).

## Aprendizajes y errores a evitar
- NUNCA modificar la respuesta JSON de endpoints en `routes/api.php` sin verificar el impacto en Flutter (`operador_appsgt`).
- Prohibido `migrate:fresh` o `db:wipe` en cualquier entorno.
- En `gastos`, `saldo_pendiente` es un accesor Eloquent (`monto_total - monto_pagado`); no debe llamarse como columna directa en SQL `sum('saldo_pendiente')`.
- En CLI Artisan, `auth()->user()` es nulo; autenticar explícitamente al usuario de la empresa correspondiente si el servicio filtra por `auth()->user()->id_empresa`.
- En selectores de unidad/equipo, mostrar siempre `id_equipo - marca` (vía `$equipo->texto_select`) para consistencia en todo el sistema.
- En Naanix REST, la llave debe calcularse estrictamente en UTC (`UtcNow.Hour + UtcNow.Day + IdCliente`).
- En catálogos y reportes de imputación a tracto, filtrar siempre `where('tipo', 'Tractos / Camiones')` en `Equipo`.
- Si un gasto previo estaba en papelera, `GastosService::registrar()` debe invocar obligatoriamente `$gasto->restore()`.
- En `ranges` de daterangepicker, claves con expresiones dinámicas requieren corchetes `[expr]: [...]`.
- Si scripts compartidos (como `cotizacion-documentacion.js`) se usan en `layouts.usuario_externo`, asegurarse de que las dependencias (`sgt-daterange.js`, `moment`, `daterangepicker`) estén inyectadas en ese layout.

## Próximos pasos
- [ ] Validar en producción telemetría continua de Naanix y retirar `SISGPSTrait.php`.
- [ ] Spec 001: Módulo de control y reportes de combustible por unidad de transporte.
- [ ] Ejecutar `php artisan fix:gasto-fscu8106019` y `fix:gasto-msmu7292298` en producción.
