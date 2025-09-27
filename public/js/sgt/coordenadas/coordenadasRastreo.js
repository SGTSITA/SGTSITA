
let equiposSearch = [];
let rastreosActivos = {};
let map;
const estadosLi = {};
  let markers = [];
  let elementoPanelRastro =[];
  let catalogoBusqueda = [];
let contenedoresDisponiblesAll =[];
let mapaAjustado = false;

let detalleConvoys;
let contenedoresDisponibles = [];
 let directionsService = null;
let directionsRenderer = [];

 let ItemsSelectsID = {};
      let intervalIdsID = {};
  
 function googleMapsReady() {
    
            initMap();
        }
  function initMap() {
    directionsService = new google.maps.DirectionsService();

    map = new google.maps.Map(document.getElementById("map"), {
      center: { lat: 0, lng: 0 },
      zoom: 2,
    });

    

     const marker = new google.maps.Marker({
    position: { lat: 0, lng: 0 },
    map: map,
  });
  }
  
  function cargaConboys2(fecha_inicio, fecha_fin)
    { 
        const overlay = document.getElementById("gridLoadingOverlay");
        overlay.style.display = "flex";
    
                
            gridApi2.setGridOption("rowData", []); 

            fetch(`/coordenadas/conboys/getconboysFinalizados?inicio=${fecha_inicio}&fin=${fecha_fin}`)
                .then(response => response.json())
                .then(data => {
                                      const rowData = data.data;
                    gridApi2.setGridOption("rowData", rowData);
                    
                    
                })
                .catch(error => {
                    console.error("❌ Error al obtener la lista de convoys grid 2:", error);
                })
                .finally(() => {
                    overlay.style.display = "none"; 
                });
    }
function cargarConvoysEnSelect(convoys) {
  const select = document.getElementById('convoys');

  // Limpiar opciones actuales excepto la primera
  select.innerHTML = '<option value="">Seleccione un convoy</option>';

  convoys.forEach(convoy => {
    const option = document.createElement('option');
    option.value = convoy.id; 
    option.textContent = `${convoy.no_conboy} - ${convoy.nombre}`;
    select.appendChild(option);
  });
}
function cargarEquiposEnSelect(dataequipos) {
  const select = document.getElementById('Equipo');

  // Limpiar opciones actuales excepto la primera
  select.innerHTML = '<option value="">Seleccione un equipo</option>';

  dataequipos.forEach(equipo => {
    const option = document.createElement('option');
    option.value = `${equipo.id_equipo}|${equipo.imei}|${equipo.id}|${equipo.tipoGps}`;
    const textoPlaca = equipo.placas?.trim() ? equipo.placas : 'SIN PLACA';
    option.textContent = `${equipo.id_equipo } - ${equipo.marca}- ${equipo.tipo}- ${textoPlaca}`;
    select.appendChild(option);
  });
}
function cargarinicial()
{
       fetch(`/coordenadas/contenedor/searchEquGps?`)
      .then(response => response.json())
    .then(data => {
        
         contenedoresDisponibles   = data.datos;

    detalleConvoys =   data.dataConten;

    contenedoresDisponiblesAll = data.datosAll;
          
    equiposSearch = data.equiposAll;
        // Convoys detalle
        data.conboys.forEach(c => {
          catalogoBusqueda.push({
            tipo: 'Convoy',
            label: c.no_conboy +" " + c.nombre, 
            value: c.no_conboy,
            id: c.id,
            value_chasis: `NO DISPONIBLE|`,
            llegada:c.geocerca_lat+"|"+c.geocerca_long+"|"+c.geocerca_radio
          });
        });
        // Contenedores (desde convoysDetalle)
        contenedoresDisponibles.forEach(cd => {
          catalogoBusqueda.push({
            tipo: 'Contenedor',
            label: cd.contenedor,
            value: cd.contenedor +"|" +cd.imei+"|"+ cd.id_contenedor+"|"+ cd.tipoGps,
            id: cd.id_contenedor,
            value_chasis:cd.contenedor +"|" +cd.imei_chasis+"|"+ cd.id_contenedor+"|"+ cd.tipoGpsChasis,
            llegada: cd.latitud +"|"+ cd.longitud+"|0"
          });
        });

        // Equipos (si tienes un array separado)
         data.equipos.forEach(eq => {
           const textoPlaca = eq.placas?.trim() ? eq.placas : ''
          catalogoBusqueda.push({
            tipo: 'Equipo',
            
            label: `${eq.id_equipo } - ${eq.marca} - ${eq.tipo} - ${textoPlaca}`,
            value: `${eq.id_equipo}|${eq.imei}|${eq.id}|${eq.tipoGps}`,
            id: eq.id,
            value_chasis: `NO DISPONIBLE|`,
            llegada: `0|0|0`
          });
        });
          
      })
  .catch(error => {
          console.error('Error al traer coordenadas:', error);
    });
}


function obtenerImeisPorConvoyId(convoyId) {
    let itemFiltrado = detalleConvoys
    .filter(item => item.conboy_id == convoyId && item.imei && item.id_contenedor)
    .map(item => item.num_contenedor + "|" + item.imei + "|" + item.id_contenedor+"|"+item.tipoGps);

   let itemFiltradoChasis = detalleConvoys
    .filter(item => item.conboy_id == convoyId && item.imei_chasis && item.id_contenedor)
    .map(item => item.num_contenedor + "|" + item.imei_chasis + "|" + item.id_contenedor+"|"+item.tipoGpsChasis);

    return [...itemFiltrado, ...itemFiltradoChasis];
}

function obtenerTabActivo() {
    const tabActivo = document.querySelector('#filtroTabs .nav-link.active');
    return tabActivo ? tabActivo.getAttribute('data-bs-target') : null;
}



const input = document.getElementById("buscadorGeneral");
const resultados = document.getElementById("resultadosBusqueda");
const chipContainer = document.getElementById("chipsBusqueda");

let filtroActivo = null;

