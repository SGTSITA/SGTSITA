/**
 * SGTSITA - Módulo de Gastos: Reporte Unificado Matriz Pivote, Catálogo de Categorías/Subcategorías y Reclasificación
 */

document.addEventListener("DOMContentLoaded", function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "";

    // ==========================================
    // 1. REPORTES UNIFICADOS (MATRIZ PIVOTE EN NUEVA PESTAÑA)
    // ==========================================
    const btnAbrirReporte = document.getElementById("btnAbrirReporteUnificado");

    if (btnAbrirReporte) {
        btnAbrirReporte.addEventListener("click", function () {
            let from = "";
            let to = "";

            // 1. Intentar leer directamente de la instancia de daterangepicker
            const picker = $("#daterange").data("daterangepicker");
            if (picker && picker.startDate && picker.endDate) {
                from = picker.startDate.format("YYYY-MM-DD");
                to = picker.endDate.format("YYYY-MM-DD");
            } else if (window.mesinicio && window.mesfin) {
                from = window.mesinicio;
                to = window.mesfin;
            } else {
                const val = $("#daterange").val() || "";
                const partes = val.split(/ - | AL | al /i);
                if (partes.length === 2) {
                    from = partes[0].trim();
                    to = partes[1].trim();
                }
            }

            // Fallback si no hay fechas válidas: año actual
            if (!from || !to) {
                from = moment().startOf("year").format("YYYY-MM-DD");
                to = moment().endOf("year").format("YYYY-MM-DD");
            }

            const url = `/gastos/reporte-unificado?from=${encodeURIComponent(from)}&to=${encodeURIComponent(to)}`;
            window.open(url, "_blank");
        });
    }

    // ==========================================
    // 2. ADMINISTRACIÓN DE CATEGORÍAS Y CONCEPTOS
    // ==========================================
    const modalCatalogo = document.getElementById("modalCatalogoCategorias");
    const btnAbrirCatalogo = document.getElementById("btnAbrirCatalogoCategorias");
    let cacheCategorias = [];
    let cacheConceptos = [];

    if (btnAbrirCatalogo) {
        btnAbrirCatalogo.addEventListener("click", function () {
            const modalBs = bootstrap.Modal.getOrCreateInstance(modalCatalogo, { focus: false });
            modalBs.show();
            cargarCatalogosModal();
        });
    }

    function cargarCatalogosModal() {
        fetch("/gastos/catalogos/categorias-conceptos")
            .then(res => res.json())
            .then(res => {
                if (res.TMensaje === "success" && res.catalogos) {
                    cacheCategorias = res.catalogos.categorias || [];
                    cacheConceptos = res.catalogos.conceptos || [];
                    renderizarTablaCategorias();
                    renderizarSelectsCategorias();
                    renderizarTablaSubcategorias();
                }
            })
            .catch(err => console.error("Error al cargar catálogos:", err));
    }

    function renderizarSelectsCategorias() {
        const selectPadre = document.getElementById("selectCatPadreSubcat");
        const filtroSelect = document.getElementById("filtroCatEnSubcat");
        const selectReclasificar = document.getElementById("reclasificarSelectCategoria");

        if (selectPadre) {
            selectPadre.innerHTML = '<option value="">-- Seleccione Categoría --</option>';
            cacheCategorias.forEach(c => {
                selectPadre.innerHTML += `<option value="${c.id}">${c.categoria}</option>`;
            });
        }

        if (filtroSelect) {
            const valPrevio = filtroSelect.value;
            filtroSelect.innerHTML = '<option value="">Todas las Categorías</option>';
            cacheCategorias.forEach(c => {
                filtroSelect.innerHTML += `<option value="${c.id}">${c.categoria}</option>`;
            });
            filtroSelect.value = valPrevio;
        }

        if (selectReclasificar) {
            selectReclasificar.innerHTML = '<option value="">-- Seleccione Categoría --</option>';
            cacheCategorias.forEach(c => {
                selectReclasificar.innerHTML += `<option value="${c.id}">${c.categoria}</option>`;
            });
        }
    }

    function renderizarTablaCategorias() {
        const tbody = document.getElementById("tbodyCategoriasCatalogo");
        if (!tbody) return;

        if (cacheCategorias.length === 0) {
            tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted py-3">No hay categorías registradas.</td></tr>';
            return;
        }

        let html = "";
        cacheCategorias.forEach(c => {
            html += `
                <tr>
                    <td><span class="badge bg-light text-dark">${c.id}</span></td>
                    <td class="fw-bold">${c.categoria}</td>
                    <td class="text-center">
                        <button type="button" class="btn btn-xs btn-outline-primary py-1 px-2 mb-0" 
                                onclick="editarCategoriaCatalogo(${c.id}, '${c.categoria.replace(/'/g, "\\'")}')" 
                                title="Editar nombre">
                            <i class="fa fa-edit"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
        tbody.innerHTML = html;
    }

    function renderizarTablaSubcategorias() {
        const tbody = document.getElementById("tbodySubcategoriasCatalogo");
        const filtroCat = document.getElementById("filtroCatEnSubcat")?.value;
        if (!tbody) return;

        let lista = cacheConceptos;
        if (filtroCat) {
            lista = lista.filter(item => String(item.categoria_gasto_id) === String(filtroCat));
        }

        if (lista.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">No hay subcategorías registradas para esta categoría.</td></tr>';
            return;
        }

        let html = "";
        lista.forEach(item => {
            const catNombre = item.categoria?.categoria || "Sin categoría";
            html += `
                <tr>
                    <td><span class="badge bg-light text-dark">${item.id}</span></td>
                    <td class="fw-bold">${item.nombre}</td>
                    <td><span class="badge bg-info text-white">${catNombre}</span></td>
                    <td class="text-center">
                        <button type="button" class="btn btn-xs btn-outline-primary py-1 px-2 mb-0" 
                                onclick="editarSubcategoriaCatalogo(${item.id}, '${item.nombre.replace(/'/g, "\\'")}', ${item.categoria_gasto_id || 'null'})" 
                                title="Editar nombre">
                            <i class="fa fa-edit"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
        tbody.innerHTML = html;
    }

    document.getElementById("filtroCatEnSubcat")?.addEventListener("change", renderizarTablaSubcategorias);

    // Formulario Crear Categoría
    document.getElementById("formNuevaCategoria")?.addEventListener("submit", function (e) {
        e.preventDefault();
        const input = document.getElementById("txtNuevaCategoria");
        const nombre = input.value.trim();
        if (!nombre) return;

        fetch("/gastos/categorias/store", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken,
            },
            body: JSON.stringify({ categoria: nombre }),
        })
            .then(res => res.json())
            .then(res => {
                if (res.TMensaje === "success") {
                    input.value = "";
                    if (typeof toastr !== "undefined") toastr.success(res.Mensaje);
                    cargarCatalogosModal();
                } else {
                    Swal.fire("Error", res.Mensaje || "No se pudo guardar la categoría", "error");
                }
            })
            .catch(err => Swal.fire("Error", "Error al registrar categoría", "error"));
    });

    // Formulario Crear Subcategoría
    document.getElementById("formNuevaSubcategoria")?.addEventListener("submit", function (e) {
        e.preventDefault();
        const catSelect = document.getElementById("selectCatPadreSubcat");
        const input = document.getElementById("txtNuevaSubcat");
        const catId = catSelect.value;
        const nombre = input.value.trim();
        if (!catId || !nombre) return;

        fetch("/gastos/conceptos/store", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken,
            },
            body: JSON.stringify({ categoria_gasto_id: catId, nombre: nombre }),
        })
            .then(res => res.json())
            .then(res => {
                if (res.TMensaje === "success") {
                    input.value = "";
                    if (typeof toastr !== "undefined") toastr.success(res.Mensaje);
                    cargarCatalogosModal();
                } else {
                    Swal.fire("Error", res.Mensaje || "No se pudo guardar la subcategoría", "error");
                }
            })
            .catch(err => Swal.fire("Error", "Error al registrar subcategoría", "error"));
    });

    // Funciones Globales para Edición In-Situ
    window.editarCategoriaCatalogo = function (id, nombreActual) {
        Swal.fire({
            title: "Editar Categoría",
            input: "text",
            inputValue: nombreActual,
            inputLabel: "Nuevo nombre de la categoría:",
            showCancelButton: true,
            confirmButtonText: "Guardar",
            cancelButtonText: "Cancelar",
            didOpen: () => {
                // Desactivar listener de foco forzado de Bootstrap para permitir escribir
                $(document).off("focusin.bs.modal");
                const input = Swal.getInput();
                if (input) {
                    setTimeout(() => {
                        input.removeAttribute("disabled");
                        input.removeAttribute("readonly");
                        input.focus();
                        input.select();
                    }, 50);
                }
            },
            inputValidator: (val) => {
                if (!val || !val.trim()) return "El nombre no puede estar vacío";
            },
        }).then(result => {
            if (result.isConfirmed) {
                fetch(`/gastos/categorias/${id}`, {
                    method: "PUT",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrfToken,
                    },
                    body: JSON.stringify({ categoria: result.value.trim() }),
                })
                    .then(res => res.json())
                    .then(res => {
                        if (res.TMensaje === "success") {
                            if (typeof toastr !== "undefined") toastr.success("Categoría actualizada");
                            cargarCatalogosModal();
                        } else {
                            Swal.fire("Error", res.Mensaje || "No se pudo actualizar", "error");
                        }
                    });
            }
        });
    };

    window.editarSubcategoriaCatalogo = function (id, nombreActual, catIdActual) {
        Swal.fire({
            title: "Editar Subcategoría",
            input: "text",
            inputValue: nombreActual,
            inputLabel: "Nuevo nombre de la subcategoría:",
            showCancelButton: true,
            confirmButtonText: "Guardar",
            cancelButtonText: "Cancelar",
            didOpen: () => {
                // Desactivar listener de foco forzado de Bootstrap para permitir escribir
                $(document).off("focusin.bs.modal");
                const input = Swal.getInput();
                if (input) {
                    setTimeout(() => {
                        input.removeAttribute("disabled");
                        input.removeAttribute("readonly");
                        input.focus();
                        input.select();
                    }, 50);
                }
            },
            inputValidator: (val) => {
                if (!val || !val.trim()) return "El nombre no puede estar vacío";
            },
        }).then(result => {
            if (result.isConfirmed) {
                fetch(`/gastos/conceptos/${id}`, {
                    method: "PUT",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrfToken,
                    },
                    body: JSON.stringify({ nombre: result.value.trim(), categoria_gasto_id: catIdActual }),
                })
                    .then(res => res.json())
                    .then(res => {
                        if (res.TMensaje === "success") {
                            if (typeof toastr !== "undefined") toastr.success("Subcategoría actualizada");
                            cargarCatalogosModal();
                        } else {
                            Swal.fire("Error", res.Mensaje || "No se pudo actualizar", "error");
                        }
                    });
            }
        });
    };

    // ==========================================
    // 3. RECLASIFICACIÓN DE GASTOS EXISTENTES
    // ==========================================
    const modalReclasificar = document.getElementById("modalReclasificarGasto");
    const selectCatReclasificar = document.getElementById("reclasificarSelectCategoria");
    const selectSubcatReclasificar = document.getElementById("reclasificarSelectSubcategoria");
    const btnReclasificarMultiple = document.getElementById("btnReclasificarMultiple");
    let selectedGastosIds = [];

    if (selectCatReclasificar) {
        selectCatReclasificar.addEventListener("change", function () {
            cargarSubcategoriasParaReclasificar(this.value);
        });
    }

    function cargarSubcategoriasParaReclasificar(categoriaId, subcategoriaSeleccionada = null) {
        selectSubcatReclasificar.innerHTML = '<option value="">-- Sin Subcategoría / General --</option>';
        if (!categoriaId) return;

        fetch(`/gastos/categorias/${categoriaId}/conceptos`)
            .then(res => res.json())
            .then(conceptos => {
                conceptos.forEach(item => {
                    const sel = (subcategoriaSeleccionada && String(subcategoriaSeleccionada) === String(item.id)) ? "selected" : "";
                    selectSubcatReclasificar.innerHTML += `<option value="${item.id}" ${sel}>${item.nombre}</option>`;
                });
            })
            .catch(err => console.error("Error al cargar conceptos para reclasificar:", err));
    }

    window.abrirModalReclasificarIndividual = function (gasto) {
        selectedGastosIds = [gasto.id];
        document.getElementById("reclasificarGastoId").value = gasto.id;
        document.getElementById("reclasificarEsMultiple").value = "0";
        document.getElementById("reclasificarModalTitulo").innerText = `Reclasificar Gasto #${gasto.id}`;
        document.getElementById("reclasificarAlertInfo").innerText = "Modifique la categoría y subcategoría para reclasificar este gasto.";
        document.getElementById("divReclasificarConceptoActual").style.display = "block";
        document.getElementById("reclasificarConceptoTxt").value = gasto.concepto || "";

        renderizarSelectsCategorias();
        document.getElementById("reclasificarSelectCategoria").value = gasto.categoria_gasto_id || "";
        cargarSubcategoriasParaReclasificar(gasto.categoria_gasto_id, gasto.gasto_concepto_id);

        const modalBs = new bootstrap.Modal(modalReclasificar);
        modalBs.show();
    };

    if (btnReclasificarMultiple) {
        btnReclasificarMultiple.addEventListener("click", function () {
            let seleccionados = [];
            if (typeof gridOptions !== "undefined" && gridOptions.api) {
                seleccionados = gridOptions.api.getSelectedRows();
            }

            if (!seleccionados || seleccionados.length === 0) {
                Swal.fire({
                    icon: "warning",
                    title: "Sin selección",
                    text: "Por favor, marque al menos una casilla en la tabla para reclasificar los gastos seleccionados.",
                });
                return;
            }

            selectedGastosIds = seleccionados.map(g => g.id);
            document.getElementById("reclasificarGastoId").value = "";
            document.getElementById("reclasificarEsMultiple").value = "1";
            document.getElementById("reclasificarModalTitulo").innerText = `Reclasificar ${seleccionados.length} Gastos Seleccionados`;
            document.getElementById("reclasificarAlertInfo").innerText = `Se asignará la nueva categoría y subcategoría a los ${seleccionados.length} gastos seleccionados.`;
            document.getElementById("divReclasificarConceptoActual").style.display = "none";

            renderizarSelectsCategorias();
            document.getElementById("reclasificarSelectCategoria").value = "";
            selectSubcatReclasificar.innerHTML = '<option value="">-- Sin Subcategoría / General --</option>';

            const modalBs = new bootstrap.Modal(modalReclasificar);
            modalBs.show();
        });
    }

    document.getElementById("formReclasificarGasto")?.addEventListener("submit", function (e) {
        e.preventDefault();
        const esMultiple = document.getElementById("reclasificarEsMultiple").value === "1";
        const catId = document.getElementById("reclasificarSelectCategoria").value;
        const subcatId = document.getElementById("reclasificarSelectSubcategoria").value || null;
        const concepto = document.getElementById("reclasificarConceptoTxt").value;

        if (!catId) {
            Swal.fire("Atención", "Debe seleccionar una categoría", "warning");
            return;
        }

        const btnGuardar = document.getElementById("btnGuardarReclasificacion");
        btnGuardar.disabled = true;

        if (esMultiple) {
            fetch("/gastos/reclasificar-multiple", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                },
                body: JSON.stringify({
                    gasto_ids: selectedGastosIds,
                    categoria_gasto_id: catId,
                    gasto_concepto_id: subcatId,
                }),
            })
                .then(res => res.json())
                .then(res => {
                    btnGuardar.disabled = false;
                    if (res.TMensaje === "success") {
                        bootstrap.Modal.getInstance(modalReclasificar)?.hide();
                        Swal.fire("¡Éxito!", res.Mensaje, "success");
                        if (typeof fetchGastos === "function") fetchGastos();
                    } else {
                        Swal.fire("Error", res.Mensaje || "No se pudo reclasificar", "error");
                    }
                })
                .catch(err => {
                    btnGuardar.disabled = false;
                    Swal.fire("Error", "Error de comunicación", "error");
                });
        } else {
            const gastoId = document.getElementById("reclasificarGastoId").value;
            fetch("/gastos/reclasificar", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                },
                body: JSON.stringify({
                    gasto_id: gastoId,
                    categoria_gasto_id: catId,
                    gasto_concepto_id: subcatId,
                    concepto: concepto,
                }),
            })
                .then(res => res.json())
                .then(res => {
                    btnGuardar.disabled = false;
                    if (res.TMensaje === "success") {
                        bootstrap.Modal.getInstance(modalReclasificar)?.hide();
                        Swal.fire("¡Éxito!", res.Mensaje, "success");
                        if (typeof fetchGastos === "function") fetchGastos();
                    } else {
                        Swal.fire("Error", res.Mensaje || "No se pudo reclasificar", "error");
                    }
                })
                .catch(err => {
                    btnGuardar.disabled = false;
                    Swal.fire("Error", "Error de comunicación", "error");
                });
        }
    });
});
