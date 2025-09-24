document.addEventListener('DOMContentLoaded', () => {
    const gridDiv = document.querySelector('#myGrid');
    let gridApi = null;
    const dateComparator = (filterDate, cellValue) => {
        if (!cellValue) return -1;
        const cellDate = new Date(cellValue);
        // Resetear horas para comparación exacta
        cellDate.setHours(0, 0, 0, 0);
        filterDate.setHours(0, 0, 0, 0);

        if (cellDate < filterDate) return -1;
        if (cellDate > filterDate) return 1;
        return 0;
    };

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
            floatingFilter: true,
            filterParams: {
                comparator: dateComparator
            }
        },
        {
            headerName: "Fecha Inicio",
            field: "fecha_inicio",
            valueFormatter: dateFormatter,
            filter: 'agDateColumnFilter',
            floatingFilter: true,
            filterParams: {
                comparator: dateComparator
            }
        },
        {
            headerName: "Fecha Fin",
            field: "fecha_fin",
            valueFormatter: dateFormatter,
            filter: 'agDateColumnFilter',
            floatingFilter: true,
            filterParams: {
                comparator: dateComparator
            }
        },
    ];

    const gridOptions = {
        columnDefs,
        rowData: (window.cotizacionesData || []).map(item => ({
            ...item,
            fecha_inicio: item.fecha_inicio ? new Date(item.fecha_inicio) : null,
            fecha_fin: item.fecha_fin ? new Date(item.fecha_fin) : null,
            fecha_movimiento: item.fecha_movimiento ? new Date(item.fecha_movimiento) : null,
            fecha_aplicacion: item.fecha_aplicacion ? new Date(item.fecha_aplicacion) : null,
        })).filter(item => {
            const fecha = item.fecha_inicio;
            return fecha instanceof Date && !isNaN(fecha) && moment(fecha).isBetween(moment().subtract(7, 'days'), moment(), 'day', '[]');
        }),

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

    const grid = agGrid.createGrid(gridDiv, gridOptions);
    gridApi = grid.api;

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

    const startDate = moment().subtract(7, 'days');
    const endDate = moment();

    $('#daterange').daterangepicker({
        startDate,
        endDate,
        maxDate: moment(),
        opens: 'right',
        locale: {
            format: 'YYYY-MM-DD',
            separator: ' al ',
            applyLabel: 'Aplicar',
            cancelLabel: 'Cancelar',
            fromLabel: 'Desde',
            toLabel: 'Hasta',
            daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
            monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto',
                'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
            firstDay: 1
        }
    }, function (start, end) {
        // ⚡ Filtro local sin fetch
        const filtrado = (window.cotizacionesData || []).map(item => ({
            ...item,
            fecha_inicio: item.fecha_inicio ? new Date(item.fecha_inicio) : null,
            fecha_fin: item.fecha_fin ? new Date(item.fecha_fin) : null,
            fecha_movimiento: item.fecha_movimiento ? new Date(item.fecha_movimiento) : null,
        })).filter(item => {
            const fi = item.fecha_inicio;
            const ff = item.fecha_fin;

            return (
                fi instanceof Date && ff instanceof Date &&
                !isNaN(fi) && !isNaN(ff) &&
                moment(fi).isSameOrAfter(start, 'day') 
               //&& moment(ff).isSameOrBefore(end, 'day')
            );
        });

        if (gridApi) {
            gridApi.setGridOption('rowData', filtrado);
            gridApi.deselectAll(); // Limpia los seleccionados que ya no están
        }




        if (gridApi) {
            gridApi.setGridOption('rowData', filtrado);
        }
    });
});
