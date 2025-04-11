document.addEventListener("DOMContentLoaded", function () {
    let formCrearProveedor = document.getElementById("formCrearProveedor");

    if (formCrearProveedor) {
        formCrearProveedor.addEventListener("submit", function(event) {
            event.preventDefault(); // 🔹 Evita el envío automático

            let form = this;
            let formData = new FormData(form);
            let rfcInput = document.getElementById("rfc");

            // 🔹 Verifica si el campo RFC existe
            if (!rfcInput) {
                Swal.fire("Error", "No se encontró el campo RFC en el formulario.", "error");
                return;
            }

            // 🔹 Validar si el RFC ya existe
            fetch(`/proveedores/validar-rfc?rfc=${encodeURIComponent(rfc)}`)
                .then(response => response.json())
                .then(data => {
                    console.log("Respuesta del servidor al validar RFC:", data); // 🔹 Verifica respuesta

                    if (data.exists) {
                        Swal.fire("Error", "El RFC ya está registrado.", "error");
                    } else {
                        // 🔹 Si el RFC no existe, enviar el formulario con AJAX
                        fetch("/proveedores/create", {
                            method: "POST",
                            body: formData,
                            headers: {
                                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    title: "¡Éxito!",
                                    text: "Proveedor creado correctamente.",
                                    icon: "success",
                                    confirmButtonText: "Aceptar"
                                }).then(() => {
                                    $("#proveedores").modal("hide"); // 🔹 Cierra el modal
                                    form.reset(); // 🔹 Limpia el formulario
                                    getProveedoresList(); // 🔹 Recarga la tabla sin recargar la página
                                });
                            } else {
                                Swal.fire("Error", "No se pudo registrar el proveedor.", "error");
                            }
                        })
                        .catch(error => {
                            Swal.fire("Error", "Hubo un problema al registrar el proveedor, RFC ya registrado.", "error");
                            console.error("Error al crear proveedor:", error);
                        });
                    }
                })
                .catch(error => {
                    Swal.fire("Error", "Hubo un problema al verificar el RFC.", "error");
                    console.error("Error en validación de RFC:", error);
                });
        });
    }
});


document.getElementById("formEditarProveedor").addEventListener("submit", function(event) {
    event.preventDefault();

    let form = this;
    let formData = new FormData(form);
    let id = document.getElementById("edit_id").value;

    fetch(`/proveedores/update/${id}`, {
        method: "POST",
        body: formData,
        headers: {
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
            "X-HTTP-Method-Override": "PATCH"
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                title: "¡Éxito!",
                text: data.message,
                icon: "success",
                confirmButtonText: "Aceptar"
            }).then(() => {
                $("#editProveedorModal").modal("hide");
                getProveedoresList(); // Recargar AG Grid después de la edición
            });
        } else {
            Swal.fire("Error", "No se pudo actualizar el proveedor.", "error");
        }
    })
    .catch(error => {
        Swal.fire("Error", "Hubo un problema al actualizar el proveedor.", "error");
        console.error("Error al actualizar proveedor:", error);
    });
});

