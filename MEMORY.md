# MEMORY.md — SGTSITA
Memoria viva del proyecto entre sesiones (máximo ~100 líneas).

## Estado actual
- Plataforma ERP de Transporte y Logística (Gologi Pro / SGTSITA) operativa.
- Módulos activos: Cotizaciones, Viajes, Contenedores, Planeación, Monitoreo, Gastos y Liquidaciones.
- Spec 011 (CONAC Pólizas & Verdad Operativa): Balance General al corte 2026-09-30 conciliado y cuadrado con Diferencia $0.00:
  - Cuentas por Cobrar (CXC `1.1.2.1%`): $0.00 exactos (reflejo de `/cuentas/cobrar` donde no existen cobros pendientes).
  - Cuentas por Pagar (CXP `2.1.1%`): $288,857.96 exactos (reflejo fiel de los 71 gastos operativos pendientes de pago).
  - Bancos (`1.1.1.2%`): $432,750.25 (Azteca $12,775.96 + Banorte $419,974.29).
  - Activo ($1,030,545.92) = Pasivo ($288,857.96) + Patrimonio ($741,687.96). Cuadrado: SI.
- Provisión y Cobro Operativo en tiempo real:
  - Al capturar costos y crear viaje (`CotizacionesController::storeV2` y `update`): provisión automática `PROVISION_VIAJE` (Cargo CXC `1.1.2.1.001`, Abono Ingresos `4.1.1.1`).
  - Al cobrar en CXC (`CuentasCobrarController::aplicar_pagos` -> `BancosService::registrarMovimiento`): generación automática de póliza bancaria (Cargo Bancos `1.1.1.2.xxx`, Abono CXC `1.1.2.1.001`), cancelando de inmediato la provisión.
- `/reporteria/viajes`: columna `peso_contenedor` configurada con `agNumberColumnFilter` y `comparator` numérico (filtros de Mayor que, Menor que, Igual a, En rango en español) y habilitada en exportaciones de Excel y PDF.
- Formatos Oficiales CONAC & Exportación: Vistas previas dinámicas en `/contabilidad/balance-general` para Balance General (ESF-01), Estado de Resultados (ERA-02) y Balanza de Comprobación (BC-03) con exportaciones PDF y Excel.
- API REST activa para Flutter `operador_appsgt` y microservicio WhatsApp (`whatsapp-bot/`).
- Stack: Laravel 9 (PHP 8.1/8.2) + MySQL 8 + Docker + Metronic UI.

## Decisiones (y por qué)
- Empresa 8 (H10-OLIVA): egresos operativos bancarios cancelan pasivo en `2.1.1.2.001` (Proveedores CXP) y cobros acreditan `1.1.2.1.001` (Clientes CXC), eliminando duplicidades contra cuentas de resultados (5.1.x) o deudores diversos.
- Póliza de Ajuste Histórico Pre-Bancario (`syncOperacionesHistoricasPreBanco`): amortiza los saldos devengados en 2025 concluidos antes de la entrada en vigor del registro bancario (/bancos/bancosv2), equilibrando contra Patrimonio / Resultados de Ejercicios Anteriores (`3.2.2.001`).
- `ContabilidadService`: el concepto `3.2.2` suma acumulativamente los resultados netos de ejercicios previos (`ingresosPrevios - gastosPrevios`) sin condicionarse a saldo cero, garantizando que el patrimonio refleje la utilidad histórica no cerrada.
- `BancosService::registrarMovimiento`: genera póliza contable automática para cualquier movimiento operativo bancario en tiempo real.
- `/reporteria/viajes`: `peso_contenedor` concatena el peso del secundario en viajes tipo Full (`27.23 / 25.10`) para mantener la misma convención de visualización que `num_contenedor`.

## Aprendizajes y errores a evitar
- NUNCA modificar la respuesta JSON de endpoints en `routes/api.php` sin verificar el impacto en Flutter (`operador_appsgt`).
- Prohibido `migrate:fresh` o `db:wipe` en cualquier entorno.
- En `gastos`, `saldo_pendiente` es un accesor Eloquent (`monto_total - monto_pagado`); no debe llamarse como columna directa en SQL `sum('saldo_pendiente')`.
- En CLI Artisan, `auth()->user()` es nulo; autenticar explícitamente al usuario de la empresa correspondiente si el servicio filtra por `auth()->user()->id_empresa`.

## Próximos pasos
- [ ] Validar en producción telemetría continua de Naanix y retirar `SISGPSTrait.php`.
- [ ] Spec 001: Módulo de control y reportes de combustible por unidad de transporte.
