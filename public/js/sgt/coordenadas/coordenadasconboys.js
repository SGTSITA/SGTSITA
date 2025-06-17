
    let contenedoresGuardados ;  
let contenedoresDisponibles = [];


function cargarinicial()
{
       fetch(`/coordenadas/contenedor/searchEquGps?`)
      .then(response => response.json())
    .then(data => {
        
         contenedoresDisponibles   = data.datos;
        
          
      })
  .catch(error => {
          console.error('Error al traer coordenadas:', error);
    });
}

document.addEventListener('DOMContentLoaded', function () {
     let gridApi;
     cargarinicial();
    definirTable();



document.getElementById("btnNuevoconboy").addEventListener("click", () => {
    limpiarFormulario();
    // Aquí abres el modal, por ejemplo con Bootstrap 5:
    const modal = new bootstrap.Modal(document.getElementById('CreateModal'));
    modal.show();
});

function limpiarFormulario() {
        const form = document.getElementById("formFiltros");
        form.reset(); // Limpia todos los inputs

        // Además limpia inputs ocultos, o elementos dinámicos si tienes
        document.getElementById("contenedores-seleccionados").innerHTML = "";
        document.getElementById("contenedores").value = "";
        document.getElementById("ItemsSelects").value = "";
        const tablaBody = document.getElementById("tablaContenedoresBody");
        if (tablaBody) {
        tablaBody.innerHTML = "";
        }
        // Si usas dataset para editar, elimina también ese id para que no interfiera
        delete form.dataset.editId;
    }
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
        const btnEditar = document.createElement("button");
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
        };

        // Botón Compartir
        const btnCompartir = document.createElement("button");
        btnCompartir.innerText = "🔗 Compartir";
        btnCompartir.classList.add("btn", "btn-sm", "btn-info");
        btnCompartir.onclick = function () {
            // const link = `${window.location.origin}/coordenadas/conboys/compartir/${data.no_conboy}/${data.id}`;
            // Aquí puedes pasarle el ID o nombre del conboy al modal
            document.getElementById("wmensajeText").innerText = `Se comparte el siguiente no. de Convoy:: ${data.no_conboy}`;
            
            // Puedes construir el link de WhatsApp si lo deseas
            const mensaje = (`Te comparto el convoy: ${data.no_conboy}`);
      
//
 // mail
                   // document.getElementById('linkMail').innerText = link;
                    document.getElementById('mensajeText').innerText = mensaje;
                    // whatsapp
                   // document.getElementById("wmensajeText").innerText = mensaje;
                   // document.getElementById("linkWhatsapp").value = link;
                    // 🟢 Armamos el link para WhatsApp
                    const textoWhatsapp = ` ${mensaje}`;
                    document.getElementById("whatsappLink").href = `https://wa.me/?text=${encodeURIComponent(textoWhatsapp)}`;
            // Mostrar modal
            document.getElementById("modalCoordenadas").style.display = "block";
        };

        container.appendChild(btnEditar);
        container.appendChild(btnCompartir);

        return container;
    }
}
           //  headerName: "E Burrero",
            //field: "Estatus_Burrero",
        //     minWidth: 180,
        //     cellRenderer: function (params) {
        //         let color = "secondary";
        //         let clasIcon ="fa fa-hourglass-half me-1"
        //         if (String(params.data.tipo_b_estado) === "2"){
        //         color = "success";
        //         clasIcon="fa fa-check-circle me-1 text-success";
        //         } 
        //         else if (String(params.data.tipo_b_estado) === "1"){
        //         color = "primary";
        //          clasIcon=" fa fa-play-circle me-1";
        //         } 
        //         else if (String(params.data.tipo_b_estado) === "0") {
        //             color = "warning";
        //              clasIcon="fa fa-hourglass-half me-1";
        //         }
        //        return `
        //                 <button class="btn btn-sm btn-outline-${color} ver-mapa-btn" 
        //                     data-tipo="b"
        //                     data-info='${JSON.stringify(params.data).replace(/'/g, "&#39;")}' 
        //                     title="Ver progreso...">
        //                     <i class="${clasIcon}"></i> ${params.data.Estatus_Burrero}
        //                 </button>
        //         `;
        //     }
        //},        
             
       
    ];
    function formatDateForInput(dateString) {
    const date = new Date(dateString);
    const year = date.getFullYear();
    const month = (`0${date.getMonth() + 1}`).slice(-2);
    const day = (`0${date.getDate()}`).slice(-2);
    return `${year}-${month}-${day}`;
}