document.getElementById("formAgregarCuenta").addEventListener("submit", function(event) {
    event.preventDefault(); // 🔹 Evita el envío del formulario hasta validar

    let form = this;
    let formData = new FormData(form);
    let cuentaClabe = document.getElementById("cuenta_clabe").value;
    let idProveedor = document.getElementById("idProveedorCuenta").value;

    // 🔹 Verificar si la CLABE ya existe antes de enviarlo
    fetch(`/cuentas-bancarias/validar-clabe?cuenta_clabe=${cuentaClabe}`)
        .then(response => response.json())
        .then(data => {
            if (data.exists) {
                Swal.fire("Error", "La CLABE ingresada ya está registrada en el sistema.", "error");
            } else {
                // Si la CLABE no está repetida, enviamos el formulario
                fetch(`/proveedores/create/cuenta`, {
                    method: "POST",
                    body: formData,
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: "¡Éxito!",
                            text: "Cuenta bancaria añadida correctamente.",
                            icon: "success",
                            confirmButtonText: "Aceptar"
                        }).then(() => {
                            $("#modalCrearCuenta").modal("hide");
                            form.reset(); // Limpiar el formulario

                            // 🔹 Recargar la lista de cuentas bancarias en el modal de cuentas
                            openCuentasBancariasModal(idProveedor);
                        });
                    } else {
                        Swal.fire("Error", "No se pudo añadir la cuenta bancaria.", "error");
                    }
                })
                .catch(error => {
                    Swal.fire("Error", "Hubo un problema al registrar la cuenta bancaria.", "error");
                    console.error("Error al agregar cuenta bancaria:", error);
                });
            }
        })
        .catch(error => {
            Swal.fire("Error", "Hubo un problema al verificar la CLABE.", "error");
            console.error("Error en validación de CLABE:", error);
        });
});



// 🔹 Traducción al español para AG Grid
const localeText = {
    page: 'Página',
    more: 'Más',
    to: 'a',
    of: 'de',
    next: 'Siguiente',
    last: 'Último',
    first: 'Primero',
    previous: 'Anterior',
    loadingOoo: 'Cargando...',
    selectAll: 'Seleccionar todo',
    searchOoo: 'Buscar...',
    blanks: 'Vacíos',
    filterOoo: 'Filtrar...',
    applyFilter: 'Aplicar filtro...',
    equals: 'Igual',
    notEqual: 'Distinto',
    lessThan: 'Menor que',
    greaterThan: 'Mayor que',
    contains: 'Contiene',
    notContains: 'No contiene',
    startsWith: 'Empieza con',
    endsWith: 'Termina con',
    andCondition: 'Y',
    orCondition: 'O',
    group: 'Grupo',
    columns: 'Columnas',
    filters: 'Filtros',
    pivotMode: 'Modo Pivote',
    groups: 'Grupos',
    values: 'Valores',
    noRowsToShow: 'Sin filas para mostrar',
    pinColumn: 'Fijar columna',
    autosizeThiscolumn: 'Ajustar columna',
    copy: 'Copiar',
    resetColumns: 'Restablecer columnas',
    blank: 'Vacíos',
    notBlank: 'No Vacíos',
    paginationPageSize: 'Registros por página'
};

// 🔹 Definición de Columnas para Proveedores
const gridOptions = {
    pagination: true,
    paginationPageSize: 100,
    paginationPageSizeSelector: [100, 200, 500],
    rowSelection: "multiple",
    localeText: localeText,
    defaultColDef: {
        resizable: true,
        flex: 1,
        minWidth: 50
    },

    columnDefs: [
        { 
            headerCheckboxSelection: true, 
            checkboxSelection: true,
            width: 50
        },
        { field: "id", hide: true },
        { field: "nombre", headerName: "Nombre / Razón Social", filter: true, floatingFilter: true, minWidth: 250 },
        { field: "rfc", headerName: "RFC", minWidth: 120 },
        { field: "regimen_fiscal", headerName: "Régimen Fiscal", minWidth: 180 },
        { field: "telefono", headerName: "Teléfono", filter: true, floatingFilter: true, minWidth: 160 },
        { field: "correo", headerName: "Correo Electrónico", filter: true, floatingFilter: true, minWidth: 220 },
        { field: "direccion", headerName: "Dirección", minWidth: 200 },
        {
            
                field: "acciones",
                headerName: "Acciones",
                minWidth: 500,
                cellRenderer: function (params) {
                    return `
                        <button class="btn btn-sm btn-outline-secondary" onclick="openEditProveedorModal(${params.data.id})">
                            <i class="fa fa-edit"></i> 
                        </button>
                        <button class="btn btn-sm btn-outline-success" onclick="openCuentasBancariasModal(${params.data.id}, '${params.data.nombre}')">
                <i class="fa fa-credit-card"></i> 
            </button>
                        <button class="btn btn-sm btn-outline-primary" 
                onclick="agregarCuentaBancaria(${params.data.id}, '${params.data.nombre}')">
                <i class="fa fa-plus"></i>
            </button>`;
                }
            }
            
    ],
    rowData: []
};




