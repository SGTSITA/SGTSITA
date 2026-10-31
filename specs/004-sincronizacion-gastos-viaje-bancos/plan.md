# Plan Técnico — Spec 004: Sincronización Integral y Consistencia de Importes entre Gastos de Viaje, Pagos Bancarios y Administración App Móvil

Cubre: RF-1, RF-2, RF-3, RF-4, RF-5, RF-6.

## 1. Archivos y Responsabilidades

### Backend y Servicios
- `app/Services/GastosService.php`:
  - `sincronizarGastoConBancos(Gasto $gasto, float $nuevoMonto, ?string $fechaGasto = null, ?string $nuevoConcepto = null)`:
    - Método centralizado atómico (`DB::transaction`).
    - Evalúa si el gasto tiene pagos activos (`pagos()->where('estatus', 'aplicado')`).
    - Si no tiene pagos: actualiza únicamente el `monto_total` e imputaciones de `$gasto`.
    - Si tiene un pago único:
      - Calcula la diferencia real entre el nuevo monto y el monto actual del pago: `$diferencia = $nuevoMonto - $pago->monto`.
      - Si la diferencia es positiva, valida disponibilidad en banco mediante `BancosService::validarsaldoparacargo()`.
      - Actualiza `gasto_pagos.monto = $nuevoMonto`.
      - Actualiza `cat_bancos_cuentas_movimientos.monto = $nuevoMonto`.
      - Si se proporciona `$nuevoConcepto`, actualiza el concepto del movimiento bancario.
      - Actualiza la estructura JSON en la columna `detalles` del movimiento bancario (reemplazando el campo `monto` del ítem correspondiente al gasto).
      - Recalcula y actualiza el saldo de la cuenta bancaria en `bancos`.
    - Sincroniza el estatus de pago del gasto mediante `$this->sincronizarEstatusPago($gasto)`.

- `app/Http/Controllers/AppMovilAdminController.php`:
  - `update(Request $request, $id)`:
    - Al editar el costo de Diésel o Urea:
      - Validar si el gasto ya está pagado (`$dieselPagadoExistente` / `$ureaPagadaExistente`).
      - **Si NO está pagado:** Sincroniza únicamente `bitacora_viajes_operadores`, `gastos_operadores` y el encabezado `Gasto->monto_total` e imputaciones (sin tocar movimientos bancarios).
      - **Si YA está pagado:** Ejecuta `GastosService::sincronizarGastoConBancos()` para ajustar en cascada el gasto, el registro de pago y el movimiento bancario en `CatBancoCuentasMovimientos` con su respectivo saldo de cuenta.

- `app/Http/Controllers/GastosController.php`:
  - `update(Request $request, Gasto $gasto)`:
    - Corregir el cálculo de ajuste en pagos únicos:
      - Comparar directamente el nuevo monto solicitado contra el monto del pago actual (`$montoTotal - $pagoExistente->monto`), evitando el cálculo relativo contra `$gasto->monto_total` que generaba deltas en cero cuando el gasto ya había sido tocado por otro módulo.
      - Sincronizar el array JSON `detalles` del movimiento bancario con el nuevo importe asignado.
      - Recalcular el saldo bancario de la cuenta afectada.

- `app/Services/ApiValidationService.php`:
  - `guardarCoordenadas(array $data)`:
    - Incorporar validaciones de seguridad para carga de combustible:
      - Validar si `costo` supera umbral máximo de seguridad ($100,000 MXN) o si el ratio `costo / litros` excede los límites normales de mercado (> $60.00/L o < $10.00/L con litros > 0).
      - Si falla la validación, retornar estrictamente:
        ```php
        return [
            'success' => false,
            'message' => 'El costo de combustible ingresado ($' . number_format((float)$data['costo'], 2) . ') excede los límites válidos. Verifique si omitió el punto decimal.',
            'data'    => [],
            'status'  => 422
        ];
        ```
      - Preservar exactamente el contrato JSON (`success`, `mensaje`, `data`) y código HTTP consumido por la app móvil Flutter sin alterar nombres de claves.

- Rutina de Regularización de Datos:
  - Crear un comando o script Artisan seguro (`php artisan fix:gasto-fscu8106019`) para corregir el caso del contenedor `FSCU8106019-ZZH86`:
    - Ajustar `GastoPago` #2317: monto = 17,529.09.
    - Ajustar `CatBancoCuentasMovimientos` #2258: monto = 17,529.09, actualizar JSON `detalles`.
    - Recalcular y actualizar saldo real en la Cuenta Bancaria #6.

---

## 2. Decisiones Técnicas y de Arquitectura
1. **Transacciones Atómicas `DB::transaction()`:** Todo ajuste entre gasto, pago y movimiento bancario debe ejecutarse en bloque atómico para garantizar consistencia ACID.
2. **Validación Condicional de Pago:** Si el gasto no ha sido pagado, las cuentas bancarias no se alteran. La propagación a bancos solo se activa cuando existe un pago aplicado.
3. **Contrato de API Sagrado:** Cumplir el principio #3 de la Constitución: no agregar campos ni renombrar llaves en la respuesta de `ApiValidationController`/`ApiValidationService`. Solo se personaliza el mensaje de error con código HTTP 422.
4. **Actualización de Columna `detalles` JSON:** En `CatBancoCuentasMovimientos`, la columna `detalles` alimenta reportes por unidad y contenedor; debe actualizarse siempre en sincronía con la columna `monto`.
