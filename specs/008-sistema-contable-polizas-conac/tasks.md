# Tareas de Implementación: Spec 008 — Sistema Contable, Motor de Pólizas CONAC y Guía Contabilizadora

**Estado General:** Pendiente de Aprobación del Usuario  

---

## Tareas Atómicas

### Fase 1: Base de Datos, Catálogo CONAC y Guía Contabilizadora
- [x] 1.1 Crear migración `create_cat_cuentas_contables_table` con índices, jerarquía padre-hijo (hasta nivel 5), `es_afectable` y multi-empresa (`id_empresa` nullable).
- [x] 1.2 Crear migración `create_guia_contabilizadora_table` (Matriz de Conversión) con reglas por evento, tipo de póliza, cuentas cargo/abono y soporte por empresa.
- [x] 1.3 Crear migración `create_polizas_and_movimientos_tables` con validación referencial e `id_equipo`.
- [x] 1.4 Crear migración `create_balance_general_conceptos_cuentas_table` para estructurar los renglones del balance y estado de resultados vinculados a cuentas CONAC.
- [x] 1.5 Crear migración para agregar `tipo_concepto` (`operativo`, `interes`, `traspaso`, `comision`) y `poliza_id` a `cat_bancos_cuentas_movimientos`.
- [x] 1.6 Crear Seeder `CatCuentasContablesSeeder` con estructura CONAC (niveles 1-3 globales, 4-5 con `es_afectable = 1`).
- [x] 1.7 Crear Seeder `GuiaContabilizadoraSeeder` con reglas predeterminadas para intereses, gastos, fletes y socios.
- [x] 1.8 Crear Seeder `BalanceGeneralConceptosSeeder` configurando la plantilla visual idéntica al modelo del PDF.
- [x] 1.9 Crear modelos Eloquent: `CatCuentaContable`, `GuiaContabilizadora`, `BalanceGeneralConceptoCuenta`, `Poliza`, `PolizaMovimiento`.

### Fase 2: Motor de Pólizas y Agregación Recursiva Ascendente
- [x] 2.1 Implementar `PolizaService` con validación estricta de partida doble (`total_debe === total_haber`) y rechazo de cuentas con `es_afectable === 0`.
- [x] 2.2 Implementar en `PolizaService` el método de resolución de asientos mediante la Guía Contabilizadora (`resolverPorEvento()`).
- [x] 2.3 Implementar `ContabilidadService::obtenerArbolSaldos()` con agregación recursiva ascendente de nivel 5 a nivel 1 considerando naturaleza deudora/acreedora y filtros multi-empresa.
- [x] 2.4 Crear pruebas unitarias automáticas de validación contable y agregación recursiva.

### Fase 3: Integración de Bancos y Separación de Intereses
- [x] 3.1 Actualizar formulario/modal de captura de abonos en bancos para permitir seleccionar `tipo_concepto = 'interes'`.
- [x] 3.2 Integrar hook en `BancosService` para emitir la póliza automática al registrar abono por interés (Cargo a Bancos, Abono a Intereses Ganados `4.3.1.1`).
- [x] 3.3 Visualizar en la tabla de movimientos bancarios la etiqueta e indicador de `Interés`.

### Fase 4: Integración Operativa (Gastos, CXC, CXP y Socios)
- [x] 4.1 Generar pólizas contables automáticas en `GastosService` imputadas a la unidad o gasto general.
- [x] 4.2 Generar póliza contable de provisión en `SociosService` al generar el corte mensual de utilidades.
- [x] 4.3 Generar póliza contable de derecho de cobro en Cotizaciones / Viajes.

### Fase 5: Reporte de Balance General y Estado de Resultados Multi-Empresa (Modelo TP-002)
- [x] 5.1 Implementar `ContabilidadController::balanceGeneral` permitiendo a usuarios con `es_admin = 1` seleccionar múltiples empresas o todas las empresas para consolidación.
- [x] 5.2 Añadir selector de Unidad / Camión (`id_equipo - marca`) o Consolidado usando `EquipoService`.
- [x] 5.3 Diseñar la vista en Blade con el formato visual del PDF: dos columnas balanceadas (Activo a la izquierda, Pasivo y Patrimonio a la derecha) y pie con Estado de Resultados (Ingresos, Intereses independientes, Gastos, Utilidad).
- [x] 5.4 Exportación del reporte a Excel, PDF e Impresión directa.

### Fase 6: Pruebas y Validación Final
- [x] 6.1 Pruebas funcionales con Chrome DevTools en entorno local.
- [x] 6.2 Verificación visual de cifras con el caso del camión TP-002 del PDF.
- [x] 6.3 Actualización de documentación y memoria viva (sin tocar archivo MEMORY.md por instrucción del usuario).
