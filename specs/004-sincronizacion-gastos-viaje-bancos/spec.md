# Spec 004 — Sincronización Integral y Consistencia de Importes entre Gastos de Viaje, Pagos Bancarios y Administración App Móvil

Estado: completado

## 1. Contexto y Problema
En el flujo operativo de viajes de transporte, los gastos asociados a un contenedor (particularmente Diésel y Urea) pueden originarse desde tres vías distintas:
1. **App Móvil de Operadores:** Captura inicial en ruta mediante el API (`guardarCoordenadas`).
2. **Panel de Planeación de Viajes:** Asignación de viáticos y pagos inmediatos desde cuentas bancarias de la empresa.
3. **Módulo Administrativo de la App Móvil (`/app-movil-admin/{id}/edit`):** Supervisión y corrección de litros, costos y comprobantes cargados por el operador.
4. **Módulo Central de Gastos (`/gastos`):** Consulta, edición y aplicación de pagos contables/bancarios.

### El Incidente Detectado (Caso Contenedor FSCU8106019-ZZH86):
1. **Captura Errónea:** En la asignación #4129, el costo de diésel (651.64 litros) fue capturado como `1752909` (omitiendo el punto decimal de `$17,529.09`).
2. **Impacto en Bancos:** Al confirmarse la planeación con pago inmediato desde la cuenta bancaria #6 (Bancomer), se generó un cargo bancario real en `cat_bancos_cuentas_movimientos` (#2258) y un pago en `gasto_pagos` (#2317) por **$1,752,909.00**.
3. **Falla de Sincronización en Admin App Móvil:** El administrador corrigió el costo a `$17,529.09` en `/app-movil-admin/59/edit`. Dicha pantalla actualizó la bitácora, la tabla legacy `gastos_operadores` y el encabezado de `gastos`, pero **ignoró por completo** los registros existentes en `gasto_pagos` y `cat_bancos_cuentas_movimientos`.
4. **Falla de Sincronización en Módulo Gastos:** Al advertir que el banco no se actualizó, el usuario editó el gasto en `/gastos`. Sin embargo, el controlador calculó `$montoDiferencia = $montoTotal - $gasto->monto_total`. Como el encabezado del gasto ya había sido modificado a `$17,529.09` previamente, la diferencia calculada fue `$0.00`. En consecuencia, el pago y el movimiento bancario mantuvieron el saldo viciado de `$1,752,909.00`, limitándose a cambiar el concepto a *"Pago gasto (Editado)"*.
5. **Afectación Financiera:** La cuenta bancaria #6 quedó con un cargo excedente artificial de **$1,735,379.91**, distorsionando el saldo contable y de tesorería del sistema.

---

## 2. Usuarios y Roles Afectados
- **Auxiliar de Tráfico / Administrador de App Móvil:** Supervisa las bitácoras de los operadores y corrige capturas de campo.
- **Encargado de Gastos y Cuentas por Pagar:** Revisa, aprueba y ajusta importes de gastos de viaje y generales.
- **Tesorero / Finanzas:** Concilia movimientos bancarios y saldos de cuentas en Bancos V2.
- **Operador de Unidad:** Captura litros y costos de combustible en la aplicación móvil.

---

## 3. Historias de Usuario
- **HU-1:** Como administrador del panel móvil (`/app-movil-admin`), quiero que al corregir el costo de combustible o insumos en una bitácora:
  - Si el gasto aún **no está pagado**, el sistema solo sincronice el gasto y su importe en `bitacora_viajes_operadores`, `gastos_operadores` y `gastos`.
  - Si el gasto **ya está pagado**, el sistema sincronice también el pago y el movimiento bancario correspondiente en Cat Bancos, para evitar descuadres entre tráfico y finanzas.
- **HU-2:** Como analista de gastos en `/gastos`, quiero que al editar el importe de un gasto que cuenta con un pago único aplicado, el sistema sincronice el monto del pago y del movimiento bancario con el nuevo total real especificado (y no con una diferencia incremental dependiente del encabezado previo), para corregir de forma definitiva cualquier desincronización previa.
- **HU-3:** Como tesorero en Bancos V2, quiero que el desglose interno (`detalles` JSON) del movimiento bancario refleje el importe corregido de forma idéntica al monto principal del movimiento, para que los reportes por unidad y contenedor sean exactos.
- **HU-4:** Como operador, quiero que si por error omito el punto decimal o capturo un importe fuera de rango en la app móvil, el backend rechace la operación devolviendo el mensaje de error en el formato JSON estándar del API (sin alterar la estructura del contrato), para corregir el valor antes de enviarlo.

