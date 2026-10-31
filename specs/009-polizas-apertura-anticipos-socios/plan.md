# Plan de Implementación: Pólizas de Apertura y Flujo de Anticipos a Socios

**Especificación:** [spec.md](./spec.md)  
**ID:** SDD-009  
**Módulo:** Contabilidad / Socios / Bancos  

---

## 1. Arquitectura y Enfoque de Solución

### A. Póliza Inicial / Saldos Iniciales
1. **Modelo y Base de Datos:**
   - La tabla `polizas` admite pólizas de apertura (`es_apertura = 1` o tipo `apertura` / folio `APE-YYYY-01`).
   - Solo se permite una póliza de apertura por empresa por ejercicio contable/fecha inicial para mantener la integridad de los saldos.
2. **Interfaz de Captura de Saldos Iniciales:**
   - En el módulo de Contabilidad se añade la pestaña / sección **"Póliza de Apertura / Saldos Iniciales"**.
   - Permite seleccionar la empresa y la fecha de corte.
   - Presenta un formulario dinámico con buscador de cuentas afectables CONAC, campos de "Debe (Cargo)" y "Haber (Abono)", con sumas en tiempo real y bloqueo si la diferencia no es estrictamente $0.00$.
3. **Agregación en Balance y Reportes:**
   - `ContabilidadService::obtenerArbolSaldos()` incluye las pólizas de apertura en el cálculo acumulado para el periodo consultado.

### B. Flujo de Adelanto y Compensación a Socios
1. **Catálogo de Cuentas:**
   - Crear la cuenta afectable `1.1.2.3.1 Anticipos a Socios` dependiente de `1.1.2.3 Deudores Diversos`.
2. **Registro de Adelanto (Sin periodo asignado):**
   - En `SociosController::storePago()`, cuando `calculo_periodo_id` es `null`:
     - Se registra el movimiento bancario de egreso.
     - Se invoca `PolizaService::resolverAsientoPorEvento('ANTICIPO_SOCIO', ...)`.
     - Póliza de Egreso: Cargo a `1.1.2.3.1 Anticipos a Socios` y Abono a `1.1.1.2 Bancos`.
3. **Compensación al Liquidar Utilidad (Con periodo asignado):**
   - Cuando se liquida un periodo para un socio:
     - El sistema calcula el total de anticipos pendientes no amortizados de ese socio.
     - Si existen anticipos pendientes:
       - Se amortiza hasta el monto de la utilidad a liquidar.
       - Asiento contable de liquidación:
         - Cargo: `2.1.1.9 Socios por Pagar` (Monto total devengado).
         - Abono: `1.1.2.3.1 Anticipos a Socios` (Monto anticipado compensado).
         - Abono: `1.1.1.2 Bancos` (Diferencia monetaria transferida, si monto liquidado > anticipo).
       - Se marca el anticipo como amortizado.

---

## 2. Fases de Implementación

- **Fase 1: Catálogo CONAC y Migración de BD**
  - Migración segura para agregar soporte de `es_apertura` en `polizas`.
  - Agregar cuenta `1.1.2.3.1 Anticipos a Socios` y eventos a `guia_contabilizadora`.
- **Fase 2: Motor de Pólizas (Apertura y Amortización)**
  - Implementar métodos de apertura y resolución de asientos en `PolizaService`.
  - Asegurar que `ContabilidadService` refleje saldos de apertura en el Balance General.
- **Fase 3: Integración en Socios y Bancos**
  - Conectar adelantos (`calculo_periodo_id = null`) con evento `ANTICIPO_SOCIO`.
  - Conectar liquidación de periodo con amortización automática de anticipos.
- **Fase 4: Interfaz de Usuario para Póliza Inicial**
  - Crear vista `/contabilidad/poliza-apertura` con tabla interactiva, buscador CONAC y balanceo en vivo.
- **Fase 5: Verificación y Pruebas Automatizadas**
  - Pruebas PHPUnit para todos los casos de uso.
  - Validación visual con Chrome DevTools.