function getRandomColor() {
    const min = 127;
    const max = 200; 
    const r = Math.floor(Math.random() * (max - min + 1)) + min;
    const g = Math.floor(Math.random() * (max - min + 1)) + min;
    const b = Math.floor(Math.random() * (max - min + 1)) + min;
    return `rgb(${r}, ${g}, ${b})`;
}
function getStrongColor() {
  // Hue (0-360): distinto tono
  const hue = Math.floor(Math.random() * 365);
  // Saturation alto (70–100%)
  const saturation = 90;
  // Lightness medio (40–50%) → ni muy claro ni muy oscuro
  const lightness = 65;
  
  return `hsl(${hue}, ${saturation}%, ${lightness}%)`;
}

function strengthenColor(rgb) {
  const [r, g, b] = rgb.match(/\d+/g).map(Number);
  // Escalar los valores hacia un rango de 100–220
  const scale = (val) => {
    if (val < 100) return 100;
    if (val > 220) return 220;
    return val;
  };
  return `rgb(${scale(r)},${scale(g)},${scale(b)})`;
}
function lightenColor(rgb, amount = 40) {
  const [r, g, b] = rgb.match(/\d+/g).map(Number);
  const nr = Math.min(255, r + amount);
  const ng = Math.min(255, g + amount);
  const nb = Math.min(255, b + amount);
  return `rgb(${nr},${ng},${nb})`;
}

function darkenColor(rgb, amount = 40) {
  const [r, g, b] = rgb.match(/\d+/g).map(Number);
  const nr = Math.max(0, r - amount);
  const ng = Math.max(0, g - amount);
  const nb = Math.max(0, b - amount);
  return `rgb(${nr},${ng},${nb})`;
}

function createMarkerIcon(color = "#FF0000", size = 40) {
const svg = `
    <svg width="${size}" height="${size}" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
      <!-- Pin principal -->
      <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"
            fill="${color}" stroke="white" stroke-width="2"/>
      <!-- Círculo animado central -->
      <circle cx="12" cy="9" r="3" fill="white">
        <animate attributeName="r" values="3;6;3" dur="1s" repeatCount="indefinite" />
      </circle>
    </svg>
  `;
  return {
    url: "data:image/svg+xml;charset=UTF-8," + encodeURIComponent(svg),
    scaledSize: new google.maps.Size(size, size)
  };
}

