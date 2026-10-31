# Plan Técnico — Spec 001: Control de Combustible

Cubre: RF-1, RF-2, RF-3, RF-4.

## 1. Archivos y responsabilidades
- `database/migrations/xxxx_create_combustible_cargas_table.php`: Migración con llaves foráneas a `viajes` y `unidades`.
- `app/Models/CombustibleCarga.php`: Modelo Eloquent con `$fillable` y relaciones tipadas.
- `app/Http/Requests/StoreCombustibleCargaRequest.php`: Validaciones de entrada en español.
- `app/Http/Controllers/CombustibleController.php`: Lógica de cálculo y vistas Blade Metronic.
- `routes/web.php` y `routes/api.php`: Rutas protegidas por permisos Spatie.

## 2. Decisiones técnicas justificadas
- **Transacciones obligatorias:** El registro de combustible y la actualización de odómetro de la unidad se envuelven en `DB::transaction()`.
- **Sincronización API móvil:** El endpoint `/api/operador/registrar-combustible` reutiliza el mismo FormRequest del panel web.
