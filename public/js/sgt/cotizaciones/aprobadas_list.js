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

document.addEventListener("DOMContentLoaded", function () {     
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

            let nextOne = document.querySelector('#nextOne');
            nextOne.disabled = false;
          }
    }

  
});

let tipoViaje = null
let cmbTipoUnidad = document.querySelector('#cmbTipoUnidad')
let cmbChasis = document.querySelector('#cmbChasis')
let cmbChasis2 = document.querySelector('#cmbChasis2')
let cmbDoly  = document.querySelector('#cmbDoly')
let btnProgramar = document.querySelector('#btnProgramar')

const formFieldsPlaneacion = [
    {'field':'txtFechaInicio','id':'txtFechaInicio','label':'Fecha salida','required': true, "type":"text", "trigger":"none"},
    {'field':'txtFechaFinal','id':'txtFechaFinal','label':'Fecha entrega','required': true, "type":"text", "trigger":"none"},
    {'field':'cmbTipoUnidad','id':'cmbTipoUnidad','label':'Tipo unidad','required': true, "type":"text", "trigger":"none"},
    {'field':'cmbCamion','id':'cmbCamion','label':'Unidad','required': true, "type":"text", "trigger":"none"},
    {'field':'cmbChasis','id':'cmbChasis','label':'Chasis','required': true, "type":"text", "trigger":"none"},
    {'field':'cmbChasis2','id':'cmbChasis2','label':'Chasis 2','required': true, "type":"text", "trigger":"none"},
    {'field':'cmbDoly','id':'cmbDoly','label':'Doly','required': true, "type":"text", "trigger":"none"},
    {'field':'cmbOperador','id':'cmbOperador','label':'Operador','required': true, "type":"text", "trigger":"none"},
    {'field':'txtSueldoOperador','id':'txtSueldoOperador','label':'Sueldo Operador','required': true, "type":"money", "trigger":"none"},
    {'field':'txtDineroViaje','id':'txtDineroViaje','label':'Dinero viaje','required': true, "type":"money", "trigger":"none"},
    {'field':'cmbBanco','id':'cmbBanco','label':'Banco','required': true, "type":"text", "trigger":"none"}
]

function setTipoViaje(valTipoViaje){
    let nextOne = document.querySelector('#nextTwo');
    nextOne.disabled = false;
    tipoViaje = valTipoViaje;
}

function programarViaje(){

    let passValidation = formFieldsPlaneacion.every((item) => {
        let field = document.getElementById(item.field);
        if(field){
            if(item.required === true && field.value.length == 0){
                Swal.fire("El campo "+item.label+" es obligatorio","Parece que no ha proporcionado información en el campo "+item.label,"warning");
                return false;
            }
        }
        return true;
    })

   if(!passValidation) return passValidation;

   const formData = {};

   formFieldsPlaneacion.forEach((item) =>{
    var input = item.field;
    var inputValue = document.getElementById(input);
    if(inputValue){
        if(item.type == "money"){
            formData[input] = (inputValue.value.length > 0) ? parseFloat(reverseMoneyFormat(inputValue.value)) : 0;
        }else{
            formData[input] = inputValue.value;
        }
    }
   });

   formData["_token"] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
   formData["num_contenedor"] = document.querySelector('#numContenedor').textContent
   let url = '/planeaciones/viaje/programar'

    $.ajax({
        url: url,
        type: "post",
        data: formData,
        beforeSend:function(){
        
        },
        success:function(data){
                Swal.fire(data.Titulo,data.Mensaje,data.TMensaje).then(function() {
                    if(data.TMensaje == "success"){
                        
                        window.location.replace("/planeaciones");
                    
                    }
                });
        },
        error:function(){       
        Swal.fire("Error","Ha ocurrido un error, intentelo nuevamente","error");
        }
    });
}

$(".moneyformat").on("focus",(e)=>{
    var val = e.target.value;
    e.target.value = reverseMoneyFormat(val);
})

$(".moneyformat").on("blur",(e) =>{
    var val = e.target.value;
    e.target.value =  moneyFormat(val);
})

cmbTipoUnidad.addEventListener('change',(e)=>{
    let isActive = (e.target.value  == "Sencillo") ? true : false
    
    cmbChasis2.disabled = isActive
    cmbDoly.disabled = isActive
})

btnProgramar.addEventListener('click',programarViaje)
