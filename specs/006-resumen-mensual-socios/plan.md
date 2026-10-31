# Plan de Implementación — Spec 006: Resumen Mensual de Utilidad, Desglose Drill-Down y Selector de Años en Módulo de Socios

## 1. Arquitectura y Archivos Afectados

1. **Backend Service (`app/Services/SociosService.php`):**
   - En el método `calculatePartnerUtility(string $startDate, string $endDate, int $idEmpresa, ?int $socioId = null, ?int $equipoId = null)`:
     - Realizar la agregación mensual sobre `$viajesDesglose`.
     - Generar el arreglo `$mesesResumen` indexado cronológicamente por `$periodoClave = substr($v['fecha_viaje'], 0, 7)`.
     - Formatear el nombre del mes en español (ej. "Agosto 2025") usando `Carbon::parse($v['fecha_viaje'])->translatedFormat('F Y')`.
     - Sumar y redondear rigurosamente: `utilidad_total_mes`, `cantidad_viajes`, `cantidad_contenedores`, `promedio_por_viaje`.
     - Adjuntar `'meses_resumen' => array_values($mesesResumen)` en la respuesta retornada del método.
     - Preservar `'viajes_desglose'` intacto para garantizar compatibilidad con exportaciones y cortes históricos.

2. **Frontend Blade View (`resources/views/socios/index.blade.php`):**
   - **Selector de Fechas (`#utilidadDaterange` y `#historialDaterange`):**
     - Añadir `showDropdowns: true`, `minYear: 2022`, `maxYear: moment().year() + 2`.
     - Añadir menú `ranges` con atajos útiles ("Este Mes", "Mes Anterior", "Año Actual", "Año 2025", "Últimos 12 Meses").
   - **Estructura HTML en Pestaña 2 ("Cálculo de Periodo"):**
     - Reemplazar el contenedor `#gridViajesDesglose` por el contenedor `#gridMesesResumen` con título: "Resumen Mensual de Operaciones y Utilidad".
     - Agregar el Modal `#modalDetalleMes` (Bootstrap 5) con cabecera dinámica, badges informativos, tabla/AG Grid de viajes del mes (`#gridModalViajesMes`) y footer con totales.
   - **Lógica JavaScript (`resources/views/socios/index.blade.php`):**
     - Crear configuración AG Grid `gridMesesResumenOptions` y su API `gridMesesApi`.
       - Columnas: Mes (`mes_nombre`), Viajes (`cantidad_viajes`), Contenedores (`cantidad_contenedores`), Utilidad Total Mes (`utilidad_total_mes`), Promedio por Viaje (`promedio_por_viaje`), Acciones (Botón "Ver Desglose").
     - En `cargarReporteUtilidad()`:
       - Poblar `gridMesesApi.setGridOption('rowData', json.meses_resumen)`.
       - Guardar en memoria global `resumenMesesData = json.meses_resumen` para acceso inmediato en el modal.
     - Implementar función `abrirModalDesgloseMes(periodoClave)`:
       - Cargar los datos del mes en el modal `#modalDetalleMes`.
       - Renderizar la sub-grilla `#gridModalViajesMes` con los viajes y contenedores del mes.
       - Mostrar modal con `new bootstrap.Modal(document.getElementById('modalDetalleMes')).show()`.

3. **Compatibilidad en Reportería General (`resources/views/reporteria/socios/index.blade.php`):**
   - Si se requiere en el futuro, la estructura de `meses_resumen` queda lista y disponible de forma transparente.

---

## 2. Garantía de Consistencia y Cuadratura Matemática

- La suma de las columnas `utilidad_total_mes` de cada elemento en `meses_resumen` coincide al 100% con `total_utilidad_bruta_viajes` del objeto general.
- Cada elemento en `viajes` dentro de un mes tiene la propiedad `utilidad_viaje`. Su suma algebraica coincide con la `utilidad_total_mes` correspondiente.
- Se implementará un pie de grilla / indicador resumen en la tabla mensual que muestre la suma consolidada de todos los meses para dar verificación visual inmediata al usuario.

---

## 3. Plan de Pruebas y Verificación

1. **Prueba Automatizada en Backend:**
   - Ejecutar script de prueba para validar que para cualquier rango multianual (incluyendo `2025-08-01` a `2026-09-30`) la diferencia entre la suma mensual y el total general sea `< 0.01` centavos.
2. **Prueba End-to-End con Chrome DevTools MCP:**
   - Iniciar sesión en `http://localhost:8080/login` con:
     - Usuario: `oliva@gmail.com`
     - Contraseña: `oliva@contenedores.sgt`
   - Navegar a `http://localhost:8080/socios`.
   - Ir a la pestaña "2. Cálculo de Periodo".
   - Abrir el selector de fechas y comprobar que los desplegables de año y mes funcionan inmediatamente y sin fricción.
   - Seleccionar el rango `01/08/2025` a `30/09/2026`.
   - Clic en "Calcular Utilidades".
   - Verificar que se muestra la tabla mensual con los 14 meses (Agosto 2025 a Septiembre 2026).
   - Verificar que la suma total de las filas mensuales cuadre con la tarjeta de "Utilidad Bruta Viajes".
   - Hacer clic en "Ver Desglose" en el mes "Octubre 2025".
   - Verificar la apertura del modal con los 10 viajes y su total de $311,480.44.
   - Revisar que la consola de JavaScript no presente errores.
