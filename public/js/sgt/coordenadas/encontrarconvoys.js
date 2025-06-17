 let contenedoresDisponibles;
 let contenedoresAsignadosAntes ;
  let gridApi;
 document.addEventListener('DOMContentLoaded', function () {

     

    definirTable();


    const modal = new bootstrap.Modal(document.getElementById('modalBuscarConvoy'), {
        backdrop: 'static',
        keyboard: false
    });

    // Mostrar el modal al cargar la vista
    modal.show();

    // Mostrar el modal manualmente si el usuario da clic en el botón
    document.getElementById('btnNuevoconboy').addEventListener('click', function () {
        modal.show();
    });

 
 


    document.getElementById('formBuscarConvoy').addEventListener('submit', function (e) {
        e.preventDefault();

        const numero = document.getElementById('numero_convoy').value;

        fetch(`/coordenadas/conboys/getconvoy/${numero}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {

                    const fechaInicio = new Date(data.data.fecha_inicio);
                    const fechaFin = new Date(data.data.fecha_fin);

                    const formatoFecha = (fecha) => {
                        return `${fecha.getDate().toString().padStart(2, '0')}/${(fecha.getMonth() + 1).toString().padStart(2, '0')}/${fecha.getFullYear()}`;
                    };
                    document.getElementById('descripcionConvoy').textContent = data.data.nombre;
                    document.getElementById('fechaInicioConvoy').textContent = formatoFecha(fechaInicio);
                    document.getElementById('fechaFinConvoy').textContent = formatoFecha(fechaFin);
                    document.getElementById('id_convoy').value = data.data.idconvoy;
                    contenedoresDisponibles = data.data.contenedoresPropios;
                    contenedoresAsignadosAntes = data.data.contenedoresPropiosAsignados;
                    contenedoresAsignadosAntes.forEach((contenedor, index) => {
                         seleccionarContenedor(contenedor.num_contenedor)
                         
                        });
                   

                   

                    document.getElementById('resultadoConvoy').style.display = 'block';
                } else {
                    alert("Convoy no encontrado.");
                }
            });
    });



    
    document.getElementById('btnGuardarContenedores').addEventListener('click', function () {
        const numeroConvoy = document.getElementById('numero_convoy').value;
        let idconvoy = document.getElementById('id_convoy').value;
    document.getElementById('ItemsSelects').value = ItemsSelects.join(';');
    
if (!ItemsSelects || ItemsSelects.length === 0) {
    alert('Por favor, seleccione al menos un contenedor.');
    return;
  }
let datap = {
    items_selects: ItemsSelects,
    idconvoy:idconvoy,
    numero_convoy:numeroConvoy,
};

        fetch(`/coordenadas/conboys/agregar`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(datap)
        })
        .then(async res => {
            if (!res.ok) {
                // Intentamos extraer el mensaje del error (por si Laravel lo devuelve)
                const errorText = await res.text();
                throw new Error(errorText || 'Error desconocido del servidor');
            }
            return res.json();
        })
        .then(data => {
            if (data.success) {
                document.getElementById('modalBuscarConvoy').style.display = 'none';
              

                Swal.fire({
                    title: 'Guardado correctamente',
                    text: data.message + ' ' + data.no_conboy,
                    icon: 'success',
                    confirmButtonText: 'Aceptar'
                });



            } else {
                Swal.fire({
                    title: 'Error',
                    text: data.message || 'No se pudo guardar.',
                    icon: 'error',
                    confirmButtonText: 'Cerrar'
                });
            }
        })
        .catch(error => {
            console.error('Error en la petición:', error);

            Swal.fire({
                title: 'Error inesperado',
                text: error.message,
                icon: 'error',
                confirmButtonText: 'Cerrar'
            });
        });
    });






    });

    function definirTable(){
  const columnDefs = [
        {
        headerCheckboxSelection: true,
        checkboxSelection: true,
        width: 40, 
        pinned: "left", 
        suppressSizeToFit: true,
        resizable: false
        },
        // { headerName: "No Coti", field: "id_cotizacion", sortable: true, filter: true },
        // { headerName: "No Asig", field: "id_asignacion", sortable: true, filter: true },
        // { headerName: "No Coor", field: "id_coordenada", sortable: true, filter: true },
        { headerName: "Id", field: "no_conboy", sortable: true, filter: true },
        { headerName: "Descripcion", field: "nombre", sortable: true, filter: true },
        { headerName: "Fecha Inicio", field: "fecha_inicio", sortable: true, filter: true },
        { headerName: "Fecha Fin", field: "fecha_fin", sortable: true, filter: true },
       
        

       {
    headerName: "Acciones",
    field: "acciones",
    cellRenderer: function (params) {
        const container = document.createElement("div");
        const data = params.data;

        // Botón Editar
       /*  const btnEditar = document.createElement("button");
        btnEditar.innerText = "✏️ Editar";
        btnEditar.classList.add("btn", "btn-sm", "btn-warning", "me-1");
        btnEditar.onclick = function () {
          document.getElementById("filtroModalLabel") .textContent ="Editar Conboy";
            limpCampos();
             //document.getElementById("contenedoresTablaSection").style.display = "block";
             
            document.getElementById("id_convoy").value = data.id;
            document.getElementById("no_convoy").value = data.no_conboy || "";
            document.getElementById("fecha_inicio").value = formatDateForInput(data.fecha_inicio) || "";
            document.getElementById("fecha_fin").value = formatDateForInput(data.fecha_fin )|| "";
            document.getElementById("nombre").value = data.nombre || "";
            document.getElementById("formFiltros").dataset.editId = data.id;
 
 
            const contenedoresFiltrados = contenedoresGuardados.filter(item => item.conboy_id == data.id);

        // Llenas la tabla con los contenedoresFiltrados
        llenarTablaContenedores(contenedoresFiltrados);
            const modal = new bootstrap.Modal(document.getElementById("CreateModal"));
            modal.show();
        }; */

        const btnRastrear = document.createElement("button");
btnRastrear.innerText = "🔗 Rastrear convoy";
btnRastrear.classList.add("btn", "btn-sm", "btn-info");

btnRastrear.onclick = function () {
    // Arma la URL que quieres abrir
    const url = `${window.location.origin}/coordenadas/rastrear`;

    // Abre la URL en una nueva pestaña o ventana
    window.open(url, '_blank');
};

        // container.appendChild(btnEditar);
        container.appendChild(btnRastrear);

        return container;
    }
}
       
       
             
       
    ];
    function formatDateForInput(dateString) {
    const date = new Date(dateString);
    const year = date.getFullYear();
    const month = (`0${date.getMonth() + 1}`).slice(-2);
    const day = (`0${date.getDate()}`).slice(-2);
    return `${year}-${month}-${day}`;
}


const gridOptions = {
        columnDefs: columnDefs,
        pagination: true,
        paginationPageSize: 100,
        // rowSelection: "multiple",
        defaultColDef: {
            resizable: true,
            flex: 1
        },
    };

    const myGridElement = document.querySelector("#myGrid");
    gridApi = agGrid.createGrid(myGridElement, gridOptions);

    cargaConboys();

    function cargaConboys()
    { 
        const overlay = document.getElementById("gridLoadingOverlay");
        overlay.style.display = "flex";
    
                
            gridApi.setGridOption("rowData", []); 
        
            fetch("/coordenadas/conboys/getconboys")
                .then(response => response.json())
                .then(data => {
                   const rowData = data.data;
                    gridApi.setGridOption("rowData", rowData);
                    contenedoresGuardados = data.dataConten;
                })
                .catch(error => {
                    console.error("❌ Error al obtener la lista de convoys:", error);
                })
                .finally(() => {
                    overlay.style.display = "none"; 
                });
    }
}

     


     //sugerencias propias

    
const seleccionados = [];
const ItemsSelects = [];

    function mostrarSugerencias() {
        const input = document.getElementById('contenedor-input');
        const filtro = input.value.trim().toUpperCase();
        const sugerenciasDiv = document.getElementById('sugerencias');
        sugerenciasDiv.innerHTML = '';

        if (filtro.length === 0) {
            sugerenciasDiv.style.display = 'none';
            return;
        }

        const filtrados = contenedoresDisponibles.filter(c =>
            
            (c.num_contenedor || '').toUpperCase().includes(filtro) &&
    !seleccionados.includes(c.num_contenedor)


        );

        filtrados.forEach(c => {
            const item = document.createElement('div');
            item.textContent = c.num_contenedor;
            item.style.padding = '5px';
            item.style.cursor = 'pointer';
            item.onclick = () => seleccionarContenedor(c.num_contenedor);
            sugerenciasDiv.appendChild(item);
        });

        sugerenciasDiv.style.display = filtrados.length ? 'block' : 'none';
    }

    function seleccionarContenedor(valor) {
        seleccionados.push(valor);
         const contenedorData = contenedoresDisponibles.find(c => c.num_contenedor === valor);

         ItemsSelects.push(valor +"-" + contenedorData.id_contenedor);
        document.getElementById('contenedor-input').value = '';
        document.getElementById('sugerencias').style.display = 'none';
        actualizarVista();
    }

    function agregarContenedor() {
        const input = document.getElementById('contenedor-input');
        const valor = input.value.trim().toUpperCase();
        if (valor && contenedoresDisponibles.includes(valor) && !seleccionados.includes(valor)) {
            seleccionados.push(valor);
           
            input.value = '';
            actualizarVista();
        }
    }

    function eliminarContenedor(idx) {
        seleccionados.splice(idx, 1);
          ItemsSelects.splice(idx, 1);
        actualizarVista();
    }

    function actualizarVista() {
        const tbody = document.querySelector('#tablaContenedores tbody');
     tbody.innerHTML = '';
         seleccionados.forEach((cont, i) => {
        const row = document.createElement('tr');
        row.innerHTML = `
          
            <td>${cont}</td>
            <td>
                <button type="button" 
                        class="btn btn-sm btn-danger"
                        onclick="eliminarContenedor(${i})">
                     <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
    

        document.getElementById('contenedores').value = seleccionados.join(';');
        document.getElementById('ItemsSelects').value = ItemsSelects.join(';');
    }


    function limpiarFormularioConvoy() {
    // Limpiar tabla de contenedores
    const tbody = document.getElementById('tablaContenedores');
    tbody.innerHTML = '';

    // Limpiar inputs
    document.getElementById('numero_convoy').value = '';
    document.getElementById('id_convoy').value = '';

    // Limpiar selects ocultos o arrays usados
    ItemsSelects.length = 0; // Si es global, la reinicias
    document.getElementById('ItemsSelects').value = '';

    // Ocultar modal si es necesario
    document.getElementById('modalBuscarConvoy').style.display = 'none';

    // Limpiar también posibles mensajes o alertas
   // document.getElementById('resultadoBusquedaConvoy')?.innerHTML = '';
}