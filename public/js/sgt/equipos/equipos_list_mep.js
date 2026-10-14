document.addEventListener("DOMContentLoaded", function () {
    const columnDefs = [
        {
            headerName: "Equipo",
            field: "id_equipo",
        },
        {
            headerName: "Placas",
            field: "placas",
        },
        {
            headerName: "IMEI",
            field: "imei",
        },
        {
            headerName: "Núm Serie / VIN",
            field: "num_serie",
        },
        {
            headerName: "Proveedor GPS",
            field: "gps_nombre",
        },
        {
            headerName: "GPS",
            field: "gps_info",

            cellRenderer: (params) => {
                const data = params.value;
                if (!data) return "";

                const conectado = data.conectado;

                const icon = conectado ? "🟢" : "⚪";
                const label =
                    data.tipo === "sistema" ? "🌐 Sistema" : "🔧 Personalizado";

                const tooltip = conectado
                    ? `Conectado (${label})`
                    : `Sin conexión (${label})`;

                return `
            <span title="${tooltip}" style="cursor:pointer;">
                ${icon}
                <small class="ms-1">${label}</small>
            </span>
        `;
            },
        },
        {
            headerName: "Acciones",
            field: "acciones",
            width: 220,
            minWidth: 180,

            cellRenderer: (params) => {
                return `
            <button
                class="btn btn-xs btn-primary btnEditarEquipo me-1"
                title="Editar equipo"
                data-id="${params.data.id}"
                data-numero="${params.data.id_equipo}"
                data-imei="${params.data.imei}"
                data-marca="${params.data.marca}"
                data-placas="${params.data.placas}"
                data-serie="${params.data.num_serie}"
                data-tipoequipo="${params.data.tipo}"
            >
                <i class="bi bi-pencil"></i>
            </button>

            <button
                class="btn btn-xs btn-warning me-1"
                title="Configurar GPS"
                onclick="abrirModalConfig('${encodeURIComponent(JSON.stringify(params.data))}')"
            >
                <i class="bi bi-gear"></i>
            </button>

            <button
                class="btn btn-xs btn-danger btnEliminarEquipo"
                title="Eliminar equipo"
                data-id="${params.data.id}"
            >
                <i class="bi bi-trash"></i>
            </button>
        `;
            },
        },
    ];

    const gridOptions = {
        columnDefs: columnDefs,
        rowData: window.equipos,
        defaultColDef: {
            sortable: true,
            filter: true,
            resizable: true,
        },
        getRowStyle: params => {
            if (params.data && params.data.is_duplicated_imei) {
                return { backgroundColor: '#f8d7da', color: '#721c24' };
            }
            return null;
        }
    };

    const eGridDiv = document.querySelector("#gridEquiposGPS");
    agGrid.createGrid(eGridDiv, gridOptions);
});

function abrirModalConfig(data) {
    let equipo = JSON.parse(decodeURIComponent(data));
    $("#equipo_id").val(equipo.id);

    $("#infoEquipo").text(`${equipo.id_equipo} - ${equipo.placas}`);

    window.currentConfigEdit = equipo.credenciales_gps || null;

    if (typeof window.currentConfigEdit === "string") {
        window.currentConfigEdit = JSON.parse(window.currentConfigEdit);
    }

    if (equipo.usar_config_global == 1) {
        $('input[name="tipo_config"][value="sistema"]').prop("checked", true);
    } else {
        $('input[name="tipo_config"][value="personalizado"]').prop(
            "checked",
            true,
        );
    }

    $('input[name="tipo_config"]:checked').trigger("change");

    $("#modalConfigGPS").modal("show");

    $("#modalConfigGPS")
        .off("shown.bs.modal")
        .on("shown.bs.modal", function () {
            $("#gps_company_id").val("").change();

            $("#gps_company_id").val(equipo.gps_company_id);

            setTimeout(() => {
                $("#gps_company_id").trigger("change");
            }, 100);
        });
}
const radios = document.querySelectorAll('input[name="tipo_config"]');
const bloqueProveedor = document.getElementById("bloqueProveedor");
const camposDinamicos = document.getElementById("camposDinamicos");

