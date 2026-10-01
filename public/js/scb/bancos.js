document.addEventListener("DOMContentLoaded", function () {
    const modalBancoEl = document.getElementById("modalBanco");
    const modalBanco = new bootstrap.Modal(modalBancoEl);

    const formBanco = document.getElementById("formBanco");

    const bancoId = document.getElementById("banco_id");
    const nombre = document.getElementById("nombre");
    const clave = document.getElementById("clave");
    const activo = document.getElementById("activo");

    const modalTitulo = document.getElementById("modalBancoTitulo");

    document
        .getElementById("btnNuevoBanco")
        .addEventListener("click", function () {
            limpiarFormularioBanco();
            modalTitulo.textContent = "Nuevo banco";
            modalBanco.show();
        });

    document.addEventListener("click", function (e) {
        const btnEditar = e.target.closest(".btnEditarBanco");

        if (btnEditar) {
            limpiarFormularioBanco();

            bancoId.value = btnEditar.dataset.id;
            nombre.value = btnEditar.dataset.nombre || "";
            clave.value = btnEditar.dataset.clave || "";
            activo.checked = Number(btnEditar.dataset.activo || 0) === 1;

            modalTitulo.textContent = "Editar banco";
            modalBanco.show();

            return;
        }

        const btnEliminar = e.target.closest(".btnEliminarBanco");

        if (btnEliminar) {
            eliminarBanco(
                btnEliminar.dataset.id,
                Number(btnEliminar.dataset.activo || 0) === 1,
            );
        }
    });

    formBanco.addEventListener("submit", async function (e) {
        e.preventDefault();

        limpiarErroresBanco();

        const id = bancoId.value;
        const isEdit = !!id;

        const url = isEdit ? `/scb/bancos/${id}` : `/scb/bancos`;

        const formData = new FormData(formBanco);

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
                    mostrarErroresBanco(data.errors);
                    return;
                }

                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: data.message || "No se pudo guardar la información.",
                });

                return;
            }

            modalBanco.hide();

            Swal.fire({
                icon: "success",
                title: "Correcto",
                text: data.message,
                timer: 1500,
                showConfirmButton: false,
            });

            actualizarTablaBanco(data.data, isEdit);
        } catch (error) {
            console.error(error);

            Swal.fire({
                icon: "error",
                title: "Error inesperado",
                text: "No se pudo procesar la solicitud.",
            });
        }
    });

    async function eliminarBanco(id, activo) {
        let Tmensaje = "¿Eliminar banco?";
        let mensaje = "Esta acción no se puede deshacer.";

        if (!activo) {
            Tmensaje = "¿Desea activar este banco?";
            mensaje =
                "Volvera a estar disponible para ser asignado en los movimientos ¿Desea continuar?";
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
            const response = await fetch(`/scb/bancos/${id}`, {
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
                    text: data.message || "No se pudo eliminar el banco.",
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

    function limpiarFormularioBanco() {
        formBanco.reset();
        bancoId.value = "";
        activo.checked = true;
        limpiarErroresBanco();
    }

    function limpiarErroresBanco() {
        ["nombre", "clave"].forEach((field) => {
            const input = document.getElementById(field);
            const error = document.getElementById(`error_${field}`);

            input.classList.remove("is-invalid");

            if (error) {
                error.textContent = "";
            }
        });
    }

    function mostrarErroresBanco(errors) {
        Object.keys(errors).forEach((field) => {
            const input = document.getElementById(field);
            const error = document.getElementById(`error_${field}`);

            if (input) {
                input.classList.add("is-invalid");
            }

            if (error) {
                error.textContent = errors[field][0];
            }
        });
    }

    function actualizarTablaBanco(banco, isEdit) {
        document.getElementById("bancos-empty-row")?.remove();

        if (isEdit) {
            const row = document.getElementById(`banco-row-${banco.id}`);

            if (!row) return;

            row.querySelector(".banco-nombre").textContent = banco.nombre;
            row.querySelector(".banco-clave").textContent =
                banco.clave || "S/N";
            row.querySelector(".banco-activo").innerHTML = banco.activo
                ? `<span class="badge bg-success">Activo</span>`
                : `<span class="badge bg-secondary">Inactivo</span>`;

            const btnEditar = row.querySelector(".btnEditarBanco");
            btnEditar.dataset.nombre = banco.nombre;
            btnEditar.dataset.clave = banco.clave || "";
            btnEditar.dataset.activo = banco.activo ? 1 : 0;

            return;
        }

        const tbody = document.querySelector("#tablaBancos tbody");

        const tr = document.createElement("tr");
        tr.id = `banco-row-${banco.id}`;

        tr.innerHTML = `
            <td>${banco.id}</td>
            <td class="fw-bold banco-nombre">${escapeHtml(banco.nombre)}</td>
            <td class="banco-clave">${banco.clave ? escapeHtml(banco.clave) : "S/N"}</td>
            <td class="banco-activo">
                ${
                    banco.activo
                        ? `<span class="badge bg-success">Activo</span>`
                        : `<span class="badge bg-secondary">Inactivo</span>`
                }
            </td>
            <td class="text-end">
                <button type="button"
                    class="btn btn-sm btn-outline-primary btnEditarBanco"
                    data-id="${banco.id}"
                    data-nombre="${escapeHtml(banco.nombre)}"
                    data-clave="${banco.clave ? escapeHtml(banco.clave) : ""}"
                    data-activo="${banco.activo ? 1 : 0}">
                    <i class="fas fa-edit"></i>
                </button>

                <button type="button"
                    class="btn btn-sm btn-outline-danger btnEliminarBanco"
                    data-id="${banco.id}">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;

        tbody.prepend(tr);
    }

    function validarTablaVacia() {
        const tbody = document.querySelector("#tablaBancos tbody");

        if (tbody.children.length > 0) return;

        tbody.innerHTML = `
            <tr id="bancos-empty-row">
                <td colspan="5" class="text-center text-muted py-4">
                    No hay bancos registrados.
                </td>
            </tr>
        `;
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
