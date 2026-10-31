# [SDD-009] Pólizas de Apertura (Saldos Iniciales) y Flujo de Adelanto/Compensación de Anticipos a Socios

## 1. Taxonomía de la Especificación
- **ID:** SDD-009
- **Módulo:** Contabilidad / Socios / Bancos
- **Tipo:** Feature & Refactor Contable
- **Prioridad:** Alta
- **Autor / Solicitante:** JoseMXN / Desarrollador
- **Estado:** Propuesta para Aprobación

---

## 2. Descripción y Contexto

Actualmente el sistema SGTSITA cuenta con procesos operando en múltiples módulos (Bancos, Viajes, Gastos, Socios). Al incorporar el nuevo sistema contable formal por partida doble bajo lineamientos CONAC, surgen dos necesidades operativas críticas:

1. **Póliza Inicial / Saldos Iniciales:**
   Al arrancar la contabilidad en una empresa que ya cuenta con histórico, no es factible reconstruir desde el inicio de los tiempos cada movimiento. Se requiere registrar una **Póliza de Apertura (Saldos Iniciales)** a una fecha de corte determinada. Esta póliza establece los saldos iniciales de Activo, Pasivo y Capital con partida doble estricta ($\sum \text{Cargos} = \sum \text{Abonos}$), nutriendo a partir de esa fecha los saldos acumulados de las cuentas CONAC para el Balance General y futuros estados financieros.

2. **Flujo de Adelantos a Socios (Anticipos antes de cálculo de operatividad):**
   En la operación real, a los socios frecuentemente se les entregan adelantos o anticipos antes de calcular el corte o reporte de utilidad del periodo. 
   - **Momento del Adelanto:** El dinero sale de Bancos, pero aún no existe gasto ni reparto reconocido. Debe cargarse a una cuenta transitoria del Activo Circulante: **`1.1.2.3.1 Anticipos a Socios`** (Deudores Diversos).
   - **Momento de la Liquidación / Reporte de Utilidad:** Al calcularse y liquidarse la utilidad del socio, la provisión reconoce el reparto en pasivo (**`2.1.1.9 Socios por Pagar`**). Al liquidar, se debe **amortizar y cancelar** la cuenta del activo contra el pasivo:
     - Cargo a `2.1.1.9 Socios por Pagar`
     - Abono a `1.1.2.3.1 Anticipos a Socios` (se salda el anticipo)
     - Abono a `1.1.1.2 Bancos` (únicamente si existe saldo diferencial a favor del socio).

---

## 3. Criterios de Aceptación (Definition of Done - DoD)

### Módulo 1: Póliza Inicial / Saldos Iniciales
- [ ] **CU-01 (Captura de Póliza Inicial con Partida Doble):**
  - **Dado:** El usuario administrador en la pantalla de Póliza Inicial de una empresa seleccionada.
  - **Cuando:** Captura o importa saldos iniciales para cuentas afectables (`es_afectable = 1`) y las sumas de Cargo y Abono cuadran ($\Delta = 0.00$).
  - **Entonces:** Se genera/actualiza la póliza con tipo `apertura` (folio `APE-YYYY-01`), vinculada a la empresa y fecha de corte. Si las sumas no cuadran, se bloquea el guardado con mensaje de error explícito.
- [ ] **CU-02 (Impacto en Balances y Reportes):**
  - **Dado:** Una póliza de apertura registrada al 01/01/2024.
  - **Cuando:** Se consulta el Balance General o Balanza con fecha posterior.
  - **Entonces:** Los saldos de apertura se suman recursivamente en el árbol CONAC, reflejando correctamente los saldos iniciales en Activo, Pasivo y Capital.

