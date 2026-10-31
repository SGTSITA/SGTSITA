# Especificación Funcional: Sistema Contable, Motor de Pólizas (CONAC), Guía Contabilizadora y Balance/Estado de Resultados Multi-Empresa

**Identificador:** `008-sistema-contable-polizas-conac`  
**Tipo:** Feature / Arquitectura Contable y Financiera  
**Prioridad:** Alta  
**Estado:** Propuesta Refinada (Esperando Aprobación de Spec y Plan)  
**Módulos Afectados:** Contabilidad, Bancos, Gastos, Cotizaciones (CXC/CXP), Socios, Reportería.  

---

## 1. Contexto y Problema

Actualmente, el sistema gestiona operaciones dispersas en varios módulos (bancos, gastos, viajes, cuentas por cobrar y por pagar, cálculo de socios), pero carece de un **motor de contabilidad formal por partida doble (pólizas)** y un **catálogo jerárquico de cuentas contables normalizado (CONAC)**. 

El modelo real enviado en el PDF (*"TP 002 AL 30 JUL 2024"*) y los requerimientos del usuario evidencian las siguientes necesidades críticas:
1. **Catálogo de Cuentas Jerárquico CONAC con Atributo `es_afectable` y Soporte Multi-Empresa:**
   - Debe contar con un campo booleano `es_afectable` (solo las cuentas con `es_afectable = 1` pueden recibir cargos y abonos en los asientos de pólizas; las cuentas de título/mayor son acumulativas).
   - El catálogo debe ser **general y compartido a nivel macro (niveles 1 a 3)** con `id_empresa = NULL`, pero **independiente y personalizable por empresa a niveles analíticos (niveles 4 y 5)** con `id_empresa = X`.
2. **Guía Contabilizadora / Matriz de Conversión Configurable:**
   - En lugar de cablear cuentas fijas en código, se incorpora una **Guía Contabilizadora (Matriz de Conversión)** que mapea eventos operativos (`INTERES_BANCARIO`, `GASTO_OPERATIVO`, `GASTO_ANTICIPADO`, `COBRO_CXC`, `PROVISION_VIAJE`, `REPARTO_SOCIOS`) a las cuentas contables de cargo y abono.
   - Permite que la Empresa 1 configure cuentas distintas a la Empresa 2, o use reglas globales por defecto.
3. **Separación de Intereses en Bancos (`tipo = interes`):**
   - En bancos (ej. Banco Azteca), se requiere clasificar abonos como `tipo = interes` para reflejarlos como productos financieros en el Estado de Resultados y no mezclarlos con fletes operativos.
4. **Balance General y Estado de Resultados Consolidado Multi-Empresa (`es_admin = 1`):**
   - Si el usuario es administrador (`es_admin = 1`), debe poder seleccionar **una o varias empresas** para emitir e imprimir el Balance General Consolidado.
   - Filtro opcional por Unidad / Camión (`id_equipo - marca`, ej. `TP-002`) como en el modelo real del PDF.
   - Tabla de mapeo de conceptos del balance a cuentas/patrones contables para diseñar el formato visual de dos columnas idéntico al PDF.

---

## 2. Casos de Uso y Flujos de Usuario

### CU-01: Catálogo de Cuentas CONAC Jerárquico, Afectable y Multi-Empresa
- **Actor:** Contador / Administrador.
- **Flujo:**
  - El sistema precarga la estructura base armonizada CONAC adaptable a transporte de carga (Activo, Pasivo, Capital, Ingresos, Costos, Gastos).
  - Cada cuenta posee: `codigo` (ej. `1.1.1.2.001`), `nombre`, `nivel` (1 a 5), `cuenta_padre_id`, `naturaleza` (`deudora` o `acreedora`), `rubro`, `id_empresa` (null = global, entero = propia de la filial) y **`es_afectable`** (0 = acumulativa/título, 1 = recibe movimientos).
  - Las cuentas de nivel 1 a 3 son generales. Las empresas pueden dar de alta sus propias subcuentas analíticas (nivel 4 y 5) que apunten a los padres globales.
  - Al consultar balances o balanzas, el cálculo suma hacia arriba de manera **recursiva** desde el nivel 5 hasta el nivel 1.

### CU-02: Registro y Separación de Intereses Bancarios
- **Actor:** Auxiliar Contable / Administrador.
- **Flujo:**
  - Al capturar un abono en bancos (`CatBancoCuentasMovimientos`), se añade el selector `tipo_concepto` con la opción `interes`.
  - La Guía Contabilizadora detecta el evento `INTERES_BANCARIO`:
    - **Cargo:** Cuenta contable de Bancos asignada a la cuenta bancaria.
    - **Abono:** `4.3.1.1 Intereses Ganados / Productos Financieros`.
  - En el Estado de Resultados se expone en un renglón dedicado: `Intereses: $1.72`.