function validarTipo(items)
{
    let tabx = items.tipo;
    let labelMuestra = items.label;
    let value = items.value;
  
    if (Array.isArray(ItemsSelectsID[items.id + "|"+  items.value])) 
      {
          ItemsSelectsID[items.id + "|"+  items.value].length = 0;
      } else {
          ItemsSelectsID[items.id + "|"+  items.value] = []; 
      }
   ItemsSelectsID[items.id + "|"+  items.value] = items.value;
   if(items.value_chasis && items.value_chasis !== 'NO DISPONIBLE|'){   
       ItemsSelectsID[items.id + "|"+  items.value] = items.value_chasis;   
   }
   ItemsSelectsID[items.id + "|"+  items.value] = items.value;
    
    if ( tabx==='Convoy'){
      ItemsSelectsID[items.id + "|"+  items.value] = obtenerImeisPorConvoyId(items.id);
    }
    mapaAjustado= false;

    elementoPanelRastro.push({
      id:items.id,
    tipo: tabx,
    value: items.value,
    label: labelMuestra
    });

catalogoBusqueda = catalogoBusqueda.filter(itemFilter => itemFilter.id !== items.id &&  itemFilter.value !== items.value);




const listaPanel = document.getElementById("ElementosRastreoPanel");

elementoPanelRastro.forEach(item => {
    const existe = Array.from(listaPanel.children).some(li =>
        li.dataset.valor === item.id + "|"+  item.tipo
    );

    if (!existe) {
        const li = document.createElement("li");
        li.classList.add(
        "list-group-item",
        "p-0", 
        "mb-2"
        );
        li.dataset.valor = item.id + "|" + item.tipo;

        const randomColor = getRandomColor();
        li.style.backgroundColor = randomColor;
        li.style.color = "white";
        // Texto a la izquierda
     
        
        estadosLi[`${item.id}|${item.tipo}`] = false;


  const content = document.createElement("div");
content.classList.add(
  "d-flex",
  "justify-content-between",
  "align-items-center",
  "p-2"
);

const spanTexto = document.createElement("span");
spanTexto.textContent = `${item.tipo} #${item.label}`;
content.appendChild(spanTexto);

        //validar primero si es convoy
        if (item.tipo === "Convoy") {
            const switchHeader = document.createElement("div");
            switchHeader.style.backgroundColor = "rgba(0,0,0,0.3)";
            switchHeader.style.color = "white";
            switchHeader.style.display = "flex";
            switchHeader.style.alignItems = "center";
            switchHeader.style.justifyContent = "center";
            switchHeader.style.height = "28px";
            switchHeader.style.width = "100%";

            const inputSwitch = document.createElement("input");
            inputSwitch.type = "checkbox";
            inputSwitch.classList.add("form-check-input", "me-2");

            const label = document.createElement("span");
            label.textContent = "Rastreo Individual";

           inputSwitch.addEventListener("change", function () {
            if (this.checked) {
                    label.textContent = "Rastrear Grupo";
                    estadosLi[`${item.id}|${item.tipo}`] = this.checked;

                } else {
                    label.textContent = "Rastreo Individual";
                    estadosLi[`${item.id}|${item.tipo}`] = this.checked;
                }

                 actualizarUbicacion(ItemsSelectsID[items.id + "|"+  items.value],tabx,items.id + "|"+  items.value + "|"+  item.tipo,labelMuestra,value,map,items.id,randomColor,estadosLi[`${item.id}|${item.tipo}`])


                  intervalIdsID[`${item.id}|${item.value}`] = setInterval(() => {
                     actualizarUbicacion(ItemsSelectsID[items.id + "|"+  items.value],tabx,items.id + "|"+  items.value + "|"+  item.tipo,labelMuestra,value,map,items.id,randomColor,estadosLi[`${item.id}|${item.tipo}`])
                     rastreosActivos[`${item.id}|${item.value}|${item.tipo}`] = true;
                  }, 5000);
            });

            switchHeader.appendChild(inputSwitch);
            switchHeader.appendChild(label);
            li.appendChild(switchHeader);

        }
            
      


        // Botón pausar/reanudar
        const btnPausar = document.createElement("button");
        btnPausar.classList.add("btn", "btn-sm", "btn-warning", "p-1");
        btnPausar.style.width = "35px";
        btnPausar.style.height = "35px";
        btnPausar.style.display = "flex";
        btnPausar.style.alignItems = "center";
        btnPausar.style.justifyContent = "center";
        btnPausar.innerHTML = '<i class="bi bi-pause-fill"></i>';
        btnPausar.id = `${item.id}|${item.value}`;

        // Evento click pausar/reanudar
        btnPausar.addEventListener("click", () => {
            const icon = btnPausar.querySelector("i");
            if (btnPausar.classList.contains("btn-warning")) {
                btnPausar.classList.replace("btn-warning", "btn-success");
                icon.classList.replace("bi-pause-fill", "bi-play-fill");
                console.log(`${item.id}|${item.value} pausado`);
            } else {
                btnPausar.classList.replace("btn-success", "btn-warning");
                icon.classList.replace("bi-play-fill", "bi-pause-fill");
                console.log(`${item.id}|${item.value} reanudado`);
            }

              if (intervalIdsID[`${item.id}|${item.value}`]) {
                  clearInterval(intervalIdsID[`${item.id}|${item.value}`]);
                 intervalIdsID[`${item.id}|${item.value}`] = null;
                    estadosLi[`${item.id}|${item.tipo}`] = false
                 rastreosActivos[`${item.id}|${item.value}|${item.tipo}`] = false;
              } else {
                 actualizarUbicacion(ItemsSelectsID[items.id + "|"+  items.value],tabx,items.id + "|"+  items.value + "|"+  item.tipo,labelMuestra,value,map,items.id,randomColor,estadosLi[`${item.id}|${item.tipo}`])


                  intervalIdsID[`${item.id}|${item.value}`] = setInterval(() => {
                     actualizarUbicacion(ItemsSelectsID[items.id + "|"+  items.value],tabx,items.id + "|"+  items.value + "|"+  item.tipo,labelMuestra,value,map,items.id,randomColor,estadosLi[`${item.id}|${item.tipo}`])
                     rastreosActivos[`${item.id}|${item.value}|${item.tipo}`] = true;
                  }, 5000);

                 
              }
        });

      

        // Botón eliminar
        const btnEliminar = document.createElement("button");
        btnEliminar.classList.add("btn", "btn-sm", "btn-danger", "p-1");
        btnEliminar.style.width = "35px";
        btnEliminar.style.height = "35px";
        btnEliminar.style.display = "flex";
        btnEliminar.style.alignItems = "center";
        btnEliminar.style.justifyContent = "center";
        btnEliminar.innerHTML = '<i class="bi bi-x"></i>';
        // Agregar botones al contenedor

        const btnGroup = document.createElement("div");
        btnGroup.appendChild(btnPausar);
        btnGroup.appendChild(btnEliminar);

        content.appendChild(btnGroup);

                
                li.appendChild(content);

        // finalmente añadimos a lista
        listaPanel.appendChild(li);


  btnEliminar.addEventListener("click", () => {
    let valorLi = li.dataset.valor;
   
    let [idStr, tipoStr] = valorLi.split("|");

    rastreosActivos[`${item.id}|${item.value}|${item.tipo}`] = false;
    let elementoEliminado = elementoPanelRastro.find(
        item => String(item.id) === idStr && String(item.tipo) === tipoStr
    );

    if (elementoEliminado) {
       
        elementoPanelRastro = elementoPanelRastro.filter(
            item => String(item.id) !== idStr || String(item.tipo) !== tipoStr
        );

      
        if (!catalogoBusqueda.some(
            el => el.id === elementoEliminado.id && el.value === elementoEliminado.value
        )) {
            catalogoBusqueda.push(elementoEliminado);
        }

        const claveBase = items.id + "|" + items.value+"|"+  item.tipo;
let borro = false;
 
   clearInterval(intervalIdsID[`${item.id}|${item.value}`]);
                 intervalIdsID[`${item.id}|${item.value}`] = null;
                    estadosLi[`${item.id}|${item.tipo}`] = false;

        Object.keys(markers).forEach(key => {

            if (key.startsWith(claveBase + "|")) {
                markers[key].setMap(null);
                               delete ItemsSelectsID[markers[key].keyItem];
                                delete markers[key];
                borro = true;
            }
        });

        if (borro) {
            li.remove();
        }

           
//ItemsSelectsID[items.id + "|"+  items.value]
        console.log(`${elementoEliminado.tipo} #${elementoEliminado.label} eliminado`);
    }
});
//alert('pasa siempre');


actualizarUbicacion(ItemsSelectsID[items.id + "|"+  items.value],tabx,items.id + "|"+  items.value + "|"+  item.tipo,labelMuestra,value,map,items.id,randomColor,estadosLi[`${item.id}|${item.tipo}`])

 intervalIdsID[`${item.id}|${item.value}`] = setInterval(() => {
                     actualizarUbicacion(ItemsSelectsID[items.id + "|"+  items.value],tabx,items.id + "|"+  items.value + "|"+  item.tipo,labelMuestra,value,map,items.id,randomColor,estadosLi[`${item.id}|${item.tipo}`])
rastreosActivos[`${item.id}|${item.value}|${item.tipo}`] = true;
                  }, 5000);
        
        }
});

   

    
}

function eliminarDelPanel(id) {
    let elementoEliminado = elementoPanelRastro.find(item => item.id === id);
    if (elementoEliminado) {
        elementoPanelRastro = elementoPanelRastro.filter(item => item.id !== id);
        li.remove();
        let index = markers.findIndex(m => m.keyItem === items.id + "|" + items.value + "|" + item.tipo);
        if (index !== -1) {
            markers[index].setMap(null);
            markers.splice(index, 1);
            delete ItemsSelectsID[items.id + "|" + items.value];
        }
        console.log(`${elementoEliminado.tipo} #${elementoEliminado.label} eliminado`);
    }
}

