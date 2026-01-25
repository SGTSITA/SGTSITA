document.addEventListener('DOMContentLoaded', () => {
    const gridDiv = document.querySelector('#myGrid');
    let gridApi = null;

    const columnDefs = [
        {
            headerName: 'Fecha inicio',
            field: 'fecha_inicio',
            checkboxSelection: true,
            headerCheckboxSelection: true,
            headerCheckboxSelectionFilteredOnly: true,
            cellClass: 'text-center',
            filter: 'agDateColumnFilter',
            floatingFilter: true,
            width: 160,
            valueFormatter: (params) => {
                if (!params.value) return '';
                const date = new Date(params.value);
                const day = String(date.getDate()).padStart(2, '0');
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const year = date.getFullYear();
                return `${day}-${month}-${year}`;
            },
            filterParams: {
                comparator: function (filterLocalDateAtMidnight, cellValue) {
                    const cellDate = new Date(cellValue);
                    const cellDateNoTime = new Date(cellDate.getFullYear(), cellDate.getMonth(), cellDate.getDate());
                    if (cellDateNoTime < filterLocalDateAtMidnight) return -1;
                    if (cellDateNoTime > filterLocalDateAtMidnight) return 1;
                    return 0;
                },
                browserDatePicker: true,
            },
        },
        {
            headerName: 'Subcliente',
            field: 'subcliente',
            filter: 'agTextColumnFilter',
            floatingFilter: true,
            width: 220,
            cellClass: 'text-center',
        },
        {
            headerName: 'Linea de Transporte',
            field: 'proveedor',
            filter: 'agTextColumnFilter',
            floatingFilter: true,
            width: 230,
            cellClass: 'text-center',
        },
        {
            headerName: '# Contenedor',
            field: 'num_contenedor',
            width: 200,
            filter: true,
            floatingFilter: true,
            autoHeight: true,
            cellRenderer: (params) => {
                const contenedor = params.value || '';
                const tipo = params.data.tipo;
                const isFull = tipo === 'Full';

                return `
            <div style="white-space: normal; line-height: 1.4;">
                ${contenedor}
                ${isFull ? `<span class="badge bg-warning text-dark ms-2">FULL</span>` : ''}
            </div>
        `;
            },
        },

        {
            headerName: 'Formato CCP',
            field: 'doc_ccp',
            cellRenderer: checkboxRenderer,
            filter: 'agTextColumnFilter',
            width: 120,
            cellClass: 'text-center',
        },
        {
            headerName: 'Boleta liberación',
            field: 'boleta_liberacion',
            cellRenderer: checkboxRenderer,
            filter: 'agTextColumnFilter',
            width: 120,
            cellClass: 'text-center',
        },
        {
            headerName: 'DODA',
            field: 'doda',
            cellRenderer: checkboxRenderer,
            filter: 'agTextColumnFilter',
            width: 110,
            cellClass: 'text-center',
        },
        {
            headerName: 'Carta porte',
            field: 'carta_porte',
            cellRenderer: checkboxRenderer,
            filter: 'agTextColumnFilter',
            width: 120,
            cellClass: 'text-center',
        },
        {
            headerName: 'Boleta vacío',
            field: 'boleta_vacio',
            cellRenderer: checkboxRenderer,
            filter: 'agTextColumnFilter',
            width: 120,
            cellClass: 'text-center',
        },
        {
            headerName: 'EIR',
            field: 'doc_eir',
            cellRenderer: checkboxRenderer,
            filter: 'agTextColumnFilter',
            width: 120,
            cellClass: 'text-center',
        },
    ];

    const rowData = window.cotizacionesData || [];

    const gridOptions = {
        columnDefs,
        rowData,
        pagination: true,
        paginationPageSize: 30,
        paginationPageSizeSelector: [30, 50, 100],
        rowSelection: 'multiple',
        suppressRowClickSelection: false,
        groupSelectsFiltered: true,
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
            if (gridApi) gridApi.deselectAll();
        },
    };

    agGrid.createGrid(gridDiv, gridOptions);
    function checkboxRenderer(params) {
        const data = params.data || {};
        const field = params.colDef.field;

        // Función auxiliar para renderizar estado genérico
        const renderIcon = (estado, isCima = false) => {
            if (isCima) {
                return `<span class="badge bg-success text-white px-2 py-1">CIMA</span>`;
            }
            if (estado) {
                return `<i class="fas fa-circle-check text-success fa-lg"></i>`;
            }
            return `<i class="fas fa-circle-xmark text-secondary fa-lg"></i>`;
        };

        // === CASO ESPECIAL: doc_eir con Full ===
        if (field === 'doc_eir') {
            if (data.tipo === 'Full') {
                const primario = renderIcon(data.eir_primario ?? false, data.cima_primario === 1);
                const secundario = renderIcon(data.eir_secundario ?? false, data.cima_secundario === 1);

                return `
                <div class="text-center" title="EIR/CIMA de ambos contenedores">
                    ${primario} / ${secundario}
                </div>
            `;
            }

            // No Full → mostrar solo uno (cima o eir)
            return `
            <div class="text-center">
                ${renderIcon(params.value, data.cima === 1)}
            </div>
        `;
        }

        // === CASO GENERAL PARA OTRAS COLUMNAS ===
        return `
        <div class="text-center">
            ${renderIcon(params.value)}
        </div>
    `;
    }

    // Exportar a Excel o PDF
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
                    text: 'Seleccione al menos un documento para exportar.',
                });
                return;
            }

            const formData = new FormData();
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
            formData.append('fileType', fileType);
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
                a.download = `documentos_seleccionados.${fileType}`;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(downloadUrl);
                a.remove();

                Swal.fire({
                    icon: 'success',
                    title: 'Descarga completa',
                    text: 'El archivo se ha descargado correctamente.',
                });
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message,
                });
            }
        });
    });

    // ========== RANGO DE FECHAS ==========
    $('#daterange').daterangepicker(
        {
            startDate: getUrlParam('fecha_inicio') || moment().subtract(7, 'days'),
            endDate: getUrlParam('fecha_fin') || moment(),
            maxDate: moment(),
            opens: 'right',
            locale: {
                format: 'YYYY-MM-DD',
                separator: ' AL ',
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
        },
        function (start, end) {
            const currentStart = getUrlParam('fecha_inicio');
            const currentEnd = getUrlParam('fecha_fin');

            if (start.format('YYYY-MM-DD') !== currentStart || end.format('YYYY-MM-DD') !== currentEnd) {
                getDatosFiltradosPorFecha(start.format('YYYY-MM-DD'), end.format('YYYY-MM-DD'));
            }
        },
    );

    function getUrlParam(name) {
        const params = new URLSearchParams(window.location.search);
        return params.get(name);
    }

    function getDatosFiltradosPorFecha(startDate, endDate) {
        const url = new URL(window.location.href);
        url.searchParams.set('fecha_inicio', startDate);
        url.searchParams.set('fecha_fin', endDate);
        window.location.href = url.toString();
    }
});
