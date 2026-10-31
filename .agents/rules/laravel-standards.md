# Reglas de Estándares de Código Laravel 9 (SGTSITA)

## 1. Arquitectura de Controladores
- Los controladores deben ser delgados (*Thin Controllers*).
- Las responsabilidades del controlador son únicamente:
  1. Recibir la petición validada (`FormRequest`).
  2. Invocar la capa de servicio (`Action` o `Service`).
  3. Retornar la respuesta (vista Blade, JSON o redirección con mensaje flash).
- Si un método de controlador tiene más de 30 líneas de lógica de negocio, debe refactorizarse a una clase de servicio en `app/Services/`.

## 2. Modelos y Eloquent
- **Asignación Masiva:** Definir siempre la propiedad `$fillable` explícitamente en cada modelo.
- **Tipado de Relaciones:** Toda relación de Eloquent debe tener tipo de retorno (`public function items(): HasMany`).
- **Consultas Seguras:** No utilizar `DB::raw()` con variables concatenadas directamente. Usar bindings (`DB::raw('... where col = ?', [$val])`).

## 3. Manejo de Errores y Logging
- Nunca capturar una excepción con un `catch(\Exception $e)` vacío.
- Registrar errores críticos con `Log::error($e->getMessage(), ['trace' => $e->getTraceAsString()])`.
- En APIs, retornar respuestas HTTP estandarizadas:
  ```json
  {
    "success": false,
    "message": "Descripción amigable del error",
    "errors": []
  }
  ```

## 4. Vistas Blade y Frontend
- Mantener la coherencia visual con el tema **Metronic** existente.
- Reutilizar componentes Blade en `resources/views/components/` o `partials/`.
- No colocar consultas Eloquent ni lógica compleja dentro de archivos Blade.
