# Plan de Implementación — Spec 005: Restauración y Visibilidad de Gastos Soft-Deleted Re-sincronizados desde App Móvil Admin

## 1. Arquitectura y Archivos Afectados
- `app/Services/GastosService.php`:
  - En `registrar()`, después de `$gasto = $this->resolverGastoExistente($data);`:
    - Si `$gasto && $gasto->trashed()`, invocar `$gasto->restore()` para que su `deleted_at` quede en `null`.
    - Proceder con `$gasto->update($payload)`.
- `app/Http/Controllers/AppMovilAdminController.php`:
  - En las consultas de `$gastoDieselExistente` y `$gastoUreaExistente`, agregar `withTrashed()` para que si un gasto existe en papelera, el controlador lo reconozca y evalúe correctamente sus pagos y vínculos.
- `app/Console/Commands/FixGastoMsmu7292298Command.php` (o comando de regularización):
  - Restaurar el gasto #2236 asociado a la asignación #3720 (contenedor MSMU7292298), confirmando sus vínculos y su visibilidad en cotización #4602.

## 2. Responsabilidades y Modelos
- `Gasto`: Modelo principal con `SoftDeletes`.
- `GastosService`: Orquestador de persistencia. Garantiza que cualquier llamada a `registrar()` para un gasto legacy reactive el registro si estaba soft-deleted.
- `AppMovilAdminController`: Controlador de interfaz de bitácoras. Maneja validaciones de formulario y despacha sincronizaciones con `GastosService`.

## 3. Transacciones y Concurrencia
- Toda mutación y restauración de gastos ocurre dentro de `DB::transaction()` en `GastosService::registrar()`.

## 4. Estrategia de Pruebas
1. **Prueba en base de datos local:**
   - Ejecutar la regularización para restaurar el gasto #2236.
   - Verificar con tinker o script que `deleted_at` sea `null`.
2. **Prueba visual vía Chrome DevTools MCP:**
   - Navegar a `http://localhost:8080/cotizaciones/4602/edit`.
   - Abrir la pestaña "Gastos Viaje" (`#nav-GastosOpe-tab`).
   - Confirmar que `gridGastosOperador` muestre la fila de "GDI02 - Diesel" por $16,827.91 junto con "GU001 - Urea" por $902.40.
   - Navegar a `http://localhost:8080/gastos`.
   - Confirmar que el gasto #2236 aparezca en el listado activo de gastos.
3. **Prueba de regresión en `/app-movil-admin/8/edit`:**
   - Volver a guardar la bitácora con los mismos valores para asegurar que el gasto no vuelva a ser enviado a papelera ni quede desincronizado.
