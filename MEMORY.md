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
- Spec 018 (Complementos y Ajustes de Liquidación): Submódulo `/liquidaciones/ajustes` ("Ajustes de Liquidación") en el menú lateral. Permite registrar liquidaciones complementarias vinculadas a una liquidación previa sin modificar el histórico cerrado. Captura dinámica de Percepciones (sueldo, viáticos, reembolsos, bonos) y Deducciones (abonos a préstamos/adelantos con amortización en `pagos_prestamos`, descuentos). Cálculo de neto en tiempo real con salida y validación bancaria vía `BancosService::registrarMovimiento` (polimorfismo a `LiquidacionAjuste`). Generación de comprobante oficial en PDF y listado en AG Grid.
- Spec 017 (Filtro de Unidades en Historial de Liquidaciones - Empresa Global): En `/liquidaciones/historial`, selector reactivo de tractocamiones (`#filtro_unidad`) con `EquipoService::getTractocamiones()`. Búsqueda no destructiva con `whereExists` para preservar la liquidación completa aunque contenga viajes con unidades distintas.
- Spec 016: Reporte Personalizado de Cuentas por Cobrar (CXC) exportable en PDF y Excel con layout oficial "ESTADO DE CUENTA - TRANSPORTE". Valores vacíos monetarios se muestran como $0; columna Sobrepeso mapeada a "Total Sobre Peso Viaje" con cálculo de precisión idéntico al formulario de edición ((peso - reglamentario) * precio_sp, e.g. HAMU4974121 = $10,152.00); columna de observaciones conectada a `observaciones_edo_cuenta`. Comando `php artisan viajes:corregir-sobrepeso-costos` (`CorregirSobrepesoViajesCostosCommand.php`) para recalcular y corregir en BD (`viajes_costos`) las diferencias de sobrepeso por decimales reales. `ViajesCostosService::procesarSobrepeso` actualizado para guardar siempre la diferencia decimal exacta.
- Spec 011 (CONAC Pólizas & Verdad Operativa): Balance General conciliado y cuadrado con Diferencia $0.00. Bancos sincronizados de `cat_banco_cuentas_movimientos`. Subcuenta Banorte `1.1.1.2.005` y `1.1.2.3.004`.
- Stack: Laravel 9 (PHP 8.1/8.2) + MySQL 8 + Docker + Metronic UI.
- Respaldo de Sistema Completo: Comando Artisan `backup:sistema-completo` (`GenerarRespaldoSistemaCompletoCommand.php`) para generar paquete `.tar.gz` con código fuente + documentos físicos (`public/cotizaciones`, `public/uploads`, `public/equipos`, `public/operador`) + volcado SQL. Excluye `.env`, `.env.*`, `vendor/`, `node_modules/`, cachés y `.git/`. Soporta `--sin-gps`, `--listar` y eliminación con `--eliminar` (o `--archivo=...`). Flag `--skip-ssl` en `mysqldump` para compatibilidad local Docker-Host y servidores de producción.

## Decisiones (y por qué)
- `LiquidacionesAjustesController`: los ajustes se modelan como entidades independientes (`liquidacion_ajustes` y `liquidacion_ajustes_detalles`) con FK a la liquidación original, evitando reabrir o alterar liquidaciones cerradas y protegiendo la trazabilidad contable y bancaria previa.
- Integración Bancaria Polimórfica: los ajustes con importe neto positivo registran egreso en `cat_bancos_cuentas_movimientos` con `referenciaable_type = LiquidacionAjuste::class` y validación de saldo disponible mediante `BancosService::validarsaldoparacargo`.
- Amortización de Deducciones: al vincular una deducción a un préstamo/adelanto activo, se actualiza `prestamos.saldo_actual` y se genera `PagoPrestamo` con `tipo_origen = 'ajuste_liquidacion'`.
- `/liquidaciones/historial`: filtro de unidad evalúa existencia con `whereExists` en la consulta principal de `Liquidaciones` para no desmembrar liquidaciones multi-equipo.
- `EquipoService` y accesor `$equipo->texto_select`: unifican consultas y formato `id_equipo - marca` en todo el sistema.
- Transacciones `DB::transaction`: obligatorias en cotizaciones, viajes, gastos, liquidaciones, ajustes y movimientos bancarios.

## Aprendizajes y errores a evitar
- NUNCA modificar la respuesta JSON de endpoints en `routes/api.php` sin verificar el impacto en Flutter (`operador_appsgt`).
- Prohibido `migrate:fresh` o `db:wipe` en cualquier entorno.
- En Docker vs Host en Windows: para comandos Artisan que interactúan con MySQL o requieren conexión de red del contenedor (`host.docker.internal`), ejecutar `docker compose exec app php artisan <comando>`.
- En liquidaciones y ajustes, validar siempre que los montos de conceptos sean mayores a cero y que el egreso neto cuente con cuenta bancaria asignada.

## Próximos pasos
- [ ] Validar en producción telemetría continua de Naanix y retirar `SISGPSTrait.php`.
- [ ] Spec 001: Módulo de control y reportes de combustible por unidad de transporte.
- [ ] Ejecutar `php artisan fix:gasto-fscu8106019` y `fix:gasto-msmu7292298` en producción.