function llenarTablaContenedores(contenedores) {
    const tabla = document.getElementById("tablaContenedoresBody"); // tbody
    tabla.innerHTML = "";

seleccionados.length = 0;
    ItemsSelects.length = 0;

    contenedores.forEach(item => {
        const row = document.createElement("tr");

        row.innerHTML = `
            <td>${item.num_contenedor}</td>
            <td></td>
        `;

        tabla.appendChild(row);

        seleccionados.push(item.num_contenedor);
        ItemsSelects.push(`${item.num_contenedor}-${item.id_contenedor}`);
    });
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

document.getElementById('formFiltros').addEventListener('submit', function(event) {
  event.preventDefault();
let idconvoy = document.getElementById('id_convoy').value;
let finicio = document.getElementById('fecha_inicio').value ;
let ffin = document.getElementById('fecha_fin').value ;
let nombre = document.getElementById('nombre').value ;
    document.getElementById('ItemsSelects').value = ItemsSelects.join(';');
    
if (!ItemsSelects || ItemsSelects.length === 0) {
    alert('Por favor, seleccione al menos un contenedor.');
    return;
  }
let datap = {
    fecha_inicio: finicio,
    fecha_fin: ffin,
    items_selects: ItemsSelects,
    nombre :nombre,
    idconvoy:idconvoy,
};
let urlSave ="/coordenadas/conboys/store";

if (idconvoy != ""){
    urlSave ="/coordenadas/conboys/update";
}

  saveconvoys(datap,urlSave);


  
 
});
  

function saveconvoys(datap,urlSave) {
 

    let responseOk = false;
  fetch(urlSave, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    },
    body: JSON.stringify(datap)
  })
  .then(res => {
    if (!res.ok) throw new Error('Error en la respuesta de red');
    return res.json();
  })
  .then(data => {
    console.log('convoy creado :', data);
    

    document.getElementById('no_convoy').value= data.no_convoy;
    

 
    
    const modalElement = document.getElementById('CreateModal'); 
    const filtroModal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
    filtroModal.hide();
   
    Swal.fire({
        title: 'Guardado correctamente',
        text: data.message + ' ' + data.no_conboy,
        icon: 'success',
        confirmButtonText: 'Aceptar'
    });


     
    cargaConboys();
  })
  .catch(error => {
    console.error('Error al guardar un conboy:', error);
    
  });
}


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
            
            (c.contenedor || '').toUpperCase().includes(filtro) &&
    !seleccionados.includes(c.contenedor)


        );

        filtrados.forEach(c => {
            const item = document.createElement('div');
            item.textContent = c.contenedor;
            item.style.padding = '5px';
            item.style.cursor = 'pointer';
            item.onclick = () => seleccionarContenedor(c.contenedor);
            sugerenciasDiv.appendChild(item);
        });

        sugerenciasDiv.style.display = filtrados.length ? 'block' : 'none';
    }

    function seleccionarContenedor(valor) {
        seleccionados.push(valor);
         const contenedorData = contenedoresDisponibles.find(c => c.contenedor === valor);

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

    function cambiarTab(tabId) {
    // Ocultamos todos los divs con clase 'tab-content'
    const tabs = document.querySelectorAll('.tab-content');
    tabs.forEach(tab => {
        tab.style.display = 'none';
    });

    // Mostramos solo el que corresponde
    const tabToShow = document.getElementById('tab-' + tabId);
    if (tabToShow) {
        tabToShow.style.display = 'block';
    } else {
        console.error(`No se encontró el tab: tab-${tabId}`);
    }
}

function mostrarTab(tab) {
    // Ocultar ambos
    document.getElementById('tab-mail').style.display = 'none';
    document.getElementById('tab-whatsapp').style.display = 'none';

    // Quitar clase activa
    const tabs = document.querySelectorAll('.nav-link');
    tabs.forEach(el => el.classList.remove('active'));

    // Mostrar el tab seleccionado
    document.getElementById(`tab-${tab}`).style.display = 'block';

    // Activar tab
    document.querySelector(`.nav-link[href="#"][onclick*="${tab}"]`).classList.add('active');
}

function cerrarModal() {
    const modal = document.getElementById('modalCoordenadas');
    limpCampos();
    if (modal) {
        modal.style.display = 'none';
    }

    // Eliminar el fondo oscuro si existe
    const backdrop = document.querySelector('.modal-backdrop');
    if (backdrop) {
        backdrop.remove();
    }
    
    // Quitar la clase modal-open del body
    document.body.classList.remove('modal-open');
    document.body.style.overflow = ''; // restaurar scroll
}

function limpCampos(){
   // document.getElementById('linkMail').innerText = "";
       
    document.getElementById('mensajeText').innerText = "";
    document.getElementById('correoDestino').value="";
    document.getElementById("wmensajeText").innerText = "";
   // document.getElementById("linkWhatsapp").value = "";
    document.getElementById("whatsappLink").href = "#";
    document.getElementById("idAsignacionCompartir").value = "";
  
    document.getElementById('idCotizacionCompartir').value ="";
    document.getElementById('idAsignacionCompartir').value ="";
}
function copiarDesdeInput(inputId) {
    const input = document.getElementById(inputId);
    input.select();
    input.setSelectionRange(0, 99999);
    document.execCommand("copy");
    alert("¡Enlace copiado!");
}


function enviarMailCoordenadas() {

    const mensaje = document.getElementById('mensajeText').innerText;
    const asunto = mensaje;

    const link = "";
    const correo = document.getElementById('correoDestino').value;
 
    fetch('/coordenadas/cotizaciones/mail-coordenadas', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({
            correo: correo,
            asunto: asunto,
            mensaje: mensaje,
            link:  link
        })
    })
    .then(res => res.json())
    .then(data => alert('Correo enviado ✅'))
    .catch(err => console.error('Error:', err));
   
    
}
function guardarYAbrirWhatsApp(event) {
    event.preventDefault(); // Evita que el enlace se abra inmediatamente
   
    window.open(document.getElementById('whatsappLink').href, '_blank');
       
}