/**
 * SGTSITA - Módulo de Captura Múltiple de Costos en Seguimiento
 * SDD-026: Cotización (viajes_costos) & Proveedor (asignaciones)
 */

document.addEventListener("DOMContentLoaded", function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "";

    // Elementos de Control Superior
    const selectCliente = document.getElementById("selectCliente");
    const selectEstadoCosto = document.getElementById("selectEstadoCosto");
    const selectEquipo = document.getElementById("selectEquipo");
    const inputPeriodo = document.getElementById("filtro_periodo");
    const btnConsultar = document.getElementById("btnConsultar");
    const btnLimpiar = document.getElementById("btnLimpiar");

    // Configuración y variables del filtro de Periodo (Fecha Entrega)
    const defaultStart = moment("2026-01-01");
    const defaultEnd = moment().add(20, "days");
    let fechaInicioFiltro = defaultStart.format("YYYY-MM-DD");
    let fechaFinFiltro = defaultEnd.format("YYYY-MM-DD");

    // Inicializar DateRangePicker con estándares SGT
    if (inputPeriodo && typeof window.initSgtDateRange === "function") {
        const rangosOpciones = {
            "Predeterminado (01/01/2026 a Hoy +20d)": [defaultStart.clone(), defaultEnd.clone()],
            "Últimos 7 días": [moment().subtract(6, "days").startOf("day"), moment().endOf("day")],
            "Últimos 30 días": [moment().subtract(29, "days").startOf("day"), moment().endOf("day")],
            "Este mes": [moment().startOf("month"), moment().endOf("month")],
            "Mes anterior": [moment().subtract(1, "month").startOf("month"), moment().subtract(1, "month").endOf("month")],
            "Año 2026": [moment("2026-01-01"), moment("2026-12-31")]
        };

        window.initSgtDateRange("#filtro_periodo", {
            startDate: defaultStart,
            endDate: defaultEnd,
            ranges: rangosOpciones,
            opens: "left"
        }, function (start, end, label) {
            fechaInicioFiltro = start.format("YYYY-MM-DD");
            fechaFinFiltro = end.format("YYYY-MM-DD");
            consultarDatos();
        });

        inputPeriodo.value = `${defaultStart.format("YYYY-MM-DD")} AL ${defaultEnd.format("YYYY-MM-DD")}`;
    }

    // Botones de Guardado
    const btnGuardarCotizacion = document.getElementById("btnGuardarCotizacion");
    const btnGuardarProveedor = document.getElementById("btnGuardarProveedor");
    const btnGuardarTodo = document.getElementById("btnGuardarTodo");
    const badgeTotalCambiosAmbos = document.getElementById("badgeTotalCambiosAmbos");
    let permiteSalirSinAlerta = false;

    // Indicadores de Cambios
    const cotStatusCambios = document.getElementById("cot_status_cambios");
    const provStatusCambios = document.getElementById("prov_status_cambios");

    // Contenedores Handsontable
    const containerCotizacion = document.getElementById("gridCostosCotizacion");
    const containerProveedor = document.getElementById("gridCostosProveedor");

    let hotCotizacion = null;
    let hotProveedor = null;

    let dataCotizacion = [];
    let dataProveedor = [];
    let dataCotizacionOriginal = [];
    let dataProveedorOriginal = [];
    let contenedoresSeleccionados = new Set();

    // Elementos del Filtro Multi-Check de Contenedores Sincronizado
    const inputBuscarContenedorCheck = document.getElementById("inputBuscarContenedorCheck");
    const btnCheckTodosContenedores = document.getElementById("btnCheckTodosContenedores");
    const btnCheckLimpiarContenedores = document.getElementById("btnCheckLimpiarContenedores");
    const listaContenedoresCheck = document.getElementById("listaContenedoresCheck");
    const badgeContenedoresSeleccionados = document.getElementById("badgeContenedoresSeleccionados");
    const textoDropdownContenedores = document.getElementById("textoDropdownContenedores");

    // Rastreo de filas modificadas (Dirty Tracking)
    const filasModificadasCot = new Set();
    const filasModificadasProv = new Set();

    let cambiosCotizacion = false;
    let cambiosProveedor = false;

    let isCalculatingCot = false;
    let isCalculatingProv = false;
    let isSyncingFilters = false;

    // Inicializar Select2 si está disponible
    if (window.jQuery && $.fn.select2) {
        $("#selectCliente").select2({ width: "100%", placeholder: "Todos los clientes" });
        if (selectEquipo) {
            $("#selectEquipo").select2({ width: "100%", placeholder: "Todos los equipos" });
        }
    }


    // ==========================================
    // RENDERIZADORES PERSONALIZADOS
    // ==========================================
    function textTruncateRenderer(instance, td, row, col, prop, value, cellProperties) {
        Handsontable.renderers.TextRenderer.apply(this, arguments);
        td.style.whiteSpace = "nowrap";
        td.style.overflow = "hidden";
        td.style.textOverflow = "ellipsis";
        td.style.verticalAlign = "middle";
        if (value) {
            td.title = value; // Tooltip nativo al pasar el cursor
        }
        return td;
    }

    function mapaRenderer(instance, td, row, col, prop, value, cellProperties) {
        Handsontable.renderers.BaseRenderer.apply(this, arguments);
        td.style.textAlign = "center";
        td.style.verticalAlign = "middle";
        td.style.whiteSpace = "nowrap";

        if (value && typeof value === "string" && value.trim() !== "") {
            td.innerHTML = `
                <a href="${value}" target="_blank" rel="noopener noreferrer" class="btn btn-xs btn-outline-info btn-mapa-grid" style="font-size:10px; line-height:16px; padding:1px 6px; text-decoration:none;">
                    <i class="fas fa-map-marker-alt text-danger me-1"></i>Mapa
                </a>
            `;
        } else {
            td.innerHTML = '<span class="text-muted" style="font-size:10px; line-height:16px;">Sin mapa</span>';
        }
        return td;
    }

    // ==========================================
    // CONFIGURACIÓN DE TABLA: COTIZACIÓN
    // ==========================================
    const columnsCotizacion = [
        { data: "contenedor", title: "# CONTENEDOR", readOnly: true, renderer: textTruncateRenderer, width: 140 },
        { data: "cliente", title: "CLIENTE", readOnly: true, renderer: textTruncateRenderer, width: 170 },
        { data: "destino", title: "DESTINO", readOnly: true, renderer: textTruncateRenderer, width: 150 },
        { data: "mapas", title: "MAPAS", readOnly: true, renderer: mapaRenderer, width: 85 },
        { data: "peso_contenedor", title: "PESO (TON)", type: "numeric", numericFormat: { pattern: "0,0.00" }, width: 115 },
        { data: "precio_viaje", title: "PRECIO VIAJE", type: "numeric", numericFormat: { pattern: "$ 0,0.00", culture: "en-US" }, width: 125 },
        { data: "burreo", title: "BURREO", type: "numeric", numericFormat: { pattern: "$ 0,0.00", culture: "en-US" }, width: 105 },
        { data: "maniobra", title: "MANIOBRA", type: "numeric", numericFormat: { pattern: "$ 0,0.00", culture: "en-US" }, width: 110 },
        { data: "estadia", title: "ESTADÍA", type: "numeric", numericFormat: { pattern: "$ 0,0.00", culture: "en-US" }, width: 110 },
        { data: "otro", title: "OTROS", type: "numeric", numericFormat: { pattern: "$ 0,0.00", culture: "en-US" }, width: 100 },
        { data: "sobrepeso", title: "SOBREPESO", type: "numeric", numericFormat: { pattern: "0,0.00" }, readOnly: true, width: 115 },
        { data: "precio_sobre_peso", title: "PRECIO S.PESO", type: "numeric", numericFormat: { pattern: "$ 0,0.00", culture: "en-US" }, width: 125 },
        { data: "total_sobrepeso", title: "TOT. TONELADA", type: "numeric", numericFormat: { pattern: "$ 0,0.00", culture: "en-US" }, readOnly: true, width: 125 },
        { data: "base_factura", title: "BASE 1 (FAC)", type: "numeric", numericFormat: { pattern: "$ 0,0.00", culture: "en-US" }, width: 120 },
        { data: "base_taref", title: "BASE 2 (TAR)", type: "numeric", numericFormat: { pattern: "$ 0,0.00", culture: "en-US" }, readOnly: true, width: 120 },
        { data: "iva", title: "IVA (16%)", type: "numeric", numericFormat: { pattern: "$ 0,0.00", culture: "en-US" }, readOnly: true, width: 110 },
        { data: "retencion", title: "RETENCIÓN", type: "numeric", numericFormat: { pattern: "$ 0,0.00", culture: "en-US" }, width: 115 },
        { data: "total", title: "TOTAL COTIZACIÓN", type: "numeric", numericFormat: { pattern: "$ 0,0.00", culture: "en-US" }, readOnly: true, width: 145 },
        { data: "id", title: "ID", readOnly: true },
        { data: "peso_reglamentario", title: "PESO_REG", readOnly: true },
        { data: "retencion_automatica", title: "RET_AUTO", readOnly: true },
        { data: "estatus", title: "ESTATUS", readOnly: true }
    ];

    // ==========================================
    // LIMPIEZA AUTOMÁTICA DE VALOR CERO AL EDITAR
    // ==========================================
    function limpiarCeroAlEditar(instance) {
        setTimeout(function () {
            const editor = instance.getActiveEditor();
            if (!editor) return;

            const textarea = editor.TEXTAREA 
                || editor.textarea 
                || (editor.holder ? editor.holder.querySelector(".handsontableInput") : null)
                || document.querySelector(".handsontableInput");

            if (!textarea) return;

            const val = editor.getValue();
            const num = parseNum(val);

            // Si el valor numérico es 0 o representa cero, vaciar el campo para captura inmediata
            if (num === 0 || val === 0 || val === "0" || val === "0.00" || val === "$ 0.00") {
                if (typeof editor.setValue === "function") {
                    editor.setValue("");
                }
                textarea.value = "";
            } else {
                // Si tiene un valor previo distinto de cero, seleccionar todo el texto para permitir sobrescribir al teclear
                if (typeof textarea.select === "function") {
                    textarea.select();
                }
            }
        }, 0);
    }

    hotCotizacion = new Handsontable(containerCotizacion, {
        data: dataCotizacion,
        columns: columnsCotizacion,
        hiddenColumns: { columns: [18, 19, 20, 21], indicators: false },
        rowHeaders: true,
        colHeaders: true,
        filters: true,
        dropdownMenu: ["filter_by_value", "filter_action_bar"],
        language: "es-MX",
        height: 520,
        width: "100%",
        fixedColumnsLeft: 2,
        rowHeights: 25,
        autoRowSize: false,
        autoWrapRow: true,
        licenseKey: "non-commercial-and-evaluation",
        afterBeginEditing: function (row, col) {
            limpiarCeroAlEditar(this);
        },
        afterFilter: function (conditionsStack) {
            actualizarResumenCotizacion();
            if (isSyncingFilters || !hotProveedor) return;

            isSyncingFilters = true;
            try {
                sincronizarFiltroColumnaContenedor(hotCotizacion, hotProveedor, conditionsStack);
            } catch (err) {
                console.error("Error al sincronizar filtro con Proveedor:", err);
            } finally {
                isSyncingFilters = false;
            }
        },
        afterChange: function (changes, source) {
            if (!changes || source === "loadData" || source === "calculation" || isCalculatingCot) {
                return;
            }

            let huboCambios = false;
            isCalculatingCot = true;
            changes.forEach(function ([row, prop, oldVal, newVal]) {
                if (oldVal === newVal) return;
                huboCambios = true;
                recalcularFilaCotizacion(row);

                // Obtener ID físico de la cotización modificada
                const physicalRow = hotCotizacion.toPhysicalRow(row);
                const actualRow = (physicalRow !== null && physicalRow !== undefined) ? physicalRow : row;
                const rowData = hotCotizacion.getSourceDataAtRow(actualRow);
                if (rowData && rowData.id) {
                    filasModificadasCot.add(rowData.id);
                    const orig = dataCotizacionOriginal.find(o => o.id === rowData.id);
                    if (orig) {
                        Object.assign(orig, rowData);
                    }
                }
            });
            isCalculatingCot = false;

            if (huboCambios) {
                actualizarResumenCotizacion();
                marcarCambiosCotizacion(true);
            }
        }
    });

    // ==========================================
    // CONFIGURACIÓN DE TABLA: PROVEEDOR
    // ==========================================
    const columnsProveedor = [
        { data: "contenedor", title: "# CONTENEDOR", readOnly: true, renderer: textTruncateRenderer, width: 140 },
        { data: "cliente", title: "CLIENTE", readOnly: true, renderer: textTruncateRenderer, width: 170 },
        { data: "destino", title: "DESTINO", readOnly: true, renderer: textTruncateRenderer, width: 150 },
        { data: "mapas", title: "MAPAS", readOnly: true, renderer: mapaRenderer, width: 85 },
        { data: "peso_contenedor", title: "PESO (TON)", readOnly: true, type: "numeric", numericFormat: { pattern: "0,0.00" }, width: 115 },
        { data: "proveedor", title: "PROVEEDOR", readOnly: true, renderer: textTruncateRenderer, width: 160 },
        { data: "precio_proveedor", title: "COSTO VIAJE", type: "numeric", numericFormat: { pattern: "$ 0,0.00", culture: "en-US" }, width: 125 },
        { data: "burreo_proveedor", title: "BURREO", type: "numeric", numericFormat: { pattern: "$ 0,0.00", culture: "en-US" }, width: 105 },
        { data: "maniobra_proveedor", title: "MANIOBRA", type: "numeric", numericFormat: { pattern: "$ 0,0.00", culture: "en-US" }, width: 110 },
        { data: "estadia_proveedor", title: "ESTADÍA", type: "numeric", numericFormat: { pattern: "$ 0,0.00", culture: "en-US" }, width: 110 },
        { data: "sobrepeso", title: "SOBREPESO", type: "numeric", numericFormat: { pattern: "0,0.00" }, readOnly: true, width: 115 },
        { data: "sobrepeso_proveedor", title: "PRECIO S.PESO", type: "numeric", numericFormat: { pattern: "$ 0,0.00", culture: "en-US" }, width: 125 },
        { data: "total_tonelada", title: "TOT. TONELADA", type: "numeric", numericFormat: { pattern: "$ 0,0.00", culture: "en-US" }, readOnly: true, width: 125 },
        { data: "base1_proveedor", title: "BASE 1", type: "numeric", numericFormat: { pattern: "$ 0,0.00", culture: "en-US" }, width: 110 },
        { data: "base2_proveedor", title: "BASE 2", type: "numeric", numericFormat: { pattern: "$ 0,0.00", culture: "en-US" }, readOnly: true, width: 110 },
        { data: "otro_proveedor", title: "OTROS", type: "numeric", numericFormat: { pattern: "$ 0,0.00", culture: "en-US" }, width: 100 },
        { data: "iva_proveedor", title: "IVA", type: "numeric", numericFormat: { pattern: "$ 0,0.00", culture: "en-US" }, readOnly: true, width: 105 },
        { data: "retencion_proveedor", title: "RETENCIÓN", type: "numeric", numericFormat: { pattern: "$ 0,0.00", culture: "en-US" }, readOnly: true, width: 115 },
        { data: "total_proveedor", title: "TOTAL PROVEEDOR", type: "numeric", numericFormat: { pattern: "$ 0,0.00", culture: "en-US" }, readOnly: true, width: 145 },
        { data: "id", title: "ID", readOnly: true },
        { data: "id_asignacion", title: "ID_ASIGNACION", readOnly: true },
        { data: "id_proveedor", title: "ID_PROVEEDOR", readOnly: true },
        { data: "estatus", title: "ESTATUS", readOnly: true }
    ];

    hotProveedor = new Handsontable(containerProveedor, {
        data: dataProveedor,
        columns: columnsProveedor,
        hiddenColumns: { columns: [19, 20, 21, 22], indicators: false },
        rowHeaders: true,
        colHeaders: true,
        filters: true,
        dropdownMenu: ["filter_by_value", "filter_action_bar"],
        language: "es-MX",
        height: 520,
        width: "100%",
        fixedColumnsLeft: 2,
        rowHeights: 25,
        autoRowSize: false,
        autoWrapRow: true,
        licenseKey: "non-commercial-and-evaluation",
        afterBeginEditing: function (row, col) {
            limpiarCeroAlEditar(this);
        },
        afterFilter: function (conditionsStack) {
            actualizarResumenProveedor();
            if (isSyncingFilters || !hotCotizacion) return;

            isSyncingFilters = true;
            try {
                sincronizarFiltroColumnaContenedor(hotProveedor, hotCotizacion, conditionsStack);
            } catch (err) {
                console.error("Error al sincronizar filtro con Cotización:", err);
            } finally {
                isSyncingFilters = false;
            }
        },
        afterChange: function (changes, source) {
            if (!changes || source === "loadData" || source === "calculation" || isCalculatingProv) {
                return;
            }

            let huboCambios = false;
            isCalculatingProv = true;
            changes.forEach(function ([row, prop, oldVal, newVal]) {
                if (oldVal === newVal) return;
                huboCambios = true;
                recalcularFilaProveedor(row);

                // Obtener ID físico de la cotización/asignación modificada
                const physicalRow = hotProveedor.toPhysicalRow(row);
                const actualRow = (physicalRow !== null && physicalRow !== undefined) ? physicalRow : row;
                const rowData = hotProveedor.getSourceDataAtRow(actualRow);
                if (rowData && rowData.id) {
                    filasModificadasProv.add(rowData.id);
                    const orig = dataProveedorOriginal.find(o => o.id === rowData.id);
                    if (orig) {
                        Object.assign(orig, rowData);
                    }
                }
            });
            isCalculatingProv = false;

            if (huboCambios) {
                actualizarResumenProveedor();
                marcarCambiosProveedor(true);
            }
        }
    });

    // ==========================================
    // RECALCULO DE FILAS (REACTIVIDAD)
    // ==========================================
    function parseNum(val) {
        if (typeof val === "number") return val;
        if (!val) return 0;
        let clean = String(val).replace(/[^0-9.-]+/g, "");
        let num = parseFloat(clean);
        return isNaN(num) ? 0 : num;
    }

    function recalcularFilaCotizacion(rowIndex) {
        const physicalRow = hotCotizacion.toPhysicalRow(rowIndex);
        const actualRow = (physicalRow !== null && physicalRow !== undefined) ? physicalRow : rowIndex;
        const rowData = hotCotizacion.getSourceDataAtRow(actualRow);
        if (!rowData) return;

        let pesoCont = parseNum(rowData.peso_contenedor);
        let pesoReg = parseNum(rowData.peso_reglamentario) || 22;
        let sobrepeso = Math.max(pesoCont - pesoReg, 0);

        let precioViaje = parseNum(rowData.precio_viaje);
        let burreo = parseNum(rowData.burreo);
        let maniobra = parseNum(rowData.maniobra);
        let estadia = parseNum(rowData.estadia);
        let otro = parseNum(rowData.otro);
        let precioSobrepeso = parseNum(rowData.precio_sobre_peso);

        let totalSobrepeso = Math.round(sobrepeso * precioSobrepeso * 100) / 100;
        let baseFactura = parseNum(rowData.base_factura);

        let iva = Math.round(baseFactura * 0.16 * 100) / 100;

        let retencion = parseNum(rowData.retencion);
        let retAuto = rowData.retencion_automatica !== false;
        if (retAuto) {
            retencion = Math.round(baseFactura * 0.04 * 100) / 100;
        }

        let total = precioViaje + burreo + maniobra + estadia + otro + totalSobrepeso + iva - retencion;
        total = Math.round(total * 100) / 100;

        let baseTaref = Math.round((total - baseFactura - iva + retencion) * 100) / 100;

        hotCotizacion.setDataAtRowProp([
            [rowIndex, "sobrepeso", sobrepeso],
            [rowIndex, "total_sobrepeso", totalSobrepeso],
            [rowIndex, "iva", iva],
            [rowIndex, "retencion", retencion],
            [rowIndex, "base_taref", baseTaref],
            [rowIndex, "total", total]
        ], "calculation");
    }

    function recalcularFilaProveedor(rowIndex) {
        const physicalRow = hotProveedor.toPhysicalRow(rowIndex);
        const actualRow = (physicalRow !== null && physicalRow !== undefined) ? physicalRow : rowIndex;
        const rowData = hotProveedor.getSourceDataAtRow(actualRow);
        if (!rowData) return;

        let sobrepeso = parseNum(rowData.sobrepeso);
        let precioProveedor = parseNum(rowData.precio_proveedor);
        let burreo = parseNum(rowData.burreo_proveedor);
        let maniobra = parseNum(rowData.maniobra_proveedor);
        let estadia = parseNum(rowData.estadia_proveedor);
        let otro = parseNum(rowData.otro_proveedor);
        let precioSobrepesoProv = parseNum(rowData.sobrepeso_proveedor);

        let totalTonelada = Math.round(sobrepeso * precioSobrepesoProv * 100) / 100;
        let base1 = parseNum(rowData.base1_proveedor);

        let iva = Math.round(base1 * 0.16 * 100) / 100;
        let retencion = Math.round(base1 * 0.04 * 100) / 100;

        let total = precioProveedor + burreo + maniobra + estadia + otro + totalTonelada + iva - retencion;
        total = Math.round(total * 100) / 100;

        let base2 = Math.round((total - base1 - iva + retencion) * 100) / 100;

        hotProveedor.setDataAtRowProp([
            [rowIndex, "total_tonelada", totalTonelada],
            [rowIndex, "iva_proveedor", iva],
            [rowIndex, "retencion_proveedor", retencion],
            [rowIndex, "base2_proveedor", base2],
            [rowIndex, "total_proveedor", total]
        ], "calculation");
    }

    // ==========================================
    // ACTUALIZACIÓN DE RESÚMENES SUPERIORES
    // ==========================================
    function formatMoney(num) {
        return "$" + Number(num || 0).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function actualizarResumenCotizacion() {
        if (!hotCotizacion) return;
        const count = hotCotizacion.countRows();
        let fletes = 0, burreos = 0, maniobras = 0, estadias = 0, sobrepesos = 0, otros = 0, ivas = 0, retenciones = 0, totales = 0;

        for (let r = 0; r < count; r++) {
            const physicalRow = hotCotizacion.toPhysicalRow(r);
            const actualRow = (physicalRow !== null && physicalRow !== undefined) ? physicalRow : r;
            const fila = hotCotizacion.getSourceDataAtRow(actualRow);
            if (!fila) continue;

            fletes += parseNum(fila.precio_viaje);
            burreos += parseNum(fila.burreo);
            maniobras += parseNum(fila.maniobra);
            estadias += parseNum(fila.estadia);
            sobrepesos += parseNum(fila.total_sobrepeso);
            otros += parseNum(fila.otro);
            ivas += parseNum(fila.iva);
            retenciones += parseNum(fila.retencion);
            totales += parseNum(fila.total);
        }

        const elViajes = document.getElementById("cot_resumen_viajes");
        const elFletes = document.getElementById("cot_resumen_fletes");
        const elBurreo = document.getElementById("cot_resumen_burreo");
        const elManiobra = document.getElementById("cot_resumen_maniobra");
        const elEstadia = document.getElementById("cot_resumen_estadia");
        const elSobrepeso = document.getElementById("cot_resumen_sobrepeso");
        const elOtros = document.getElementById("cot_resumen_otros");
        const elIva = document.getElementById("cot_resumen_iva");
        const elRetencion = document.getElementById("cot_resumen_retencion");
        const elTotal = document.getElementById("cot_resumen_total");

        if (elViajes) elViajes.textContent = count;
        if (elFletes) elFletes.textContent = formatMoney(fletes);
        if (elBurreo) elBurreo.textContent = formatMoney(burreos);
        if (elManiobra) elManiobra.textContent = formatMoney(maniobras);
        if (elEstadia) elEstadia.textContent = formatMoney(estadias);
        if (elSobrepeso) elSobrepeso.textContent = formatMoney(sobrepesos);
        if (elOtros) elOtros.textContent = formatMoney(otros);
        if (elIva) elIva.textContent = formatMoney(ivas);
        if (elRetencion) elRetencion.textContent = formatMoney(retenciones);
        if (elTotal) elTotal.textContent = formatMoney(totales);
    }

    function actualizarResumenProveedor() {
        if (!hotProveedor) return;
        const count = hotProveedor.countRows();
        let fletes = 0, burreos = 0, maniobras = 0, estadias = 0, sobrepesos = 0, otros = 0, ivas = 0, retenciones = 0, totales = 0;

        for (let r = 0; r < count; r++) {
            const physicalRow = hotProveedor.toPhysicalRow(r);
            const actualRow = (physicalRow !== null && physicalRow !== undefined) ? physicalRow : r;
            const fila = hotProveedor.getSourceDataAtRow(actualRow);
            if (!fila) continue;

            fletes += parseNum(fila.precio_proveedor);
            burreos += parseNum(fila.burreo_proveedor);
            maniobras += parseNum(fila.maniobra_proveedor);
            estadias += parseNum(fila.estadia_proveedor);
            sobrepesos += parseNum(fila.total_tonelada);
            otros += parseNum(fila.otro_proveedor);
            ivas += parseNum(fila.iva_proveedor);
            retenciones += parseNum(fila.retencion_proveedor);
            totales += parseNum(fila.total_proveedor);
        }

        const elViajes = document.getElementById("prov_resumen_viajes");
        const elFletes = document.getElementById("prov_resumen_fletes");
        const elBurreo = document.getElementById("prov_resumen_burreo");
        const elManiobra = document.getElementById("prov_resumen_maniobra");
        const elEstadia = document.getElementById("prov_resumen_estadia");
        const elSobrepeso = document.getElementById("prov_resumen_sobrepeso");
        const elOtros = document.getElementById("prov_resumen_otros");
        const elIva = document.getElementById("prov_resumen_iva");
        const elRetencion = document.getElementById("prov_resumen_retencion");
        const elTotal = document.getElementById("prov_resumen_total");

        if (elViajes) elViajes.textContent = count;
        if (elFletes) elFletes.textContent = formatMoney(fletes);
        if (elBurreo) elBurreo.textContent = formatMoney(burreos);
        if (elManiobra) elManiobra.textContent = formatMoney(maniobras);
        if (elEstadia) elEstadia.textContent = formatMoney(estadias);
        if (elSobrepeso) elSobrepeso.textContent = formatMoney(sobrepesos);
        if (elOtros) elOtros.textContent = formatMoney(otros);
        if (elIva) elIva.textContent = formatMoney(ivas);
        if (elRetencion) elRetencion.textContent = formatMoney(retenciones);
        if (elTotal) elTotal.textContent = formatMoney(totales);
    }

    function actualizarEstadoBotonGuardarTodo() {
        if (!btnGuardarTodo) return;
        const countCot = filasModificadasCot.size;
        const countProv = filasModificadasProv.size;

        // Solo aparece si detecta cambios en AMBAS pestañas
        if (countCot > 0 && countProv > 0) {
            btnGuardarTodo.classList.remove("d-none");
            if (badgeTotalCambiosAmbos) {
                badgeTotalCambiosAmbos.textContent = `${countCot} cot + ${countProv} prov`;
            }
        } else {
            btnGuardarTodo.classList.add("d-none");
        }
    }

    function marcarCambiosCotizacion(tiene) {
        cambiosCotizacion = tiene && filasModificadasCot.size > 0;
        btnGuardarCotizacion.disabled = !cambiosCotizacion;
        if (cambiosCotizacion) {
            const count = filasModificadasCot.size;
            cotStatusCambios.innerHTML = `<span class="badge bg-warning text-dark"><i class="fas fa-exclamation-triangle me-1"></i>${count} viaje(s) modificado(s) sin guardar</span>`;
        } else {
            cotStatusCambios.innerHTML = '<span class="text-secondary"><i class="fas fa-check-circle text-success me-1"></i>Sin cambios pendientes</span>';
        }
        actualizarEstadoBotonGuardarTodo();
    }

    function marcarCambiosProveedor(tiene) {
        cambiosProveedor = tiene && filasModificadasProv.size > 0;
        btnGuardarProveedor.disabled = !cambiosProveedor;
        if (cambiosProveedor) {
            const count = filasModificadasProv.size;
            provStatusCambios.innerHTML = `<span class="badge bg-warning text-dark"><i class="fas fa-exclamation-triangle me-1"></i>${count} viaje(s) modificado(s) sin guardar</span>`;
        } else {
            provStatusCambios.innerHTML = '<span class="text-secondary"><i class="fas fa-check-circle text-success me-1"></i>Sin cambios pendientes</span>';
        }
        actualizarEstadoBotonGuardarTodo();
    }

    // ==========================================
    // CONSULTA ASÍNCRONA DE DATOS
    // ==========================================
    function consultarDatos() {
        const clienteId = selectCliente ? selectCliente.value : "";
        const estadoCosto = selectEstadoCosto ? selectEstadoCosto.value : "todos";
        const equipoId = selectEquipo ? selectEquipo.value : "";

        // Obtener rango de fechas activo
        let fechaIni = fechaInicioFiltro;
        let fechaFin = fechaFinFiltro;
        if (window.jQuery && $("#filtro_periodo").data("daterangepicker")) {
            const drp = $("#filtro_periodo").data("daterangepicker");
            fechaIni = drp.startDate.format("YYYY-MM-DD");
            fechaFin = drp.endDate.format("YYYY-MM-DD");
        }

        Swal.fire({
            title: "Cargando viajes...",
            text: "Consultando cotizaciones y costos de seguimiento",
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const params = new URLSearchParams({
            estado_costo: estadoCosto
        });
        if (clienteId) {
            params.append("cliente_id", clienteId);
        }
        if (equipoId) {
            params.append("equipo_id", equipoId);
        }
        if (fechaIni) {
            params.append("fecha_inicio", fechaIni);
        }
        if (fechaFin) {
            params.append("fecha_fin", fechaFin);
        }

        fetch("/cotizaciones/costos-multiple/datos?" + params.toString(), {
            method: "GET",
            headers: {
                "Accept": "application/json",
                "X-Requested-With": "XMLHttpRequest"
            }
        })
        .then(async res => {
            const data = await res.json().catch(() => ({}));
            if (!res.ok) {
                throw new Error(data.Mensaje || `Error del servidor (código ${res.status})`);
            }
            return data;
        })
        .then(res => {
            Swal.close();

            if (res.TMensaje === "success") {
                filasModificadasCot.clear();
                filasModificadasProv.clear();

                dataCotizacionOriginal = (res.cotizaciones || []).map(r => ({ ...r }));
                dataProveedorOriginal = (res.proveedores || []).map(r => ({ ...r }));

                contenedoresSeleccionados.clear();
                if (inputBuscarContenedorCheck) {
                    inputBuscarContenedorCheck.value = "";
                }

                dataCotizacion = [...dataCotizacionOriginal];
                dataProveedor = [...dataProveedorOriginal];

                hotCotizacion.loadData(dataCotizacion);
                hotProveedor.loadData(dataProveedor);

                poblarSelectorContenedores();

                actualizarResumenCotizacion();
                actualizarResumenProveedor();

                marcarCambiosCotizacion(false);
                marcarCambiosProveedor(false);

                if (dataCotizacion.length === 0 && dataProveedor.length === 0) {
                    Swal.fire({
                        icon: "info",
                        title: "Sin Viajes",
                        text: "No se encontraron viajes en seguimiento con los filtros seleccionados."
                    });
                }
            } else {
                Swal.fire({
                    icon: "error",
                    title: res.Titulo || "Error al consultar",
                    text: res.Mensaje || "No se pudieron obtener los viajes."
                });
            }
        })
        .catch(err => {
            Swal.close();
            console.error("Error al consultar datos:", err);
            Swal.fire({
                icon: "error",
                title: "Error al consultar",
                text: err.message || "Ocurrió un error al contactar al servidor."
            });
        });
    }

    // ==========================================
    // SINCRONIZACIÓN Y FILTRO MULTI-CHECK DE CONTENEDORES
    // ==========================================
    function poblarSelectorContenedores() {
        if (!listaContenedoresCheck) return;

        // Extraer lista única de contenedores de ambos datasets
        const mapa = new Map();
        dataCotizacionOriginal.forEach(r => {
            if (r.contenedor && !mapa.has(r.contenedor)) {
                mapa.set(r.contenedor, { contenedor: r.contenedor, cliente: r.cliente || "Sin cliente" });
            }
        });
        dataProveedorOriginal.forEach(r => {
            if (r.contenedor && !mapa.has(r.contenedor)) {
                mapa.set(r.contenedor, { contenedor: r.contenedor, cliente: r.cliente || "Sin cliente" });
            }
        });

        const contenedores = Array.from(mapa.values()).sort((a, b) => a.contenedor.localeCompare(b.contenedor));

        if (contenedores.length === 0) {
            listaContenedoresCheck.innerHTML = '<div class="text-muted text-center py-4 fs-7"><i class="fas fa-box-open d-block mb-1 fs-5 opacity-50"></i>No hay contenedores cargados</div>';
            actualizarIndicadorFiltroContenedores(0, 0);
            return;
        }

        let html = "";
        contenedores.forEach((item, idx) => {
            const isChecked = contenedoresSeleccionados.has(item.contenedor);
            html += `
                <div class="item-check-contenedor ${isChecked ? 'item-selected' : ''} border-bottom border-light d-flex align-items-center" data-contenedor="${item.contenedor.toLowerCase()}" data-cliente="${item.cliente.toLowerCase()}">
                    <input class="check-contenedor-item me-2" type="checkbox" value="${item.contenedor}" id="chk_cnt_${idx}" ${isChecked ? "checked" : ""}>
                    <label class="form-check-label text-dark fs-7 cursor-pointer flex-grow-1 mb-0 d-flex justify-content-between align-items-center" for="chk_cnt_${idx}">
                        <div class="text-truncate pe-1" style="max-width: 250px;">
                            <span class="fw-bold ${isChecked ? 'text-primary' : 'text-dark'} d-block text-truncate">${item.contenedor}</span>
                            <small class="text-muted text-truncate d-block" style="font-size: 11px;">${item.cliente}</small>
                        </div>
                        <span class="badge badge-sm ${isChecked ? 'bg-primary text-white' : 'd-none'} badge-item-check py-0 px-1" style="font-size: 10px;">
                            <i class="fas fa-check text-xxs"></i>
                        </span>
                    </label>
                </div>
            `;
        });

        listaContenedoresCheck.innerHTML = html;

        // Listeners para cada checkbox
        listaContenedoresCheck.querySelectorAll(".check-contenedor-item").forEach(chk => {
            chk.addEventListener("change", function () {
                const parent = this.closest(".item-check-contenedor");
                const badgeCheck = parent ? parent.querySelector(".badge-item-check") : null;
                const txtCont = parent ? parent.querySelector(".fw-bold") : null;

                if (this.checked) {
                    contenedoresSeleccionados.add(this.value);
                    if (parent) parent.classList.add("item-selected");
                    if (badgeCheck) badgeCheck.classList.remove("d-none");
                    if (txtCont) {
                        txtCont.classList.remove("text-dark");
                        txtCont.classList.add("text-primary");
                    }
                } else {
                    contenedoresSeleccionados.delete(this.value);
                    if (parent) parent.classList.remove("item-selected");
                    if (badgeCheck) badgeCheck.classList.add("d-none");
                    if (txtCont) {
                        txtCont.classList.remove("text-primary");
                        txtCont.classList.add("text-dark");
                    }
                }
                aplicarFiltroContenedoresSincronizado();
            });
        });

        actualizarIndicadorFiltroContenedores(contenedoresSeleccionados.size, contenedores.length);
    }

    function aplicarFiltroContenedoresSincronizado() {
        if (!hotCotizacion || !hotProveedor) return;

        const totalDisponibles = new Set([
            ...dataCotizacionOriginal.map(r => r.contenedor),
            ...dataProveedorOriginal.map(r => r.contenedor)
        ].filter(Boolean)).size;

        if (contenedoresSeleccionados.size === 0 || contenedoresSeleccionados.size === totalDisponibles) {
            // Sin filtro específico, mostrar todos los datos
            dataCotizacion = [...dataCotizacionOriginal];
            dataProveedor = [...dataProveedorOriginal];
            actualizarIndicadorFiltroContenedores(0, totalDisponibles);
        } else {
            // Filtrar ambos datasets por los contenedores marcados
            dataCotizacion = dataCotizacionOriginal.filter(r => contenedoresSeleccionados.has(r.contenedor));
            dataProveedor = dataProveedorOriginal.filter(r => contenedoresSeleccionados.has(r.contenedor));
            actualizarIndicadorFiltroContenedores(contenedoresSeleccionados.size, totalDisponibles);
        }

        hotCotizacion.loadData(dataCotizacion);
        hotProveedor.loadData(dataProveedor);

        actualizarResumenCotizacion();
        actualizarResumenProveedor();
    }

    function actualizarIndicadorFiltroContenedores(seleccionados, total) {
        if (badgeContenedoresSeleccionados) {
            if (seleccionados > 0 && seleccionados < total) {
                badgeContenedoresSeleccionados.className = "badge badge-sm bg-gradient-primary text-white border-0";
                badgeContenedoresSeleccionados.textContent = `${seleccionados} de ${total} seleccionados`;
                if (textoDropdownContenedores) {
                    textoDropdownContenedores.innerHTML = `<i class="fas fa-filter text-primary me-1"></i> <strong class="text-primary">${seleccionados} contenedor(es) filtrado(s)</strong>`;
                }
            } else {
                badgeContenedoresSeleccionados.className = "badge badge-sm bg-light text-dark border";
                badgeContenedoresSeleccionados.textContent = "Todos (sin filtro)";
                if (textoDropdownContenedores) {
                    textoDropdownContenedores.innerHTML = '<i class="fas fa-boxes text-primary me-1"></i> Seleccionar contenedores para sincronizar...';
                }
            }
        }
    }

    function sincronizarEstadoCheckboxesSuperiores(setContenedores) {
        if (!listaContenedoresCheck) return;
        const totalDisponibles = new Set([
            ...dataCotizacionOriginal.map(r => r.contenedor),
            ...dataProveedorOriginal.map(r => r.contenedor)
        ].filter(Boolean)).size;

        const items = listaContenedoresCheck.querySelectorAll(".check-contenedor-item");
        items.forEach(chk => {
            const isChecked = setContenedores.has(chk.value);
            chk.checked = isChecked;
            const parent = chk.closest(".item-check-contenedor");
            if (parent) {
                if (isChecked) {
                    parent.classList.add("item-selected");
                    const b = parent.querySelector(".badge-item-check");
                    if (b) b.classList.remove("d-none");
                    const txt = parent.querySelector(".fw-bold");
                    if (txt) {
                        txt.classList.remove("text-dark");
                        txt.classList.add("text-primary");
                    }
                } else {
                    parent.classList.remove("item-selected");
                    const b = parent.querySelector(".badge-item-check");
                    if (b) b.classList.add("d-none");
                    const txt = parent.querySelector(".fw-bold");
                    if (txt) {
                        txt.classList.remove("text-primary");
                        txt.classList.add("text-dark");
                    }
                }
            }
        });

        actualizarIndicadorFiltroContenedores(setContenedores.size, totalDisponibles);
    }

    function sincronizarFiltroColumnaContenedor(origenHot, destinoHot, conditionsStack) {
        if (!destinoHot) return;
        const pluginDest = destinoHot.getPlugin("filters");
        if (!pluginDest) return;

        // La columna 0 es siempre 'contenedor' en ambas tablas
        const condCol0 = (conditionsStack || []).find(c => c.column === 0);

        if (condCol0 && condCol0.conditions && condCol0.conditions.length > 0) {
            pluginDest.removeConditions(0);
            condCol0.conditions.forEach(c => {
                pluginDest.addCondition(0, c.name, c.args, condCol0.operation);
            });
            pluginDest.filter();

            // Sincronizar el dropdown superior si es filtro por valor
            const byVal = condCol0.conditions.find(c => c.name === "by_value");
            if (byVal && Array.isArray(byVal.args) && Array.isArray(byVal.args[0])) {
                contenedoresSeleccionados = new Set(byVal.args[0]);
                sincronizarEstadoCheckboxesSuperiores(contenedoresSeleccionados);
            }
        } else {
            // Se limpió o no hay filtro en columna 0
            pluginDest.removeConditions(0);
            pluginDest.filter();

            contenedoresSeleccionados.clear();
            sincronizarEstadoCheckboxesSuperiores(contenedoresSeleccionados);
        }

        actualizarResumenCotizacion();
        actualizarResumenProveedor();
    }

    // Buscador en tiempo real dentro del dropdown
    if (inputBuscarContenedorCheck && listaContenedoresCheck) {
        inputBuscarContenedorCheck.addEventListener("input", function () {
            const query = this.value.trim().toLowerCase();
            const items = listaContenedoresCheck.querySelectorAll(".item-check-contenedor");
            items.forEach(item => {
                const cont = item.getAttribute("data-contenedor") || "";
                const cli = item.getAttribute("data-cliente") || "";
                if (!query || cont.includes(query) || cli.includes(query)) {
                    item.style.setProperty("display", "flex", "important");
                } else {
                    item.style.setProperty("display", "none", "important");
                }
            });
        });
    }

    // Botón Seleccionar Todos
    if (btnCheckTodosContenedores && listaContenedoresCheck) {
        btnCheckTodosContenedores.addEventListener("click", function (e) {
            e.stopPropagation();
            const items = listaContenedoresCheck.querySelectorAll(".check-contenedor-item");
            items.forEach(chk => {
                const parent = chk.closest(".item-check-contenedor");
                if (!parent || parent.style.display !== "none") {
                    chk.checked = true;
                    contenedoresSeleccionados.add(chk.value);
                    if (parent) {
                        parent.classList.add("item-selected");
                        const badgeCheck = parent.querySelector(".badge-item-check");
                        if (badgeCheck) badgeCheck.classList.remove("d-none");
                        const txtCont = parent.querySelector(".fw-bold");
                        if (txtCont) {
                            txtCont.classList.remove("text-dark");
                            txtCont.classList.add("text-primary");
                        }
                    }
                }
            });
            aplicarFiltroContenedoresSincronizado();
        });
    }

    // Botón Limpiar Selección
    if (btnCheckLimpiarContenedores && listaContenedoresCheck) {
        btnCheckLimpiarContenedores.addEventListener("click", function (e) {
            e.stopPropagation();
            const items = listaContenedoresCheck.querySelectorAll(".check-contenedor-item");
            items.forEach(chk => {
                chk.checked = false;
                const parent = chk.closest(".item-check-contenedor");
                if (parent) {
                    parent.classList.remove("item-selected");
                    const badgeCheck = parent.querySelector(".badge-item-check");
                    if (badgeCheck) badgeCheck.classList.add("d-none");
                    const txtCont = parent.querySelector(".fw-bold");
                    if (txtCont) {
                        txtCont.classList.remove("text-primary");
                        txtCont.classList.add("text-dark");
                    }
                }
            });
            contenedoresSeleccionados.clear();
            aplicarFiltroContenedoresSincronizado();
        });
    }

    if (btnConsultar) {
        btnConsultar.addEventListener("click", function () {
            const tieneCambios = filasModificadasCot.size > 0 || filasModificadasProv.size > 0;
            if (tieneCambios) {
                confirmarSalidaSiHayCambios(
                    function () {
                        consultarDatos();
                    },
                    null,
                    "consultar"
                );
            } else {
                consultarDatos();
            }
        });
    }

    if (selectEstadoCosto) {
        selectEstadoCosto.addEventListener("change", function () {
            const tieneCambios = filasModificadasCot.size > 0 || filasModificadasProv.size > 0;
            if (tieneCambios) {
                confirmarSalidaSiHayCambios(
                    function () {
                        consultarDatos();
                    },
                    null,
                    "consultar"
                );
            } else {
                consultarDatos();
            }
        });
    }

    if (btnLimpiar) {
        btnLimpiar.addEventListener("click", function () {
            const ejecutarLimpieza = function () {
                if (window.jQuery && $.fn.select2) {
                    $("#selectCliente").val("").trigger("change");
                    if (selectEquipo) $("#selectEquipo").val("").trigger("change");
                } else {
                    if (selectCliente) selectCliente.value = "";
                    if (selectEquipo) selectEquipo.value = "";
                }
                if (selectEstadoCosto) {
                    selectEstadoCosto.value = "todos";
                }
                if (window.jQuery && $("#filtro_periodo").data("daterangepicker")) {
                    const drp = $("#filtro_periodo").data("daterangepicker");
                    drp.setStartDate(defaultStart.clone());
                    drp.setEndDate(defaultEnd.clone());
                    fechaInicioFiltro = defaultStart.format("YYYY-MM-DD");
                    fechaFinFiltro = defaultEnd.format("YYYY-MM-DD");
                    $("#filtro_periodo").val(`${fechaInicioFiltro} AL ${fechaFinFiltro}`);
                }
                contenedoresSeleccionados.clear();
                if (inputBuscarContenedorCheck) {
                    inputBuscarContenedorCheck.value = "";
                }
                consultarDatos();
            };

            const tieneCambios = filasModificadasCot.size > 0 || filasModificadasProv.size > 0;
            if (tieneCambios) {
                confirmarSalidaSiHayCambios(
                    ejecutarLimpieza,
                    null,
                    "consultar"
                );
            } else {
                ejecutarLimpieza();
            }
        });
    }

    // ==========================================
    // FUNCIONES ASÍNCRONAS DE GUARDADO
    // ==========================================
    async function guardarCostosCotizacionAsync() {
        if (filasModificadasCot.size === 0) return { success: true, count: 0 };

        const todasLasFilas = (dataCotizacionOriginal && dataCotizacionOriginal.length > 0) ? dataCotizacionOriginal : dataCotizacion;
        const filas = todasLasFilas.filter(r => r && r.id && filasModificadasCot.has(r.id));

        if (filas.length === 0) return { success: true, count: 0 };

        // Validar que los pesos no excedan 60 toneladas en las filas modificadas
        for (let i = 0; i < filas.length; i++) {
            let p = parseNum(filas[i].peso_contenedor);
            if (p > 60) {
                throw new Error(`El contenedor ${filas[i].contenedor} tiene un peso de ${p} ton, el cual excede el máximo permitido de 60 ton.`);
            }
        }

        const res = await fetch("/cotizaciones/costos-multiple/guardar-cotizacion", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "X-CSRF-TOKEN": csrfToken,
                "X-Requested-With": "XMLHttpRequest"
            },
            body: JSON.stringify({ filas: filas })
        });

        const data = await res.json().catch(() => ({}));
        if (!res.ok || data.TMensaje !== "success") {
            throw new Error(data.Mensaje || `Error al guardar cotizaciones (código ${res.status})`);
        }

        filasModificadasCot.clear();
        marcarCambiosCotizacion(false);
        return { success: true, count: filas.length, mensaje: data.Mensaje || "Costos de cotización actualizados." };
    }

    async function guardarCostosProveedorAsync() {
        if (filasModificadasProv.size === 0) return { success: true, count: 0 };

        const todasLasFilas = (dataProveedorOriginal && dataProveedorOriginal.length > 0) ? dataProveedorOriginal : dataProveedor;
        const filas = todasLasFilas.filter(r => r && r.id && filasModificadasProv.has(r.id));

        if (filas.length === 0) return { success: true, count: 0 };

        const res = await fetch("/cotizaciones/costos-multiple/guardar-proveedor", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "X-CSRF-TOKEN": csrfToken,
                "X-Requested-With": "XMLHttpRequest"
            },
            body: JSON.stringify({ filas: filas })
        });

        const data = await res.json().catch(() => ({}));
        if (!res.ok || data.TMensaje !== "success") {
            throw new Error(data.Mensaje || `Error al guardar costos de proveedor (código ${res.status})`);
        }

        filasModificadasProv.clear();
        marcarCambiosProveedor(false);
        return { success: true, count: filas.length, mensaje: data.Mensaje || "Costos de proveedor actualizados." };
    }

    // ==========================================
    // GUARDADO MASIVO: COTIZACIÓN
    // ==========================================
    if (btnGuardarCotizacion) {
        btnGuardarCotizacion.addEventListener("click", async function () {
            if (filasModificadasCot.size === 0) {
                Swal.fire({
                    icon: "info",
                    title: "Sin modificaciones",
                    text: "No se han detectado cambios en los costos de cotización para guardar."
                });
                return;
            }

            const count = filasModificadasCot.size;
            const confirmResult = await Swal.fire({
                title: "¿Guardar Costos de Cotización?",
                text: `Se actualizarán únicamente los costos de ${count} viaje(s) modificado(s).`,
                icon: "question",
                showCancelButton: true,
                confirmButtonText: "Sí, guardar",
                cancelButtonText: "Cancelar"
            });
            if (!confirmResult.isConfirmed) return;

            btnGuardarCotizacion.disabled = true;
            btnGuardarCotizacion.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Guardando...';

            Swal.fire({
                title: "Guardando cambios...",
                text: `Actualizando costos de ${count} viaje(s). Por favor espere...`,
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            try {
                const res = await guardarCostosCotizacionAsync();
                Swal.fire({
                    icon: "success",
                    title: "Guardado exitoso",
                    text: res.mensaje || "Los costos se actualizaron correctamente."
                }).then(() => {
                    consultarDatos();
                });
            } catch (err) {
                console.error("Error al guardar cotizaciones:", err);
                Swal.fire({
                    icon: "error",
                    title: "Error al guardar",
                    text: err.message || "Ocurrió un error inesperado al procesar el guardado."
                });
            } finally {
                btnGuardarCotizacion.disabled = filasModificadasCot.size === 0;
                btnGuardarCotizacion.innerHTML = '<i class="fas fa-save me-1"></i> Guardar Costos Cotización';
            }
        });
    }

    // ==========================================
    // GUARDADO MASIVO: PROVEEDOR
    // ==========================================
    if (btnGuardarProveedor) {
        btnGuardarProveedor.addEventListener("click", async function () {
            if (filasModificadasProv.size === 0) {
                Swal.fire({
                    icon: "info",
                    title: "Sin modificaciones",
                    text: "No se han detectado cambios en los costos de proveedor para guardar."
                });
                return;
            }

            const count = filasModificadasProv.size;
            const confirmResult = await Swal.fire({
                title: "¿Guardar Costos de Proveedor?",
                text: `Se actualizarán únicamente los costos de ${count} viaje(s) modificado(s).`,
                icon: "question",
                showCancelButton: true,
                confirmButtonText: "Sí, guardar",
                cancelButtonText: "Cancelar"
            });
            if (!confirmResult.isConfirmed) return;

            btnGuardarProveedor.disabled = true;
            btnGuardarProveedor.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Guardando...';

            Swal.fire({
                title: "Guardando cambios...",
                text: `Actualizando costos de ${count} viaje(s). Por favor espere...`,
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            try {
                const res = await guardarCostosProveedorAsync();
                Swal.fire({
                    icon: "success",
                    title: "Guardado exitoso",
                    text: res.mensaje || "Los costos de proveedor se actualizaron correctamente."
                }).then(() => {
                    consultarDatos();
                });
            } catch (err) {
                console.error("Error al guardar proveedor:", err);
                Swal.fire({
                    icon: "error",
                    title: "Error al guardar",
                    text: err.message || "Ocurrió un error inesperado al procesar el guardado."
                });
            } finally {
                btnGuardarProveedor.disabled = filasModificadasProv.size === 0;
                btnGuardarProveedor.innerHTML = '<i class="fas fa-save me-1"></i> Guardar Costos Proveedor';
            }
        });
    }

    // ==========================================
    // GUARDAR TODO (AMBAS PESTAÑAS SIMULTÁNEAMENTE)
    // ==========================================
    if (btnGuardarTodo) {
        btnGuardarTodo.addEventListener("click", async function () {
            const countCot = filasModificadasCot.size;
            const countProv = filasModificadasProv.size;

            if (countCot === 0 || countProv === 0) return;

            const confirmResult = await Swal.fire({
                title: "¿Guardar Todo (Ambas Pestañas)?",
                html: `Se actualizarán simultáneamente los costos de:<br>
                       <b class="text-primary">${countCot} viaje(s) de Cotización</b> y <b class="text-success">${countProv} viaje(s) de Proveedor</b>.`,
                icon: "question",
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-save me-1"></i> Sí, guardar todo',
                cancelButtonText: "Cancelar"
            });
            if (!confirmResult.isConfirmed) return;

            btnGuardarTodo.disabled = true;
            btnGuardarTodo.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Guardando Todo...';

            Swal.fire({
                title: "Guardando ambas pestañas...",
                text: `Actualizando ${countCot} cotizaciones y ${countProv} proveedores...`,
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            try {
                await guardarCostosCotizacionAsync();
                await guardarCostosProveedorAsync();

                actualizarEstadoBotonGuardarTodo();

                await Swal.fire({
                    icon: "success",
                    title: "¡Guardado Completo!",
                    text: `Se actualizaron correctamente ${countCot} viaje(s) de cotización y ${countProv} viaje(s) de proveedor.`
                });

                consultarDatos();
            } catch (err) {
                console.error("Error al guardar todo:", err);
                Swal.fire({
                    icon: "error",
                    title: "Error al guardar todo",
                    text: err.message || "Ocurrió un error inesperado al procesar el guardado conjunto."
                });
            } finally {
                btnGuardarTodo.disabled = false;
                btnGuardarTodo.innerHTML = '<i class="fas fa-save fs-6"></i> <span>Guardar Todo (Ambas Pestañas)</span> <span class="badge bg-white text-success font-weight-bold" id="badgeTotalCambiosAmbos">0</span>';
                actualizarEstadoBotonGuardarTodo();
            }
        });
    }

    // ==========================================
    // PREVENCIÓN AL SALIR O RECARGAR SIN GUARDAR
    // ==========================================
    // Función reutilizable para modal SweetAlert con 3 opciones estandarizadas
    function confirmarSalidaSiHayCambios(onSalir, onCancelar, accion = "salir") {
        const countCot = filasModificadasCot.size;
        const countProv = filasModificadasProv.size;
        const tieneCambios = countCot > 0 || countProv > 0;

        if (!tieneCambios) {
            permiteSalirSinAlerta = true;
            if (typeof onSalir === "function") onSalir();
            return;
        }

        let detalleTexto = "";
        if (countCot > 0 && countProv > 0) {
            detalleTexto = `${countCot} viaje(s) de Cotización y ${countProv} viaje(s) de Proveedor`;
        } else if (countCot > 0) {
            detalleTexto = `${countCot} viaje(s) de Cotización`;
        } else {
            detalleTexto = `${countProv} viaje(s) de Proveedor`;
        }

        let tituloModal = "¿Desea salir sin guardar?";
        let btnConfirmarTexto = '<i class="fas fa-save me-1"></i> Guardar y salir';
        let btnDenegarTexto = '<i class="fas fa-sign-out-alt me-1"></i> Salir sin guardar';
        let subtituloModal = `Tiene modificaciones sin guardar en <b>${detalleTexto}</b>.<br><br>¿Qué desea hacer antes de continuar?`;

        if (accion === "recargar") {
            tituloModal = "¿Desea recargar la página?";
            btnConfirmarTexto = '<i class="fas fa-save me-1"></i> Guardar y recargar';
            btnDenegarTexto = '<i class="fas fa-redo me-1"></i> Recargar sin guardar';
            subtituloModal = `Tiene modificaciones sin guardar en <b>${detalleTexto}</b>.<br><br>Si recarga ahora, se perderán los cambios no guardados.`;
        } else if (accion === "consultar") {
            tituloModal = "¿Desea actualizar la consulta?";
            btnConfirmarTexto = '<i class="fas fa-save me-1"></i> Guardar y consultar';
            btnDenegarTexto = '<i class="fas fa-undo me-1"></i> Consultar sin guardar';
            subtituloModal = `Tiene modificaciones sin guardar en <b>${detalleTexto}</b>.<br><br>¿Desea guardarlas antes de actualizar la lista?`;
        }

        Swal.fire({
            title: tituloModal,
            html: subtituloModal,
            icon: "warning",
            showDenyButton: true,
            showCancelButton: true,
            confirmButtonText: btnConfirmarTexto,
            denyButtonText: btnDenegarTexto,
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#28a745',
            denyButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            allowOutsideClick: false
        }).then(async (result) => {
            if (result.isConfirmed) {
                // Opción 1: Guardar y proceder
                Swal.fire({
                    title: "Guardando cambios...",
                    text: "Por favor espere mientras se guardan sus modificaciones...",
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                try {
                    if (filasModificadasCot.size > 0) {
                        await guardarCostosCotizacionAsync();
                    }
                    if (filasModificadasProv.size > 0) {
                        await guardarCostosProveedorAsync();
                    }
                    permiteSalirSinAlerta = true;
                    if (typeof onSalir === "function") onSalir();
                } catch (err) {
                    console.error("Error al guardar antes de proceder:", err);
                    Swal.fire({
                        icon: "error",
                        title: "Error al guardar",
                        text: err.message || "No se pudieron guardar los cambios. La acción fue cancelada."
                    });
                    if (typeof onCancelar === "function") onCancelar();
                }
            } else if (result.isDenied) {
                // Opción 2: Proceder sin guardar
                permiteSalirSinAlerta = true;
                if (typeof onSalir === "function") onSalir();
            } else {
                // Opción 3: Cancelar
                if (typeof onCancelar === "function") onCancelar();
            }
        });
    }

    // 1. Intercepción prioritaria de atajos de recarga (F5, Ctrl+R, Cmd+R)
    window.addEventListener("keydown", function (e) {
        const isF5 = e.key === "F5" || e.keyCode === 116;
        const isCtrlR = (e.ctrlKey || e.metaKey) && (e.key === "r" || e.key === "R" || e.keyCode === 82);

        if (isF5 || isCtrlR) {
            const tieneCambios = filasModificadasCot.size > 0 || filasModificadasProv.size > 0;
            if (tieneCambios && !permiteSalirSinAlerta) {
                e.preventDefault();
                e.stopPropagation();

                confirmarSalidaSiHayCambios(
                    function () {
                        permiteSalirSinAlerta = true;
                        window.location.reload();
                    },
                    function () {
                        // Cancelar: el usuario permanece en la página
                    },
                    "recargar"
                );
            }
        }
    }, true);

    // 2. Recarga de navegador o cierre de pestaña (beforeunload para clics en botón de recarga del browser)
    window.addEventListener("beforeunload", function (e) {
        if (!permiteSalirSinAlerta && (filasModificadasCot.size > 0 || filasModificadasProv.size > 0)) {
            e.preventDefault();
            e.returnValue = "Tiene modificaciones pendientes en los costos sin guardar. ¿Está seguro de que desea salir?";
            return e.returnValue;
        }
    });

    // 3. Intercepción del botón Atrás / Retroceder del navegador (popstate)
    function colocarBarreraHistorial() {
        try {
            if (!window.history.state || !window.history.state.sgt_costos_barrera) {
                window.history.pushState({ sgt_costos_barrera: true }, "", window.location.href);
            }
        } catch (e) {
            // ignore
        }
    }

    colocarBarreraHistorial();

    window.addEventListener("popstate", function () {
        if (permiteSalirSinAlerta) {
            return;
        }

        const tieneCambios = filasModificadasCot.size > 0 || filasModificadasProv.size > 0;
        if (!tieneCambios) {
            permiteSalirSinAlerta = true;
            window.history.back();
            return;
        }

        // Re-colocar barrera para evitar retroceso involuntario mientras decide
        colocarBarreraHistorial();

        // Mostrar SweetAlert con 3 opciones
        confirmarSalidaSiHayCambios(
            function () {
                // Salir (sea tras guardar o salir sin guardar)
                permiteSalirSinAlerta = true;
                window.history.go(-2);
            },
            function () {
                // Cancelar: el usuario permanece en la página
            },
            "salir"
        );
    });

    // 4. Clics en enlaces internos de navegación (etiquetas <a>)
    document.addEventListener("click", function (e) {
        if (permiteSalirSinAlerta) return;

        const link = e.target.closest("a");
        if (!link) return;

        const href = link.getAttribute("href");
        if (!href || href === "#" || href.startsWith("#") || href.startsWith("javascript:") || link.target === "_blank") {
            return;
        }

        const tieneCambios = filasModificadasCot.size > 0 || filasModificadasProv.size > 0;
        if (!tieneCambios) return;

        e.preventDefault();
        e.stopPropagation();

        confirmarSalidaSiHayCambios(function () {
            permiteSalirSinAlerta = true;
            window.location.href = href;
        }, null, "salir");
    }, true);

    // ==========================================
    // AJUSTE DE RENDER AL CAMBIAR DE PESTAÑA
    // ==========================================
    const tabCotizacionNav = document.getElementById("tab-cotizacion-nav");
    const tabProveedorNav = document.getElementById("tab-proveedor-nav");

    if (tabCotizacionNav) {
        tabCotizacionNav.addEventListener("shown.bs.tab", function () {
            if (hotCotizacion) hotCotizacion.render();
        });
    }

    if (tabProveedorNav) {
        tabProveedorNav.addEventListener("shown.bs.tab", function () {
            if (hotProveedor) hotProveedor.render();
        });
    }



    // ==========================================
    // EXPORTACIÓN A EXCEL CON FILTROS ACTIVOS
    // ==========================================
    const btnExportarExcel = document.getElementById("btnExportarExcel");
    if (btnExportarExcel) {
        btnExportarExcel.addEventListener("click", function (e) {
            e.preventDefault();
            const clienteId = selectCliente?.value || "";
            const estadoCosto = selectEstadoCosto?.value || "todos";
            const equipoId = selectEquipo?.value || "";

            let fechaIni = fechaInicioFiltro;
            let fechaFin = fechaFinFiltro;
            if (window.jQuery && $("#filtro_periodo").data("daterangepicker")) {
                const drp = $("#filtro_periodo").data("daterangepicker");
                fechaIni = drp.startDate.format("YYYY-MM-DD");
                fechaFin = drp.endDate.format("YYYY-MM-DD");
            }

            const params = new URLSearchParams({
                cliente_id: clienteId,
                estado_costo: estadoCosto,
                equipo_id: equipoId
            });
            if (fechaIni) {
                params.append("fecha_inicio", fechaIni);
            }
            if (fechaFin) {
                params.append("fecha_fin", fechaFin);
            }

            window.location.href = `/cotizaciones/costos-multiple/exportar?${params.toString()}`;
        });
    }

    // ==========================================
    // CARGA INICIAL AUTOMÁTICA
    // ==========================================
    // Carga inicial inmediata con "Todos los clientes" y "No capturados" por defecto
    consultarDatos();
});