input.addEventListener('input', function () {
   
const query = this.value.trim().toLowerCase();
    resultados.innerHTML = '';
    chipContainer.innerHTML = '';
    filtroActivo = null;

    if (query.length < 2) {
     //detener();
     // limpiarMarcadores();
      return;
    }

    const coincidencias = catalogoBusqueda.filter(item =>
        item.label.toLowerCase().includes(query)
    );

    if (coincidencias.length === 0) {
        const div = document.createElement('div');
        div.classList.add('dropdown-item', 'text-muted');
        div.textContent = 'Sin resultados';
        resultados.appendChild(div);
        return;
    }

    // Mostrar chips por tipo
 const tiposUnicos = [...new Set(coincidencias.map(item => item.tipo))];
    tiposUnicos.forEach(tipo => {
        const chip = document.createElement('button');
        chip.className = 'btn btn-outline-secondary btn-sm rounded-pill me-2 mb-1';
        chip.textContent = tipo;
        chip.onclick = () => {
            filtroActivo = tipo;
            //document.getElementById('tituloSeguimiento').textContent = 'Seguimiento '+  tipo;
            document.querySelectorAll('#chipsBusqueda .btn').forEach(btn => btn.classList.remove('active'));
            chip.classList.add('active');
            mostrarResultadosFiltrados(query);
        };
        chipContainer.appendChild(chip);
    });

    mostrarResultadosFiltrados(query);
});