---

## 4. Requisitos Funcionales (Notación EARS)

### Módulo App Móvil Admin (`/app-movil-admin`)
- **RF-1 (Sincronización Condicional según Estado de Pago):**
  - CUANDO el usuario actualiza el costo de diésel o urea en `/app-movil-admin/{id}`,
  - SI el gasto asociado **no está pagado** (sin pagos activos en `gasto_pagos`),
  - EL SISTEMA debe actualizar únicamente la bitácora, `gastos_operadores` y el `monto_total` e imputación del `Gasto`.
  - SI el gasto asociado **ya está pagado** (cuenta con pagos aplicados y movimiento bancario en `CatBancoCuentasMovimientos`),
  - EL SISTEMA debe actualizar en cascada y atómicamente el monto del `Gasto`, el monto del `GastoPago` vinculado, y el monto, concepto y columna `detalles` JSON del `CatBancoCuentasMovimientos`, recalculando el saldo de la cuenta bancaria.

### Módulo Central de Gastos (`/gastos`)
- **RF-2 (Cálculo Absoluto de Ajuste en Pagos Únicos):**
  - CUANDO el usuario edita un gasto en `/gastos` que posee un único pago activo aplicado (`pagos->count() === 1`),
  - EL SISTEMA debe ajustar el monto de `GastoPago` y del `CatBancoCuentasMovimientos` para igualar exactamente el nuevo `monto_total` del formulario, actualizando simultáneamente el array de objetos en la columna `detalles` del movimiento bancario y el saldo bancario.
- **RF-3 (Ajuste en Pagos Múltiples o Parciales):**
  - CUANDO el gasto posea pagos parciales o múltiples,
  - SI el nuevo monto total es inferior a la suma pagada acumulada,
  - EL SISTEMA debe impedir la operación con mensaje de error explicativo o requerir la cancelación/ajuste explícito de pagos para evitar saldos negativos.

### Módulo de Bancos y Servicios (`GastosService` y `BancosService`)
- **RF-4 (Método Unificado de Sincronización Bancaria por Modificación de Importe):**
  - EL SISTEMA debe disponer en `GastosService` de un método dedicado (`sincronizarImporteGastoConBancos(Gasto $gasto, float $nuevoMonto)`) responsable de mantener la coherencia matemática entre `gastos`, `gasto_pagos`, `cat_bancos_cuentas_movimientos` y el recálculo del saldo de la cuenta bancaria afectada bajo `DB::transaction()`.

### Validación en Origen (API Móvil con Contrato Sagrado)
- **RF-5 (Validación de Rango y Preservación Estricta de Contrato JSON):**
  - CUANDO un operador envía datos de carga de combustible en el API (`ApiValidationService::guardarCoordenadas`),
  - SI el costo total excede el umbral de seguridad (ej. > $100,000 MXN) o el costo por litro excede un rango plausible (ej. > $60.00 MXN/L o < $10.00 MXN/L con litros > 0),
  - EL SISTEMA debe rechazar la petición retornando la estructura JSON idéntica actual:
    `{"success": false, "mensaje": "...", "data": []}` con código HTTP 422, sin alterar nombres de claves ni agregar campos para no requerir actualización de la app Flutter.

### Regularización de Datos Históricos
- **RF-6 (Corrección del Caso Específico FSCU8106019-ZZH86):**
  - EL SISTEMA debe incluir una rutina de corrección de datos para ajustar el `GastoPago` #2317 y el `CatBancoCuentasMovimientos` #2258 de $1,752,909.00 a $17,529.09, actualizando su estructura `detalles` y recalculando el saldo neto de la cuenta bancaria #6.

---

## 5. Criterios de Aceptación (Definition of Done - DoD)
- [x] Al corregir un costo no pagado en `/app-movil-admin`, se actualizan bitácora, gastos_operadores y gastos sin tocar bancos.
- [x] Al corregir un costo ya pagado en `/app-movil-admin`, se actualizan bitácora, gastos, pago y movimiento bancario con saldo recalculado.
- [x] Al editar un gasto pagado en `/gastos`, el movimiento bancario y el JSON `detalles` se ajustan al importe exacto sin importar si el encabezado ya tenía dicho monto.
- [x] La respuesta JSON de error en el API móvil preserva estrictamente las llaves `success`, `mensaje` y `data` con HTTP 422.
- [x] La cuenta bancaria #6 y el contenedor `FSCU8106019-ZZH86` quedan regularizados con cargo real de $17,529.09 y saldo restituido.
- [x] Todas las mutaciones operan bajo transacciones `DB::transaction()`.
