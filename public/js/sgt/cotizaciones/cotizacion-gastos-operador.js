const gridOptionsOperador = {
    pagination: true,
    paginationPageSize: 100,
    paginationPageSizeSelector: [100, 200, 500],
    rowSelection: {
        mode: "multiRow",
        headerCheckbox: true,
    },
    rowClassRules: {
        "bg-gradient-warning": (params) =>
            params.data.Estatus === "Pago Pendiente",
    },
    onRowSelected: (event) => {
        btnPaymentStatus();
    },
    rowData: [],

    columnDefs: [
        { field: "IdCotizacion", hide: true },
        { field: "IdGasto", hide: true },
        { field: "Gasto", width: 210 },
        {
            field: "Monto",
            width: 110,
            valueFormatter: (params) => currencyFormatter(params.value),
            cellStyle: { textAlign: "right" },
        },
        { field: "Estatus", width: 150 },
        { field: "Fecha", filter: true, floatingFilter: true },
        { field: "FechaPago", filter: true, floatingFilter: true },
        { field: "BancoPago", filter: true, floatingFilter: true },
    ],

    localeText: localeText,
};

const gridElementGastosOperador = document.querySelector("#gridGastosOperador");
const btnElminar = document.querySelector("#btnDelete2");
let apiGridGastosOperador = gridElementGastosOperador
    ? agGrid.createGrid(gridElementGastosOperador, gridOptionsOperador)
    : null;
// const gridInstance = new agGrid.Grid(gridElementGastosOperador, gridOptions);

const paginationTitle = document.querySelector("#ag-32-label");

if (paginationTitle) {
    paginationTitle.textContent = "Registros por página";
}

let IdContenedorViaje = null;

$(document).on("click", "#btnGuardarKmDiesel", function () {
    const cotizacionId = $("#cotizacion_km_diesel_id").val();
    const kmRecorridos = $("#km_recorridos").val();
    const litrosDiesel = $("#litros_diesel").val();

    if (!cotizacionId) {
        Swal.fire({
            icon: "warning",
            title: "Cotización no encontrada",
            text: "No se encontró el ID de la cotización.",
        });
        return;
    }

    $.ajax({
        url: `/cotizaciones/${cotizacionId}/km-diesel`,
        method: "PATCH",
        data: {
            _token: $('meta[name="csrf-token"]').attr("content"),
            km_recorridos: kmRecorridos,
            litros_diesel: litrosDiesel,
        },
        beforeSend: function () {
            $("#btnGuardarKmDiesel")
                .prop("disabled", true)
                .html('<i class="fas fa-spinner fa-spin"></i>');
        },
        success: function (response) {
            Swal.fire({
                icon: "success",
                title: "Actualizado",
                text: response.message || "Datos actualizados correctamente.",
                timer: 1400,
                showConfirmButton: false,
            });

            $("#km_recorridos").val(response.data.km_recorridos ?? "");
            $("#litros_diesel").val(response.data.litros_diesel ?? "");
        },
        error: function (xhr) {
            const msg =
                xhr.responseJSON?.message ||
                Object.values(xhr.responseJSON?.errors || {})?.[0]?.[0] ||
                "No se pudieron actualizar los datos.";

            Swal.fire({
                icon: "error",
                title: "Error",
                text: msg,
            });
        },
        complete: function () {
            $("#btnGuardarKmDiesel")
                .prop("disabled", false)
                .html('<i class="fas fa-save"></i>');
        },
    });
});

