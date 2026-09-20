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
            headerName: 'ID',

            checkboxSelection: true,
            headerCheckboxSelection: true,
            headerCheckboxSelectionFilteredOnly: true,
            cellClass: 'text-center',
            filter: 'agNumberColumnFilter',
            floatingFilter: true,
            width: 100,
        },
        { headerName: 'Operador', field: 'operador', filter: 'agTextColumnFilter', floatingFilter: true },

        { headerName: 'Cliente', field: 'cliente', filter: 'agTextColumnFilter', floatingFilter: true },
        { headerName: 'Subcliente', field: 'subcliente', filter: 'agTextColumnFilter', floatingFilter: true },
        { headerName: 'Contenedor', field: 'num_contenedor', filter: 'agTextColumnFilter', floatingFilter: true },
        {
            headerName: 'Monto',
            field: 'monto',
            valueFormatter: currencyFormatter,
            filter: 'agNumberColumnFilter',
            floatingFilter: true,
            cellStyle: { textAlign: 'right' },
        },
        { headerName: 'Motivo', field: 'motivo', filter: 'agTextColumnFilter', floatingFilter: true },
        {
            headerName: 'Fecha Movimiento',
            field: 'fecha_movimiento',
            valueFormatter: dateFormatter,
            filter: 'agDateColumnFilter',
            floatingFilter: true,
            filterParams: {
                comparator: dateComparator,
            },
        },
        {
            headerName: 'Fecha Inicio',
            field: 'fecha_inicio',
            valueFormatter: dateFormatter,
            filter: 'agDateColumnFilter',
            floatingFilter: true,
            filterParams: {
                comparator: dateComparator,
            },
        },
        {
            headerName: 'Fecha Fin',
            field: 'fecha_fin',
            valueFormatter: dateFormatter,
            filter: 'agDateColumnFilter',
            floatingFilter: true,
            filterParams: {
                comparator: dateComparator,
            },
        },
    ];

    const gridOptions = {
        columnDefs,
        rowData: [],

        pagination: true,
        paginationPageSize: 30,
        paginationPageSizeSelector: [30, 50, 100],
        rowSelection: 'multiple',
        suppressRowClickSelection: false,
        defaultColDef: {
            sortable: true,
            filter: true,
            resizable: true,
            floatingFilter: true,
        },
        animateRows: true,
        onGridReady: (params) => {
            gridApi = params.api;
        },
        onFilterChanged: () => {
            if (gridApi) gridApi.deselectAll();
        },
    };

    const grid = agGrid.createGrid(gridDiv, gridOptions);
    gridApi = grid.api;

    // Exportar a Excel o PDF
    document.querySelectorAll('.exportButton').forEach((button) => {
        button.addEventListener('click', async function () {
            const fileType = this.dataset.filetype;
            const selectedRows = gridApi.getSelectedRows();
            const selectedIds = selectedRows.map((row) => row.id);

            if (selectedIds.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Sin selección',
                    text: 'Seleccione al menos una fila para exportar.',
                });
                return;
            }

            const statusVal = document.getElementById('statusFilter') ? document.getElementById('statusFilter').value : 'por_pagar';
            const formData = new FormData();
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
            formData.append('fileType', fileType);
            formData.append('status', statusVal);
            selectedIds.forEach((id) => formData.append('selected_ids[]', id));

            try {
                const response = await fetch(exportUrl, {
                    method: 'POST',
                    body: formData,
                });

                if (!response.ok) throw new Error('Error al generar el archivo.');

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
                    showConfirmButton: true,
                });
            } catch (error) {
                Swal.fire('Error', error.message, 'error');
            }
        });
    });

    function currencyFormatter(params) {
        return new Intl.NumberFormat('es-MX', {
            style: 'currency',
            currency: 'MXN',
        }).format(params.value || 0);
    }

    function dateFormatter(params) {
        if (!params.value) return '';
        const date = new Date(params.value);
        return date.toLocaleDateString('es-MX');
    }

    const startDate = moment().subtract(7, 'days');
    const endDate = moment();

    $('#daterange').daterangepicker(
        {
            startDate,
            endDate,
            // maxDate: moment(),
            opens: 'right',
            locale: {
                format: 'YYYY-MM-DD',
                separator: ' al ',
                applyLabel: 'Aplicar',
                cancelLabel: 'Cancelar',
                fromLabel: 'Desde',
                toLabel: 'Hasta',
                customRangeLabel: 'Personalizado',
                daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
                monthNames: [
                    'Enero',
                    'Febrero',
                    'Marzo',
                    'Abril',
                    'Mayo',
                    'Junio',
                    'Julio',
                    'Agosto',
                    'Septiembre',
                    'Octubre',
                    'Noviembre',
                    'Diciembre',
                ],
                firstDay: 1,
            },
            ranges: {
                'Hoy': [moment(), moment()],
                'Últimos 7 días': [moment().subtract(6, 'days'), moment()],
                'Últimos 30 días': [moment().subtract(29, 'days'), moment()],
                'Este mes': [moment().startOf('month'), moment().endOf('month')],
                'Mes anterior': [
                    moment().subtract(1, 'month').startOf('month'),
                    moment().subtract(1, 'month').endOf('month')
                ]
            }
        },
        function (start, end) {
            applyFilters();
        }
    );

    async function applyFilters() {
        const daterange = $('#daterange').data('daterangepicker');
        if(!daterange) return;
        const start = daterange.startDate.format('YYYY-MM-DD');
        const end = daterange.endDate.format('YYYY-MM-DD');
        const status = document.getElementById('statusFilter') ? document.getElementById('statusFilter').value : 'por_pagar';

        try {
            const url = `/reporteria/gastos-pagar/data?status=${status}&from=${start}&to=${end}`;
            const response = await fetch(url);
            if (!response.ok) throw new Error('Error al obtener los datos.');
            const data = await response.json();

            // Formatear fechas si es necesario para el grid, aunque si el server regresa YYYY-MM-DD suele bastar
            const filtrado = data.map((item) => ({
                ...item,
                fecha_inicio: item.fecha_inicio ? new Date(item.fecha_inicio) : null,
                fecha_fin: item.fecha_fin ? new Date(item.fecha_fin) : null,
                fecha_movimiento: item.fecha_movimiento ? new Date(item.fecha_movimiento) : null,
            }));

            if (gridApi) {
                gridApi.setGridOption('rowData', filtrado);
                gridApi.deselectAll(); 
            }
        } catch (error) {
            console.error('Error fetching data:', error);
            Swal.fire('Error', 'No se pudieron cargar los datos.', 'error');
        }
    }

    const statusFilter = document.getElementById('statusFilter');
    if (statusFilter) {
        statusFilter.addEventListener('change', applyFilters);
    }
    
    // Poblar inicialmente
    applyFilters();
});
