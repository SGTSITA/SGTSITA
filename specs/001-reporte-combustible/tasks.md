# Tareas — Spec 001: Control de Combustible

- [ ] **T1. Crear migración y modelo `CombustibleCarga`.** RF-1
  - Hecho cuando: Exista la tabla en base de datos con índices y el modelo con `$fillable` y relaciones.
- [ ] **T2. Crear FormRequest y controlador `CombustibleController`.** RF-1, RF-2, RF-3
  - Hecho cuando: Las validaciones funcionen y el cálculo de rendimiento se guarde bajo `DB::transaction()`.
- [ ] **T3. Diseñar la vista en panel Metronic y ruta web.** RF-4
  - Hecho cuando: La pantalla permita listar y registrar cargas de diesel por viaje.
- [ ] **T4. Habilitar endpoint en `routes/api.php` para la app móvil.** RF-1
  - Hecho cuando: La petición POST desde la app móvil registre la carga exitosamente.