// 🔹 Inicializar AG Grid
const myGridElement = document.querySelector("#proveedoresGrid");
let apiGrid = agGrid.createGrid(myGridElement, gridOptions);

// 🔹 Cargar datos desde el backend
function getProveedoresList() {
    fetch('/proveedores/list')
        .then(response => response.json())
        .then(data => {
            apiGrid.setGridOption("rowData", data.list); // Cargar los proveedores en la tabla AG Grid
        })
        .catch(error => console.error("Error al obtener la lista de proveedores:", error));
}

// 🔹 Redirigir a la Edición del Proveedor
function openEditProveedorModal(idProveedor) {
    fetch(`/proveedores/${idProveedor}/edit`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.proveedor) {
                document.getElementById("edit_id").value = data.proveedor.id;
                document.getElementById("edit_nombre").value = data.proveedor.nombre;
                document.getElementById("edit_correo").value = data.proveedor.correo;
                document.getElementById("edit_telefono").value = data.proveedor.telefono;
                document.getElementById("edit_direccion").value = data.proveedor.direccion;
                document.getElementById("edit_regimen_fiscal").value = data.proveedor.regimen_fiscal;
                document.getElementById("edit_rfc").value = data.proveedor.rfc;
                document.getElementById("edit_tipo").value = data.proveedor.tipo;

                // 🔹 Asegurar que el modal se muestra correctamente con Bootstrap
                let editModal = new bootstrap.Modal(document.getElementById("editProveedorModal"));
                editModal.show();
            } else {
                Swal.fire("Error", "No se pudo cargar la información del proveedor.", "error");
            }
        })
        .catch(error => {
            Swal.fire("Error", "Hubo un problema al obtener los datos del proveedor.", "error");
            console.error("Error al cargar proveedor:", error);
        });
}

