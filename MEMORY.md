# MEMORY.md — SGTSITA
Memoria viva del proyecto entre sesiones (máximo ~100 líneas).

## Estado actual
- Plataforma ERP de Transporte y Logística (Gologi Pro / SGTSITA) operativa.
- Módulos activos: Cotizaciones, Viajes, Contenedores, Planeación, Monitoreo, Gastos y Liquidaciones.
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
