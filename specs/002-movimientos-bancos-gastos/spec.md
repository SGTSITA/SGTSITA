# Spec 002 — Integración de Movimientos y Transferencias Bancarias en Modal de Gastos con Persistencia de Borrador

Estado: completado

## 1. Contexto y Problema
En la ruta `/gastos`, el usuario utiliza el modal `modalGastoNew` para capturar un nuevo gasto (seleccionando si aplica a Periodo, Unidad o Viaje, montos, conceptos, fechas y cuenta bancaria de retiro).
Frecuentemente, tras completar todos los campos del formulario, al intentar guardar con condición de pago Contado, el backend rechaza la operación con el error *"Saldo insuficiente"* o el usuario advierte que falta un depósito o ajuste.

Actualmente, si el usuario sale a Bancos (`/bancosv2`) para generar el abono o traspaso, pierde la totalidad de los datos capturados en el formulario de gasto. Al volver a `/gastos`, debe rellenar manualmente todo desde cero, generando frustración, retrasos operativos y duplicidad de esfuerzo.

## 2. Usuarios y Roles Afectados
- **Auxiliar de Gastos / Cuentas por Pagar:** Captura diaria de gastos operativos y administrativos.
- **Administrador / Finanzas:** Supervisa la imputación de gastos y conciliación de saldos de cuentas de la empresa.

## 3. Historias de Usuario
- **HU-1:** Como capturista de gastos, quiero registrar depósitos/abonos o transferencias entre cuentas bancarias directamente desde el modal de gasto sin salir de la pantalla ni perder los campos capturados, para que el pago de contado pueda procesarse inmediatamente.
- **HU-2:** Como capturista de gastos, quiero que los saldos y cuentas del selector bancario se actualicen en tiempo real al aplicar un movimiento o transferencia, para seleccionar la cuenta con saldo verificado de inmediato.
- **HU-3:** Como usuario del sistema, quiero que el formulario de registro de gasto guarde automáticamente un borrador en memoria local/sesión (`sessionStorage`), de modo que si por accidente navego a otra pantalla o refresco la página, no pierda ningún dato capturado.

## 4. Requisitos Funcionales (EARS)
- **RF-1 (Botones de acción bancaria en modal):**
  - CUANDO el usuario visualiza el campo "Cuenta de retiro (Banco)" en `#modalGastoNew`, EL SISTEMA debe mostrar los botones de acción rápida con estilo Metronic:
    - `+ Movimiento` (botón azul/índigo con icono `fa-plus-circle`).
    - `⇄ Transferir` (botón verde con icono `fa-exchange-alt`).
- **RF-2 (Sub-modal de Movimiento in-situ):**
  - CUANDO el usuario hace clic en `+ Movimiento`, EL SISTEMA debe desplegar el sub-modal de movimiento bancario permitiendo seleccionar la cuenta (preseleccionando la cuenta activa del gasto), tipo (Ingreso/Abono o Egreso/Cargo), monto, fecha, concepto, referencia y origen (`ajuste`, `manual`, etc.).
  - CUANDO se guarda el movimiento exitosamente, EL SISTEMA registra el movimiento en `cat_bancos_cuentas_movimientos`, actualiza el saldo de la cuenta vía AJAX, refresca las opciones del select `#id_banco1New` y mantiene el modal de gasto intacto con todos sus datos previos.
- **RF-3 (Sub-modal de Transferencia in-situ):**
  - CUANDO el usuario hace clic en `⇄ Transferir`, EL SISTEMA debe desplegar el sub-modal de transferencia bancaria entre cuentas de la empresa (cuenta origen, cuenta destino, monto, fecha y concepto).
  - CUANDO se procesa la transferencia exitosamente bajo transacción segura, EL SISTEMA actualiza los saldos de ambas cuentas, refresca el select `#id_banco1New` y mantiene el formulario de gasto listo para guardar.
- **RF-4 (Persistencia y Restauración de Borrador - Draft Autosave):**
  - MIENTRAS el usuario edita cualquier campo del formulario `#formGastoNew` (inputs de texto, montos, fechas, radios `formasAplicar`, selección en Choices.js de unidades o viajes, condiciones de pago), EL SISTEMA debe serializar y guardar el estado en `sessionStorage` (aislado por empresa).
  - CUANDO el usuario abre `#modalGastoNew` para un nuevo registro y existe un borrador guardado en la sesión, EL SISTEMA debe restaurar automáticamente todos los campos y selecciones (incluyendo instancias de Choices.js y fechas de diferido) y mostrar una indicación de *"Borrador restaurado"* con opción de *"Descartar borrador"*.
  - CUANDO el gasto se guarda exitosamente en el servidor (código `success`), EL SISTEMA debe eliminar automáticamente el borrador de `sessionStorage`.
- **RF-5 (Seguridad y Permisos):**
  - Las operaciones de movimiento y transferencia ejecutadas desde el módulo de gastos deben operar bajo el middleware `permission:gastos`, garantizando que el usuario con permiso de gastos pueda realizar ajustes de saldo sin bloqueo por tokens ajenos de finanzas.

## 5. Criterios de Aceptación (DoD)
- [ ] Botones visuales `+ Movimiento` y `⇄ Transferir` integrados en el selector de bancos de `#modalGastoNew`.
- [ ] Sub-modales de Movimiento y Transferencia funcionales con envío AJAX seguro bajo `DB::transaction()`.
- [ ] Recarga dinámica de opciones de cuenta con saldo actualizado sin recargar la página completa.
- [ ] Guardado automático y restauración transparente de todos los campos en `sessionStorage` (inputs, radios, selects, Choices.js).
- [ ] Eliminación de borrador al confirmar el guardado exitoso del gasto.
- [ ] Pruebas visuales y de consola verificadas con Chrome DevTools.
