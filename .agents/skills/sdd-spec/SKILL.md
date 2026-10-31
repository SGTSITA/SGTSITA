---
name: sdd-spec
description: Procedimiento para crear, estructurar y validar especificaciones funcionales bajo la metodología Spec-Driven Development (SDD) antes de implementar código en SGTSITA.
---

# Procedimiento de Spec-Driven Development (SDD)

Utiliza este procedimiento cuando el usuario solicite una nueva funcionalidad, un módulo completo o una refactorización mayor.

## Paso 1: Crear el archivo de Especificación
1. Ubica el siguiente número disponible en la carpeta `specs/` (ejemplo: `specs/001-modulo-embarques.spec.md`).
2. Copia la estructura base desde `specs/template.spec.md`.

## Paso 2: Redactar los Componentes Clave
Asegúrate de que la especificación contenga obligatoriamente:
- **Taxonomía:** Nombre, Tipo (Feature / Bugfix / Refactor), Módulo afectado, Prioridad.
- **Contexto y Problema:** Qué dolor resuelve y para qué rol de usuario (Administrador, Chofer, Cliente).
- **Criterios de Aceptación (DoD):** Redactados en formato *Given-When-Then* o checklist verificable.
- **Impacto Técnico:** Tablas de BD a crear o modificar, rutas nuevas, FormRequests necesarios y permisos Spatie requeridos.

## Paso 3: Validación con el Usuario
- Presentar la especificación al usuario antes de modificar o crear archivos de código.
- Ajustar según el feedback recibido.

## Paso 4: Plan de Implementación
- Dividir la especificación en tareas atómicas con casillas de verificación `[ ]`.
- Marcar cada tarea `[x]` a medida que se complete y verifique.
