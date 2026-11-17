// public/js/sgt/liquidaciones/ajustes.js
// Control frontend de Ajustes y Complementos de Liquidación

let gridApiAjustes = null;
let prestamosOperadorActual = [];
let contadorPartidas = 0;

const currencyFormatter = (value) => {
    return new Intl.NumberFormat("es-MX", {
        style: "currency",
        currency: "MXN",
    }).format(value || 0);
};

const columnDefsAjustes = [
    {
        headerName: "Folio",
        field: "folio",
        width: 140,
        pinned: "left",
        cellRenderer: (params) => {
            return `<strong class="text-primary">${params.value}</strong>`;
        },
    },
    {
        headerName: "Fecha",
        field: "fecha",
        width: 110,
        cellRenderer: (params) => {
            if (!params.value) return "";
            const parts = params.value.split("-");
            return parts.length === 3 ? `${parts[2]}/${parts[1]}/${parts[0]}` : params.value;
        },
    },
    {
        headerName: "Liquidación",
        field: "folio_liquidacion",
        width: 120,
        cellRenderer: (params) => {
            return `<span class="badge bg-secondary text-white">${params.value}</span>`;
        },
    },
    {
        headerName: "Operador",
        field: "operador",
        minWidth: 180,
        flex: 1,
    },
    {
        headerName: "Contenedores",
        field: "contenedores",
        width: 160,
    },
    {
        headerName: "Motivo del Ajuste",
        field: "motivo",
        minWidth: 200,
        flex: 1,
        tooltipField: "motivo",
    },
    {
        headerName: "Percepciones (+)",
        field: "total_percepciones",
        width: 140,
        valueFormatter: (params) => currencyFormatter(params.value),
        cellStyle: { textAlign: "right", color: "#28a745" },
    },
    {
        headerName: "Deducciones (-)",
        field: "total_deducciones",
        width: 140,
        valueFormatter: (params) => currencyFormatter(params.value),
        cellStyle: { textAlign: "right", color: "#dc3545" },
    },
    {
        headerName: "Neto Pagado",
        field: "total_neto",
        width: 140,
        valueFormatter: (params) => currencyFormatter(params.value),
        cellStyle: { textAlign: "right", fontWeight: "bold" },
    },
    {
        headerName: "Banco Egreso",
        field: "banco",
        width: 180,
    },
    {
        headerName: "Estatus",
        field: "estatus",
        width: 110,
        cellRenderer: (params) => {
            const status = params.value;
            if (status === "cancelado") {
                return '<span class="badge bg-danger text-white">Cancelado</span>';
            }
            return '<span class="badge bg-success text-white">Aplicado</span>';
        },
    },
    {
        headerName: "Acciones",
        field: "acciones",
        width: 130,
        sortable: false,
        filter: false,
        pinned: "right",
        cellRenderer: (params) => {
            const row = params.data;
            let btns = `
                <div class="d-flex gap-1 justify-content-center align-items-center h-100">
                    <button class="btn btn-sm btn-outline-danger p-1" title="Ver Comprobante PDF" onclick="imprimirComprobanteAjuste(${row.id})">
                        <i class="fa fa-file-pdf"></i>
                    </button>`;

            if (row.estatus !== "cancelado") {
                btns += `
                    <button class="btn btn-sm btn-outline-warning p-1" title="Cancelar Ajuste" onclick="confirmarCancelarAjuste(${row.id}, '${row.folio}')">
                        <i class="fa fa-ban"></i>
                    </button>`;
            }

            btns += `</div>`;
            return btns;
        },
    },
];