### CU-03: Guía Contabilizadora / Matriz de Conversión
- **Actor:** Contador General / Administrador del Sistema.
- **Flujo:**
  - Permite configurar qué cuentas se afectan en cada transacción según la empresa:
    - Evento: `GASTO_OPERATIVO` -> Cargo a `Gastos Operativos (5.1.*)` / Abono a `Bancos (1.1.1.2.*)` o `CXP Proveedores (2.1.1.*)`.
    - Evento: `GASTO_ANTICIPADO` -> Cargo a `Gastos Pagados por Anticipado (1.1.5.*)` / Abono a `Bancos`.
    - Evento: `INTERES_BANCARIO` -> Cargo a `Bancos` / Abono a `Productos Financieros (4.3.1.*)`.
    - Evento: `PROVISION_VIAJE` -> Cargo a `Clientes CXC (1.1.2.1.*)` / Abono a `Ingresos por Fletes (4.1.1.*)`.
    - Evento: `REPARTO_SOCIOS` -> Cargo a `Resultado del Ejercicio (3.2.1.*)` / Abono a `Reparto de Utilidades por Pagar (2.1.9.*)`.
  - Si la Empresa 1 requiere que los gastos se contabilicen en una cuenta distinta a la Empresa 2, se define una regla con `id_empresa = 1`. Si no existe regla por empresa, se aplica la regla default (`id_empresa IS NULL`).

### CU-04: Motor de Generación y Validación de Pólizas Contables
- **Actor:** Sistema (Servicios de Bancos, Gastos, Cotizaciones, Socios).
- **Flujo:**
  - Al confirmar un movimiento operativo, el `PolizaService` evalúa la Guía Contabilizadora y crea una póliza (`ingreso`, `egreso`, `diario`).
  - **Validación Estricta:**
    - Solo se permite cargar o abonar a cuentas con **`es_afectable = 1`**.
    - Partida doble estricta: `total_debe === total_haber`.
    - Asociación opcional a `id_equipo` para rastreabilidad por camión.

### CU-05: Balance General y Estado de Resultados Consolidado Multi-Empresa (Modelo TP-002)
- **Actor:** Contador / Gerente / Socio (`es_admin = 1`).
- **Flujo:**
  - **Selector de Empresas:**
    - Si `es_admin = 1`, puede seleccionar una, varias o todas las empresas filiales para consolidar cifras.
    - Si `es_admin = 0`, el reporte se limita estrictamente a la empresa del usuario autenticado.
  - **Selector de Unidad:**
    - Selector opcional de tractocamión (`id_equipo - marca`, ej. `TP-002 - KENWORTH AZUL`) o "Todas las unidades".
  - **Selector de Fecha de Corte:**
    - Fecha de corte (ej. `30 JUL 2024`).
  - **Representación Visual (Fiel al PDF):**
    - **Columna Izquierda (ACTIVO):** Bancos, Inversiones, CXC, Préstamos otorgados, Gastos por comprobar, Equipo de transporte, Chasis, Gastos pagados por anticipado.
    - **Columna Derecha (PASIVO Y PATRIMONIO):** Préstamos recibidos, Cuentas por pagar, Acreedores, Resultado ejercicios anteriores, Resultado del ejercicio, Reparto de utilidades.
    - **Pie (ESTADO DE RESULTADOS):** Ingresos por fletes, Intereses ganados, Gastos del periodo, Utilidad neta (vinculada al Patrimonio).
    - Opciones de exportación: Imprimir, PDF y Excel.

---

## 3. Modelo de Datos y Estructura Técnica

### 3.1. Tabla `cat_cuentas_contables`
| Columna | Tipo | Descripción |
|---|---|---|
| `id` | BIGINT PK | Identificador |
| `id_empresa` | BIGINT NULL | NULL = cuenta general CONAC compartida; ID = subcuenta propia de la filial |
| `codigo` | VARCHAR(40) | Código jerárquico (ej. `1`, `1.1`, `1.1.1`, `1.1.1.2`, `1.1.1.2.001`) |
| `nombre` | VARCHAR(191) | Nombre contable (ej. "BBVA BANCOMER", "GASTOS DE VIAJE") |
| `nivel` | TINYINT | Nivel jerárquico (1 a 5) |
| `cuenta_padre_id` | BIGINT NULL FK | Relación reflexiva a cuenta superior |
| `rubro` | ENUM | `activo`, `pasivo`, `capital`, `ingresos`, `costos`, `gastos`, `resultado` |
| `naturaleza` | ENUM | `deudora`, `acreedora` |
| **`es_afectable`** | BOOLEAN | **1 = recibe cargos/abonos en pólizas; 0 = cuenta de mayor acumulativa** |
| `activo` | BOOLEAN | 1 = activa, 0 = inactiva |
| `timestamps` | TIMESTAMP | Auditoría |

*Índices:* `UNIQUE(codigo, id_empresa)`, `INDEX(cuenta_padre_id)`, `INDEX(es_afectable)`.

