document.addEventListener('DOMContentLoaded', function () {
    let gridApi;

    const columnDefs = [
        {
            headerName: '',
            width: 60,
            checkboxSelection: true,
            headerCheckboxSelection: true,
            headerCheckboxSelectionFilteredOnly: true,
            // No se necesita más aquí
        },
        { headerName: '# Contenedor', field: 'num_contenedor', filter: 'agTextColumnFilter', floatingFilter: true },
        { headerName: 'Subcliente', field: 'subcliente', filter: 'agTextColumnFilter', floatingFilter: true },
        {
            headerName: 'Importe pendiente',
            field: 'restante',
            filter: 'agTextColumnFilter',
            floatingFilter: true,
            valueFormatter: (p) => `$${parseFloat(p.value || 0).toLocaleString()}`,
        },
        { headerName: 'Tipo de viaje', field: 'tipo_viaje', filter: 'agTextColumnFilter', floatingFilter: true },
        { headerName: 'Estatus', field: 'estatus', filter: 'agTextColumnFilter', floatingFilter: true },
        {
            headerName: 'Carta Porte',
            field: 'carta_porte',
            cellRenderer: (params) => {
                return params.value
                    ? `<i class="fas fa-circle-check text-success fa-lg"></i>`
                    : `<i class="fas fa-circle-xmark text-secondary fa-lg"></i>`;
            },
        },
        {
            headerName: 'XML CP',
            field: 'carta_porte_xml',
            cellRenderer: (params) => {
                return params.value
                    ? `<i class="fas fa-circle-check text-success fa-lg"></i>`
                    : `<i class="fas fa-circle-xmark text-secondary fa-lg"></i>`;
            },
        },
    ];

    const gridOptions = {
        columnDefs,
        rowData: [],
        rowSelection: 'multiple',
        animateRows: true,
        defaultColDef: {
            sortable: true,
            filter: true,
            resizable: true,
        },
        onGridReady: function (params) {
            gridApi = params.api;

            fetch('/reporteria/viajes-por-cobrar/data')
                .then((res) => res.json())
                .then((data) => {
                    console.log('Datos cargados:', data);
                    gridApi.applyTransaction({ add: data });

                    // ✅ ¡IMPORTANTE! Guardar para exportar
                    window._cotizacionesExport = data; // <-- esta línea es CLAVE

                    const totalGenerado = data.reduce((sum, item) => sum + parseFloat(item.restante || 0), 0);
                    const retenido = data
                        .filter((c) => !c.carta_porte || !c.carta_porte_xml)
                        .reduce((sum, item) => sum + parseFloat(item.restante || 0), 0);
                    const pagoNeto = totalGenerado - retenido;

                    window._totalesExport = {
                        totalGenerado,
                        retenido,
                        pagoNeto,
                    };
                });
        },
    };

    const gridDiv = document.querySelector('#vxcGrid');
    agGrid.createGrid(gridDiv, gridOptions);

    // Exportar
    document.getElementById('exportExcel')?.addEventListener('click', () => exportar('excel'));
    document.getElementById('exportPDF')?.addEventListener('click', () => exportar('pdf'));

    function exportar(tipo) {
        Swal.fire({
            title: 'Generando archivo...',
            text: 'Por favor espera un momento',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            },
        });

        fetch(`/reporteria/viajes-por-cobrar/exportar`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                tipo,
                cotizaciones: window._cotizacionesExport,
                totales: window._totalesExport,
            }),
        })
            .then((res) => {
                if (!res.ok) throw new Error('Error al generar el archivo');
                return res.blob();
            })
            .then((blob) => {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `viajes_por_cobrar.${tipo === 'excel' ? 'xlsx' : 'pdf'}`;
                a.click();
                window.URL.revokeObjectURL(url);

                Swal.fire({
                    icon: 'success',
                    title: 'Archivo generado',
                    text: 'La descarga comenzará automáticamente',
                    timer: 2500,
                    showConfirmButton: false,
                });
            })
            .catch(() => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo generar el archivo',
                });
            });
    }
});
