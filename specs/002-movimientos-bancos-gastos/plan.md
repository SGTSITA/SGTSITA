# Plan Técnico — Spec 002: Movimientos y Transferencias Bancarias en Gastos

Cubre: RF-1, RF-2, RF-3, RF-4, RF-5.

## 1. Archivos y Responsabilidades

### Backend
- `app/Http/Controllers/GastosController.php`:
  - `getCuentasBancarias(Request $request)`: Retorna en formato JSON las cuentas activas de la empresa con saldos vigentes usando `$this->bancosService->getCuentasOption(...)`.
  - `storeMovimientoBancario(Request $request)`: Valida y procesa un abono/cargo en la cuenta seleccionada bajo `DB::transaction()`.
  - `storeTransferenciaBancaria(Request $request)`: Valida y transfiere fondos entre dos cuentas bajo `DB::transaction()`.
- `routes/web.php`:
  - Nuevas rutas dentro del grupo `Route::middleware('permission:gastos')->prefix('gastos')`:
    - `GET /gastos/cuentas-bancarias` -> `gastos.cuentas_bancarias`
    - `POST /gastos/cuentas-bancarias/movimiento` -> `gastos.bancos_movimiento`
    - `POST /gastos/cuentas-bancarias/transferencia` -> `gastos.bancos_transferencia`

### Frontend / Vistas
- `resources/views/gastos/modals/modal_movimiento_in_gasto.blade.php`: Sub-modal específico para captura de movimientos (ingresos/egresos/ajustes) con selector de cuenta, monto, fecha, concepto y origen.
- `resources/views/gastos/modals/modal_transferencia_in_gasto.blade.php`: Sub-modal específico para transferencias entre cuentas bancarias de la empresa.
- `resources/views/gastos/index.blade.php`:
  - Inclusión de los botones `+ Movimiento` y `⇄ Transferir` en `#divCuentaRetiroNew`.
  - Inclusión de los dos sub-modales.
  - Funciones JavaScript para apertura y cierre de sub-modales manteniendo el estado de `#modalGastoNew`.
  - Función `actualizarSelectBancos(cuentaSeleccionadaId = null)` para refrescar el select `#id_banco1New` vía AJAX tras registrar un movimiento o transferencia.
  - Motor de borrador con `sessionStorage`:
    - `saveGastoDraft()` con debounce.
    - `restoreGastoDraft()` con reconstrucción de radios, Choices.js (`selectUnidadesNew`, `selectViajesNew`), selects dinámicos y Flatpickr.
    - `clearGastoDraft()` al guardar con éxito o descarte manual.

## 2. Decisiones Técnicas Justificadas
1. **Transacciones `DB::transaction()` obligatorias:** Tanto el registro de abono como la transferencia entre cuentas modifican balances bancarios; se exige transacción atómica para prevenir inconsistencias.
2. **Aislamiento de rutas en Gastos:** Los endpoints de movimiento y transferencia expuestos en `GastosController` heredan la seguridad de `permission:gastos` y evitan bloqueos del middleware `finanzas:3`, que está restringido al catálogo central de finanzas.
3. **Persistencia desacoplada con `sessionStorage`:** Se utiliza la clave `sgt_gasto_draft_{empresa_id}`. Esto asegura que la captura permanezca incluso si el usuario navega a otra pantalla o refresca la página, pero no interfiere con otros usuarios o empresas.
