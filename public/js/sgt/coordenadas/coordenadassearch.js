const preguntasPorTipo = {
    b: [
        { texto: "1)¿ Registro en Puerto ?", campo: "registro_puerto" },
        { texto: "2)¿ Dentro de Puerto ?", campo: "dentro_puerto" },
        { texto: "3)¿ Descarga Vacío ?", campo: "descarga_vacio" },
        { texto: "4)¿ Cargado Contenedor ?", campo: "cargado_contenedor" },
        { texto: "5)¿ En Fila Fiscal ?", campo: "fila_fiscal" },
        { texto: "6)¿ Modulado ?", campo: "modulado_tipo", opciones: ["5.1) Verde","5.2) Amarillo","5.3) Rojo", "5.4) OVT"] },
        { texto: "7)¿ Descarga en patio ?", campo: "descarga_patio" },
    ],
    f: [
        { texto: "8) ¿Carga en patio?", campo: "cargado_patio" },
        { texto: "9) ¿Inicio ruta?", campo: "en_destino" },
        { texto: "10)¿Inicia carga?", campo: "inicio_descarga" },
        { texto: "11)¿Fin descarga?", campo: "fin_descarga" },
        { texto: "12 ¿Recepción Doctos Firmados?", campo: "recepcion_doc_firmados" },
    ],
    c: [
        { texto: "¿1) Registro en Puerto ?", campo: "registro_puerto" },
        { texto: "¿2) Dentro de Puerto ?", campo: "dentro_puerto" },
        { texto: "¿3) Descarga Vacío?", campo: "descarga_vacio" },
        { texto: "¿4) Cargado Contenedor?", campo: "cargado_contenedor" },
        { texto: "¿5) En Fila Fiscal?", campo: "fila_fiscal" },
        { texto: "¿6) Modulado?", campo: "modulado_tipo", opciones: ["5.1) Verde","5.2) Amarillo","5.3) Rojo", "5.4) OVT"] },
        { texto: "¿7) En Destino?", campo: "en_destino" },
        { texto: "¿8) Inicio Descarga?", campo: "inicio_descarga" },
        { texto: "¿9) Fin Descarga?", campo: "fin_descarga" },
        { texto: "¿10) Recepción Doctos Firmados?", campo: "recepcion_doc_firmados" },
    ],
};