function mostrarResultadosFiltrados(query) {
    resultados.innerHTML = '';
    const sugerencias = catalogoBusqueda
        .filter(item =>
            item.label.toLowerCase().includes(query) &&
            (!filtroActivo || item.tipo === filtroActivo)
        )
        .slice(0, 10);

    sugerencias.forEach(item => {
        const div = document.createElement('div');
        div.classList.add('dropdown-item');
        div.textContent = `${item.label}`;
        div.onclick = () => {
          //document.getElementById('tituloSeguimiento').textContent = 'Seguimiento '+  item.tipo;
          // document.querySelectorAll('#chipsBusqueda .btn').forEach(btn => {
          //       if (btn.textContent.trim() === item.tipo) {
          //         btn.classList.add('active');
          //       } else {
          //         btn.classList.remove('active');
          //       }
          //     });

//input.value =item.label;
input.value='';
document.getElementById('resultadosBusqueda').innerHTML = '';
chipContainer.innerHTML = '';
//input.dispatchEvent(new Event('input'));
          validarTipo(item)
        
        };
        resultados.appendChild(div);
    });
}


  
function actualizarUbicacionReal(coordenadaData){
    fetch('/coordenadas/rastrear/savehistori', {
        method: 'POST',
         headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    },
    body: JSON.stringify(coordenadaData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Coordenada guardada:', data.data);
        } else {
            console.warn('Error al guardar coordenada', data);
        }
    })
    .catch(error => {
        console.error('Error en la solicitud:', error);
    });

}

 function actualizarUbicacion(imeis,t,KEYITEM,labelMuestra,num_convoy,map,idProceso,colorBG,estado) {
  console.log('obteniendo ubicacion convoy :', KEYITEM);


  let partes = KEYITEM.split("|");


let id = partes[0];
let value = partes[1];


let keyInterval = id + "|" + value;

let tipo = "";

    let responseOk = false;
  fetch("/coordenadas/ubicacion-vehiculo", {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    },
    body: JSON.stringify({ imeis: imeis})
  })
  .then(res => res.json())
  .then(data => {
    //console.log('Ubicaciones recibidas:', data);


    if(rastreosActivos[`${KEYITEM}`]===false){
        console.log('Rastreo eliminado', KEYITEM);
        return;

        }
    const dataUbi= data;
  console.log('obteniendo unicacion convoy, sucess data :', KEYITEM);
//limpiarMarcadores();
  responseOk = true;
    if (Array.isArray(dataUbi)) {
      dataUbi.forEach((item, index) => {
        tipo= item.tipogps;
         let latlocal ='';
        let lnglocal='';
        let nEconomico='';
        let id_contenConvoy ='';
       

          //console.log('For response ... :', KEYITEM);
 
       //   let datosGeocerca = convoysAll.find(c => c.no_conboy === num_convoy)
         
       
        latlocal =parseFloat( item.ubicacion.lat);
        lnglocal =parseFloat( item.ubicacion.lng);
        idConvoyOContenedor=  item.id_contenendor;
        tipo= tipo + ' '+ item.contenedor;
          // if(datosGeocerca ) {

          // actualizarMapa(latlocal, lnglocal,datosGeocerca,idConvoy,map)
          // }
            let continueShowing = true;

        if (continueShowing){
          //console.log('continuar agregando marcador:', KEYITEM+"|"+item.contenedor+"|"+item.ubicacion.tipoEquipo);
        if (markers[KEYITEM+"|"+item.contenedor+"|"+item.ubicacion.tipoEquipo]) {

                markers[KEYITEM+"|"+item.contenedor+"|"+item.ubicacion.tipoEquipo].setPosition({ lat: latlocal, lng: lnglocal });
        } else {
        if (latlocal !==0 && lnglocal !==0) {

          let  colorMarker =colorBG;

          if(item.ubicacion.tipoEquipo==='Camion'){
            colorMarker = getStrongColor();
          }else{
            colorMarker = getStrongColor();
          }

       //   console.log('Agregando marcador:', item.ubicacion.tipoEquipo);

          // let esMostrarPrimero =  1
          // if(esMostrarPrimero){
            const newMarker = new google.maps.Marker({
              position: { lat: latlocal, lng: lnglocal },
              map: map,
               icon: createMarkerIcon(colorMarker, 40),
            });

            newMarker.keyItem = KEYITEM+"|"+item.contenedor+"|"+item.ubicacion.tipoEquipo;

             let  contentC ='';
              if(t === 'Equipo'){
               // `${eq.id_equipo } - ${eq.marca}- ${eq.tipo}- ${textoPlaca}`,
               const equipo = labelMuestra.split(' - ').map(part => part.trim());
               let idEq= parseInt(item.id_contenendor);
               let filtroEqu= equiposSearch.find(equipo => equipo.id === idEq);
               
                let marcaLocal = equipo[1];
                let placaLocal = equipo[3];
contentC = `
            <div style="
                    background-color: ${colorBG};
                    padding: 5px;
                    border-radius: 8px;
                    font-family: Arial, sans-serif;
                    box-shadow: 0 2px 6px rgba(255, 255, 255, 1);
                    max-width: 240px;
                  ">
              <div style="font-weight: bold; font-size: 17px; margin-bottom: 6px;">
               
              </div>
              <div class="text-white fs-6 lh-base" style="font-size: 17px; line-height: 1.5;">
                <div><strong >Equipo:</strong> ${filtroEqu.id_equipo}</div>
                <div><strong >Marca:</strong> ${filtroEqu.marca}</div>
                <div><strong >Placas:</strong> ${filtroEqu.placas || 'sin placas'}</div>

              </div>
           
            </div>
          `;
              }
              else  if(t==='Contenedor') {
            contentC = `
                <div style="
                        background-color:  ${colorBG};
                        padding: 5px;
                        border-radius: 8px;
                        font-family: Arial, sans-serif;
                        box-shadow: 0 2px 6px  rgba(255, 255, 255, 1);
                        max-width: 270px;
                    ">
                <div style="font-weight: bold; font-size: 17px; margin-bottom: 6px;">
                
                </div>
                <div class="text-white fs-6 lh-base" style="font-size: 17px; line-height: 1.5;">
                    <div><strong >Equipo:</strong> ${item.EquipoBD}</div>
                    <div><strong >Contenedor:</strong> ${item.contenedor}</div>
                </div>
                <button id="btnRuta_${KEYITEM+"|"+item.contenedor+"|"+item.ubicacion.tipoEquipo}" style="margin-top:5px;" class="btn btn-primary mt-2">Mostrar ruta</button><br>
            <span class="text-white fs-6 lh-base" id="infoRuta_${KEYITEM+"|"+item.contenedor+"|"+item.ubicacion.tipoEquipo}" style="font-size:14px; color:#333;"></span>
          `;

              }
              else{
                contentC = `
            <div style="
                    background-color:  ${colorBG};
                    padding: 5px;
                    border-radius: 8px;
                    font-family: Arial, sans-serif;
                    box-shadow: 0 2px 6px  rgba(255, 255, 255, 1);
                    max-width: 270px;
                  "> 
              <div style="font-weight: bold; font-size: 17px; margin-bottom: 6px;">
               
              </div>
              <div class="text-white fs-6 lh-base" style="font-size: 17px; line-height: 1.5;">
              <div><strong >Convoy:</strong> ${num_convoy} </div>
                <div><strong >Equipo:</strong> ${item.EquipoBD}</div>
                <div><strong >Contenedor:</strong> ${item.contenedor}</div>
              </div>
              <button id="btnRuta_${KEYITEM+"|"+item.contenedor+"|"+item.ubicacion.tipoEquipo}" style="margin-top:5px;" class="btn btn-primary mt-2">Mostrar ruta</button><br>
            <span class="text-white fs-6 lh-base" id="infoRuta_${KEYITEM+"|"+item.contenedor+"|"+item.ubicacion.tipoEquipo}" style="font-size:14px; color:#333;"></span>
            </div>
          `;
              }
               // const newMarker = L.marker([latlocal, lnglocal]).addTo(map).bindPopup(tipoSpans + ' '+ item.contenedor).openPopup();
               const infoWindow = new google.maps.InfoWindow({
            content: contentC 
          });
          
           // markers.push(newMarker);
          
        
           newMarker.addListener('click', () => {
 infoWindow.open(map, newMarker);



            const contenedorRes = item.contenedor;
             let info = contenedoresDisponibles.find(d => d.contenedor === contenedorRes);
                if (!info) {
                  if (t.toLowerCase().includes('convoy')) {
                     info = contenedoresDisponiblesAll.find(d => d.contenedor === contenedorRes);
                  }else if(t==='Equipo'){
                   
                    const ahora = new Date();
                       info = contenedoresDisponibles.find(d => {
                            const inicio = new Date(d.fecha_inicio);
                            const fin = new Date(d.fecha_fin);

                            return ahora >= inicio && ahora <= fin;
                        });
                         console.log(info);
                         if (!info) {
                          alert("Información del viaje no encontrada.");
                          return;
                         }
                  }
                }
                let extraInfo = '';

                if (t === 'Equipo') {
                  extraInfo = `
                    <p><strong>IMEI CHASIS:</strong> ${info.imei_chasis}</p>
                           `;
                }

                const contenido = `
                  <div class="p-3">
                    <h5 class="mb-2">🚚 Información del Viaje</h5>
                    <p><strong>Cliente:</strong> ${info.cliente}</p>
                    <p><strong>Contenedor:</strong> ${info.contenedor}</p>
                    <p><strong>Origen:</strong> ${info.origen}</p>
                    <p><strong>Destino:</strong> ${info.destino}</p>
                    <p><strong>Contrato:</strong> ${info.tipo_contrato}</p>
                    <p><strong>Fecha Inicio:</strong> ${info.fecha_inicio}</p>
                    <p><strong>IMEI:</strong> ${info.imei}</p>
                   
                                   ${extraInfo}
                  </div>
                `;

            //  document.getElementById('contenidoModalViaje').innerHTML = contenido;

              
              let infoCMaps=[]; 
              if(t==='Convoy'){

                let contenedoresConvoy = detalleConvoys.filter(d => d.conboy_id === parseInt(id));
                infoCMaps=contenedoresConvoy;
                   mostrarInfoConvoy(contenedoresConvoy,item.EquipoBD,"");
              }else{
                let resultadoComoArray = info ? [info] : [];
                infoCMaps=resultadoComoArray;
               mostrarInfoConvoy(resultadoComoArray,item.EquipoBD,"");
              }
              
//rutas para ver 
   

          google.maps.event.addListenerOnce(infoWindow, 'domready', () => {
        const btn = document.getElementById(`btnRuta_${KEYITEM+"|"+item.contenedor+"|"+item.ubicacion.tipoEquipo}`);
            const infoSpan = document.getElementById(`infoRuta_${KEYITEM+"|"+item.contenedor+"|"+item.ubicacion.tipoEquipo}`);
        btn.addEventListener('click', () => {

            directionsRenderer.forEach(renderer => {
                renderer.setMap(renderer.getMap() ? null : map);
            });

            const position = newMarker.getPosition(); 
                const origin = {
                    lat: position.lat(), 
                    lng: position.lng()
                };


            let latLlegada = parseInt(info.latitud);
            let lngLlegada = parseInt(info.longitud);
            // Si ya existe la ruta, la ocultamos
            if (directionsRenderer[`${KEYITEM+"|"+item.contenedor+"|"+item.ubicacion.tipoEquipo}`]) {
                directionsRenderer[`${KEYITEM+"|"+item.contenedor+"|"+item.ubicacion.tipoEquipo}`].setMap(directionsRenderer[`${KEYITEM+"|"+item.contenedor+"|"+item.ubicacion.tipoEquipo}`].getMap() ? null : map);
                btn.textContent = directionsRenderer[`${KEYITEM+"|"+item.contenedor+"|"+item.ubicacion.tipoEquipo}`].getMap() ? 'Ocultar ruta' : 'Mostrar ruta';
                return;
            }

            // Creamos el DirectionsRenderer
            directionsRenderer[`${KEYITEM+"|"+item.contenedor+"|"+item.ubicacion.tipoEquipo}`] = new google.maps.DirectionsRenderer({ map: map });
            
            const request = {
                origin: origin,
                destination: { lat: latLlegada, lng: lngLlegada },
                travelMode: google.maps.TravelMode.DRIVING
            };
            
            directionsService.route(request, (result, status) => {
                if (status === 'OK') {
                    directionsRenderer[`${KEYITEM+"|"+item.contenedor+"|"+item.ubicacion.tipoEquipo}`].setDirections(result);
                    btn.textContent = 'Ocultar ruta';

                     const leg = result.routes[0].legs[0];
                        infoSpan.textContent = `Distancia: ${leg.distance.text}, Tiempo estimado: ${leg.duration.text}`;
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se ha configurado direccion del mapa en cotizaciones'
                    });
                }
            });
        });
    });

//final de rutas en el mapa

             
            });
          //} //end mostrar primero
           
        
        // if (index === 0) {
        //   map.setCenter({ lat: latlocal, lng: lnglocal });
        // map.setZoom(15);
        // }  
        markers[KEYITEM+"|"+item.contenedor+"|"+item.ubicacion.tipoEquipo] = newMarker;
          if (!mapaAjustado) {
              const bounds = new google.maps.LatLngBounds();
               Object.values(markers).forEach(marker => bounds.extend(marker.getPosition()));
              map.fitBounds(bounds);
              mapaAjustado= true;


              const listener = google.maps.event.addListenerOnce(map, "bounds_changed", function() {
                        if (map.getZoom() > 10) map.setZoom(10); 
                    });

          }
        }

        }// fin de else de validacion imei existe en el array markers
        }


        Object.keys(markers).forEach(key => {
            
                if (key.startsWith(KEYITEM + "|")) {
                    if(estado){
                       if (item.ubicacion.esDatoEmp === 'SI' && key.includes(item.contenedor)) {
                            markers[key].setMap(map); // 
                        } 
                        else {
                             markers[key].setMap(null); // ocultar 
                            console.log('Marcador principal empresa no encontrado:', key);
                        }
                    } else {
                         markers[key].setMap(map);
                    }
                }
          });
            
      /*   if(estado){
             
          
            //eliminar todos los marcadores q correspondel al convoy buscado
          Object.keys(markers).forEach(key => {
                if (key.startsWith(KEYITEM + "|")) {    
                    if( item.ubicacion.esDatoEmp !=='SI' && key.includes(item.contenedor)){ // ocultar todos los que no corresponede
                      markers[key].setMap(null); 
                    console.log('Marcador eliminado:', key);
                      
                    }
                    
                                    }
            });
            
            //tenemos q mostrar grupo, un contenedor que debe ser el de la empresa al menos 1.
        }else{
           Object.keys(markers).forEach(key => {
                if (key.startsWith(KEYITEM + "|")) {    
                    if( item.ubicacion.esDatoEmp !=='SI'){ // volver asignar el correspondiente al mapa todos
                      markers[key].setMap(map); 
                    console.log('Marcador mapa asignado:', key);
                      
                    }
                    
                                    }
            });
        } */



        if (t!=='Convoy') {
            idProceso = 0;
        }

      const datasave = {
          latitud: latlocal,
          longitud: lnglocal,
          ubicacionable_id: idConvoyOContenedor,
          tipo: tipo,
          tipoRastreo: t,
          idProceso:idProceso
      };
        if (idConvoyOContenedor!= "" && latlocal !==0 && lnglocal !==0) {
      actualizarUbicacionReal(datasave)
        }
        
      
      });
    } else {
      console.warn('La respuesta no es un array de ubicaciones:', data);
    }
    
  
  })
  .catch(error => {
    console.error('Error al obtener ubicaciones:', error);
    detener(keyInterval);
  });
}
// Para detener la actualización con un botón
//document.getElementById('btnDetener').addEventListener('click', function() {
//  detener();
//});


