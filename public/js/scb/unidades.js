document.addEventListener("DOMContentLoaded", function () {
    const modalUnidadEl = document.getElementById("modalUnidad");
    const modalUnidad = new bootstrap.Modal(modalUnidadEl);

    const formUnidad = document.getElementById("formUnidad");

    const unidadId = document.getElementById("unidad_id");
    const descripcion = document.getElementById("descripcion");
    const placas = document.getElementById("placas");
    const activo = document.getElementById("activo");

    const modalTitulo = document.getElementById("modalUnidadTitulo");

    document
        .getElementById("btnNuevaUnidad")
        .addEventListener("click", function () {
            limpiarFormularioUnidad();
            modalTitulo.textContent = "Nueva unidad";
            modalUnidad.show();
        });

    document.addEventListener("click", function (e) {
        const btnEditar = e.target.closest(".btnEditarUnidad");

        if (btnEditar) {
            limpiarFormularioUnidad();

            unidadId.value = btnEditar.dataset.id;
            descripcion.value = btnEditar.dataset.descripcion || "";
            placas.value = btnEditar.dataset.placas || "";
            activo.checked = Number(btnEditar.dataset.activo || 0) === 1;

            modalTitulo.textContent = "Editar unidad";
            modalUnidad.show();

            return;
        }

        const btnEliminar = e.target.closest(".btnEliminarUnidad");

        if (btnEliminar) {
            eliminarUnidad(
                btnEliminar.dataset.id,
                Number(btnEliminar.dataset.activo || 0) === 1,
            );
        }
    });

    placas.addEventListener("input", function () {
        this.value = this.value.toUpperCase();
    });

    formUnidad.addEventListener("submit", async function (e) {
        e.preventDefault();

        limpiarErroresUnidad();

        const id = unidadId.value;
        const isEdit = !!id;

        const url = isEdit ? `/scb/unidades/${id}` : `/scb/unidades`;

        const formData = new FormData(formUnidad);

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
                    mostrarErroresUnidad(data.errors);
                    return;
                }

                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: data.message || "No se pudo guardar la información.",
                });

                return;
            }

            modalUnidad.hide();

            Swal.fire({
                icon: "success",
                title: "Correcto",
                text: data.message,
                timer: 1500,
                showConfirmButton: false,
            });

            actualizarTablaUnidad(data.data, isEdit);
        } catch (error) {
            console.error(error);

            Swal.fire({
                icon: "error",
                title: "Error inesperado",
                text: "No se pudo procesar la solicitud.",
            });
        }
    });

    async function eliminarUnidad(id, activo) {
        let Tmensaje = "¿Desea desactivar esta unidad?";
        let mensaje =
            "Al desactivar la unidad, esta ya no estará disponible para ser asignada en los movimientos ¿Desea continuar?";

        if (!activo) {
            Tmensaje = "¿Desea activar esta unidad?";
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
            const response = await fetch(`/scb/unidades/${id}`, {
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
                    text: data.message || "No se pudo eliminar la unidad.",
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

    function limpiarFormularioUnidad() {
        formUnidad.reset();
        unidadId.value = "";
        activo.checked = true;
        limpiarErroresUnidad();
    }

    function limpiarErroresUnidad() {
        ["descripcion", "placas"].forEach((field) => {
            const input = document.getElementById(field);
            const error = document.getElementById(`error_${field}`);

            if (input) {
                input.classList.remove("is-invalid");
            }

            if (error) {
                error.textContent = "";
            }
        });
    }

    function mostrarErroresUnidad(errors) {
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

    function actualizarTablaUnidad(unidad, isEdit) {
        document.getElementById("unidades-empty-row")?.remove();

        if (isEdit) {
            const row = document.getElementById(`unidad-row-${unidad.id}`);

            if (!row) return;

            row.querySelector(".unidad-descripcion").textContent =
                unidad.descripcion;
            row.querySelector(".unidad-placas").textContent =
                unidad.placas || "S/N";
            row.querySelector(".unidad-activo").innerHTML = unidad.activo
                ? `<span class="badge bg-success">Activo</span>`
                : `<span class="badge bg-secondary">Inactivo</span>`;

            const btnEditar = row.querySelector(".btnEditarUnidad");

            btnEditar.dataset.descripcion = unidad.descripcion;
            btnEditar.dataset.placas = unidad.placas || "";
            btnEditar.dataset.activo = unidad.activo ? 1 : 0;

            return;
        }

        const tbody = document.querySelector("#tablaUnidades tbody");

        const tr = document.createElement("tr");
        tr.id = `unidad-row-${unidad.id}`;

        tr.innerHTML = `
            <td>${unidad.id}</td>
            <td class="fw-bold unidad-descripcion">${escapeHtml(unidad.descripcion)}</td>
            <td class="unidad-placas">${unidad.placas ? escapeHtml(unidad.placas) : "S/N"}</td>
            <td class="unidad-activo">
                ${
                    unidad.activo
                        ? `<span class="badge bg-success">Activo</span>`
                        : `<span class="badge bg-secondary">Inactivo</span>`
                }
            </td>
            <td class="text-end">
                <button type="button"
                    class="btn btn-sm btn-outline-primary btnEditarUnidad"
                    data-id="${unidad.id}"
                    data-descripcion="${escapeHtml(unidad.descripcion)}"
                    data-placas="${unidad.placas ? escapeHtml(unidad.placas) : ""}"
                    data-activo="${unidad.activo ? 1 : 0}">
                    <i class="fas fa-edit"></i>
                </button>

                <button type="button"
                    class="btn btn-sm btn-outline-danger btnEliminarUnidad"
                    data-id="${unidad.id}" data-activo="${unidad.activo ? 1 : 0}">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;

        tbody.prepend(tr);
    }

    function validarTablaVacia() {
        const tbody = document.querySelector("#tablaUnidades tbody");

        if (tbody.children.length > 0) return;

        tbody.innerHTML = `
            <tr id="unidades-empty-row">
                <td colspan="5" class="text-center text-muted py-4">
                    No hay unidades registradas.
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
