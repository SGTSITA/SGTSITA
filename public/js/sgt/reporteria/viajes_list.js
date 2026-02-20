let fechaInicioViajes;
let fechaFinViajes;

document.addEventListener('DOMContentLoaded', () => {
    const gridDiv = document.querySelector('#viajesGrid');
    let gridApi = null;

    const columnDefs = [
        {
            headerName: 'ID',
            field: 'id',
            checkboxSelection: true,
            headerCheckboxSelection: true,
            headerCheckboxSelectionFilteredOnly: true,
            width: 100,
            cellClass: 'text-center',
        },
        { headerName: 'Contenedor', field: 'contenedor', filter: 'agTextColumnFilter', floatingFilter: true, flex: 1 },
        { headerName: 'Proveedor', field: 'proveedor', filter: 'agTextColumnFilter', floatingFilter: true, flex: 1 },
        { headerName: 'Operador', field: 'operador', filter: 'agTextColumnFilter', floatingFilter: true, flex: 1 },

        { headerName: 'Cliente', field: 'cliente', filter: 'agTextColumnFilter', floatingFilter: true, flex: 1 },
        { headerName: 'Subcliente', field: 'subcliente', filter: 'agTextColumnFilter', floatingFilter: true, flex: 1 },
        { headerName: 'Origen', field: 'origen', filter: 'agTextColumnFilter', floatingFilter: true, flex: 1 },
        { headerName: 'Destino', field: 'destino', filter: 'agTextColumnFilter', floatingFilter: true, flex: 1 },
        { headerName: 'Estatus', field: 'estatus', filter: 'agTextColumnFilter', floatingFilter: true, width: 150 },
    ];

    const gridOptions = {
        columnDefs,
        rowData: (window.viajesData || []).filter((item) => {
            const fecha = moment(item.fecha_salida, 'DD-MM-YYYY');
            return fecha.isValid() && fecha.isBetween(moment().subtract(7, 'days'), moment(), 'day', '[]');
        }),
        suppressRowClickSelection: false,
        rowSelection: 'multiple',
        groupSelectsFiltered: true,
        pagination: true,
        paginationPageSize: 30,
        defaultColDef: {
            sortable: true,
            filter: true,
            resizable: true,
        },
        animateRows: true,
        onGridReady: (params) => {
            gridApi = params.api;
        },
        onFilterChanged: () => {
            gridApi.deselectAll();
        },
    };

    const grid = agGrid.createGrid(gridDiv, gridOptions);
    gridApi = grid.api;

    // ========== Exportar seleccionados ==========
    document.querySelectorAll('.exportButton').forEach((button) => {
        button.addEventListener('click', async function () {
            if (!gridApi) return;

            const fileType = this.dataset.filetype;
            const selectedRows = gridApi.getSelectedRows();
            const selectedIds = selectedRows.map((row) => row.id);

            if (selectedIds.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Sin selección',
                    text: 'Selecciona al menos un viaje para exportar.',
                });
                return;
            }

            const formData = new FormData();
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
            formData.append('fileType', fileType);
            selectedIds.forEach((id) => formData.append('cotizacion_ids[]', id));

            try {
                const response = await fetch(exportUrl, { method: 'POST', body: formData });
                if (!response.ok) throw new Error('Error al generar el archivo.');

                const blob = await response.blob();
                const downloadUrl = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = downloadUrl;
                a.download = `viajes_seleccionados.${fileType}`;
                document.body.appendChild(a);
                a.click();
                a.remove();
                window.URL.revokeObjectURL(downloadUrl);

                Swal.fire({
                    icon: 'success',
                    title: 'Descarga completa',
                    text: 'El archivo se descargó correctamente.',
                });
            } catch (error) {
                Swal.fire({ icon: 'error', title: 'Error', text: error.message });
            }
        });
    });

    // ========== Exportar TODO ==========
    document.getElementById('exportButtonGenericExcel')?.addEventListener('click', async function () {
        const data = document.getElementById('txtDataGenericExcel')?.value;
        if (!data || JSON.parse(data).length === 0) {
            Swal.fire({ icon: 'warning', title: 'Sin datos', text: 'No hay información para exportar.' });
            return;
        }

        const formData = new FormData();
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        formData.append('fileType', 'xlsx');
        formData.append('exportAll', 'true');

        try {
            const response = await fetch(exportUrl, { method: 'POST', body: formData });
            if (!response.ok) throw new Error('Error al generar el archivo.');

            const blob = await response.blob();
            const downloadUrl = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = downloadUrl;
            a.download = `viajes_tablero_completo.xlsx`;
            document.body.appendChild(a);
            a.click();
            a.remove();
            window.URL.revokeObjectURL(downloadUrl);

            Swal.fire({ icon: 'success', title: 'Descarga completa', text: 'El archivo se descargó correctamente.' });
        } catch (error) {
            Swal.fire({ icon: 'error', title: 'Error', text: error.message });
        }
    });

    // ========== Filtro local por fecha ==========

    const startDate = moment().subtract(7, 'days');
    const endDate = moment();

    $('#daterange').daterangepicker(
        {
            startDate,
            endDate,
            maxDate: moment().endOf('month'),
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
                Hoy: [moment(), moment()],
                'Últimos 7 días': [moment().subtract(6, 'days'), moment()],
                'Últimos 30 días': [moment().subtract(29, 'days'), moment()],
                'Este mes': [moment().startOf('month'), moment().endOf('month')],
                'Mes anterior': [
                    moment().subtract(1, 'month').startOf('month'),
                    moment().subtract(1, 'month').endOf('month'),
                ],
            },
        },
        function (start, end) {
            // ⚡ Filtro local sin fetch
            const filtrado = (window.viajesData || []).filter((item) => {
                const fecha = moment(item.fecha_salida, 'DD-MM-YYYY'); // Asegúrate del formato
                return fecha.isValid() && fecha.isBetween(start, end, 'day', '[]'); // incluye extremos
            });

            if (gridApi) {
                gridApi.setGridOption('rowData', filtrado);
                document.getElementById('txtDataGenericExcel').value = JSON.stringify(filtrado);
            }
        },
    );
});
