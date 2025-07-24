
    let contenedoresGuardados ;  
    let contenedoresGuardadosTodos ;  
let contenedoresDisponibles = [];
let userBloqueo = false;
const seleccionados = [];
const ItemsSelects = [];

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
function BloquearHabilitarEdicion(block){
    document.getElementById('id_convoy').readOnly = block;
    document.getElementById('nombre').readOnly = block;
    document.getElementById('fecha_inicio').readOnly = block;
    document.getElementById('fecha_fin').readOnly = block;




}

function abrirMapaEnNuevaPestana( contenedor,tipoS) {
        const url = `/coordenadas/mapa_rastreo?contenedor=${contenedor}&tipoS=${encodeURIComponent(tipoS)}`;
    window.open(url, '_blank');
}
document.addEventListener('DOMContentLoaded', function () {
     let gridApi;
     cargarinicial();
    definirTable();

  const modal = new bootstrap.Modal(document.getElementById('modalBuscarConvoy'), {
        backdrop: 'static',
        keyboard: false
    });


document.getElementById('btnBuscarconboy').addEventListener('click', function () {
        modal.show();
    });

document.getElementById("btnNuevoconboy").addEventListener("click", () => {
    limpiarFormulario();
    // Aquí abres el modal, por ejemplo con Bootstrap 5:
    const modal = new bootstrap.Modal(document.getElementById('CreateModal'));
    modal.show();
});

document.getElementById('formBuscarConvoy').addEventListener('submit', function (e) {
        e.preventDefault();

        const numero = document.getElementById('numero_convoy').value;

        fetch(`/coordenadas/conboys/getconvoy/${numero}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                   let  tablalimpiar = document.getElementById("tablaContenedoresBodyBuscar"); 
    tablalimpiar.innerHTML = "";
    seleccionados.length = 0;
    ItemsSelects.length = 0;


                    const fechaInicio = new Date(data.data.fecha_inicio);
                    const fechaFin = new Date(data.data.fecha_fin);

                    let blockUser = data.data.BockUser 
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
                         seleccionarContenedor2(contenedor.num_contenedor)
                         
                        });
                  
                   

                    document.getElementById('resultadoConvoy').style.display = 'block';
                } else {
                    alert("Convoy no encontrado.");
                }
            });
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


    document.getElementById('tipo_disolucion').addEventListener('change', function () {
            const tipo = this.value;
            document.querySelectorAll('.tipo-campo').forEach(el => el.style.display = 'none');

            if (tipo === 'geocerca') {
               
                document.getElementById('geocercaConfig').style.display = 'block';
            } else if (tipo === 'tiempo') {
                //document.getElementById('campo-tiempo').style.display = 'block';
            } 
        });
});
function abrirGeocerca() {
  const url = '/configurar-geocerca'; 
  const win = window.open(url, 'ConfigurarGeocerca', 'width=800,height=600');
}

function setGeocercaData(lat, lng, radio) {
    document.getElementById('geocerca_lat').value = lat;
    document.getElementById('geocerca_lng').value = lng;
    document.getElementById('geocerca_radio').value = radio;

    alert('Geocerca guardada correctamente');
}
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

                    const modal = bootstrap.Modal.getInstance(document.getElementById('modalBuscarConvoy'));
                    modal.hide();

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

function definirTable(){
  const columnDefs = [
        {
        headerCheckboxSelection: true,
        checkboxSelection: true,
        width: 50, 
        pinned: "left", 
        suppressSizeToFit: true,
        resizable: false
        },
       
        { headerName: "Convoy", field: "no_conboy", sortable: true, filter: true },
        { headerName: "Descripcion", field: "nombre", sortable: true, filter: true },
        { headerName: "Fecha Inicio", field: "fecha_inicio", sortable: true, filter: true },
        { headerName: "Fecha Fin", field: "fecha_fin", sortable: true, filter: true },
       
        

       {
    headerName: "Acciones",
   
   
    cellRenderer: function (params) {
        const container = document.createElement("div");
      //  container.classList.add("d-flex", "flex-wrap", "gap-1", "justify-content-start");
        const data = params.data;

        // Botón Editar
        const btnEditar = document.createElement("button");
        btnEditar.innerHTML = `<i class="fas fa-edit text-white"></i>`;
       
        btnEditar.title ="Editar";
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

            BloquearHabilitarEdicion(data.BlockUser);
 
            const contenedoresFiltrados = contenedoresGuardados.filter(item => item.conboy_id == data.id);

         // Llenas la tabla con los contenedoresFiltrados
            llenarTablaContenedores(contenedoresFiltrados,data.BlockUser);
            const modal = new bootstrap.Modal(document.getElementById("CreateModal"));
            modal.show();
        };

        // Botón Compartir
        const btnCompartir = document.createElement("button");
        btnCompartir.innerText = "🔗";
        btnCompartir.title ="Compartir"
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
    //boton rastreo contenedores de los convoys
    const btnRastreo = document.createElement("button");
    btnRastreo.type = "button";
    btnRastreo.classList.add("btn", "btn-sm", "btn-success");
    btnRastreo.title = "Rastrear contenedor";
    btnRastreo.id = "btnRastreo";

    // Añadir ícono + texto como HTML
    btnRastreo.innerHTML = `<i class="fa fa-shipping-fast me-1"></i>`;

    // Evento onclick personalizado (usa el contenedor que necesitas)
    btnRastreo.onclick = function () {
        const contenedoresDelConvoy = contenedoresGuardadosTodos
    .filter(c => c.conboy_id === data.id)
    .map(c => c.num_contenedor);
    const listaStr = contenedoresDelConvoy.join(' / ');
        let tipos= "Convoy: " + data.no_conboy;
        abrirMapaEnNuevaPestana(listaStr,tipos); 
    };
 //boton cam cbiar estatus de los convoys
   const btnEstatus = document.createElement("button");
btnEstatus.type = "button";
btnEstatus.classList.add("btn", "btn-sm", "btn-outline-primary");
btnEstatus.title = "Cambio estatus";
btnEstatus.id = "btnEstatus";
btnEstatus.innerHTML = `<i class="fa fa-sync-alt me-1"></i>`;
 btnEstatus.setAttribute("data-id", data.id);

   btnEstatus.onclick = function () {

    const modalElement = document.getElementById('modalCambiarEstatus');
modalElement.setAttribute("data-id", this.dataset.id);


    const modal = new bootstrap.Modal(document.getElementById('modalCambiarEstatus'));
   
    
    modal.show();
};

            container.appendChild(btnEditar);
            container.appendChild(btnCompartir);
            container.appendChild(btnRastreo);
            container.appendChild(btnEstatus);

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


function llenarTablaContenedores(contenedores,val) {
    const tabla = document.getElementById("tablaContenedoresBody"); // tbody
    tabla.innerHTML = "";

seleccionados.length = 0;
    ItemsSelects.length = 0;

     

    contenedores.forEach((item, i) => {
        const row = document.createElement("tr");
         let botonEliminar = '';
        if (!val) {
            botonEliminar = `
                    <button type="button" class="btn btn-sm btn-danger" onclick="confirmarEliminacion('${item.id_contenedor}', '${item.conboy_id}', this,${i})">
                        <i class="bi bi-trash"></i> 
                    </button>
                `;
           
        }

        row.innerHTML = `
            <td>${item.num_contenedor}</td>
            <td>${botonEliminar}</td>
        `;

        tabla.appendChild(row);

        seleccionados.push(item.num_contenedor);
        ItemsSelects.push(`${item.num_contenedor}-${item.id_contenedor}-${item.imei}`);
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
                    contenedoresGuardadosTodos = data.dataConten2;
                    
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
let tipo_disolucion = document.getElementById('tipo_disolucion').value ;
let geocerca_lat = document.getElementById('geocerca_lat').value ;
let geocerca_lng = document.getElementById('geocerca_lng').value ;
let geocerca_radio = document.getElementById('geocerca_radio').value ;

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
  tipo_disolucion :tipo_disolucion,
            geocerca_lat:geocerca_lat,
            geocerca_lng :geocerca_lng,
            geocerca_radio:geocerca_radio,

};
let urlSave ="/coordenadas/conboys/store";

if (idconvoy != ""){
    urlSave ="/coordenadas/conboys/update";
}

  saveconvoys(datap,urlSave);


  
 
});
  document.getElementById("btnGuardarCambios").addEventListener("click", function () {

    const form = document.getElementById("formCambiarEstatus");
    const formData = new FormData(form);
    const modal = document.getElementById("modalCambiarEstatus");
const id = modal.getAttribute("data-id");
    formData.append("idconvoy", id);

    fetch("/coordenadas/conboys/estatus", {
        method: "POST",
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
             Swal.fire({
                        title: 'Cambio de Estatus realizado correctamente',
                        text: data.message + ' ' + data.no_conboy,
                        icon: 'success',
                        confirmButtonText: 'Aceptar',
                        timer: 1500
                    }).then(() => {

                    setTimeout(() => {
                        window.location.reload(); 
                    }, 300); 

                    });
            const modal = bootstrap.Modal.getInstance(document.getElementById("modalCambiarEstatus"));
            modal.hide();
        } else {
            // Mostrar errores
            alert("Ocurrió un error al guardar");
        }
    })
    .catch(error => {
        console.error("Error en el envío AJAX:", error);
    });
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
        confirmButtonText: 'Aceptar',
        timer: 1500
    }).then(() => {

      setTimeout(() => {
        window.location.reload(); 
    }, 300); 

    });


     
    
  })
  .catch(error => {
    console.error('Error al guardar un conboy:', error);
    
  });
}




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

         ItemsSelects.push(valor +"-" + contenedorData.id_contenedor+'-'+ contenedorData.imei);
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

    function mostrarSugerencias2() {
        const input = document.getElementById('contenedor-input2');
        const filtro = input.value.trim().toUpperCase();
        const sugerenciasDiv = document.getElementById('sugerencias2');
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
            item.onclick = () => seleccionarContenedor2(c.num_contenedor);
            sugerenciasDiv.appendChild(item);
        });

        sugerenciasDiv.style.display = filtrados.length ? 'block' : 'none';
    }
 function seleccionarContenedor2(valor) {
        seleccionados.push(valor);
         const contenedorData = contenedoresDisponibles.find(c => c.num_contenedor === valor);

         ItemsSelects.push(valor +"-" + contenedorData.id_contenedor+'-'+ contenedorData.imei);
        document.getElementById('contenedor-input2').value = '';
        document.getElementById('sugerencias2').style.display = 'none';
        actualizarVista2();
    }

    function agregarContenedor2() {
        const input = document.getElementById('contenedor-input2');
        const valor = input.value.trim().toUpperCase();
        if (valor && contenedoresDisponibles.includes(valor) && !seleccionados.includes(valor)) {
            seleccionados.push(valor);
           
            input.value = '';
            actualizarVista2();
        }
    }

       function actualizarVista2() {
        const tbody = document.querySelector('#tablaContenedoresBuscar tbody');
     tbody.innerHTML = '';
         seleccionados.forEach((cont, i) => {
        const row = document.createElement('tr');
        row.innerHTML = `
          
            <td>${cont}</td>
            <td>
                <button type="button" 
                        class="btn btn-sm btn-danger"
                        onclick="eliminarContenedor2(${i})">
                     <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
    

        document.getElementById('contenedores').value = seleccionados.join(';');
        document.getElementById('ItemsSelects').value = ItemsSelects.join(';');
    }

     function eliminarContenedor2(idx) {
        seleccionados.splice(idx, 1);
          ItemsSelects.splice(idx, 1);
        actualizarVista2();
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

function mostrarTab(tab, event) {
    event.preventDefault();

    // Ocultar ambos
    document.getElementById('tab-mail').style.display = 'none';
    document.getElementById('tab-whatsapp').style.display = 'none';

    // Quitar clase activa
    const tabs = document.querySelectorAll('.nav-link');
    tabs.forEach(el => el.classList.remove('active'));

    // Mostrar el tab seleccionado
    document.getElementById(`tab-${tab}`).style.display = 'block';

    // Activar tab
    event.currentTarget.classList.add('active');
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


    function limpiarFormularioConvoy2() {
    // Limpiar tabla de contenedores
    const tbody = document.getElementById('tablaContenedoresBuscar');
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

function confirmarEliminacion(idContenedor, idConvoy, boton,idx) {
    Swal.fire({
        title: '¿Eliminar contenedor?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/coordenadas/conboys/eliminar-contenedor/${idContenedor}/${idConvoy}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) throw new Error("Error al eliminar");
                return response.json();
            })
            .then(data => {
                Swal.fire('¡Eliminado!', 'El contenedor ha sido eliminado.', 'success');

               eliminarContenedor(idx);
                const fila = boton.closest('tr');
                fila.remove();
            })
            .catch(error => {
                console.error(error);
                Swal.fire('Error', 'No se pudo eliminar el contenedor.', 'error');
            });
        }
    });
}