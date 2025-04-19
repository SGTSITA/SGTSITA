document.addEventListener('DOMContentLoaded', () => {
    const gridDiv = document.querySelector('#viajesGrid');
    let gridApi = null;

    const columnDefs = [
        { headerName: "ID", field: "id", checkboxSelection: true, headerCheckboxSelection: true, width: 100, cellClass: 'text-center' },
        { headerName: "Cliente", field: "cliente", filter: 'agTextColumnFilter', floatingFilter: true, flex: 1 },
        { headerName: "Contenedor", field: "contenedor", filter: 'agTextColumnFilter', floatingFilter: true, flex: 1 },
        { headerName: "Subcliente", field: "subcliente", filter: 'agTextColumnFilter', floatingFilter: true, flex: 1 },
        { headerName: "Origen", field: "origen", filter: 'agTextColumnFilter', floatingFilter: true, flex: 1 },
        { headerName: "Destino", field: "destino", filter: 'agTextColumnFilter', floatingFilter: true, flex: 1 },
        { headerName: "Fecha salida", field: "fecha_salida", filter: 'agDateColumnFilter', floatingFilter: true, width: 140 },
        { headerName: "Fecha llegada", field: "fecha_llegada", filter: 'agDateColumnFilter', floatingFilter: true, width: 140 },
        { headerName: "Estatus", field: "estatus", filter: 'agTextColumnFilter', floatingFilter: true, width: 150 }
    ];
    

    const gridOptions = {
        columnDefs,
        rowData: window.viajesData || [],
        rowSelection: 'multiple',
        pagination: true,
        paginationPageSize: 30,
        defaultColDef: {
            sortable: true,
            filter: true,
            resizable: true
        },
        animateRows: true,
        onGridReady: (params) => {
            gridApi = params.api;
        }
    };

    agGrid.createGrid(gridDiv, gridOptions);

    document.querySelectorAll('.exportButton').forEach(button => {
        button.addEventListener('click', async function () {
            if (!gridApi) return;
    
            const fileType = this.dataset.filetype;
            const selectedRows = gridApi.getSelectedRows();
            const selectedIds = selectedRows.map(row => row.id);
    
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
            selectedIds.forEach(id => formData.append('cotizacion_ids[]', id));
    
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
                a.download = `viajes_seleccionados.${fileType}`;
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
                    text: error.message
                });
            }
        });
    });
    // ✅ Exportar todo el tablero (sin selección)
document.getElementById('exportButtonGenericExcel')?.addEventListener('click', async function () {
    const data = document.getElementById('txtDataGenericExcel')?.value;

    if (!data || JSON.parse(data).length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Sin datos',
            text: 'No hay información para exportar.'
        });
        return;
    }

    const formData = new FormData();
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    formData.append('fileType', 'xlsx');
    formData.append('exportAll', 'true');

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
        a.download = `viajes_tablero_completo.xlsx`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(downloadUrl);
        a.remove();

        Swal.fire({
            icon: 'success',
            title: 'Descarga completa',
            text: 'El archivo se descargó correctamente.',
        });
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message
        });
    }
});

    
    

    // ========== DATERANGE ==========
    const startParam = getUrlParam('fecha_inicio');
    const endParam = getUrlParam('fecha_fin');

    const startDate = startParam ? moment(startParam, 'YYYY-MM-DD') : moment().subtract(7, 'days');
    const endDate = endParam ? moment(endParam, 'YYYY-MM-DD') : moment();

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
        const url = new URL("/reporteria/viajes/buscador", window.location.origin);
        url.searchParams.set('fecha_inicio', start.format('YYYY-MM-DD'));
        url.searchParams.set('fecha_fin', end.format('YYYY-MM-DD'));
        window.location.href = url.toString();
    });

    function getUrlParam(name) {
        const params = new URLSearchParams(window.location.search);
        return params.get(name);
    }
});
