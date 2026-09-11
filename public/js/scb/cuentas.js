document.addEventListener("DOMContentLoaded", function () {
    const modalCuentaEl = document.getElementById("modalCuenta");
    const modalCuenta = new bootstrap.Modal(modalCuentaEl);

    const formCuenta = document.getElementById("formCuenta");

    const cuentaId = document.getElementById("cuenta_id");
    const bancoId = document.getElementById("banco_id");
    const beneficiario = document.getElementById("beneficiario");
    const numeroCuenta = document.getElementById("numero_cuenta");
    const clabe = document.getElementById("clabe");
    const moneda = document.getElementById("moneda");
    const saldoInicial = document.getElementById("saldo_inicial");
    const activo = document.getElementById("activo");

    const modalTitulo = document.getElementById("modalCuentaTitulo");

    document
        .getElementById("btnNuevaCuenta")
        .addEventListener("click", function () {
            limpiarFormularioCuenta();
            modalTitulo.textContent = "Nueva cuenta";
            modalCuenta.show();
        });

    document.addEventListener("click", function (e) {
        const btnEditar = e.target.closest(".btnEditarCuenta");

        if (btnEditar) {
            limpiarFormularioCuenta();

            cuentaId.value = btnEditar.dataset.id;
            bancoId.value = btnEditar.dataset.bancoId || "";
            beneficiario.value = btnEditar.dataset.beneficiario || "";
            numeroCuenta.value = btnEditar.dataset.numeroCuenta || "";
            clabe.value = btnEditar.dataset.clabe || "";
            moneda.value = btnEditar.dataset.moneda || "MXN";
            saldoInicial.value = btnEditar.dataset.saldoInicial || 0;
            activo.checked = Number(btnEditar.dataset.activo || 0) === 1;

            modalTitulo.textContent = "Editar cuenta";
            modalCuenta.show();

            return;
        }

        const btnEliminar = e.target.closest(".btnEliminarCuenta");

        if (btnEliminar) {
            eliminarCuenta(
                btnEliminar.dataset.id,
                Number(btnEliminar.dataset.activo || 0) === 1,
            );
        }
    });

    formCuenta.addEventListener("submit", async function (e) {
        e.preventDefault();

        limpiarErroresCuenta();

        const id = cuentaId.value;
        const isEdit = !!id;

        const url = isEdit ? `/scb/cuentas/${id}` : `/scb/cuentas`;

        const formData = new FormData(formCuenta);

        if (isEdit) {
            formData.append("_method", "PUT");
        }

        try {
            const response = await fetch(url, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector(
                        'meta[name="csrf-token"]',
                    ).content,
                    Accept: "application/json",
                },
                body: formData,
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                if (response.status === 422 && data.errors) {
                    mostrarErroresCuenta(data.errors);
                    return;
                }

                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: data.message || "No se pudo guardar la información.",
                });

                return;
            }

            modalCuenta.hide();

            Swal.fire({
                icon: "success",
                title: "Correcto",
                text: data.message,
                timer: 1500,
                showConfirmButton: false,
            });

            actualizarTablaCuenta(data.data, isEdit);
        } catch (error) {
            console.error(error);

            Swal.fire({
                icon: "error",
                title: "Error inesperado",
                text: "No se pudo procesar la solicitud.",
            });
        }
    });

    async function eliminarCuenta(id, activo) {
        let Tmensaje = "¿Desea desactivar esta cuenta?";
        let mensaje =
            "Al desactivar la cuenta, esta ya no estará disponible para ser asignada en los movimientos ¿Desea continuar?";

        if (!activo) {
            Tmensaje = "¿Desea activar esta cuenta?";
            mensaje =
                "Volvera a estar disponible para ser asignada en los movimientos ¿Desea continuar?";
        }

        const confirmacion = await Swal.fire({
            icon: "warning",
            title: Tmensaje,
            text: mensaje,
            showCancelButton: true,
            confirmButtonText: "Sí, eliminar",
            cancelButtonText: "Cancelar",
            confirmButtonColor: "#dc3545",
        });

        if (!confirmacion.isConfirmed) return;

        try {
            const response = await fetch(`/scb/cuentas/${id}`, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector(
                        'meta[name="csrf-token"]',
                    ).content,
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
                    text: data.message || "No se pudo eliminar la cuenta.",
                });

                return;
            }
            Swal.fire({
                icon: "success",
                title: "Correcto",
                text: data.message,
                timer: 1500,
                showConfirmButton: false,
            });

            location.reload();

            validarTablaVacia();
        } catch (error) {
            console.error(error);

            Swal.fire({
                icon: "error",
                title: "Error inesperado",
                text: "No se pudo procesar la solicitud.",
            });
        }
    }

    function limpiarFormularioCuenta() {
        formCuenta.reset();
        cuentaId.value = "";
        bancoId.value = "";
        moneda.value = "MXN";
        saldoInicial.value = 0;
        activo.checked = true;
        limpiarErroresCuenta();
    }

    function limpiarErroresCuenta() {
        [
            "banco_id",
            "beneficiario",
            "numero_cuenta",
            "clabe",
            "moneda",
            "saldo_inicial",
        ].forEach((field) => {
            const input = document.getElementById(field);
            const error = document.getElementById(`error_${field}`);

            if (input) input.classList.remove("is-invalid");
            if (error) error.textContent = "";
        });
    }

    function mostrarErroresCuenta(errors) {
        Object.keys(errors).forEach((field) => {
            const input = document.getElementById(field);
            const error = document.getElementById(`error_${field}`);

            if (input) input.classList.add("is-invalid");
            if (error) error.textContent = errors[field][0];
        });
    }

    function actualizarTablaCuenta(cuenta, isEdit) {
        document.getElementById("cuentas-empty-row")?.remove();

        if (isEdit) {
            const row = document.getElementById(`cuenta-row-${cuenta.id}`);

            if (!row) return;

            row.querySelector(".cuenta-banco").textContent =
                cuenta.banco?.nombre || "S/N";
            row.querySelector(".cuenta-beneficiario").textContent =
                cuenta.beneficiario || "S/N";
            row.querySelector(".cuenta-numero").textContent =
                cuenta.numero_cuenta || "S/N";
            row.querySelector(".cuenta-clabe").textContent =
                cuenta.clabe || "S/N";
            row.querySelector(".cuenta-moneda").textContent =
                cuenta.moneda || "MXN";
            row.querySelector(".cuenta-saldo").textContent =
                `$${formatMoney(cuenta.saldo_inicial || 0)}`;
            row.querySelector(".cuenta-activo").innerHTML = cuenta.activo
                ? `<span class="badge bg-success">Activo</span>`
                : `<span class="badge bg-secondary">Inactivo</span>`;

            const btnEditar = row.querySelector(".btnEditarCuenta");

            btnEditar.dataset.bancoId = cuenta.banco_id;
            btnEditar.dataset.beneficiario = cuenta.beneficiario || "";
            btnEditar.dataset.numeroCuenta = cuenta.numero_cuenta || "";
            btnEditar.dataset.clabe = cuenta.clabe || "";
            btnEditar.dataset.moneda = cuenta.moneda || "MXN";
            btnEditar.dataset.saldoInicial = cuenta.saldo_inicial || 0;
            btnEditar.dataset.activo = cuenta.activo ? 1 : 0;

            return;
        }

        const tbody = document.querySelector("#tablaCuentas tbody");

        const tr = document.createElement("tr");
        tr.id = `cuenta-row-${cuenta.id}`;

        tr.innerHTML = `
            <td>${cuenta.id}</td>
            <td class="cuenta-banco fw-bold">${escapeHtml(cuenta.banco?.nombre || "S/N")}</td>
            <td class="cuenta-beneficiario">${escapeHtml(cuenta.beneficiario || "S/N")}</td>
            <td class="cuenta-numero">${escapeHtml(cuenta.numero_cuenta || "S/N")}</td>
            <td class="cuenta-clabe">${escapeHtml(cuenta.clabe || "S/N")}</td>
            <td class="cuenta-moneda">${escapeHtml(cuenta.moneda || "MXN")}</td>
            <td class="cuenta-saldo text-end">$${formatMoney(cuenta.saldo_inicial || 0)}</td>
            <td class="cuenta-activo">
                ${
                    cuenta.activo
                        ? `<span class="badge bg-success">Activo</span>`
                        : `<span class="badge bg-secondary">Inactivo</span>`
                }
            </td>
            <td class="text-end">
                <button type="button"
                    class="btn btn-sm btn-outline-primary btnEditarCuenta"
                    data-id="${cuenta.id}"
                    data-banco-id="${cuenta.banco_id}"
                    data-beneficiario="${escapeHtml(cuenta.beneficiario || "")}"
                    data-numero-cuenta="${escapeHtml(cuenta.numero_cuenta || "")}"
                    data-clabe="${escapeHtml(cuenta.clabe || "")}"
                    data-moneda="${escapeHtml(cuenta.moneda || "MXN")}"
                    data-saldo-inicial="${cuenta.saldo_inicial || 0}"
                    data-activo="${cuenta.activo ? 1 : 0}">
                    <i class="fas fa-edit"></i>
                </button>

                <button type="button"
                    class="btn btn-sm btn-outline-danger btnEliminarCuenta"
                    data-id="${cuenta.id}">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;

        tbody.prepend(tr);
    }

    function validarTablaVacia() {
        const tbody = document.querySelector("#tablaCuentas tbody");

        if (tbody.children.length > 0) return;

        tbody.innerHTML = `
            <tr id="cuentas-empty-row">
                <td colspan="9" class="text-center text-muted py-4">
                    No hay cuentas registradas.
                </td>
            </tr>
        `;
    }

    function formatMoney(value) {
        return Number(value || 0).toLocaleString("es-MX", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
    }

    function escapeHtml(value) {
        return String(value ?? "")
            .replaceAll("&", "&amp;")
            .replaceAll("<", "&lt;")
            .replaceAll(">", "&gt;")
            .replaceAll('"', "&quot;")
            .replaceAll("'", "&#039;");
    }
});
