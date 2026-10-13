# Tareas de Implementación: Pólizas de Apertura y Flujo de Anticipos a Socios (SDD-009)

## Fase 1: Catálogo CONAC y Base de Datos
- [x] **T1.1:** Crear migración segura para agregar flag `es_apertura` a tabla `polizas`.
- [x] **T1.2:** Registrar cuenta afectable `1.1.2.3.1 Anticipos a Socios` en `cat_cuentas_contables`.
- [x] **T1.3:** Agregar eventos `ANTICIPO_SOCIO` y `LIQUIDACION_SOCIOS_CON_ANTICIPO` en `guia_contabilizadora`.

## Fase 2: Lógica de Negocio y Motor Contable
- [x] **T2.1:** Implementar en `PolizaService` el registro y validación de Póliza Inicial (`guardarPolizaApertura()`).
- [x] **T2.2:** Asegurar que `ContabilidadService` sume saldos de póliza de apertura en árbol recursivo y balance.
- [x] **T2.3:** Implementar resolución de póliza de amortización de anticipos en `PolizaService`.

## Fase 3: Integración Operativa de Socios
- [x] **T3.1:** Modificar `SociosController::storePago` para disparar `ANTICIPO_SOCIO` cuando el pago no tiene periodo asignado.
- [x] **T3.2:** Integrar la amortización del anticipo al liquidar el corte en `SociosController` o `SociosService`.
- [x] **T3.3:** Reflejar el saldo pendiente de anticipos en la consulta del socio (`getSocioBalance`).

## Fase 4: Vistas e Interfaz Gráfica
- [x] **T4.1:** Crear vista Blade para captura y edición de Póliza de Apertura (`resources/views/contabilidad/poliza-apertura.blade.php`).
- [x] **T4.2:** Agregar enlace a la Póliza Inicial en el menú del Sidebar y en la barra de navegación de Contabilidad.
- [x] **T4.3:** Incorporar la cuenta de "Anticipos a Socios" en el mapeo de conceptos del Balance General (Activo Circulante).

## Fase 5: Pruebas y Validación
- [x] **T5.1:** Escribir y ejecutar suite de pruebas unitarias `tests/Unit/ContabilidadAperturaAnticiposTest.php`.
- [x] **T5.2:** Validar con Chrome DevTools la captura de póliza inicial y el cuadre en el Balance General.