function detener(keyInterval){
    if (intervalIdsID[keyInterval]) {
                  clearInterval(intervalIdsID[keyInterval]);
                 intervalIdsID[keyInterval] = null;

                 
              }
}

function mostrarInfoConvoy(contenedores,equipo,chasis) {
  const tabs = document.getElementById("contenedorTabs");
  const content = document.getElementById("contenedorTabsContent");

  tabs.innerHTML = "";
  content.innerHTML = "";
  let info = "";

  contenedores.forEach((contenedor, index) => {
    let tabId =  contenedor.num_contenedor;
    if(!tabId){
        tabId =  contenedor.contenedor;
    }
   

    // Crear pestaña
    tabs.innerHTML += `
      <li class="nav-item" role="presentation">
        <button class="nav-link ${index === 0 ? "active" : ""}" 
                id="${tabId}-tab" 
                data-bs-toggle="tab" 
                data-bs-target="#${tabId}" 
                type="button" 
                role="tab" 
                aria-controls="${tabId}" 
                aria-selected="${index === 0 ? "true" : "false"}">
           ${tabId}
        </button>
      </li>
    `;

      info = contenedoresDisponiblesAll.find(d => d.contenedor === contenedor.num_contenedor);
      if(!info){
        info = contenedoresDisponiblesAll.find(d => d.contenedor === contenedor.contenedor);

      }
       // <p><strong>Contenedor:</strong> ${info.contenedor}</p>
if(info){
let filtroEqu= equiposSearch.find(equipo => equipo.id === info.id_equipo_unico);
    
     let infoContenido = `  
                  <div class="tab-pane fade ${index === 0 ? "show active" : ""}" 
           id="${tabId}" 
           role="tabpanel" 
           aria-labelledby="${tabId}-tab">
                   
                    <p><strong>Cliente:</strong> ${info.cliente}</p>
                  
                    <p><strong>Origen:</strong> ${info.origen}</p>
                    <p><strong>Destino:</strong> ${info.destino}</p>
                    <p><strong>Contrato:</strong> ${info.tipo_contrato}</p>
                    <p><strong>Fecha Inicio:</strong> ${info.fecha_inicio}</p>
                    <p><strong>Fecha Fin:</strong> ${info.fecha_fin}</p>
                    <p><strong>Contacto Entrega:</strong> ${info.cp_contacto_entrega}</p>
                    <p><strong>Operador:</strong> ${info.beneficiario}</p>
                    <p><strong>Telefono:</strong> ${info.telefono_beneficiario}</p>
                    <p>
                        <span style="margin-right: 15px;">
                            <strong>IMEI:</strong> ${info.imei}
                        </span>
                        <strong>Equipo:</strong> ${info.id_equipo}
                        <strong>Placas:</strong> ${filtroEqu.placas}
                    </p>
                    <p>
                        <span style="margin-right: 15px;">
                            <strong>IMEI CHASIS:</strong> ${info.imei_chasis}
                        </span>
                        <strong>Chasis:</strong> ${info.id_equipo_chasis}
                    </p>
                  </div>
                `;


    // Crear contenido
      content.innerHTML += infoContenido;
}else{
    Swal.fire({
        title: 'Información de viaje no disponible',
        text: 'No se encontró información para el contenedor seleccionado.',
        icon: 'warning'
    });
}

  
  });

  const modal = new bootstrap.Modal(document.getElementById('modalInfoViaje'));
  modal.show();
}
function crearurlmapalatitudlongitud(lat, lng) {
    return `https://www.google.com/maps/search/?api=1&query=${lat},${lng}`;
}