const gridOptionsAjustes = {
    columnDefs: columnDefsAjustes,
    rowData: [],
    pagination: true,
    paginationPageSize: 15,
    paginationPageSizeSelector: [10, 15, 25, 50, 100],
    defaultColDef: {
        resizable: true,
        sortable: true,
        filter: true,
    },
    enableBrowserTooltips: true,
    localeText: {
        page: "Página",
        more: "Más",
        to: "a",
        of: "de",
        next: "Siguiente",
        last: "Último",
        first: "Primero",
        previous: "Anterior",
        loadingOoo: "Cargando datos...",
        noRowsToShow: "No hay ajustes de liquidación registrados en este periodo.",
    },
};

document.addEventListener("DOMContentLoaded", () => {
    const gridDiv = document.querySelector("#gridAjustes");
    if (gridDiv) {
        gridApiAjustes = agGrid.createGrid(gridDiv, gridOptionsAjustes);
    }

    // Listeners de filtros
    $("#filtro_operador, #filtro_banco, #filtro_estatus").on("change", () => {
        const picker = $("#daterange").data("daterangepicker");
        if (picker) {
            getAjustes(picker.startDate.format("YYYY-MM-DD"), picker.endDate.format("YYYY-MM-DD"));
        }
    });

    // Búsqueda con debounce en el input de liquidación
    let timeoutBusquedaLiq = null;
    $("#buscar_liq_input").on("keyup input", function () {
        clearTimeout(timeoutBusquedaLiq);
        const q = $(this).val();
        if (q.length < 1) {
            $("#contenedorResultadosBusqueda").hide().empty();
            return;
        }
        timeoutBusquedaLiq = setTimeout(() => {
            buscarLiquidacionesDropdown(q);
        }, 300);
    });
});

function getAjustes(startDate, endDate) {
    if (!gridApiAjustes) return;

    const _token = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");
    const id_operador = $("#filtro_operador").val() || "";
    const id_banco = $("#filtro_banco").val() || "";
    const estatus = $("#filtro_estatus").val() || "";

    $.ajax({
        url: "/liquidaciones/ajustes/data",
        type: "POST",
        data: { _token, startDate, endDate, id_operador, id_banco, estatus },
        success: (response) => {
            gridApiAjustes.setGridOption("rowData", response.data || []);
        },
        error: (xhr) => {
            console.error("Error al cargar ajustes:", xhr);
            Swal.fire("Error", "No se pudieron consultar los ajustes de liquidación.", "error");
        },
    });
}

function abrirModalNuevoAjuste() {
    $("#formNuevoAjuste")[0].reset();
    limpiarLiquidacionSeleccionada();
    $("#bodyPartidasAjuste").empty();
    contadorPartidas = 0;
    prestamosOperadorActual = [];
    calcularTotalesPartidas();

    // Agregar primera fila por defecto
    agregarFilaConcepto();

    $("#modalNuevoAjuste").modal("show");
}

function buscarLiquidacionesDropdown(term) {
    const q = term || $("#buscar_liq_input").val();
    const contenedor = $("#contenedorResultadosBusqueda");

    $.ajax({
        url: "/liquidaciones/ajustes/buscar-liquidaciones",
        type: "GET",
        data: { q },
        success: (res) => {
            contenedor.empty();
            if (res.items && res.items.length > 0) {
                res.items.forEach((item) => {
                    const btn = $(`
                        <a href="javascript:void(0)" class="list-group-item list-group-item-action py-2">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1 text-primary font-weight-bold">${item.folio} - ${item.operador_nombre}</h6>
                                <small class="text-success font-weight-bold">$${numberFormat(item.total_pagado)}</small>
                            </div>
                            <small class="text-muted d-block">
                                <i class="fa fa-calendar me-1"></i> ${item.fecha} | 
                                <i class="fa fa-box me-1"></i> ${item.contenedores}
                            </small>
                        </a>
                    `);
                    btn.on("click", () => seleccionarLiquidacion(item));
                    contenedor.append(btn);
                });
                contenedor.show();
            } else {
                contenedor.html('<div class="list-group-item py-2 text-muted">No se encontraron liquidaciones coincidentes.</div>').show();
            }
        },
    });
}

