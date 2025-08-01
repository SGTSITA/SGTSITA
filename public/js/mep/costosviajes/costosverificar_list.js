document.addEventListener('DOMContentLoaded', function () {
    const gridDiv = document.querySelector('#tablaPendientesMEP');

    // Función para marcar celdas modificadas
    function cellHighlightRules(fieldName) {
        return {
            'highlight-cell': (params) => {
                return params.data?.highlight?.[fieldName] === true;
            }
        };
    }

    function money(params) {
        return `$${parseFloat(params.value || 0).toFixed(4)}`;
    }

    const columnDefs = [
        { headerName: "Contenedor", field: "contenedor", flex: 1 },
        { headerName: "Destino", field: "destino", flex: 1 },
        { headerName: "Estatus", field: "estatus", flex: 1 },
        { headerName: "Costo del viaje", field: "precio_viaje", valueFormatter: money, flex: 1, cellClassRules: cellHighlightRules('precio_viaje') },
        { headerName: "Burreo", field: "burreo", valueFormatter: money, flex: 1, cellClassRules: cellHighlightRules('burreo') },
        { headerName: "Maniobra", field: "maniobra", valueFormatter: money, flex: 1, cellClassRules: cellHighlightRules('maniobra') },
        { headerName: "Estadía", field: "estadia", valueFormatter: money, flex: 1, cellClassRules: cellHighlightRules('estadia') },
        { headerName: "Otros", field: "otro", valueFormatter: money, flex: 1, cellClassRules: cellHighlightRules('otro') },
        { headerName: "IVA", field: "iva", valueFormatter: money, flex: 1, cellClassRules: cellHighlightRules('iva') },
        { headerName: "Retención", field: "retencion", valueFormatter: money, flex: 1, cellClassRules: cellHighlightRules('retencion') },
        { headerName: "Base 1", field: "base1", valueFormatter: money, flex: 1, cellClassRules: cellHighlightRules('base1') },
        { headerName: "Base 2", field: "base2", valueFormatter: money, flex: 1, cellClassRules: cellHighlightRules('base2') },
        { headerName: "Sobrepeso", field: "sobrepeso", valueFormatter: money, flex: 1, cellClassRules: cellHighlightRules('sobrepeso') },
        { headerName: "Total", field: "total", valueFormatter: money, flex: 1, cellClassRules: cellHighlightRules('total') },
        { headerName: "Precio sobrepeso", field: "precio_sobrepeso", valueFormatter: money, flex: 1, cellClassRules: cellHighlightRules('precio_sobrepeso') }
    ];

    const grid = agGrid.createGrid(gridDiv, {
        columnDefs: columnDefs,
        rowData: [],
        pagination: true,
        defaultColDef: {
            resizable: true,
            sortable: true,
            filter: true
        }
    });

    fetch('/costos/mep/pendientes')
        .then(res => res.json())
        .then(data => grid.setGridOption('rowData', data));
});
