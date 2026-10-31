function abrirModalAbono(idPrestamo) {
    document.getElementById("id_prestamo_abono").value = idPrestamo;

    document.getElementById("monto_abono").value = "";

    const modal = new bootstrap.Modal(document.getElementById("modalAbono"));
    modal.show();
}

function abrirDetallePrestamo(idPrestamo) {
    // Limpiar contenido previo
    document.getElementById("totalPrestamo").innerText = "$0.00";
    const tbody = document.querySelector("#movimientosPrestamoTable tbody");
    tbody.innerHTML = "";
    document.getElementById("deudaActual").innerText = "$0.00";

    // Abrimos modal mientras cargamos
    const modal = new bootstrap.Modal(
        document.getElementById("detallePrestamoModal"),
    );
    modal.show();

    // Fetch al backend
    fetch(`/prestamos/lista-detalle/${idPrestamo}`)
        .then((res) => {
            if (!res.ok) throw new Error("Error al cargar historial");
            return res.json();
        })
        .then((data) => {
            // data = { total: 10000, movimientos: [...] }
            document.getElementById("totalPrestamo").innerText =
                `$${formatNumber({ value: data.total })}`;

            let deuda = data.total;

            const tbody = document.querySelector(
                "#movimientosPrestamoTable tbody",
            );
            tbody.innerHTML = "";
            document.getElementById("nombreOperador").innerText =
                data.prestamos.operador.nombre ?? "";

            data.historial.forEach((mov) => {
                const fecha = mov.fecha_pago
                    ? mov.fecha_pago.split("-").reverse().join("/")
                    : new Date(mov.created_at).toLocaleDateString();

                // parseamos monto, si es inválido ponemos 0
                const monto = parseFloat(mov.monto_pago) || 0;

                const tr = document.createElement("tr");
                tr.innerHTML = `
        <td>${mov.tipo_origen ?? ""}</td>
        <td>${fecha}</td>
        <td>$${formatNumber({ value: monto })}</td>
        <td>${mov.referencia ?? ""}</td>
    `;
                tbody.appendChild(tr);

                // Calcular deuda actual
                if (
                    mov.tipo_origen &&
                    (mov.tipo_origen.toLowerCase() === "directo" ||
                        mov.tipo_origen.toLowerCase() === "liquidacion")
                ) {
                    deuda -= monto;
                } else {
                    deuda += monto;
                }
            });

            document.getElementById("deudaActual").innerText =
                `$${formatNumber({ value: deuda })}`;
        })
        .catch((err) => {
            console.error(err);
            tbody.innerHTML = `<tr><td colspan="4" class="text-center text-danger">No se pudo cargar el historial</td></tr>`;
        });
}

function formatNumber(params) {
    const value = Number(params.value ?? 0);
    return value.toLocaleString("en-US", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

const formAbono = document.getElementById("formAbono");

if (formAbono) {
    formAbono.addEventListener("submit", function (e) {
        e.preventDefault();

        const idPrestamo = document.getElementById("id_prestamo_abono").value;
        const monto = parseFloat(document.getElementById("monto_abono").value);
        const idBancoAbono = document.getElementById("id_banco_abono").value;
        const referencia = document.getElementById("referencia").value;

        const fechaAbono = document.getElementById(
            "FechaAplicacionAbono",
        ).value;
        const _token = document.querySelector(
            'meta[name="csrf-token"]',
        ).content;

        if (!idBancoAbono) {
            errorServidor("Seleccione un banco para el abono.", "validacion");
            return;
        }

        if (!fechaAbono) {
            errorServidor("Seleccione fecha aplicacion.", "validacion");
            return;
        }

        fetch(`/prestamos/${idPrestamo}/abonar`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": _token,
            },
            body: JSON.stringify({
                monto,
                id_banco_abono: idBancoAbono,
                referencia,
                fechaAbono,
            }),
        })
            .then(async (response) => {
                // console.log('Status:', response.status);

                const text = await response.text();
                //  console.log('Respuesta cruda:', text);

                try {
                    return JSON.parse(text);
                } catch (e) {
                    throw new Error("No es JSON válido");
                }
            })
            .then((data) => {
                if (data.success) {
                    Swal.fire({
                        icon: "success",
                        title: "Abono registrado",
                        text: "El abono ha sido registrado correctamente.",
                        confirmButtonColor: "#3085d6",
                    });
                    bootstrap.Modal.getInstance(
                        document.getElementById("modalAbono"),
                    ).hide();
                    location.reload();
                } else {
                    errorServidor(
                        data.message ??
                            "No se pudo registrar el abono. Intenta nuevamente",
                        data.titulo,
                    );
                }
            })
            .catch(() => errorServidor());
    });
}

function errorServidor(
    mssagex = "No se pudo conectar con el servidor. Intenta nuevamente",
    titulo,
) {
    Swal.fire({
        icon: "error",
        title: titulo ?? "Error de conexión",
        text: mssagex,
    });
}

function visualizarPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    // Título
    doc.setFontSize(16);
    doc.text("Detalle del Préstamo", 14, 20);

    // Datos ejemplo
    const operador = document.getElementById("nombreOperador").innerText;
    const totalPrestamo = document.getElementById("totalPrestamo").innerText;
    const deudaActual = document.getElementById("deudaActual").innerText;

    doc.setFontSize(12);
    doc.text(`${operador}`, 14, 30);
    doc.text(`Total Préstamo: ${totalPrestamo}`, 14, 38);
    doc.text(`Deuda Actual: ${deudaActual}`, 14, 46);

    // Tabla
    const rows = [];
    document
        .querySelectorAll("#movimientosPrestamoTable tbody tr")
        .forEach((tr) => {
            const cols = Array.from(tr.children).map((td) => td.innerText);
            rows.push(cols);
        });

    doc.autoTable({
        head: [["Tipo", "Fecha", "Monto", "Referencia"]],
        body: rows,
        startY: 55,
        theme: "grid",
        headStyles: { fillColor: [41, 128, 185], textColor: 255 },
        styles: { fontSize: 10 },
        didDrawPage: function (data) {
            const pageHeight =
                doc.internal.pageSize.height ||
                doc.internal.pageSize.getHeight();
            doc.setFontSize(10);
            doc.text(
                "SGT - Sistema de Gestión de Transporte",
                data.settings.margin.left,
                pageHeight - 10,
            );
        },
    });

    doc.output("dataurlnewwindow");
}

document.getElementById("exportarPDF").addEventListener("click", visualizarPDF);
