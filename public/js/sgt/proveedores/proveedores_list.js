document.addEventListener("DOMContentLoaded", function () {
    const myGridElement = document.querySelector("#myGrid");

    if (!myGridElement) {
        console.error("❌ No se encontró el div #myGrid en el DOM.");
        return;
    }

    // 🔹 Definir las columnas para AG Grid
    const columnDefs = [
        { headerCheckboxSelection: true, checkboxSelection: true, width: 50 },
        { headerName: "No", field: "id", sortable: true, filter: true },
        { headerName: "Cliente", field: "cliente", sortable: true, filter: true },
        { headerName: "Origen", field: "origen", sortable: true, filter: true },
        { headerName: "Destino", field: "destino", sortable: true, filter: true },
        { headerName: "# Contenedor", field: "contenedor", sortable: true, filter: true },
        { headerName: "Estatus", field: "estatus", sortable: true, filter: true },
        { headerName: "Coordenadas", field: "coordenadas", sortable: true, filter: true },
        {
            headerName: "Acciones",
            field: "acciones",
            minWidth: 200,
            cellRenderer: function (params) {
                return `
                    <a href="${params.data.edit_url}" class="btn btn-xs btn-outline-primary">
                        <i class="fa fa-edit"></i> Editar
                    </a>
                `;
            }
        }
    ];

    let gridApi; // Variable global para capturar la API de AG Grid

    const gridOptions = {
        columnDefs: columnDefs,
        rowData: [],
        pagination: true,
        paginationPageSize: 100,
        paginationPageSizeSelector: [100, 200, 500],
        rowSelection: "multiple",
        defaultColDef: {
            resizable: true,
            flex: 1,
            minWidth: 100
        },
        localeText: {
            noRowsToShow: "No hay cotizaciones disponibles",
            page: "Página",
            more: "Más",
            to: "a",
            of: "de",
            next: "Siguiente",
            last: "Último",
            first: "Primero",
            previous: "Anterior",
            loadingOoo: "Cargando...",
            searchOoo: "Buscar...",
        },
        onGridReady: function (params) {
            gridApi = params.api; // Capturar la API de AG Grid
            getCotizacionesList();
        }
    };

    // Inicializar AG Grid
    agGrid.createGrid(myGridElement, gridOptions);

    // 🔹 Obtener la lista de cotizaciones desde el backend
    function getCotizacionesList() {
        fetch('/cotizaciones/list')
            .then(response => response.json())
            .then(data => {
                console.log("✅ Datos recibidos en AG Grid:", data.list);

                if (gridApi) {
                    gridApi.setRowData(data.list);
                } else {
                    console.error("❌ AG Grid API aún no está disponible.");
                }
            })
            .catch(error => console.error("❌ Error al obtener la lista de cotizaciones:", error));
    }
});
