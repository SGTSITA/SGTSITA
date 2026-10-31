# Spec 001 — Módulo de Control y Reporte de Combustible por Unidad

Estado: borrador

## Contexto y objetivo
SGTSITA requiere un módulo especializado para registrar las cargas de combustible de cada tractocamión/unidad en viaje, calcular el rendimiento (km/litro) y contrastarlo con los viáticos asignados al operador.

## Usuarios / actores
- Administradores y despachadores de SGTSITA (revisan rendimientos y aprueban gastos).
- Operadores (reportan litros cargados y foto de ticket mediante la app móvil).

## Historias de usuario
- HU-1: Como administrador de flota, quiero registrar y consultar las cargas de combustible por viaje y por tractocamión, para detectar consumos excesivos o anomalías.
- HU-2: Como liquidador de gastos, quiero ver el total en pesos y litros de combustible consumidos en un viaje, para realizar la liquidación precisa del chofer.

## Requisitos funcionales (criterios de aceptación en EARS)
- RF-1: CUANDO el usuario registra una carga de combustible, EL SISTEMA valida unidad, viaje, kilometraje, litros, costo total y foto de ticket.
- RF-2: SI el kilometraje ingresado es menor al odómetro anterior de la unidad, ENTONCES EL SISTEMA muestra advertencia de discrepancia de kilometraje.
- RF-3: EL SISTEMA calcula automáticamente el rendimiento promedio en km/l para cada tramo de viaje.
- RF-4: CUANDO se finaliza la liquidación del viaje, EL SISTEMA consolida el gasto de combustible dentro del estado de cuenta del operador.

## Criterios de finalización
- Migración de tabla `combustible_cargas` ejecutada.
- FormRequest y Controlador con transacciones `DB::transaction`.
- Pantalla en Metronic y endpoints para la app móvil probados.
