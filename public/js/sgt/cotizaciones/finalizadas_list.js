document.addEventListener('DOMContentLoaded', function () {
    console.log('✅ DOM completamente cargado.');

    if (typeof agGrid === 'undefined' || typeof agGrid.createGrid === 'undefined') {
        console.error('🚨 Error: AG Grid no está cargado o está usando una versión incorrecta.');
        return;
    }

    console.log('✅ AG Grid está disponible.');

    var gridDiv = document.querySelector('#gridFinalizadas');
    if (!gridDiv) {
        console.error('🚨 Error: No se encontró el contenedor de la tabla (#gridFinalizadas).');
        return;
    }

    console.log('✅ Contenedor de AG Grid encontrado.');

    var gridOptions = {
        columnDefs: [
            { headerName: 'No', field: 'id', width: 80 },
            { headerName: 'Cliente', field: 'cliente', width: 150 },
            { headerName: 'Origen', field: 'origen', width: 200 },
            { headerName: 'Destino', field: 'destino', width: 200 },
            { headerName: '# Contenedor', field: 'contenedor', width: 200 },
            { headerName: 'Estatus', field: 'estatus', width: 120 },
            {
                headerName: 'Coordenadas',
                field: 'coordenadas',
                width: 150,
                cellRenderer: function (params) {
                    if (params.value === 'Ver') {
                        return `<a href="/index.cooredenadas/${params.data.id}" class="btn btn-xs btn-primary">
                                    <i class="fa-solid fa-map-marker-alt"></i> Ver
                                </a>`;
                    }
                    return 'N/A';
                },
            },
            {
                headerName: 'Acciones',
                field: 'id',
                width: 200,
                cellRenderer: function (params) {
                    return `
                        <a href="${params.data.edit_url}" class="btn btn-xs btn-warning">
                            <i class="fa-solid fa-edit"></i>
                        </a>
                        <a href="${params.data.pdf_url}" class="btn btn-xs btn-danger">
                            <i class="fa-solid fa-file-pdf"></i>
                        </a>
                    `;
                },
            },
        ],
        defaultColDef: { resizable: true, sortable: true, filter: true },
        pagination: true,
        paginationPageSize: 10,
        onGridReady: function (params) {
            console.log('✅ AG Grid listo.');
            window.gridApi = params.api;

            fetch('/cotizaciones/finalizadas')
                .then((response) => response.json())
                .then((data) => {
                    console.log('✅ Datos recibidos:', data.list);
                    window.gridApi.applyTransaction({ add: data.list });
                })
                .catch((error) => console.error('🚨 Error al cargar cotizaciones:', error));
        },
    };

    agGrid.createGrid(gridDiv, gridOptions);
});
