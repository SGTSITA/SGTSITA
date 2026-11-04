# MEMORY.md — SGTSITA
Memoria viva del proyecto entre sesiones (máximo ~100 líneas).

## Estado actual
- Spec 024 (Devengo de sueldos, anticipos de operadores y deducciones de liquidación):
  - `OperadorGastosContabilidadService`: deduplicación automática de comprobaciones (`limpiarComprobacionesDuplicadas()`) que elimina viáticos redundantes provenientes de `viaticos_operadores` cuando ya existen en `gastos_operadores` (75 duplicados eliminados en Empresa 8).
  - Sueldos y anticipos segregados: no se muestran en `/planeaciones/viajes/editar/{id}` ni en listados por cotización para evitar confusión o borrado accidental. Accesibles exclusivamente desde `/gastos` mediante el filtro de tipo "Sueldos" o la categoría "Sueldos de Operadores".
  - Conciliación de Sueldo vs Dinero Sin Justificar en `/reporteria/utilidad`:
    - Cuando existen anticipos de viaje no comprobados (`dineroViajeSinJustificar`), la columna `pagoOperacion` refleja el neto pagado de la liquidación (`LiquidacionContenedor.total_pagado`, ej. $4,700.00) y `dineroViajeSinJustificar` muestra el saldo retenido ($300.00).
    - Caso Contenedor `HMMU4301580-JHK16` (Cotización 2263): Gastos de viaje corregidos a $17,106.26 (sin duplicidad de tránsito y talacha), Pago Operación $4,700.00, Sin Justificar $300.00, Utilidad $31,173.74.
  - Catálogo Balance General: prefijo `1.1.1.1%` configurado como `EFECTIVO Y CAJA`.
  - Balance General Empresa 8 (Oliva) al 2026-09-30: Activo $1,938,007.88 = Pasivo $911,327.96 + Patrimonio $1,026,679.92. Bancos en positivo $432,750.25 (Azteca + Banorte). Cuadrado estrictamente con Diferencia $0.00.
  - Nota Consolidado Multi-empresa: El déficit bancario en la vista consolidada global al 30/09 se debe a que empresas secundarias (2, 6, 9, 27) tienen pagos de gastos cargados a bancos sin pólizas de apertura ni depósitos registrados. Al filtrar por Empresa 8 (Oliva), Bancos es estrictamente positivo ($432,750.25).
- Plataforma ERP de Transporte y Logística (Gologi Pro / SGTSITA) operativa.
- Módulos activos: Cotizaciones, Viajes, Contenedores, Planeación, Monitoreo, Gastos y Liquidaciones.
- Spec 020 (Cierre Contable de Ejercicios, Acumulación Real 3.2.1, SGT DateRangePicker y Reorganización de Navegación):
  - En `/contabilidad/balance-general`: botones "Matriz y Cuentas CONAC" y "Guía Contabilizadora" removidos; botón de acceso a "Guía Contabilizadora" integrado en `/contabilidad/configuracion`.
  - Selector de fechas del Balance General migrado a `initSgtDateRange` con accesos rápidos: Ejercicio 2025, Ejercicio 2026, Este mes, Mes anterior y Personalizado.
  - Acumulación real en `3.2.1`: Si no existen pólizas de cierre contable formal (`cierre_ejercicio`), `3.2.2` permanece estrictamente en $0.00 y `3.2.1` acumula el resultado operativo integral desde inicio de operaciones con Balance General cuadrado ($A = P + C$, Diferencia $0.00).
- Spec 011 (CONAC Pólizas & Verdad Operativa): Balance General al corte 2026-09-30 conciliado y cuadrado con Diferencia $0.00.
  - Cuentas por Cobrar (CXC `1.1.2.1%`): Al corte 30/09 muestra exactamente $297,650.00.
  - Cuentas por Pagar (CXP `2.1.1%`): $594,357.96 exactos reflejando deuda operativa y gastos devengados.
  - Bancos (`1.1.1.2%`): $432,750.25 (Azteca $12,775.96 + Banorte $419,974.29).
  - Activo ($1,938,007.88) = Pasivo ($911,327.96) + Patrimonio ($1,026,679.92). Cuadrado: SI (Diferencia: $0.00).

## Decisiones (y por qué)
- Deduplicación de Viáticos: Al sincronizar comprobaciones desde `viaticos_operadores`, si ya existe un gasto registrado con `origen_legacy = 'gastos_operadores'` para la misma cotización y monto, se omite la creación de duplicados y se purgan las pólizas y registros redundantes para evitar duplicar costos en utilidades.
- Reconciliación Sueldo / Anticipo No Justificado: Cuando un operador no comprueba la totalidad de un anticipo y la diferencia se deduce en su liquidación (`LiquidacionContenedor`), la reportería muestra el sueldo neto efectivamente pagado y el anticipo retenido por separado, reflejando el flujo de efectivo real sin inflar el costo global.
- Aislamiento de Sueldos en UI: Los sueldos de operadores se protegen de modificaciones incidentales en la planeación y cotizaciones; su consulta y filtro se centraliza en el módulo `/gastos`.
- Transacciones `DB::transaction`: obligatorias en cotizaciones, viajes, gastos, liquidaciones, ajustes y movimientos bancarios.

## Aprendizajes y errores a evitar
- NUNCA modificar la respuesta JSON de endpoints en `routes/api.php` sin verificar el impacto en Flutter (`operador_appsgt`).
- Prohibido `migrate:fresh` o `db:wipe` en cualquier entorno.
- En Docker vs Host en Windows: para comandos Artisan que interactúan con MySQL o requieren conexión de red del contenedor (`host.docker.internal`), ejecutar `docker compose exec app php artisan <comando>`.
- `tipo_gasto` en tabla `gastos` es un ENUM de MySQL (`'general','periodo','unidad','viaje','cotizacion','contenedor','operador'`). Para sueldos se mantiene `tipo_gasto = 'operador'` con categoría `'Sueldos de Operadores'` y `origen_legacy = 'asignacion_sueldo_operador'`, filtrando a nivel de servicio/vista para no quebrar la estructura de base de datos.
- En el Balance General, el "Consolidado Global" suma todas las empresas del sistema. Si empresas inactivas o secundarias tienen movimientos sin aperturas bancarias, el saldo de bancos global puede verse afectado negativamente; se debe revisar por empresa individual (ej. Empresa 8, Oliva).

## Próximos pasos
- [ ] Ejecutar en producción `php artisan contabilidad:sync-historico 8 --force` tras desplegar cambios.
- [ ] Validar en producción telemetría continua de Naanix y retirar `SISGPSTrait.php`.
- [ ] Spec 001: Módulo de control y reportes de combustible por unidad de transporte.
