document.addEventListener("DOMContentLoaded", function () {
    const modalMovimiento = new bootstrap.Modal(
        document.getElementById("modalMovimiento"),
    );

    const modalVerMovimiento = new bootstrap.Modal(
        document.getElementById("modalVerMovimiento"),
    );

    const form = document.getElementById("formMovimiento");
    const movimientoId = document.getElementById("movimiento_id");
    const modalTitulo = document.getElementById("modalMovimientoTitulo");

    const tbodyDetalles = document.querySelector(
        "#tablaDetallesMovimiento tbody",
    );

    const templateDetalle = document.getElementById(
        "templateDetalleMovimiento",
    );

    const totalDetalles = document.getElementById("totalDetalles");
    const errorDetalles = document.getElementById("error_detalles");

    const btnNuevoMovimiento = document.getElementById("btnNuevoMovimiento");
    const btnAgregarDetalle = document.getElementById("btnAgregarDetalle");
    const btnBuscarEstadoCuenta = document.getElementById(
        "btnBuscarEstadoCuenta",
    );

    const filtroCuenta = document.getElementById("filtro_cuenta_id");
    const filtroFechaInicio = document.getElementById("filtro_fecha_inicio");
    const filtroFechaFin = document.getElementById("filtro_fecha_fin");

    inicializarFechasFiltro();

    if (btnNuevoMovimiento) {
        btnNuevoMovimiento.addEventListener("click", function () {
            abrirModalNuevoMovimiento();
        });
    }

    if (btnAgregarDetalle) {
        btnAgregarDetalle.addEventListener("click", function () {
            intentarAgregarDetalle();
        });
    }

    if (btnBuscarEstadoCuenta) {
        btnBuscarEstadoCuenta.addEventListener("click", function () {
            cargarEstadoCuenta();
        });
    }

    document.addEventListener("click", async function (e) {
        const btnDetalle = e.target.closest(".btnEliminarDetalle");

        if (btnDetalle) {
            btnDetalle.closest("tr").remove();
            actualizarResumenDetallesMovimiento();
            return;
        }

        const btnVer = e.target.closest(".btnVerMovimiento");

        if (btnVer) {
            await verMovimiento(btnVer.dataset.id);
            return;
        }

        const btnEditar = e.target.closest(".btnEditarMovimiento");

        if (btnEditar) {
            await cargarMovimientoParaEditar(btnEditar.dataset.id);
            return;
        }

        const btnEliminar = e.target.closest(".btnEliminarMovimiento");

        if (btnEliminar) {
            await eliminarMovimiento(btnEliminar.dataset.id);
        }
    });

    document.addEventListener("input", function (e) {
        if (
            e.target.id === "total_movimiento" ||
            e.target.classList.contains("detalle-monto")
        ) {
            actualizarResumenDetallesMovimiento();
        }
    });

    form.addEventListener("submit", async function (e) {
        e.preventDefault();

        limpiarErrores();

        if (!validarTotalesMovimiento()) {
            actualizarResumenDetallesMovimiento();
            return;
        }

        const id = movimientoId.value;
        const isEdit = !!id;

        const payload = {
            cuenta_id: document.getElementById("cuenta_id").value,
            tipo: document.getElementById("tipo").value,
            fecha_movimiento: document.getElementById("fecha_movimiento").value,
            concepto: document.getElementById("concepto").value,
            referencia_bancaria: document.getElementById("referencia_bancaria")
                .value,
            observaciones: document.getElementById("observaciones").value,
            total_movimiento: obtenerTotalMovimientoCapturado(),
            detalles: obtenerDetalles(),
        };

        const url = isEdit ? `/scb/movimientos/${id}` : `/scb/movimientos`;

        if (isEdit) {
            payload._method = "PUT";
        }

        try {
            const response = await fetch(url, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": getCsrfToken(),
                    Accept: "application/json",
                    "Content-Type": "application/json",
                },
                body: JSON.stringify(payload),
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                if (response.status === 422 && data.errors) {
                    mostrarErrores(data.errors);

                    Swal.fire({
                        icon: "warning",
                        title: "Validación",
                        text:
                            data.message ||
                            "Revisa los datos capturados del movimiento.",
                    });

                    return;
                }

                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: data.message || "No se pudo guardar el movimiento.",
                });

                return;
            }

            modalMovimiento.hide();

            Swal.fire({
                icon: "success",
                title: "Correcto",
                text: data.message || "Movimiento guardado correctamente.",
                timer: 1500,
                showConfirmButton: false,
            });

            if (filtrosEstadoCuentaCompletos()) {
                await cargarEstadoCuenta();
            } else {
                mostrarMensajeInicialTabla();
            }
        } catch (error) {
            console.error(error);

            Swal.fire({
                icon: "error",
                title: "Error inesperado",
                text: "No se pudo procesar la solicitud.",
            });
        }
    });

    function abrirModalNuevoMovimiento() {
        limpiarFormulario();

        modalTitulo.textContent = "Nuevo movimiento";

        const hoy = obtenerFechaHoy();

        document.getElementById("fecha_movimiento").value = hoy;

        const cuentaFiltro = filtroCuenta?.value || "";

        if (cuentaFiltro) {
            document.getElementById("cuenta_id").value = cuentaFiltro;
        }

        actualizarResumenDetallesMovimiento();

        modalMovimiento.show();
    }

    function intentarAgregarDetalle() {
        const totalCapturado = obtenerTotalMovimientoCapturado();
        const totalDetallesActual = calcularTotalDetallesMovimiento();

        if (!totalCapturado || totalCapturado <= 0) {
            Swal.fire({
                icon: "warning",
                title: "Total requerido",
                text: "Primero captura el total del movimiento.",
            });

            document.getElementById("total_movimiento")?.focus();
            return;
        }

        if (totalDetallesActual > totalCapturado) {
            Swal.fire({
                icon: "warning",
                title: "Total capturado mayor",
                text: "El total capturado en detalles es mayor al total del movimiento.",
            });

            return;
        } else if (totalDetallesActual === totalCapturado) {
            Swal.fire({
                icon: "info",
                title: "Total alcanzado",
                text: "El total de detalles ya alcanza el total del movimiento. Revisa los detalles capturados.",
            });
            return;
        }

        agregarDetalle();
    }

    function agregarDetalle(detalle = null) {
        const clone = templateDetalle.content.cloneNode(true);
        const row = clone.querySelector("tr");

        if (detalle) {
            row.querySelector(".detalle-unidad").value =
                detalle.unidad_id || "";

            row.querySelector(".detalle-descripcion").value =
                detalle.descripcion || "";

            row.querySelector(".detalle-referencia").value =
                detalle.referencia || "";

            row.querySelector(".detalle-monto").value = Number(
                detalle.monto || 0,
            ).toFixed(2);
        }

        tbodyDetalles.appendChild(row);

        actualizarResumenDetallesMovimiento();
    }

    function obtenerDetalles() {
        const detalles = [];

        tbodyDetalles.querySelectorAll("tr").forEach((row) => {
            const unidadId = row.querySelector(".detalle-unidad").value;

            const descripcion = row
                .querySelector(".detalle-descripcion")
                .value.trim();

            const referencia = row
                .querySelector(".detalle-referencia")
                .value.trim();

            const monto = Number(
                row.querySelector(".detalle-monto").value || 0,
            );

            if (!descripcion && monto <= 0) {
                return;
            }

            detalles.push({
                unidad_id: unidadId || null,
                descripcion: descripcion,
                referencia: referencia || null,
                monto: monto,
            });
        });

        return detalles;
    }

    function obtenerTotalMovimientoCapturado() {
        return Number(document.getElementById("total_movimiento")?.value || 0);
    }

    function calcularTotalDetallesMovimiento() {
        let total = 0;

        tbodyDetalles.querySelectorAll(".detalle-monto").forEach((input) => {
            total += Number(input.value || 0);
        });

        return Number(total.toFixed(2));
    }

    function actualizarResumenDetallesMovimiento() {
        const totalCapturado = obtenerTotalMovimientoCapturado();
        const totalDetalle = calcularTotalDetallesMovimiento();
        const diferencia = Number((totalCapturado - totalDetalle).toFixed(2));

        if (totalDetalles) {
            totalDetalles.textContent = money(totalDetalle);
        }

        setText("lblTotalCapturado", money(totalCapturado));
        setText("lblTotalDetallesModal", money(totalDetalle));
        setText("lblDiferenciaMovimiento", money(diferencia));

        const lblDiferencia = document.getElementById(
            "lblDiferenciaMovimiento",
        );

        if (lblDiferencia) {
            lblDiferencia.classList.remove(
                "text-success",
                "text-danger",
                "text-warning",
            );

            lblDiferencia.classList.add(
                Math.abs(diferencia) < 0.01 ? "text-success" : "text-danger",
            );
        }

        if (totalDetalle > totalCapturado && totalCapturado > 0) {
            mostrarErrorDetalles(
                "El total de detalles supera el total capturado del movimiento.",
            );
        } else {
            ocultarErrorDetalles();
        }
    }

    function validarTotalesMovimiento() {
        const totalCapturado = obtenerTotalMovimientoCapturado();
        const detalles = obtenerDetalles();
        const totalDetalle = calcularTotalDetallesMovimiento();

        if (!totalCapturado || totalCapturado <= 0) {
            marcarCampoInvalido(
                "total_movimiento",
                "El total del movimiento debe ser mayor a cero.",
            );

            Swal.fire({
                icon: "warning",
                title: "Total requerido",
                text: "Captura el total del movimiento.",
            });

            return false;
        }

        if (!detalles.length) {
            mostrarErrorDetalles("Agrega al menos un detalle al movimiento.");

            Swal.fire({
                icon: "warning",
                title: "Detalles requeridos",
                text: "Agrega al menos un detalle con monto mayor a cero.",
            });

            return false;
        }
        /*
        const detalleInvalido = detalles.find(
            (detalle) => !detalle.descripcion || Number(detalle.monto) <= 0,
        );

        if (detalleInvalido) {
            mostrarErrorDetalles(
                "Cada detalle debe tener descripción y monto mayor a cero.",
            );

            Swal.fire({
                icon: "warning",
                title: "Detalle incompleto",
                text: "Cada detalle debe tener descripción y monto mayor a cero.",
            });

            return false;
        } */

        if (totalDetalle > totalCapturado) {
            mostrarErrorDetalles(
                "La suma de detalles supera el total del movimiento.",
            );

            Swal.fire({
                icon: "error",
                title: "El detalle se pasó",
                text: "La suma de detalles supera el total del movimiento.",
            });

            return false;
        }

        //diferencia
        let Diferencia = Number((totalCapturado - totalDetalle).toFixed(2));

        if (Math.abs(Diferencia) >= 0.01) {
            mostrarErrorDetalles(
                "El total capturado debe coincidir exactamente con el total de detalles.",
            );

            Swal.fire({
                icon: "warning",
                title: "Totales no coinciden",
                text:
                    "El total capturado debe coincidir exactamente con el total de detalles. Revisa los montos capturados. Diferencia: " +
                    money(Diferencia),
            });

            return false;
        }

        return true;
    }

    async function cargarEstadoCuenta() {
        const cuentaId = filtroCuenta?.value || "";
        const fechaInicio = filtroFechaInicio?.value || "";
        const fechaFin = filtroFechaFin?.value || "";

        if (!cuentaId || !fechaInicio || !fechaFin) {
            Swal.fire({
                icon: "warning",
                title: "Filtros requeridos",
                text: "Selecciona cuenta bancaria, fecha inicio y fecha fin.",
            });

            return;
        }

        if (fechaFin < fechaInicio) {
            Swal.fire({
                icon: "warning",
                title: "Rango inválido",
                text: "La fecha fin no puede ser menor que la fecha inicio.",
            });

            return;
        }

        pintarLoadingEstadoCuenta();

        try {
            const params = new URLSearchParams({
                cuenta_id: cuentaId,
                fecha_inicio: fechaInicio,
                fecha_fin: fechaFin,
            });

            const response = await fetch(
                `/scb/movimientos/estado-cuenta?${params.toString()}`,
                {
                    headers: {
                        Accept: "application/json",
                    },
                },
            );

            const data = await response.json();

            if (!response.ok || !data.success) {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text:
                        data.message ||
                        "No se pudo cargar el estado de cuenta.",
                });

                mostrarMensajeInicialTabla();
                return;
            }

            document
                .getElementById("resumenEstadoCuenta")
                ?.classList.remove("d-none");

            setText("lblSaldoInicial", money(data.saldo_inicial));
            setText("lblTotalCargos", money(data.total_cargos));
            setText("lblTotalAbonos", money(data.total_abonos));
            setText("lblSaldoFinal", money(data.saldo_final));

            pintarTablaEstadoCuenta(data.movimientos || []);
        } catch (error) {
            console.error(error);

            Swal.fire({
                icon: "error",
                title: "Error inesperado",
                text: "No se pudo cargar el estado de cuenta.",
            });

            mostrarMensajeInicialTabla();
        }
    }

    function pintarTablaEstadoCuenta(movimientos) {
        const tbody = document.getElementById("tbodyMovimientos");

        if (!tbody) return;

        tbody.innerHTML = "";

        if (!movimientos.length) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        No hay movimientos en el periodo seleccionado.
                    </td>
                </tr>
            `;

            return;
        }

        movimientos.forEach((mov) => {
            const cargo = Number(mov.cargo || 0);
            const abono = Number(mov.abono || 0);
            const saldo = Number(mov.saldo || 0);

            const cargoHtml =
                cargo > 0
                    ? `<span class="monto-cargo">-${money(cargo)}</span>`
                    : `<span class="text-muted">-</span>`;

            const abonoHtml =
                abono > 0
                    ? `<span class="monto-abono">+${money(abono)}</span>`
                    : `<span class="text-muted">-</span>`;

            const saldoClass =
                saldo >= 0 ? "monto-saldo-positivo" : "monto-saldo-negativo";

            tbody.insertAdjacentHTML(
                "beforeend",
                `
                <tr id="movimiento-row-${mov.id}">
                    <td>${escapeHtml(mov.fecha || "S/N")}</td>

                    <td>
                        <div class="fw-bold">${escapeHtml(mov.concepto || "")}</div>
                        ${
                            Number(mov.detalles_count || 0) > 0
                                ? `<small class="text-muted">${mov.detalles_count} detalle(s)</small>`
                                : ""
                        }
                    </td>

                    <td>${escapeHtml(mov.referencia || "S/N")}</td>

                    <td class="text-end">${cargoHtml}</td>

                    <td class="text-end">${abonoHtml}</td>

                   <td class="text-end ${saldoClass}">
                        ${money(saldo)}
                    </td>

                    <td class="text-end">
                        <button type="button"
                            class="btn btn-sm btn-outline-info btnVerMovimiento"
                            data-id="${mov.id}">
                            <i class="fas fa-eye"></i>
                        </button>

                        <button type="button"
                            class="btn btn-sm btn-outline-primary btnEditarMovimiento"
                            data-id="${mov.id}">
                            <i class="fas fa-edit"></i>
                        </button>

                        <button type="button"
                            class="btn btn-sm btn-outline-danger btnEliminarMovimiento"
                            data-id="${mov.id}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `,
            );
        });
    }

    function pintarLoadingEstadoCuenta() {
        const tbody = document.getElementById("tbodyMovimientos");

        if (!tbody) return;

        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center text-muted py-4">
                    <div class="spinner-border spinner-border-sm me-2"></div>
                    Cargando estado de cuenta...
                </td>
            </tr>
        `;
    }

    async function cargarMovimientoParaEditar(id) {
        limpiarFormulario();

        const movimiento = await obtenerMovimiento(id);

        if (!movimiento) return;

        movimientoId.value = movimiento.id;

        document.getElementById("cuenta_id").value = movimiento.cuenta_id;
        document.getElementById("tipo").value = movimiento.tipo;

        document.getElementById("fecha_movimiento").value =
            movimiento.fecha_movimiento?.substring(0, 10) || obtenerFechaHoy();

        document.getElementById("total_movimiento").value = Number(
            movimiento.total || 0,
        ).toFixed(2);

        document.getElementById("concepto").value = movimiento.concepto || "";

        document.getElementById("referencia_bancaria").value =
            movimiento.referencia_bancaria || "";

        document.getElementById("observaciones").value =
            movimiento.observaciones || "";

        tbodyDetalles.innerHTML = "";

        (movimiento.detalles || []).forEach((detalle) => {
            agregarDetalle(detalle);
        });

        actualizarResumenDetallesMovimiento();

        modalTitulo.textContent = "Editar movimiento";
        modalMovimiento.show();
    }

    async function verMovimiento(id) {
        const movimiento = await obtenerMovimiento(id);

        if (!movimiento) return;

        const detallesHtml = (movimiento.detalles || [])
            .map(
                (detalle) => `
                    <tr>
                        <td>${escapeHtml(detalle.unidad?.descripcion || "S/N")}</td>
                        <td>${escapeHtml(detalle.descripcion || "")}</td>
                        <td>${escapeHtml(detalle.referencia || "S/N")}</td>
                        <td class="text-end">${money(detalle.monto)}</td>
                    </tr>
                `,
            )
            .join("");

        const tipoBadge =
            movimiento.tipo === "abono"
                ? `<span class="badge bg-success">Abono</span>`
                : `<span class="badge bg-danger">Cargo</span>`;

        document.getElementById("contenidoVerMovimiento").innerHTML = `
            <div class="mb-3">
                <div class="fw-bold fs-6">${escapeHtml(movimiento.concepto)}</div>
                <div class="text-muted small">
                    ${formatDate(movimiento.fecha_movimiento)} ·
                    ${tipoBadge} ·
                    ${escapeHtml(movimiento.referencia_bancaria || "S/N")}
                </div>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-md-6">
                    <div class="border rounded-4 p-3 bg-light h-100">
                        <small class="text-muted fw-bold">Banco</small>
                        <div class="fw-bold">
                            ${escapeHtml(movimiento.cuenta?.banco?.nombre || "S/N")}
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded-4 p-3 bg-light h-100">
                        <small class="text-muted fw-bold">Cuenta</small>
                        <div class="fw-bold">
                            ${escapeHtml(movimiento.cuenta?.beneficiario || "S/N")}
                        </div>
                        <small>
                            ${escapeHtml(movimiento.cuenta?.numero_cuenta || "Sin cuenta")}
                        </small>
                    </div>
                </div>
            </div>

            <div class="border rounded-4 p-3 mb-3">
                <div class="row g-2">
                    <div class="col-md-6">
                        <small class="text-muted fw-bold">Total movimiento</small>
                        <div class="fs-5 fw-bold">${money(movimiento.total || 0)}</div>
                    </div>

                    <div class="col-md-6">
                        <small class="text-muted fw-bold">Observaciones</small>
                        <div>${escapeHtml(movimiento.observaciones || "S/N")}</div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Unidad</th>
                            <th>Descripción</th>
                            <th>Referencia</th>
                            <th class="text-end">Monto</th>
                        </tr>
                    </thead>

                    <tbody>
                        ${
                            detallesHtml ||
                            `
                            <tr>
                                <td colspan="4" class="text-center text-muted">
                                    Sin detalles.
                                </td>
                            </tr>
                        `
                        }
                    </tbody>

                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-end">Total</th>
                            <th class="text-end">${money(movimiento.total || 0)}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        `;

        modalVerMovimiento.show();
    }

    async function obtenerMovimiento(id) {
        try {
            const response = await fetch(`/scb/movimientos/${id}`, {
                headers: {
                    Accept: "application/json",
                },
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: data.message || "No se pudo consultar el movimiento.",
                });

                return null;
            }

            return data.data;
        } catch (error) {
            console.error(error);

            Swal.fire({
                icon: "error",
                title: "Error inesperado",
                text: "No se pudo consultar el movimiento.",
            });

            return null;
        }
    }

    async function eliminarMovimiento(id) {
        const confirmacion = await Swal.fire({
            icon: "warning",
            title: "¿Eliminar movimiento?",
            text: "Esta acción eliminará también sus detalles y recalculará el estado de cuenta.",
            showCancelButton: true,
            confirmButtonText: "Sí, eliminar",
            cancelButtonText: "Cancelar",
            confirmButtonColor: "#dc3545",
        });

        if (!confirmacion.isConfirmed) return;

        try {
            const response = await fetch(`/scb/movimientos/${id}`, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": getCsrfToken(),
                    Accept: "application/json",
                },
                body: new URLSearchParams({
                    _method: "DELETE",
                }),
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: data.message || "No se pudo eliminar el movimiento.",
                });

                return;
            }

            Swal.fire({
                icon: "success",
                title: "Eliminado",
                text: data.message || "Movimiento eliminado correctamente.",
                timer: 1500,
                showConfirmButton: false,
            });

            if (filtrosEstadoCuentaCompletos()) {
                await cargarEstadoCuenta();
            } else {
                document.getElementById(`movimiento-row-${id}`)?.remove();
                validarTablaVaciaEstadoCuenta();
            }
        } catch (error) {
            console.error(error);

            Swal.fire({
                icon: "error",
                title: "Error inesperado",
                text: "No se pudo procesar la solicitud.",
            });
        }
    }

    function limpiarFormulario() {
        form.reset();

        movimientoId.value = "";
        tbodyDetalles.innerHTML = "";

        if (totalDetalles) {
            totalDetalles.textContent = money(0);
        }

        limpiarErrores();

        document.getElementById("fecha_movimiento").value = obtenerFechaHoy();

        const totalMovimiento = document.getElementById("total_movimiento");

        if (totalMovimiento) {
            totalMovimiento.value = "";
        }

        actualizarResumenDetallesMovimiento();
    }

    function limpiarErrores() {
        [
            "cuenta_id",
            "tipo",
            "fecha_movimiento",
            "total_movimiento",
            "concepto",
            "referencia_bancaria",
            "observaciones",
        ].forEach((field) => {
            const input = document.getElementById(field);
            const error = document.getElementById(`error_${field}`);

            if (input) input.classList.remove("is-invalid");
            if (error) error.textContent = "";
        });

        ocultarErrorDetalles();
    }

    function mostrarErrores(errors) {
        Object.keys(errors).forEach((field) => {
            if (field.startsWith("detalles")) {
                mostrarErrorDetalles(errors[field][0]);
                return;
            }

            marcarCampoInvalido(field, errors[field][0]);
        });
    }

    function marcarCampoInvalido(field, message) {
        const input = document.getElementById(field);
        const error = document.getElementById(`error_${field}`);

        if (input) input.classList.add("is-invalid");
        if (error) error.textContent = message;
    }

    function mostrarErrorDetalles(message) {
        if (!errorDetalles) return;

        errorDetalles.textContent = message;
        errorDetalles.classList.remove("d-none");
    }

    function ocultarErrorDetalles() {
        if (!errorDetalles) return;

        errorDetalles.classList.add("d-none");
        errorDetalles.textContent = "";
    }

    function validarTablaVaciaEstadoCuenta() {
        const tbody = document.getElementById("tbodyMovimientos");

        if (!tbody) return;

        if (tbody.children.length > 0) return;

        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center text-muted py-4">
                    No hay movimientos en el periodo seleccionado.
                </td>
            </tr>
        `;
    }

    function mostrarMensajeInicialTabla() {
        const tbody = document.getElementById("tbodyMovimientos");

        if (!tbody) return;

        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center text-muted py-4">
                    Selecciona cuenta bancaria y rango de fechas para consultar.
                </td>
            </tr>
        `;
    }

    function filtrosEstadoCuentaCompletos() {
        return !!(
            filtroCuenta?.value &&
            filtroFechaInicio?.value &&
            filtroFechaFin?.value
        );
    }

    function inicializarFechasFiltro() {
        if (!filtroFechaInicio || !filtroFechaFin) return;

        const hoy = new Date();
        const primerDia = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
        const ultimoDia = new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0);

        if (!filtroFechaInicio.value) {
            filtroFechaInicio.value = toInputDate(primerDia);
        }

        if (!filtroFechaFin.value) {
            filtroFechaFin.value = toInputDate(ultimoDia);
        }
    }

    function obtenerFechaHoy() {
        return new Date().toISOString().substring(0, 10);
    }

    function toInputDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, "0");
        const day = String(date.getDate()).padStart(2, "0");

        return `${year}-${month}-${day}`;
    }

    function money(value) {
        return Number(value || 0).toLocaleString("es-MX", {
            style: "currency",
            currency: "MXN",
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
    }

    function formatDate(value) {
        if (!value) return "S/N";

        const date = new Date(value);

        if (isNaN(date.getTime())) {
            return value;
        }

        return date.toLocaleDateString("es-MX");
    }

    function escapeHtml(value) {
        return String(value ?? "")
            .replaceAll("&", "&amp;")
            .replaceAll("<", "&lt;")
            .replaceAll(">", "&gt;")
            .replaceAll('"', "&quot;")
            .replaceAll("'", "&#039;");
    }

    function setText(id, value) {
        const element = document.getElementById(id);

        if (element) {
            element.textContent = value;
        }
    }

    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]').content;
    }
});