$(document).on("change", 'input[name="tipo_config"]', function () {
    if (this.value === "personalizado" && this.checked) {
        $("#camposDinamicos").removeClass("d-none");
        $("#gps_company_id").trigger("change");
    } else {
        $("#camposDinamicos").addClass("d-none");
        $("#camposDinamicos").html("");
    }
});

$(document).on("change", "#gps_company_id", function () {
    const selected = this.options[this.selectedIndex];
    const config = selected.getAttribute("data-config");

    const container = document.getElementById("camposDinamicos");
    container.innerHTML = "";

    if (!config) return;

    let fields;

    try {
        fields = config;

        if (typeof fields === "string") {
            fields = JSON.parse(fields);
        }

        if (typeof fields === "string") {
            fields = JSON.parse(fields);
        }
    } catch (e) {
        console.error("Error parseando config", e);
        return;
    }

    if (!Array.isArray(fields)) {
        console.error("No es array:", fields);
        return;
    }

    fields.forEach((field, index) => {
        let value = "";

        if (window.currentConfigEdit && window.currentConfigEdit[field.field]) {
            value = window.currentConfigEdit[field.field];
        }

        container.innerHTML += `
        <div class="mb-2">
            <label>${field.label}</label>

            <input type="hidden"
                name="cuentaConfig[${index}][field]"
                value="${field.field}">

            <input
                type="${field.type}"
                name="cuentaConfig[${index}][valor]"
                class="form-control"
                value="${value}"
                required
            >
        </div>
    `;
    });
});

function mostrarStatus(msg, tipo) {
    const div = document.getElementById("statusConexion");

    div.className = `alert alert-${tipo}`;
    div.innerText = msg;
    div.classList.remove("d-none");
}

function validarCampos() {
    let valid = true;

    if ($('input[name="tipo_config"]:checked').val() === 'personalizado') {
        document.querySelectorAll("#camposDinamicos input").forEach((input) => {
            if (!input.value.trim()) {
                input.classList.add("is-invalid");
                valid = false;
            } else {
                input.classList.remove("is-invalid");
            }
        });
    }

    return valid;
}
$("#formConfigGPS").on("submit", function (e) {
    e.preventDefault();

    if (!$("#gps_company_id").val()) {
        Swal.fire({
            icon: "warning",
            title: "Proveedor GPS requerido",
            text: "Debe seleccionar un proveedor de GPS antes de continuar",
        });
        return;
    }

    if (!validarCampos()) {
        Swal.fire({
            icon: "warning",
            title: "Campos requeridos",
            text: "Completa todos los campos antes de continuar",
        });
        return;
    }
    let formData = {
        equipo_id: $("#equipo_id").val(),
        gps_company_id: $("#gps_company_id").val(),
        tipo_config: $('input[name="tipo_config"]:checked').val(),
        cuentaConfig: [],
    };

    let temp = {};

    $("#camposDinamicos input").each(function () {
        let name = $(this).attr("name");
        let value = $(this).val();

        let match = name.match(/cuentaConfig\[(\d+)\]\[(field|valor)\]/);

        if (match) {
            let index = match[1];
            let key = match[2];

            if (!temp[index]) temp[index] = {};

            temp[index][key] = value;
        }
    });
    formData.cuentaConfig = Object.values(temp);
    Swal.fire({
        title: "Validando conexión...",
        text: "Por favor espera",
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        },
    });
    const token = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content");
    $.ajax({
        url: "/gps/config/store-equipos",
        method: "POST",
        data: {
            ...formData,
            _token: token,
        },
        success: function (response) {
            Swal.close();

            if (response.success) {
                Swal.fire({
                    icon: "success",
                    title: "Configuración guardada",
                    text: "Credenciales válidas ✅",
                });

                $("#modalConfigGPS").modal("hide");
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Error de conexión",
                    text:
                        response.message ||
                        "No se pudieron validar las credenciales",
                });
            }
        },
        error: function (xhr) {
            Swal.close();

            Swal.fire({
                icon: "error",
                title: "Error inesperado",
                text: "Ocurrió un problema al guardar la configuración",
            });

            console.error(xhr.responseText);
        },
    });
});

