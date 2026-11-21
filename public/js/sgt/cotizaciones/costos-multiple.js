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

    // Rastreo de filas modificadas (Dirty Tracking)
    const filasModificadasCot = new Set();
    const filasModificadasProv = new Set();

    let cambiosCotizacion = false;
    let cambiosProveedor = false;

    let isCalculatingCot = false;
    let isCalculatingProv = false;

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
        hiddenColumns: { columns: [17, 18, 19, 20], indicators: false },
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
        afterFilter: function () {
            actualizarResumenCotizacion();
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
        afterFilter: function () {
            actualizarResumenProveedor();
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

    function marcarCambiosCotizacion(tiene) {
        cambiosCotizacion = tiene && filasModificadasCot.size > 0;
        btnGuardarCotizacion.disabled = !cambiosCotizacion;
        if (cambiosCotizacion) {
            const count = filasModificadasCot.size;
            cotStatusCambios.innerHTML = `<span class="badge bg-warning text-dark"><i class="fas fa-exclamation-triangle me-1"></i>${count} viaje(s) modificado(s) sin guardar</span>`;
        } else {
            cotStatusCambios.innerHTML = '<span class="text-secondary"><i class="fas fa-check-circle text-success me-1"></i>Sin cambios pendientes</span>';
        }
    }

    function marcarCambiosProveedor(tiene) {
        cambiosProveedor = tiene && filasModificadasProv.size > 0;
        btnGuardarProveedor.disabled = !cambiosProveedor;
        if (cambiosProveedor) {
            const count = filasModificadasProv.size;
            provStatusCambios.innerHTML = `<span class="badge bg-warning text-dark"><i class="fas fa-exclamation-triangle me-1"></i>${count} asignación(es) modificada(s) sin guardar</span>`;
        } else {
            provStatusCambios.innerHTML = '<span class="text-secondary"><i class="fas fa-check-circle text-success me-1"></i>Sin cambios pendientes</span>';
        }
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

                dataCotizacion = res.cotizaciones || [];
                dataProveedor = res.proveedores || [];

                hotCotizacion.loadData(dataCotizacion);
                hotProveedor.loadData(dataProveedor);

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

    if (btnConsultar) {
        btnConsultar.addEventListener("click", consultarDatos);
    }

    if (selectEstadoCosto) {
        selectEstadoCosto.addEventListener("change", consultarDatos);
    }

    if (btnLimpiar) {
        btnLimpiar.addEventListener("click", function () {
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
            consultarDatos();
        });
    }

    // ==========================================
    // GUARDADO MASIVO: COTIZACIÓN
    // ==========================================
    if (btnGuardarCotizacion) {
        btnGuardarCotizacion.addEventListener("click", function () {
            // Validar si existen modificaciones pendientes
            if (filasModificadasCot.size === 0) {
                Swal.fire({
                    icon: "info",
                    title: "Sin modificaciones",
                    text: "No se han detectado cambios en los costos de cotización para guardar."
                });
                return;
            }

            const todasLasFilas = dataCotizacion;
            const filas = todasLasFilas.filter(r => r && r.id && filasModificadasCot.has(r.id));

            if (filas.length === 0) {
                Swal.fire({
                    icon: "info",
                    title: "Sin modificaciones",
                    text: "No hay registros modificados para guardar."
                });
                return;
            }

            // Validar que los pesos no excedan 60 toneladas en las filas modificadas
            for (let i = 0; i < filas.length; i++) {
                let p = parseNum(filas[i].peso_contenedor);
                if (p > 60) {
                    Swal.fire({
                        icon: "warning",
                        title: "Peso fuera de rango",
                        text: `El contenedor ${filas[i].contenedor} tiene un peso de ${p} ton, el cual excede el máximo permitido de 60 ton.`
                    });
                    return;
                }
            }

            Swal.fire({
                title: "¿Guardar Costos de Cotización?",
                text: `Se actualizarán únicamente los costos de ${filas.length} viaje(s) modificado(s).`,
                icon: "question",
                showCancelButton: true,
                confirmButtonText: "Sí, guardar",
                cancelButtonText: "Cancelar"
            }).then(result => {
                if (!result.isConfirmed) return;

                // Bloquear botón y UI inmediatamente para evitar doble clic o "Max request"
                btnGuardarCotizacion.disabled = true;
                btnGuardarCotizacion.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Guardando...';

                Swal.fire({
                    title: "Guardando cambios...",
                    text: `Actualizando costos de ${filas.length} viaje(s). Por favor espere...`,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                fetch("/cotizaciones/costos-multiple/guardar-cotizacion", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                        "X-CSRF-TOKEN": csrfToken,
                        "X-Requested-With": "XMLHttpRequest"
                    },
                    body: JSON.stringify({ filas: filas })
                })
                .then(async res => {
                    const data = await res.json().catch(() => ({}));
                    if (!res.ok) {
                        throw new Error(data.Mensaje || `Error del servidor (código ${res.status})`);
                    }
                    return data;
                })
                .then(res => {
                    btnGuardarCotizacion.innerHTML = '<i class="fas fa-save me-1"></i> Guardar Costos Cotización';

                    if (res.TMensaje === "success") {
                        filasModificadasCot.clear();
                        marcarCambiosCotizacion(false);

                        Swal.fire({
                            icon: "success",
                            title: res.Titulo || "Guardado exitoso",
                            text: res.Mensaje || "Los costos se actualizaron correctamente."
                        }).then(() => {
                            consultarDatos();
                        });
                    } else {
                        btnGuardarCotizacion.disabled = false;
                        Swal.fire({
                            icon: "error",
                            title: res.Titulo || "Error",
                            text: res.Mensaje || "No se pudieron guardar los costos."
                        });
                    }
                })
                .catch(err => {
                    btnGuardarCotizacion.disabled = false;
                    btnGuardarCotizacion.innerHTML = '<i class="fas fa-save me-1"></i> Guardar Costos Cotización';
                    console.error("Error al guardar cotizaciones:", err);
                    Swal.fire({
                        icon: "error",
                        title: "Error al guardar",
                        text: err.message || "Ocurrió un error inesperado al procesar el guardado."
                    });
                });
            });
        });
    }

    // ==========================================
    // GUARDADO MASIVO: PROVEEDOR
    // ==========================================
    if (btnGuardarProveedor) {
        btnGuardarProveedor.addEventListener("click", function () {
            // Validar si existen modificaciones pendientes
            if (filasModificadasProv.size === 0) {
                Swal.fire({
                    icon: "info",
                    title: "Sin modificaciones",
                    text: "No se han detectado cambios en los costos de proveedor para guardar."
                });
                return;
            }

            const todasLasFilas = dataProveedor;
            const filas = todasLasFilas.filter(r => r && r.id && filasModificadasProv.has(r.id));

            if (filas.length === 0) {
                Swal.fire({
                    icon: "info",
                    title: "Sin modificaciones",
                    text: "No hay asignaciones modificadas para guardar."
                });
                return;
            }

            Swal.fire({
                title: "¿Guardar Costos de Proveedor?",
                text: `Se actualizarán únicamente los costos de ${filas.length} asignación(es) modificada(s).`,
                icon: "question",
                showCancelButton: true,
                confirmButtonText: "Sí, guardar",
                cancelButtonText: "Cancelar"
            }).then(result => {
                if (!result.isConfirmed) return;

                // Bloquear botón y UI inmediatamente para evitar doble clic o "Max request"
                btnGuardarProveedor.disabled = true;
                btnGuardarProveedor.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Guardando...';

                Swal.fire({
                    title: "Guardando cambios...",
                    text: `Actualizando costos de ${filas.length} asignación(es). Por favor espere...`,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                fetch("/cotizaciones/costos-multiple/guardar-proveedor", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                        "X-CSRF-TOKEN": csrfToken,
                        "X-Requested-With": "XMLHttpRequest"
                    },
                    body: JSON.stringify({ filas: filas })
                })
                .then(async res => {
                    const data = await res.json().catch(() => ({}));
                    if (!res.ok) {
                        throw new Error(data.Mensaje || `Error del servidor (código ${res.status})`);
                    }
                    return data;
                })
                .then(res => {
                    btnGuardarProveedor.innerHTML = '<i class="fas fa-save me-1"></i> Guardar Costos Proveedor';

                    if (res.TMensaje === "success") {
                        filasModificadasProv.clear();
                        marcarCambiosProveedor(false);

                        Swal.fire({
                            icon: "success",
                            title: res.Titulo || "Guardado exitoso",
                            text: res.Mensaje || "Los costos de proveedor se actualizaron correctamente."
                        }).then(() => {
                            consultarDatos();
                        });
                    } else {
                        btnGuardarProveedor.disabled = false;
                        Swal.fire({
                            icon: "error",
                            title: res.Titulo || "Error",
                            text: res.Mensaje || "No se pudieron guardar los costos."
                        });
                    }
                })
                .catch(err => {
                    btnGuardarProveedor.disabled = false;
                    btnGuardarProveedor.innerHTML = '<i class="fas fa-save me-1"></i> Guardar Costos Proveedor';
                    console.error("Error al guardar proveedor:", err);
                    Swal.fire({
                        icon: "error",
                        title: "Error al guardar",
                        text: err.message || "Ocurrió un error inesperado al procesar el guardado."
                    });
                });
            });
        });
    }

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