// Función para limpiar marcadores
function limpiarMarcadores() {
     markers.forEach(marker => marker.setMap(null)); 
    markers = [];
    
}
function limpiarMarcadoresItemPrincipal(intemDad) {
     markers.forEach(marker => marker.setMap(null)); 
    markers = [];
    
}
 let inicio ='';
 let fin ='';

document.addEventListener('DOMContentLoaded', function () {
      const hoy = moment();
   inicio = hoy.clone().subtract(10, 'days'); 
     fin= hoy.clone().add(10, 'days');         

    $('#daterange').daterangepicker({
        startDate: inicio,
        endDate: fin,
        minDate: inicio,
        maxDate: fin,
        locale: { format: 'YYYY-MM-DD' },
        opens: 'left'
    });
    
     cargarinicial();
     cargaConvoysTab();


     $('#daterange').on('apply.daterangepicker', function(ev, picker) {
        const fechaInicio = picker.startDate.format('YYYY-MM-DD');
        const fechaFin = picker.endDate.format('YYYY-MM-DD');

        console.log('Rango seleccionado:', fechaInicio, fechaFin);

       cargaConboys2(fechaInicio, fechaFin);
    });
               
});



let contenedoresGuardados ;  
let contenedoresGuardadosTodos ;  
//let contenedoresDisponibles = [];
let userBloqueo = false;
const seleccionados = [];
const ItemsSelects = [];



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