document.addEventListener("DOMContentLoaded", function () {
    let gridApi;



    let mapModal = document.getElementById('modalMapa');
    if (mapModal) {
        makeDraggable(mapModal);
    }



let PreguntaA;



  

    const columnDefs = [
        { headerCheckboxSelection: true, checkboxSelection: true, width: 50 },
        { headerName: "No Coti", field: "id_cotizacion", sortable: true, filter: true },
        { headerName: "No Asig", field: "id_asignacion", sortable: true, filter: true },
        { headerName: "No Coor", field: "id_coordenada", sortable: true, filter: true },
        { headerName: "Cliente", field: "cliente", sortable: true, filter: true },
        { headerName: "Origen", field: "origen", sortable: true, filter: true },
        { headerName: "Destino", field: "destino", sortable: true, filter: true },
        { headerName: "# Contenedor", field: "contenedor", sortable: true, filter: true },
        {
            headerName: "E Burrero",
            field: "Estatus_Burrero",
            minWidth: 180,
            cellRenderer: function (params) {
                let color = "secondary";
                if (params.data.tipo_b_estado === "2") color = "success";
                else if (params.data.tipo_b_estado === "1") color = "danger";
                else if (params.data.tipo_b_estado === "0") color = "warning";
        
                return `
                        <button class="btn btn-sm btn-outline-${color} ver-mapa-btn" 
                            data-tipo="c"
                            data-info='${JSON.stringify(params.data).replace(/'/g, "&#39;")}' 
                            title="Ver progreso...">
                            <i class="fa fa-sync-alt me-1"></i> ${params.data.Estatus_Burrero}
                        </button>
                `;
            }
        },        
        {
            headerName: "E Foraneo",
            field: "Estatus_Foraneo",
            minWidth: 180,
            cellRenderer: function (params) {
                let color = "secondary";
                if (params.data.tipo_f_estado === "2") color = "success";
                else if (params.data.tipo_f_estado === "1") color = "danger";
                else if (params.data.tipo_f_estado === "0") color = "warning";
        
                return `
                  
                    <button class="btn btn-sm btn-outline-${color} ver-mapa-btn" 
                    data-tipo="c"
                    data-info='${JSON.stringify(params.data).replace(/'/g, "&#39;")}' 
                    title="Ver progreso...">
                    <i class="fa fa-sync-alt me-1"></i> ${params.data.Estatus_Foraneo}
                </button>
                `;
            }
        },  
        {
            headerName: "E Completo",
            field: "Estatus_Completo",
            minWidth: 180,
            cellRenderer: function (params) {
                let color = "secondary";
                if (params.data.tipo_c_estado === "2") color = "success";
                else if (params.data.tipo_c_estado === "1") color = "danger";
                else if (params.data.tipo_c_estado === "0") color = "warning";
        
                return `
                     <button class="btn btn-sm btn-outline-${color} ver-mapa-btn" 
            data-tipo="c"
            data-info='${JSON.stringify(params.data).replace(/'/g, "&#39;")}' 
            title="Ver progreso...">
        <i class="fa fa-sync-alt me-1"></i> ${params.data.Estatus_Completo}
    </button>
                `;
            }
        }
    ];
    

    const gridOptions = {
        columnDefs: columnDefs,
        pagination: true,
        paginationPageSize: 100,
        rowSelection: "multiple",
        defaultColDef: {
            resizable: true,
            flex: 1
        },
    };

    const myGridElement = document.querySelector("#myGrid");
    gridApi = agGrid.createGrid(myGridElement, gridOptions);
    

    


    
        getCoordenadasList("");
   
function getCoordenadasList(parametros) {
        const overlay = document.getElementById("gridLoadingOverlay");
        overlay.style.display = "flex";
    
              
        gridApi.setGridOption("rowData", []); 
    
        fetch("/coordenadas/contenedor/search?" + parametros.toString())
            .then(response => response.json())
            .then(data => {
                PreguntaA= data.preguntas;
                gridApi.setGridOption("rowData", data.datos);
            })
            .catch(error => {
                console.error("❌ Error al obtener la lista de coordenadas:", error);
            })
            .finally(() => {
                overlay.style.display = "none"; 
            });
    }

    document.getElementById("formFiltros").addEventListener("submit", function (e) {
        e.preventDefault();
    
       
    
        const form = e.target;
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);
    
        
        getCoordenadasList(params.toString()); 
        
        

    });

    
});




    document.addEventListener('click', function(e) {
        if (e.target.closest('.ver-mapa-btn')) {
            const btn = e.target.closest('.ver-mapa-btn');
            const tipo = btn.dataset.tipo;
            const info = JSON.parse(btn.dataset.info);
            abrirModalCuestionario(tipo, info);
        }
    });

   
    
    function abrirModalCuestionario(tipoCuestionario, parametersW){
        const preguntas = preguntasPorTipo[tipoCuestionario];
        let contenido = "";
    let contenedor =  parametersW["contenedor"];
    document.getElementById("numeroContenedor").textContent = "# Contenedor:  " +  contenedor;
        preguntas.forEach(p => {
            const valor = parametersW[p.campo];
    
            if (valor && typeof valor === 'string' && valor.includes(',')) {
                const [lat, lng] = valor.split(',').map(v => parseFloat(v.trim()));
            
                if (!isNaN(lat) && !isNaN(lng)) {
                    contenido += `
                        <div class="d-flex flex-column justify-content-between border rounded p-2 mb-2" style="min-height: 100px;">
                            <div class="mb-2"><strong>${p.texto}</strong></div>
                            <div class="mt-auto">
                                <button onclick="verMapa(${lat}, ${lng})" class="btn btn-sm btn-primary ms-2" id="btnVerMapa">Ver Mapa</button>
                            </div>
                        </div>
                    `;
                } else {
                    contenido += `<div><strong>${p.texto}</strong> </div>`;
                }
            } else {
                contenido += `<div><strong>${p.texto}</strong> <span>Sin responder</span></div>`;
            }
    
       
    
        document.getElementById("modal-body-cuestionario").innerHTML = contenido;
        

        document.getElementById('myModal').style.display = 'block';

    })
}
   
    
      function closeModal() {
        document.getElementById('myModal').style.display = 'none';
      }
    
      window.onclick = function(event) {
        if (event.target === document.getElementById('myModal')) {
          closeModal();
        }
      }
   
function limpiarFiltros() {
  
    const modal = document.getElementById('filtroModal'); 
    const inputs = modal.querySelectorAll('input, select, textarea');

    inputs.forEach(element => {
        if (element.tagName === 'SELECT') {
            element.selectedIndex = 0; 
        } else {
            element.value = ''; 
        }
    });

}

function verMapa(lat, lng) {
    const url = `https://maps.google.com/maps?q=${lat},${lng}&z=15&output=embed`;
    document.getElementById('iframeMapa').src = url;
    document.getElementById('modalMapa').style.display = 'block';
}

function cerrarModalMapa() {
    document.getElementById('modalMapa').style.display = 'none';
    document.getElementById('iframeMapa').src = ''; 
}





function makeDraggable(element) {
    let isMouseDown = false;
    let offsetX, offsetY;

    const modalHeader = element.querySelector('.modal-header');
    if (modalHeader) {
        modalHeader.addEventListener('mousedown', function (e) {
            isMouseDown = true;
            offsetX = e.clientX - element.offsetLeft;
            offsetY = e.clientY - element.offsetTop;
        });

        window.addEventListener('mousemove', function (e) {
            if (isMouseDown) {
                element.style.left = (e.clientX - offsetX) + 'px';
                element.style.top = (e.clientY - offsetY) + 'px';
            }
        });

        window.addEventListener('mouseup', function () {
            isMouseDown = false;
        });
    }
}