function seleccionarLiquidacion(item) {
    $("#input_id_liquidacion").val(item.id);
    $("#input_id_operador").val(item.id_operador);

    $("#resumen_folio_liq").text(item.folio);
    $("#resumen_operador_nombre").text(item.operador_nombre);
    $("#resumen_fecha_liq").text(item.fecha);
    $("#resumen_contenedores").text(item.contenedores);
    $("#resumen_total_original").text("$" + numberFormat(item.total_pagado));

    $("#cardLiquidacionSeleccionada").show();
    $("#buscar_liq_input").val("").closest(".input-group").hide();
    $("#contenedorResultadosBusqueda").hide().empty();

    // Consultar préstamos y adelantos activos del operador
    $.ajax({
        url: `/liquidaciones/ajustes/prestamos-operador/${item.id_operador}`,
        type: "GET",
        success: (res) => {
            prestamosOperadorActual = res.items || [];
            // Actualizar selectores de préstamos existentes en la tabla
            $(".select-prestamo-partida").each(function () {
                actualizarOpcionesPrestamo($(this));
            });
        },
    });
}

function limpiarLiquidacionSeleccionada() {
    $("#input_id_liquidacion").val("");
    $("#input_id_operador").val("");
    $("#cardLiquidacionSeleccionada").hide();
    $("#buscar_liq_input").closest(".input-group").show();
    $("#contenedorResultadosBusqueda").hide().empty();
    prestamosOperadorActual = [];
}

function agregarFilaConcepto() {
    contadorPartidas++;
    const idx = contadorPartidas;

    const row = $(`
        <tr id="fila_partida_${idx}" class="align-middle fila-partida">
            <td>
                <select name="partidas[${idx}][tipo]" class="form-select form-select-sm select-tipo-partida" onchange="cambiarTipoPartida(${idx})" required>
                    <option value="percepcion">Percepción (+)</option>
                    <option value="deduccion">Deducción (-)</option>
                </select>
            </td>
            <td>
                <select name="partidas[${idx}][categoria_concepto]" class="form-select form-select-sm select-concepto-partida mb-1" id="select_concepto_${idx}" onchange="cambiarCategoriaConcepto(${idx})" required>
                    <optgroup label="Percepciones">
                        <option value="sueldo_operador">Sueldo Operador</option>
                        <option value="dinero_viaje">Dinero de Viaje / Viáticos</option>
                        <option value="gastos_justificados">Gastos Justificados / Reembolso</option>
                        <option value="bono">Bono / Compensación</option>
                        <option value="otro">Otro (Percepción)</option>
                    </optgroup>
                </select>
                <!-- Selector dinámico de préstamo/adelanto -->
                <div id="contenedor_prestamo_${idx}" style="display: none;">
                    <select name="partidas[${idx}][id_prestamo]" class="form-select form-select-sm select-prestamo-partida" id="select_prestamo_${idx}">
                        <option value="">-- Seleccionar Préstamo/Adelanto --</option>
                    </select>
                </div>
            </td>
            <td>
                <input type="text" name="partidas[${idx}][descripcion]" class="form-control form-control-sm" placeholder="Detalle o justificación..." />
            </td>
            <td>
                <input type="number" step="0.01" min="0.01" name="partidas[${idx}][monto]" class="form-control form-control-sm text-end input-monto-partida" placeholder="0.00" oninput="calcularTotalesPartidas()" required />
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger p-1" title="Eliminar fila" onclick="eliminarFilaPartida(${idx})">
                    <i class="fa fa-trash"></i>
                </button>
            </td>
        </tr>
    `);

    $("#bodyPartidasAjuste").append(row);
    actualizarOpcionesPrestamo(row.find(".select-prestamo-partida"));
    calcularTotalesPartidas();
}