function openCuentasBancariasModal(idProveedor) {
    fetch(`/proveedores/${idProveedor}/cuentas`)
        .then(response => response.json())
        .then(data => {
            let tbody = document.getElementById("cuentasBancariasBody");
            tbody.innerHTML = "";

            // 🔹 Asignar el nombre del proveedor
            document.getElementById("cuentasProveedorNombre").textContent = data.proveedor ? data.proveedor.nombre : "Desconocido";

            if (data.success) {
                if (data.cuentas.length > 0) {
                    data.cuentas.forEach((cuenta, index) => {
                        let estadoSwitch = cuenta.deleted_at === null ? "checked" : "";
                        let estadoTexto = cuenta.deleted_at === null ? "Activo" : "Inactivo";
                        let estadoClase = cuenta.deleted_at === null ? "text-success" : "text-danger";

                        let row = `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${cuenta.nombre_banco}</td>
                                <td>${cuenta.nombre_beneficiario}</td>
                                <td>${cuenta.cuenta_bancaria}</td>
                                <td>${cuenta.cuenta_clabe}</td>
                                <td class="text-center">
                                    <div class="form-check form-switch d-flex justify-content-center">
                                        <input class="form-check-input" type="checkbox" role="switch" 
                                            id="switchCuenta${cuenta.id}" ${estadoSwitch} 
                                            onchange="toggleCuentaEstado(${cuenta.id})">
                                        <span class="ms-2 ${estadoClase}" id="estadoTexto${cuenta.id}">${estadoTexto}</span>
                                    </div>
                                </td>
                            </tr>`;
                        tbody.innerHTML += row;
                    });

                    $("#cuentasBancariasModal").modal("show");

                } else {
                    Swal.fire({
                        title: "Sin cuentas bancarias",
                        text: "Este proveedor no tiene cuentas bancarias registradas. Para verlas, primero debes añadir una.",
                        icon: "info",
                        confirmButtonText: "Aceptar"
                    });
                }
            } else {
                Swal.fire("Error", "No se pudieron cargar las cuentas bancarias.", "error");
            }
        })
        .catch(error => {
            Swal.fire("Error", "Hubo un problema al obtener los datos.", "error");
            console.error("Error al cargar cuentas bancarias:", error);
        });
}
function openCuentasBancariasModal(idProveedor) {
    fetch(`/proveedores/${idProveedor}/cuentas`)
        .then(response => response.json())
        .then(data => {
            let tbody = document.getElementById("cuentasBancariasBody");
            tbody.innerHTML = "";

            // 🔹 Asignar el nombre del proveedor
            document.getElementById("cuentasProveedorNombre").textContent = data.proveedor ? data.proveedor.nombre : "Desconocido";

            if (data.success) {
                if (data.cuentas.length > 0) {
                    data.cuentas.forEach((cuenta, index) => {
                        let estadoSwitch = cuenta.deleted_at === null ? "checked" : "";
                        let estadoTexto = cuenta.deleted_at === null ? "Activo" : "Inactivo";
                        let estadoClase = cuenta.deleted_at === null ? "text-success" : "text-danger";

                        let row = `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${cuenta.nombre_banco}</td>
                                <td>${cuenta.nombre_beneficiario}</td>
                                <td>${cuenta.cuenta_bancaria}</td>
                                <td>${cuenta.cuenta_clabe}</td>
                                <td class="text-center">
                                    <div class="form-check form-switch d-flex justify-content-center">
                                        <input class="form-check-input" type="checkbox" role="switch" 
                                            id="switchCuenta${cuenta.id}" ${estadoSwitch} 
                                            onchange="toggleCuentaEstado(${cuenta.id})">
                                        <span class="ms-2 ${estadoClase}" id="estadoTexto${cuenta.id}">${estadoTexto}</span>
                                    </div>
                                </td>
                            </tr>`;
                        tbody.innerHTML += row;
                    });

                    $("#cuentasBancariasModal").modal("show");

                } else {
                    Swal.fire({
                        title: "Sin cuentas bancarias",
                        text: "Este proveedor no tiene cuentas bancarias registradas. Para verlas, primero debes añadir una.",
                        icon: "info",
                        confirmButtonText: "Aceptar"
                    });
                }
            } else {
                Swal.fire("Error", "No se pudieron cargar las cuentas bancarias.", "error");
            }
        })
        .catch(error => {
            Swal.fire("Error", "Hubo un problema al obtener los datos.", "error");
            console.error("Error al cargar cuentas bancarias:", error);
        });
}




function toggleCuentaEstado(idCuenta) {
    let switchElement = document.getElementById(`switchCuenta${idCuenta}`);
    let estado = switchElement.checked ? 1 : 0;

    fetch(`/cuentas-bancarias/${idCuenta}/estado`, {
        method: "PATCH",
        headers: {
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
            "Content-Type": "application/json"
        },
        body: JSON.stringify({ activo: estado })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                title: "¡Éxito!",
                text: data.message,
                icon: "success",
                confirmButtonText: "Aceptar"
            });
        } else {
            Swal.fire("Error", "No se pudo actualizar el estado de la cuenta bancaria.", "error");
            switchElement.checked = !switchElement.checked; // 🔹 Revertir el cambio si hay error
        }
    })
    .catch(error => {
        Swal.fire("Error", "Hubo un problema al cambiar el estado.", "error");
        console.error("Error en toggleCuentaEstado:", error);
        switchElement.checked = !switchElement.checked;
    });
}

function agregarCuentaBancaria(idProveedor, nombreProveedor) {
    // Asignar el ID del proveedor al campo oculto del formulario
    document.getElementById("idProveedorCuenta").value = idProveedor;

    // Asignar el nombre del proveedor al modal
    document.getElementById("nombreProveedorCuenta").textContent = nombreProveedor;

    // Mostrar el modal
    $("#modalCrearCuenta").modal("show");
}