document.addEventListener('DOMContentLoaded', () => {
    const gridDiv = document.querySelector('#myGrid');
    let gridApi = null;

    const columnDefs = [
        {
            headerName: "ID",

            checkboxSelection: true,
            headerCheckboxSelection: true,
            headerCheckboxSelectionFilteredOnly: true,
            cellClass: 'text-center',
            filter: 'agNumberColumnFilter',
            floatingFilter: true,
            width: 100
        },
        { headerName: "Operador", field: "operador", filter: 'agTextColumnFilter', floatingFilter: true },

        { headerName: "Cliente", field: "cliente", filter: 'agTextColumnFilter', floatingFilter: true },
        { headerName: "Subcliente", field: "subcliente", filter: 'agTextColumnFilter', floatingFilter: true },
        { headerName: "Contenedor", field: "num_contenedor", filter: 'agTextColumnFilter', floatingFilter: true },
        {
            headerName: "Monto",
            field: "monto",
            valueFormatter: currencyFormatter,
            filter: 'agNumberColumnFilter',
            floatingFilter: true,
            cellStyle: { textAlign: 'right' }
        },
        { headerName: "Motivo", field: "motivo", filter: 'agTextColumnFilter', floatingFilter: true },
        {
            headerName: "Fecha Movimiento",
            field: "fecha_movimiento",
            valueFormatter: dateFormatter,
            filter: 'agDateColumnFilter',
            floatingFilter: true
        },
        {
            headerName: "Fecha Aplicación",
            field: "fecha_aplicacion",
            valueFormatter: dateFormatter,
            filter: 'agDateColumnFilter',
            floatingFilter: true
        }
    ];

    const gridOptions = {
        columnDefs,
        rowData: window.cotizacionesData || [],
        pagination: true,
        paginationPageSize: 30,
        paginationPageSizeSelector: [30, 50, 100],
        rowSelection: 'multiple',
        suppressRowClickSelection: false,
        defaultColDef: {
            sortable: true,
            filter: true,
            resizable: true,
            floatingFilter: true
        },
        animateRows: true,
        onGridReady: params => {
            gridApi = params.api;
        },
        onFilterChanged: () => {
            if (gridApi) gridApi.deselectAll();
        }
    };

    agGrid.createGrid(gridDiv, gridOptions);

    // Exportar a Excel o PDF
    // Exportar a Excel o PDF
    document.querySelectorAll('.exportButton').forEach(button => {
        button.addEventListener('click', async function () {
            const fileType = this.dataset.filetype;
            const selectedRows = gridApi.getSelectedRows();
            const selectedIds = selectedRows.map(row => row.id);

            if (selectedIds.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Sin selección',
                    text: 'Seleccione al menos una fila para exportar.',
                });
                return;
            }

            const formData = new FormData();
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
            formData.append('fileType', fileType);
            selectedIds.forEach(id => formData.append('selected_ids[]', id));

            try {
                const response = await fetch(exportUrl, {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) throw new Error("Error al generar el archivo.");

                const blob = await response.blob();
                const downloadUrl = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = downloadUrl;
                a.download = `gastos_por_pagar.${fileType}`;
                document.body.appendChild(a);
                a.click();
                a.remove();


                Swal.fire({
                    icon: 'success',
                    title: 'Exportación completa',
                    text: `El archivo se ha descargado correctamente como ${fileType.toUpperCase()}.`,
                    timer: 3000,
                    showConfirmButton: true
                });


            } catch (error) {
                Swal.fire('Error', error.message, 'error');
            }
        });
    });



    function currencyFormatter(params) {
        return new Intl.NumberFormat('es-MX', {
            style: 'currency',
            currency: 'MXN'
        }).format(params.value || 0);
    }

    function dateFormatter(params) {
        if (!params.value) return '';
        const date = new Date(params.value);
        return date.toLocaleDateString('es-MX');
    }
});