function cambiarTipoPartida(idx) {
    const tipo = $(`#fila_partida_${idx} .select-tipo-partida`).val();
    const selectConcepto = $(`#select_concepto_${idx}`);
    selectConcepto.empty();

    if (tipo === "percepcion") {
        selectConcepto.append(`
            <optgroup label="Percepciones">
                <option value="sueldo_operador">Sueldo Operador</option>
                <option value="dinero_viaje">Dinero de Viaje / Viáticos</option>
                <option value="gastos_justificados">Gastos Justificados / Reembolso</option>
                <option value="bono">Bono / Compensación</option>
                <option value="otro">Otro (Percepción)</option>
            </optgroup>
        `);
        $(`#contenedor_prestamo_${idx}`).hide();
    } else {
        selectConcepto.append(`
            <optgroup label="Deducciones">
                <option value="abono_prestamo">Abono a Préstamo</option>
                <option value="abono_adelanto">Abono a Adelanto</option>
                <option value="descuento">Descuento Operativo / Daño</option>
                <option value="otro">Otro (Deducción)</option>
            </optgroup>
        `);
        cambiarCategoriaConcepto(idx);
    }

    calcularTotalesPartidas();
}

function cambiarCategoriaConcepto(idx) {
    const concepto = $(`#select_concepto_${idx}`).val();
    const contenedorPrestamo = $(`#contenedor_prestamo_${idx}`);
    const selectPrestamo = $(`#select_prestamo_${idx}`);

    if (concepto === "abono_prestamo" || concepto === "abono_adelanto") {
        actualizarOpcionesPrestamo(selectPrestamo, concepto === "abono_adelanto" ? "adelanto" : "prestamo");
        contenedorPrestamo.show();
    } else {
        contenedorPrestamo.hide();
        selectPrestamo.val("");
    }
}

function actualizarOpcionesPrestamo(selectElement, filtroTipo) {
    selectElement.empty().append('<option value="">-- Seleccionar Préstamo/Adelanto --</option>');
    if (!prestamosOperadorActual || prestamosOperadorActual.length === 0) {
        selectElement.append('<option value="" disabled>Sin préstamos activos del chofer</option>');
        return;
    }

    prestamosOperadorActual.forEach((p) => {
        if (!filtroTipo || p.tipo === filtroTipo) {
            selectElement.append(`<option value="${p.id}">${p.texto}</option>`);
        }
    });
}

function eliminarFilaPartida(idx) {
    $(`#fila_partida_${idx}`).remove();
    calcularTotalesPartidas();
}

function calcularTotalesPartidas() {
    let sumPercepciones = 0;
    let sumDeducciones = 0;

    $(".fila-partida").each(function () {
        const tipo = $(this).find(".select-tipo-partida").val();
        const monto = parseFloat($(this).find(".input-monto-partida").val()) || 0;

        if (tipo === "percepcion") {
            sumPercepciones += monto;
        } else {
            sumDeducciones += monto;
        }
    });

    const netoFinal = Math.max(0, sumPercepciones - sumDeducciones);

    $("#resumenPercepciones").text("+$" + numberFormat(sumPercepciones));
    $("#resumenDeducciones").text("-$" + numberFormat(sumDeducciones));
    $("#resumenNetoFinal").text("$" + numberFormat(netoFinal));
    $("#badgeTotalNetoHeader").text("$" + numberFormat(netoFinal));
}

