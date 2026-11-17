# [SDD-XXX] Título de la Especificación

## 1. Taxonomía de la Especificación
- **ID:** SDD-XXX
- **Módulo:** (Ej: Cotizaciones / Facturación / WhatsApp / Usuarios / Embarques)
- **Tipo:** Feature / Bugfix / Refactor
- **Prioridad:** Alta / Media / Baja
- **Autor / Solicitante:** JoseMXN / Desarrollador

---

## 2. Descripción y Contexto
> Explica brevemente qué necesidad o problema del negocio resuelve este cambio y a qué roles de usuario impacta.

---

## 3. Criterios de Aceptación (Definition of Done - DoD)
> Define con claridad qué debe cumplirse para considerar la tarea completada.

- [ ] **Escenario 1 (Caso Éxito):**
  - **Dado:** El usuario con rol `administrador` autenticado en el sistema.
  - **Cuando:** Envía el formulario de cotización con datos válidos.
  - **Entonces:** Se genera el registro en BD, se descarga el PDF correspondiente y se notifica por WhatsApp.
- [ ] **Escenario 2 (Validación de Error):**
  - **Dado:** El usuario envía campos vacíos o cantidades negativas.
  - **Cuando:** Se procesa la petición en el FormRequest.
  - **Entonces:** Retorna HTTP 422 con los mensajes de error en español sin guardar nada en BD.
- [ ] **Escenario 3 (Permisos):**
  - **Dado:** Un usuario sin el permiso necesario.
  - **Cuando:** Intenta acceder a la ruta o enviar la acción.
  - **Entonces:** Recibe una respuesta HTTP 403 Forbidden.

---

## 4. Impacto Técnico y Cambios Previstos
- **Base de Datos:**
  - [ ] Migración: `xxxx_xx_xx_create_xxx_table.php`
  - [ ] Modelo: `app/Models/Xxx.php` con `$fillable` y relaciones
- **Lógica de Negocio:**
  - [ ] FormRequest: `app/Http/Requests/XxxRequest.php`
  - [ ] Controlador: `app/Http/Controllers/XxxController.php`
  - [ ] Servicio: `app/Services/XxxService.php` (si aplica)
- **Frontend / Vistas:**
  - [ ] Vistas Blade: `resources/views/xxx/...`
- **Permisos (Spatie):**
  - [ ] Permiso nuevo: `xxx.ver`, `xxx.crear`, etc.

---

## 5. Plan de Ejecución (Tareas Atómicas)
- [ ] 1. Crear y ejecutar migración de base de datos.
- [ ] 2. Configurar Modelo Eloquent con relaciones y fillable.
- [ ] 3. Crear FormRequest con reglas de validación en español.
- [ ] 4. Implementar lógica de controlador / servicio bajo transacción `DB::transaction`.
- [ ] 5. Diseñar o ajustar la vista Blade con el diseño Metronic.
- [ ] 6. Probar flujo completo y verificar criterios de aceptación.