### 3.2. Tabla `guia_contabilizadora` (Matriz de Conversión)
| Columna | Tipo | Descripción |
|---|---|---|
| `id` | BIGINT PK | Identificador |
| `id_empresa` | BIGINT NULL | NULL = regla por defecto para todas las empresas; ID = regla específica de la empresa |
| `evento` | VARCHAR(60) | Clave del evento: `INTERES_BANCARIO`, `GASTO_OPERATIVO`, `GASTO_ANTICIPADO`, etc. |
| `subtipo` | VARCHAR(60) NULL | Subclasificación opcional (ej. categoría de gasto, tipo de banco) |
| `tipo_poliza` | ENUM | `ingreso`, `egreso`, `diario` |
| `cuenta_cargo_id` | BIGINT NULL FK | Cuenta contable a cargar (o null si se resuelve dinámicamente) |
| `cuenta_abono_id` | BIGINT NULL FK | Cuenta contable a abonar (o null si se resuelve dinámicamente) |
| `regla_dinamica` | VARCHAR(60) NULL | `cuenta_banco`, `cuenta_categoria_gasto`, `cuenta_cliente` |
| `descripcion_asiento` | VARCHAR(191) | Plantilla para el concepto del asiento |
| `activo` | BOOLEAN | Default 1 |
| `timestamps` | TIMESTAMP | Auditoría |

### 3.3. Tabla `balance_general_conceptos_cuentas` (Mapeo de Conceptos del Balance)
| Columna | Tipo | Descripción |
|---|---|---|
| `id` | BIGINT PK | Identificador |
| `id_empresa` | BIGINT NULL | NULL = plantilla general; ID = personalizada por empresa |
| `seccion` | ENUM | `activo`, `pasivo`, `patrimonio`, `estado_resultados` |
| `concepto` | VARCHAR(100) | Título visual: `BANCOS`, `CXC`, `PRESTAMOS`, `INTERESES`, etc. |
| `cuenta_codigo_prefijo` | VARCHAR(30) | Prefijo o código contable que agrupa (ej. `1.1.1.2%`, `4.3.1%`) |
| `cuenta_id` | BIGINT NULL FK | Cuenta específica si no es por prefijo |
| `columna` | TINYINT | 1 = izquierda (Activo), 2 = derecha (Pasivo/Patrimonio), 3 = pie (Estado de Resultados) |
| `orden` | INT | Orden de aparición |
| `es_calculado` | BOOLEAN | 1 = proviene de fórmula (ej. Utilidad del Ejercicio), 0 = sumatoria de cuentas |
| `timestamps` | TIMESTAMP | Auditoría |

### 3.4. Tablas `polizas` y `poliza_movimientos`
- `polizas`:
  - `id`, `id_empresa`, `id_equipo` (opcional), `tipo` (`ingreso`, `egreso`, `diario`), `numero` (consecutivo mensual), `fecha`, `concepto`, `origen_tipo`, `origen_id`, `estatus`, `total_debe`, `total_haber`, `creado_por`, `timestamps`.
- `poliza_movimientos`:
  - `id`, `poliza_id`, `cuenta_contable_id` (debe tener `es_afectable = 1`), `id_equipo` (opcional), `linea`, `referencia`, `concepto_detalle`, `debe`, `haber`, `timestamps`.

### 3.5. Alter en `cat_bancos_cuentas_movimientos`
- Agregar columna `tipo_concepto` (VARCHAR 40 default `'operativo'`), permitiendo valores: `'operativo'`, `'interes'`, `'traspaso'`, `'comision'`.

---

## 4. Criterios de Aceptación (DoD)

1. **Atributo `es_afectable` Riguroso:**
   - La inserción de movimientos de póliza valida que la cuenta tenga `es_afectable === 1`. Si se intenta imputar a una cuenta de título (ej. `1.1.1`), el sistema lanza excepción `CuentaNoAfectableException`.
2. **Jerarquía Multi-Empresa:**
   - Una filial consulta las cuentas con `id_empresa IS NULL` o `id_empresa = $idEmpresa`.
   - Puede crear subcuentas afectables en nivel 4 y 5 ligadas a su empresa sin alterar las de otras filiales.
3. **Agregación Recursiva Ascendente:**
   - El cálculo de saldos en `ContabilidadService` suma las cuentas nivel 5 a sus padres nivel 4, nivel 3, nivel 2 y nivel 1 respetando su naturaleza contable (Deudora: Debe - Haber; Acreedora: Haber - Debe).
4. **Guía Contabilizadora Operativa:**
   - Al registrar abono de `tipo_concepto = 'interes'` en bancos, se genera automáticamente póliza sin intervención manual.
   - Las empresas pueden sobreescribir cuentas en la guía contabilizadora por empresa.
5. **Consolidación Multi-Empresa para `es_admin = 1`:**
   - Un usuario administrador puede emitir el Balance General seleccionando múltiples empresas o todas las empresas.
   - Si no es admin, se fuerza únicamente su empresa.
6. **Cuadratura y Fidelidad con el PDF (`TP 002`):**
   - El reporte reproduce con precisión las secciones del modelo del PDF:
     - Activo vs Pasivo + Patrimonio balanceados.
     - Intereses ganados en renglón independiente dentro del Estado de Resultados.
     - Utilidad del ejercicio coincidente entre Estado de Resultados y Patrimonio.