function getGastosOperador() {
    const metaToken = document.querySelector('meta[name="csrf-token"]');
    const spanContenedor = document.querySelector("#spanContenedor");

    if (!metaToken || !spanContenedor) {
        console.warn("No se encontró token CSRF o spanContenedor");
        return;
    }

    const _token = metaToken.getAttribute("content");
    const numContenedor = spanContenedor.textContent?.trim();

    if (!numContenedor) {
        console.warn("No hay número de contenedor");
        return;
    }

    $.ajax({
        url: "/cotizaciones/gastos-operador/get",
        type: "post",
        data: { _token, numContenedor },

        beforeSend: () => {},

        success: (response) => {
            try {
                const data = Array.isArray(response) ? response : [];

                if (gridElementGastosOperador && apiGridGastosOperador) {
                    apiGridGastosOperador.setGridOption("rowData", data);
                }

                const totalGastos = data.reduce((total, d) => {
                    return total + Number(d.Monto ?? 0);
                }, 0);

                const totalGastosOperador = document.querySelector(
                    "#totalGastosOperador",
                );

                if (totalGastosOperador) {
                    totalGastosOperador.textContent = moneyFormat(totalGastos);
                }
            } catch (error) {
                console.warn("Error controlado en getGastosOperador:", error);
            }
        },

        error: (xhr) => {
            console.warn("Error al obtener gastos operador:", xhr);
        },
    });
}

function putGastosOperador() {
    let spanContenedor = document.querySelector("#spanContenedor");
    let numContenedor = spanContenedor.textContent;

    let textDescripcion = document.querySelector(
        "#txtDescripcionGastoOperador",
    );
    let textMonto = document.querySelector("#txtMontoGastoOperador");
    let checkPagoInmediato = document.querySelector("#checkPagoInmediato");
    let bancosGastos = document.querySelector("#bancosGastos");
    let fechaAplicacion = document.querySelector("#txtFechaAplicacion");

    let pagoInmediato = checkPagoInmediato.checked;

    if (!textDescripcion.value || !textMonto.value) {
        Swal.fire(
            "Complete información",
            "Debes capturar descripción y monto",
            "warning",
        );
        return;
    }

    if (pagoInmediato) {
        if (!bancosGastos.value || !fechaAplicacion.value) {
            Swal.fire(
                "Datos incompletos",
                "Para pago inmediato debes seleccionar banco y fecha",
                "warning",
            );
            return;
        }
    }

    let montoGasto = reverseMoneyFormat(textMonto.value);
    let descripcion = textDescripcion.value;

    let _token = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content");

    Swal.fire({
        title: "Procesando...",
        text: "Registrando gasto",
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        },
    });

    $.ajax({
        url: "/cotizaciones/gastos-operador/registrar",
        type: "post",
        data: {
            numContenedor,
            descripcion,
            montoGasto,
            pagoInmediato,
            banco: bancosGastos.value,
            fechaAplicacion: fechaAplicacion.value,
            _token,
        },
        success: (response) => {
            Swal.fire(response.Titulo, response.Mensaje, response.TMensaje);

            if (response.TMensaje === "success") {
                getGastosOperador();
                $("#modal-gastos-operador").modal("hide");

                // limpiar
                textDescripcion.value = "";
                textMonto.value = "";
                bancosGastos.selectedIndex = 0;
            }
        },
        error: () => {
            Swal.fire("Error", "No se pudo registrar el gasto", "error");
        },
    });
}

let btnPayment = document.querySelector("#btnPayment");
if (btnPayment) {
    btnPayment.addEventListener("click", () => {
        paymentGastosOperador();
    });
}

function btnPaymentStatus() {
    if (gridElementGastosOperador) {
        let seleccion = apiGridGastosOperador.getSelectedRows();
        btnPayment.disabled = seleccion.length == 0 ? true : false;
        btnElminar.disabled = seleccion.length == 0 ? true : false;
    }
}

function paymentGastosOperador() {
    let seleccionPago = [];
    let totalPago = 0;

    if (gridElementGastosOperador) {
        seleccionPago = apiGridGastosOperador.getSelectedRows();
    }

    let validarGastos = seleccionPago.every((gasto) => {
        if (gasto.Estatus != "Pago Pendiente") return false;

        totalPago += parseFloat(gasto.Monto);
        return true;
    });

    if (!validarGastos) {
        Swal.fire(
            "No es posible pagar",
            'Solo se admiten Gastos con estatus "Pago Pendiente"',
            "warning",
        );
        return false;
    }

    let totalPagoGastosOperador = document.querySelector(
        "#totalPagoGastosOperador",
    );

    if (totalPagoGastosOperador)
        totalPagoGastosOperador.textContent = moneyFormat(totalPago);

    const modalElement = document.getElementById("modal-pagar-gastos-operador");
    const bootstrapModal = new bootstrap.Modal(modalElement);
    bootstrapModal.show();
}