### Módulo 2: Adelantos a Socios y Cancelación contra Utilidades
- [ ] **CU-03 (Registro de Adelanto a Socio):**
  - **Dado:** El usuario registra un abono a socio sin asignar periodo de corte (`calculo_periodo_id = null`).
  - **Cuando:** Se procesa el pago.
  - **Entonces:** 
    1. Se registra el movimiento de egreso en `cat_bancos_cuentas_movimientos`.
    2. Se genera póliza de egreso por evento `ANTICIPO_SOCIO`:
       - **Cargo:** `1.1.2.3.1 Anticipos a Socios` (Activo Circulante).
       - **Abono:** `1.1.1.2 Bancos`.
    3. En el Balance General, el monto se refleja en el Activo Circulante bajo "Deudores Diversos / Anticipo a Socios".
- [ ] **CU-04 (Compensación y Amortización al Liquidar Periodo):**
  - **Dado:** Un socio que tiene \$10,000 acumulados en anticipos y un corte de utilidad donde le corresponden \$15,000.
  - **Cuando:** Se realiza la liquidación del periodo.
  - **Entonces:**
    1. Se genera la póliza de liquidación amortizando el anticipo:
       - **Cargo:** `2.1.1.9 Socios por Pagar` (\$15,000).
       - **Abono:** `1.1.2.3.1 Anticipos a Socios` (\$10,000 cancelando el saldo activo).
       - **Abono:** `1.1.1.2 Bancos` (\$5,000 por el remanente transferido).
    2. La cuenta `1.1.2.3.1 Anticipos a Socios` queda en \$0.00 para ese socio, y el banco solo desembolsa la diferencia real.

---

## 4. Impacto Técnico y Cambios Previstos

### Base de Datos & Migraciones
1. **Modificación / Extensión en `polizas`:**
   - Permitir `tipo = 'apertura'` (actualmente enum `ingreso`, `egreso`, `diario`; o bien `tipo = 'diario'` con flag `es_apertura = true` para compatibilidad de tipos estándar CONAC).
2. **Catálogo CONAC y Guía Contabilizadora:**
   - Registrar la subcuenta CONAC: `1.1.2.3.1 Anticipos a Socios` (afectable = 1, padre `1.1.2.3`).
   - Agregar eventos a `guia_contabilizadora`:
     - `ANTICIPO_SOCIO`: Cargo `1.1.2.3.1`, Abono `1.1.1.2`.
     - `LIQUIDACION_SOCIOS_CON_ANTICIPO`: Cargo `2.1.1.9`, Abono `1.1.2.3.1`, Abono `1.1.1.2`.

### Servicios & Controladores
1. **`app/Services/PolizaService.php`:**
   - Soporte para creación de pólizas de apertura (`crearPolizaApertura()`) con validación estricta de cuadre.
   - Manejo de reglas de amortización en `resolverAsientoPorEvento()` para compensar anticipos.
2. **`app/Services/SociosService.php` y `app/Http/Controllers/SociosController.php`:**
   - Al registrar pagos en `SociosController::storePago()`:
     - Si `calculo_periodo_id` es nulo $\rightarrow$ disparar evento `ANTICIPO_SOCIO`.
     - Si liquida periodo $\rightarrow$ verificar si existen anticipos previos para compensar y emitir póliza con desglose de amortización.
3. **`app/Http/Controllers/ContabilidadController.php`:**
   - Vistas y endpoints para:
     - `/contabilidad/poliza-apertura`: Vista de captura interactiva de saldos iniciales por empresa.
     - `/contabilidad/poliza-apertura/guardar`: Endpoint para validar y almacenar la póliza de apertura con transacción `DB::transaction()`.

---

## 5. Pruebas y Verificación
- Pruebas unitarias en PHPUnit (`tests/Unit/ContabilidadAperturaAnticiposTest.php`):
  1. Test de cuadre estricto de Póliza Inicial.
  2. Test de cálculo de saldos en Balance incorporando Póliza de Apertura.
  3. Test de generación de póliza de anticipo a socios a la cuenta `1.1.2.3.1`.
  4. Test de amortización y compensación de anticipo al liquidar periodo.
- Verificación visual con el MCP de Chrome DevTools en entorno local.