function guardarNuevoAjuste(e) {
    e.preventDefault();

    const idLiquidacion = $("#input_id_liquidacion").val();
    if (!idLiquidacion) {
        Swal.fire("Atención", "Debe buscar y seleccionar la liquidación original que afecta.", "warning");
        return;
    }

    const filas = $(".fila-partida");
    if (filas.length === 0) {
        Swal.fire("Atención", "Debe agregar al menos un concepto en el ajuste.", "warning");
        return;
    }

    // Validar montos mayores a 0
    let montoInvalido = false;
    filas.each(function () {
        const m = parseFloat($(this).find(".input-monto-partida").val()) || 0;
        if (m <= 0) montoInvalido = true;
    });

    if (montoInvalido) {
        Swal.fire("Atención", "Todos los conceptos deben tener un importe mayor a cero.", "warning");
        return;
    }

    const neto = parseFloat($("#badgeTotalNetoHeader").text().replace(/[^0-9.-]+/g, "")) || 0;
    const bancoId = $("#id_banco_ajuste").val();

    if (neto > 0 && !bancoId) {
        Swal.fire("Cuenta Bancaria Requerida", "El ajuste tiene un importe neto a pagar de $" + numberFormat(neto) + ". Debe seleccionar la cuenta bancaria de donde saldrá el dinero.", "warning");
        return;
    }

    const form = $("#formNuevoAjuste");
    const formData = form.serialize();
    const btn = $("#btnGuardarAjuste");

    btn.prop("disabled", true).html('<i class="fa fa-spinner fa-spin me-1"></i> Guardando...');

    $.ajax({
        url: "/liquidaciones/ajustes/store",
        type: "POST",
        data: formData,
        success: (res) => {
            btn.prop("disabled", false).html('<i class="fa fa-check me-1"></i> Aplicar y Guardar Ajuste');

            if (res.success) {
                $("#modalNuevoAjuste").modal("hide");

                Swal.fire({
                    title: res.Titulo || "Ajuste Aplicado",
                    text: res.Mensaje,
                    icon: "success",
                    showCancelButton: true,
                    confirmButtonText: '<i class="fa fa-file-pdf"></i> Imprimir Comprobante',
                    cancelButtonText: "Cerrar",
                    confirmButtonColor: "#c0392b",
                }).then((result) => {
                    if (result.isConfirmed) {
                        imprimirComprobanteAjuste(res.id_ajuste);
                    }
                });

                // Recargar grilla
                const picker = $("#daterange").data("daterangepicker");
                if (picker) {
                    getAjustes(picker.startDate.format("YYYY-MM-DD"), picker.endDate.format("YYYY-MM-DD"));
                }
            } else {
                Swal.fire(res.Titulo || "Atención", res.Mensaje || "Ocurrió un error al procesar el ajuste.", res.TMensaje || "warning");
            }
        },
        error: (xhr) => {
            btn.prop("disabled", false).html('<i class="fa fa-check me-1"></i> Aplicar y Guardar Ajuste');
            const err = xhr.responseJSON;
            Swal.fire(err?.Titulo || "Error", err?.Mensaje || "Ocurrió un error al guardar el ajuste.", "error");
        },
    });
}

function imprimirComprobanteAjuste(id) {
    window.open(`/liquidaciones/ajustes/${id}/comprobante`, "_blank");
}

function confirmarCancelarAjuste(id, folio) {
    Swal.fire({
        title: "Cancelar Ajuste " + folio,
        text: "¿Está seguro de cancelar este ajuste? Se revertirán los movimientos en bancos y las amortizaciones de préstamos asociadas.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, cancelar ajuste",
        cancelButtonText: "No cancelar",
        confirmButtonColor: "#d33",
    }).then((result) => {
        if (result.isConfirmed) {
            cancelarAjuste(id);
        }
    });
}

function cancelarAjuste(id) {
    const _token = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");

    $.ajax({
        url: `/liquidaciones/ajustes/${id}/cancelar`,
        type: "POST",
        data: { _token },
        success: (res) => {
            Swal.fire(res.Titulo, res.Mensaje, res.TMensaje);
            if (res.success) {
                const picker = $("#daterange").data("daterangepicker");
                if (picker) {
                    getAjustes(picker.startDate.format("YYYY-MM-DD"), picker.endDate.format("YYYY-MM-DD"));
                }
            }
        },
        error: () => {
            Swal.fire("Error", "No se pudo cancelar el ajuste.", "error");
        },
    });
}

function numberFormat(val) {
    return parseFloat(val || 0).toLocaleString("es-MX", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