function applyPaymentGastosOperador() {
    let totalPagoGastosOperador = document.querySelector(
        "#totalPagoGastosOperador",
    );

    let totalPago = reverseMoneyFormat(totalPagoGastosOperador.textContent);

    let bancosPagoGastos = document.querySelector("#bancosPagoGastos");
    let bank = bancosPagoGastos.value;

    let spanContenedor = document.querySelector("#spanContenedor");
    let numContenedor = spanContenedor.textContent;

    let gastosPagar = apiGridGastosOperador.getSelectedRows();

    let fechaApp = document.querySelector("#txtFechaAplicacionOper");
    let _token = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content");

    Swal.fire({
        title: "Procesando...",
        text: "Aplicando pago gasto",
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        },
    });

    $.ajax({
        url: "/cotizaciones/gastos-operador/pagar",
        type: "post",
        data: {
            totalPago,
            numContenedor,
            bank,
            gastosPagar,
            _token,
            fechaAplicacion: fechaApp.value,
        },
        beforeSend: () => {},
        success: (response) => {
            Swal.fire(response.Titulo, response.Mensaje, response.TMensaje);

            if (response.TMensaje == "success") {
                getGastosOperador();
                $("#modal-pagar-gastos-operador").modal("hide");
            }
        },
        error: () => {
            Swal.fire(
                "Error inesperado",
                "Ocurrio un error mientras procesamos su solicitud",
                "error",
            );
        },
    });
}

function eliminarGastoOperador() {
    let gastosselec = null;
    if (gridElementGastosOperador) {
        gastosselec = apiGridGastosOperador.getSelectedRows();
    }
    let pagado = gastosselec.every((gasto) => {
        if (gasto.Estatus != "Pago Pendiente") return false;
    });

    let fechaMovi = gastosselec[0].FechaPago;

    let swalOptions = {
        title: "¿Desea eliminar el gasto seleccionado?",
        text: 'Estas a punto de eliminar un gasto, si se encuentra seguro haga click en "Si, Eliminar"',
        icon: "warning",

        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Si, Eliminar!",
        cancelButtonText: "Cancelar",
    };

    if (fechaMovi) {
        swalOptions.input = "text";
        swalOptions.inputLabel = "Fecha Cancelación";
        swalOptions.inputValue = fechaMovi;

        swalOptions.didOpen = () => {
            const input = Swal.getInput();

            input.type = "date";
            input.required = true;
        };

        swalOptions.inputValidator = (value) => {
            if (!value) {
                return "La fecha es obligatoria";
            }
        };
    }

    Swal.fire(swalOptions).then((result) => {
        if (result.isConfirmed) {
            const fechaCancelacion = fechaMovi ? result.value : null;
            let seleccionEliminarPago = apiGridGastosOperador.getSelectedRows();
            let _token = document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute("content");

            let spanContenedor = document.querySelector("#spanContenedor");
            let numContenedor = spanContenedor.textContent;

            $.ajax({
                url: "/cotizaciones/gastos-operador/eliminar",
                type: "post",
                data: {
                    seleccionEliminarPago,
                    fechacancelacion: fechaCancelacion,
                    numContenedor,
                    _token,
                },
                beforeSend: () => {},
                success: (response) => {
                    Swal.fire(
                        response.Titulo,
                        response.Mensaje,
                        response.TMensaje,
                    );

                    if (response.TMensaje == "success") {
                        getGastosOperador();
                    }
                },
                error: () => {
                    Swal.fire(
                        "Error inesperado",
                        "Ocurrio un error mientras procesamos su solicitud",
                        "error",
                    );
                },
            });
        }
    });
}

if (btnElminar)
    btnElminar.addEventListener("click", () => {
        eliminarGastoOperador();
    });
