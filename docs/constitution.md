# Constitución — SGTSITA

Principios innegociables. Toda spec, plan y tarea debe cumplirlos.

1. **Estabilidad del Stack**: Laravel 9 + PHP 8.1/8.2 + Docker. No actualizar dependencias mayores sin plan de migración probado.
2. **La spec manda**: nada se implementa si no está en la spec activa (`specs/NNN-*/spec.md`). Si falta una definición, se para y se pregunta.
3. **Contratos de API Sagrados**: los endpoints consumidos por la app móvil `operador_appsgt` (`routes/api.php`) no deben romperse ni alterar sus nombres de clave JSON.
4. **Transacciones Atómicas**: toda operación que modifique más de un modelo (cotizaciones, asignación de viajes, liquidaciones) debe estar encapsulada en `DB::transaction()`.
5. **Seguridad y Privacidad de Secretos**: credenciales de base de datos, sesiones de WhatsApp y llaves API se leen estrictamente de `.env`. Prohibido quemar secretos en código.
6. **Verificación como Puerta**: probar endpoints con pruebas de red y vistas con Chrome DevTools antes de dar por terminada una tarea. Prohibido avanzar con errores en log.
7. **Thin Controllers y Centralización en Servicios (Principio DRY)**: prohibido duplicar consultas de Eloquent o lógica de catálogos directamente en los controladores. Toda consulta de catálogos o entidades compartidas (ej. tractocamiones/equipos, bancos, clientes) debe encapsularse en su servicio de dominio correspondiente (ej. `EquipoService`) e inyectarse en los controladores, garantizando un punto único de verdad y mantenimiento.
