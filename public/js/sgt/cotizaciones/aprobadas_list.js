document.addEventListener("DOMContentLoaded", function () {
  
    const localeText = {
        page: 'Página',
        more: 'Más',
        to: 'a',
        of: 'de',
        next: 'Siguiente',
        last: 'Último',
        first: 'Primero',
        previous: 'Anterior',
        loadingOoo: 'Cargando...',
        selectAll: 'Seleccionar todo',
        searchOoo: 'Buscar...',
        blanks: 'Vacíos',
        filterOoo: 'Filtrar...',
        applyFilter: 'Aplicar filtro...',
        equals: 'Igual',
        notEqual: 'Distinto',
        lessThan: 'Menor que',
        greaterThan: 'Mayor que',
        contains: 'Contiene',
        notContains: 'No contiene',
        startsWith: 'Empieza con',
        endsWith: 'Termina con',
        andCondition: 'Y',
        orCondition: 'O',
        group: 'Grupo',
        columns: 'Columnas',
        filters: 'Filtros',
        pivotMode: 'Modo Pivote',
        groups: 'Grupos',
        values: 'Valores',
        noRowsToShow: 'Sin filas para mostrar',
        pinColumn: 'Fijar columna',
        autosizeThiscolumn: 'Ajustar columna',
        copy: 'Copiar',
        resetColumns: 'Restablecer columnas',
        blank: 'Vacíos',
        notBlank: 'No Vacíos',
        paginationPageSize: 'Registros por página'
      };
      
    if (typeof agGrid === "undefined" || typeof agGrid.createGrid === "undefined") {
        console.error("🚨 Error: AG Grid no está cargado o está usando una versión incorrecta.");
        return;
    }

   

    var gridDiv = document.querySelector("#gridAprobadas");
    if (!gridDiv) {
        console.error("🚨 Error: No se encontró el contenedor de la tabla (#gridAprobadas).");
        return;
    }

 

    var gridOptions = {
        pagination: true,
        paginationPageSize: 50,
        paginationPageSizeSelector: [50, 100, 500],
        rowSelection: {
            mode: "singleRow",
            headerCheckbox: false,
        },
        columnDefs: [
            { headerName: "No", field: "id", width: 80 , hide: true},
            { headerName: "Sub Cliente", field: "subcliente", width: 80 , hide: true},
            { headerName: "# Contenedor", field: "contenedor", width: 200,filter: true, floatingFilter: true },
            { headerName: "Cliente", field: "cliente", width: 150,filter: true, floatingFilter: true },
            { headerName: "Origen", field: "origen", width: 200 ,filter: true, floatingFilter: true},
            { headerName: "Destino", field: "destino", width: 200, filter: true, floatingFilter: true },
           
            { headerName: "Estatus",
            field: "estatus",
            minWidth: 180,
            cellRenderer: function (params) {
                let color = "secondary";
                if (params.data.estatus === "Aprobada") color = "success";
                else if (params.data.estatus === "Cancelada") color = "danger";
                else if (params.data.estatus === "Pendiente") color = "warning";
        
                return `
                    <button class="btn btn-sm btn-outline-${color}" onclick="abrirCambioEstatus(${params.data.id})" title="Cambiar estatus">
                        <i class="fa fa-check me-1"></i> ${params.data.estatus}
                    </button>
                `;
            } },
       
  
        ],
        defaultColDef: { resizable: true, sortable: true, filter: true },
        localeText: localeText,
        onRowSelected:(event)=>{
            seleccionContenedor()
        },
        onGridReady: function (params) {

            var paginationTitle = document.querySelector("#ag-32-label");
            paginationTitle.textContent = 'Registros por página';
           
            window.gridApi = params.api;

            fetch("/cotizaciones/aprobadas")
                .then(response => response.json())
                .then(data => {
                   
                    window.gridApi.applyTransaction({ add: data.list });
                })
                .catch(error => console.error("🚨 Error al cargar cotizaciones:", error));
        }
    };

    let apiGridAprobadas = agGrid.createGrid(gridDiv, gridOptions);

    function  seleccionContenedor(){
        if(gridDiv){
            let seleccion = apiGridAprobadas.getSelectedRows();
            let numContenedorLabel = document.querySelectorAll('.numContenedorLabel');
            let nombreClienteLabel = document.querySelectorAll('.nombreClienteLabel');
            seleccion.forEach((contenedor) =>{
                
                numContenedorLabel.forEach(lb=> lb.textContent = contenedor.contenedor)
                nombreClienteLabel.forEach(cl => cl.textContent = `${contenedor.cliente} / ${contenedor.subcliente}`)
            })
          }
    }
});