function cargaConvoysTab(){
    let gridApi;
    let gridApi2;
   //  cargarinicial();
    definirTable();
    definirTable2();

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
                    
                
                    document.getElementById("no_convoy").value = data.data.no_conboy || "";
                    document.getElementById("fecha_inicio").value = formatDateForInput(data.data.fecha_inicio) || "";
                    document.getElementById("fecha_fin").value = formatDateForInput(data.data.fecha_fin )|| "";
                    document.getElementById("nombre").value = data.data.nombre || "";
                    document.getElementById("tipo_disolucion").value = data.data.tipo_disolucion || "";
                    document.getElementById("geocerca_lat").value = data.data.geocerca_lat || "";
                    document.getElementById("geocerca_lng").value = data.data.geocerca_lng || "";
                    document.getElementById("geocerca_radio").value = data.data.geocerca_radio || "";



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

}

  
function abrirGeocerca() {
  const url = '/configurar-geocerca'; 
  const win = window.open(url, 'ConfigurarGeocerca', 'width=800,height=600');
}
function formatDateForInput(dateString) {
     if (!dateString) return "";
    const date = new Date(dateString);

    if (isNaN(date.getTime())) return ""; 

    const year = date.getFullYear();
    const month = (`0${date.getMonth() + 1}`).slice(-2);
    const day = (`0${date.getDate()}`).slice(-2);
    const hours = (`0${date.getHours()}`).slice(-2);
    const minutes = (`0${date.getMinutes()}`).slice(-2);

    return `${year}-${month}-${day}T${hours}:${minutes}`;
}
function setGeocercaData(lat, lng, radio) {
    document.getElementById('geocerca_lat').value = lat;
    document.getElementById('geocerca_lng').value = lng;
    document.getElementById('geocerca_radio').value = radio;

    alert('Geocerca guardada correctamente');
}
  document.getElementById('btnGuardarContenedores').addEventListener('click', function () {
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

        const numeroConvoy = document.getElementById('numero_convoy').value;
      //  let idconvoy = document.getElementById('id_convoy').value;
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
    numero_convoy:numeroConvoy,
  tipo_disolucion :tipo_disolucion,
            geocerca_lat:geocerca_lat,
            geocerca_lng :geocerca_lng,
            geocerca_radio:geocerca_radio,

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
         headerName: '',
        field: 'checkbox',
        headerCheckboxSelection: true,
        checkboxSelection: true,
        width: 50,
        pinned: 'left',
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
            document.getElementById("tipo_disolucion").value = data.tipo_disolucion || "";
            document.getElementById("geocerca_lat").value = data.geocerca_lat || "";
            document.getElementById("geocerca_lng").value = data.geocerca_lng || "";
            document.getElementById("geocerca_radio").value = data.geocerca_radio || "";


              
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
            // 
            document.getElementById("wmensajeText").innerText = `Se comparte el siguiente no. de Convoy:: ${data.no_conboy}`;
            
            // 
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
   
function onSelectionChanged() {
    const selectedRows = gridApi.getSelectedRows();
    const btn = document.getElementById('btnRastrearconboysSelec');
    if (selectedRows.length > 1) {
        btn.classList.remove('d-none');
    } else {
        btn.classList.add('d-none');
    }
}
document.getElementById('btnRastrearconboysSelec').addEventListener('click', () => {
    const selectedRows = gridApi.getSelectedRows();

    
    const ids = selectedRows.map(row => row.id); 

    if (ids.length > 1) {
      
        const query = new URLSearchParams({ ids: ids.join(',') }).toString();
        const url = `/coordenadas/mapa_rastreo_varios?${query}`;
        window.open(url, '_blank');
    }
});

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
        ItemsSelects.push(`${item.num_contenedor}|${item.id_contenedor}|${item.imei}`);
    });
}

    const gridOptions = {
        columnDefs: columnDefs,
        pagination: true,
        paginationPageSize: 100,
        rowSelection: "multiple",
        defaultColDef: {
            resizable: true,
            flex: 1
        },
         onSelectionChanged: onSelectionChanged
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
                    contenedoresGuardados = data.dataConten;
                    contenedoresGuardadosTodos = data.dataConten2;
                   const rowData = data.data;
                    gridApi.setGridOption("rowData", rowData);
                    
                    
                })
                .catch(error => {
                    console.error("❌ Error al obtener la lista de convoys:", error);
                })
                .finally(() => {
                    overlay.style.display = "none"; 
                });
    }
}

function definirTable2(){
  const columnDefs2 = [
        {
         headerName: '',
        field: 'checkbox',
        headerCheckboxSelection: true,
        checkboxSelection: true,
        width: 50,
        pinned: 'left',
        suppressSizeToFit: true,
        resizable: false
        },
       
        { headerName: "Tipo", field: "ubicacionable_type", sortable: true, filter: true },
        { headerName: "Contenedor", field: "contenedor", sortable: true, filter: true },
        { headerName: "# Convoy", field: "no_conboy", sortable: true, filter: true },
        { headerName: "Info Extra", field: "cliente", sortable: true, filter: true },
       
        

       {
    headerName: "Acciones",
   
   
    cellRenderer: function (params) {
        const container = document.createElement("div");
      //  container.classList.add("d-flex", "flex-wrap", "gap-1", "justify-content-start");
        const data = params.data;

        // Botón Editar
        const btnHistorial = document.createElement("button");
        btnHistorial.innerHTML = `<i class="fas fa-history text-white"></i>`;
       
        btnHistorial.title ="Historial";
        btnHistorial.classList.add("btn", "btn-sm", "btn-warning", "me-1");
        btnHistorial.onclick = function () {

const url = `/mapa-comparacion?idSearch=${data.ubicacionable_id}&type=${data.ubicacionable_type}&latitud_seguimiento=${0}&longitud_seguimiento=${0}&contenedor=${data.contenedor}`;
// Abrir
window.open(url, "_blank");
         
 
        };

        // Botón Compartir
        const btnCompartir = document.createElement("button");
        btnCompartir.innerText = "🔗";
        btnCompartir.title ="Compartir"
        btnCompartir.classList.add("btn", "btn-sm", "btn-info");
        btnCompartir.onclick = function () {
          
        };
   


            container.appendChild(btnHistorial);
            container.appendChild(btnCompartir);
         

            return container;
        }
    }
          
             
       
    ];
   


    
   const gridOptions2 = {
        columnDefs: columnDefs2,
        pagination: true,
        paginationPageSize: 100,
        rowSelection: "multiple",
        defaultColDef: {
            resizable: true,
            flex: 1
        }
    };
   

    const myGridElement2 = document.querySelector("#myGridConvoyFinalizados");
    gridApi2 = agGrid.createGrid(myGridElement2, gridOptions2);
 
    cargaConboys2(inicio.format('YYYY-MM-DD'), fin.format('YYYY-MM-DD'));

  
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

         ItemsSelects.push(valor +"|" + contenedorData.id_contenedor+'|'+ contenedorData.imei);
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
        if (typeof contenedorData !== 'undefined') {

            ItemsSelects.push(valor +"|" + contenedorData.id_contenedor+'|'+ contenedorData.imei);
        document.getElementById('contenedor-input2').value = '';
        document.getElementById('sugerencias2').style.display = 'none';
        actualizarVista2();
        }
         
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
   