$("#btnNuevoEquipo").on("click", function () {
    // limpiar
    $("#equipo_id").val("");
    $("#numero_equipo").val("");
    $("#imei").val("");
    $("#marca").val("");
    $("#placas").val("");
    $("#num_serie").val("");
    $("#tipo_equipo").val("");

    $("#tituloEquipo").text("Nuevo equipo");

    $("#modalEquipo").modal("show");
});

$(document).on("click", ".btnEditarEquipo", function () {
    const btn = $(this);

    // console.log(btn.data('tipoEquipo'));

    $("#equipo_id").val(btn.data("id"));
    $("#numero_equipo").val(btn.data("numero"));
    $("#imei").val(btn.data("imei"));
    $("#marca").val(btn.data("marca"));
    $("#placas").val(btn.data("placas"));
    $("#num_serie").val(btn.data("serie"));
    $("#tipo_equipo").val(btn.data("tipoequipo"));

    $("#tituloEquipo").text("Editar equipo");

    $("#modalEquipo").modal("show");
});

$("#btnGuardarEquipo").on("click", function () {
    const data = {
        equipo_id: $("#equipo_id").val(),
        numero_equipo: $("#numero_equipo").val(),
        imei: $("#imei").val(),
        marca: $("#marca").val(),
        placas: $("#placas").val(),
        num_serie: $("#num_serie").val(),
        tipo_equipo: $("#tipo_equipo").val(),

        _token: $('meta[name="csrf-token"]').attr("content"),
    };

    $.ajax({
        url: "/equipos/update-mep",
        method: "POST",
        data: data,

        beforeSend: function () {
            Swal.fire({
                title: "Guardando...",
                text: "Por favor espera",
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                },
            });
        },

        success: function (res) {
            Swal.close();

            Swal.fire({
                icon: "success",
                title: "Guardado",
                text: res.message ?? "El equipo se guardó correctamente",
                timer: 1500,
                showConfirmButton: false,
            });

            $("#modalEquipo").modal("hide");

            // recargar tabla
            location.reload();
        },

        error: function (err) {
            Swal.close();

            const message = err.responseJSON && err.responseJSON.message 
                ? err.responseJSON.message 
                : "No se pudo guardar el equipo";

            Swal.fire({
                icon: "error",
                title: "Error",
                text: message,
            });

            console.error(err);
        },
    });
});

$(document).on("click", ".btnEliminarEquipo", function () {
    const id = $(this).data("id");

    Swal.fire({
        title: "¿Estás seguro?",
        text: "El equipo será desactivado y no aparecerá en el catálogo activo, pero se conservará en el historial.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Sí, desactivar",
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/equipos/desactivar/${id}`,
                method: "POST",
                data: {
                    _token: $('meta[name="csrf-token"]').attr("content"),
                    _method: "PATCH",
                    tipo: "desactivado"
                },
                beforeSend: function () {
                    Swal.fire({
                        title: "Desactivando...",
                        text: "Por favor espera",
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                },
                success: function (res) {
                    Swal.close();
                    if (res.success) {
                        Swal.fire({
                            icon: "success",
                            title: "Desactivado",
                            text: res.message || "El equipo se desactivó con éxito",
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: res.message || "No se pudo desactivar el equipo"
                        });
                    }
                },
                error: function (err) {
                    Swal.close();
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Ocurrió un error al intentar desactivar el equipo"
                    });
                    console.error(err);
                }
            });
        }
    });
});
