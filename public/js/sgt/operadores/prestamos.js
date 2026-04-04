function formatNumber(params) {
    const value = Number(params.value ?? 0);
    return value.toLocaleString("en-US", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

document.addEventListener("DOMContentLoaded", function () {
    function mapeoRows(resumen) {
        return resumen.map((r) => ({
            id_operador: r.id_operador,

            operador: r.operador ?? "Sin operador",

            conteo_prestamos: parseInt(r.conteo_prestamos ?? 0),
            total_prestamos: parseFloat(r.total_prestamos ?? 0),

            conteo_adelantos: parseInt(r.conteo_adelantos ?? 0),
            total_adelantos: parseFloat(r.total_adelantos ?? 0),

            total_deuda: parseFloat(r.total_deuda ?? 0),

            conteo_movimientos: parseInt(r.conteo_movimientos ?? 0),
            total_abonos: parseFloat(r.total_abonos ?? 0),

            saldo_final: parseFloat(r.saldo_final ?? 0),
        }));
    }

    const columnDefs = [
        {
            headerName: "Operador",
            field: "operador",
            flex: 1.5,
            width: 280,
        },

        {
            headerName: "Préstamos (#)",
            field: "conteo_prestamos",
            width: 122,
            cellStyle: { textAlign: "center" },
        },

        {
            headerName: "Préstamos ($)",
            field: "total_prestamos",
            width: 122,
            cellStyle: { textAlign: "right" },
            valueFormatter: formatNumber,
        },

        {
            headerName: "Adelantos (#)",
            field: "conteo_adelantos",
            width: 122,
            cellStyle: { textAlign: "center" },
        },

        {
            headerName: "Adelantos ($)",
            field: "total_adelantos",
            width: 122,
            cellStyle: { textAlign: "right" },
            valueFormatter: formatNumber,
        },

        {
            headerName: "Total Deuda",
            field: "total_deuda",
            width: 130,
            cellStyle: {
                textAlign: "right",
                fontWeight: "600",
            },
            valueFormatter: formatNumber,
        },

        {
            headerName: "Abonos (#)",
            field: "conteo_movimientos",
            width: 130,
            cellStyle: { textAlign: "center" },
        },

        {
            headerName: "Total Abonado",
            field: "total_abonos",
            width: 140,
            cellStyle: { textAlign: "right" },
            valueFormatter: formatNumber,
        },

        {
            headerName: "Saldo Final",
            field: "saldo_final",
            width: 150,
            cellStyle: {
                textAlign: "right",
                fontWeight: "bold",
                color: "#b02a37",
            },
            valueFormatter: formatNumber,
        },

        {
            headerName: "Acciones",
            field: "acciones",
            width: 125,
            cellRenderer: (params) => `
            <button class="btn btn-sm btn-primary btn-ver-operador" data-id="${params.data.id_operador}">
                👁 Ver
            </button>
        `,
        },
    ];

    document.addEventListener("click", function (e) {
        if (e.target.closest(".btn-ver-operador")) {
            const id = e.target.closest(".btn-ver-operador").dataset.id;
            window.location.href = `/prestamos/operador/${id}`;
        }
    });

    const gridOptions = {
        columnDefs,
        rowData: [],
        pagination: true,
        paginationPageSize: 10,
        animateRows: true,
        defaultColDef: {
            resizable: true,
            sortable: true,
            filter: true,
        },
        getRowId: (params) => params.data.id_operador,
        onGridReady: (params) => {
            params.api.sizeColumnsToFit();
        },
    };

    const gridDiv = document.querySelector("#gridPrestamosActivos");

    const gridApi = agGrid.createGrid(gridDiv, gridOptions);

    // Botón para recargar datos
    const btnRecargar = document.getElementById("btnRecargarGrid");
    if (btnRecargar) {
        btnRecargar.addEventListener("click", () => {
            getlistprestamos();
        });
    }

    function getlistprestamos() {
        fetch("/prestamos/lista")
            .then((response) => response.json())
            .then((data) => {
                const prestamosActualizados = data.prestamos;
                const mappedRows = mapeoRows(prestamosActualizados);
                gridApi.setGridOption("rowData", mappedRows);
            })
            .catch((error) => {
                console.error("Error al recargar los datos:", error);
                errorServidor(
                    "Error al recargar los datos. Por favor, intente de nuevo.",
                );
            });
    }

    getlistprestamos();
});
