const fecha = new Date();
//const APP_URL = document.querySelector('meta[name="APP_URL"]').getAttribute('content');
let ajustes = [];
let canEndTravel = 0;
var allEvents = null;
var festivos = [];
let dpReady = false;
//let buscarContenedor  = document.querySelector('#txtBuscarContenedor')

function formatFecha(fechaISO) {
    const fecha = new Date(fechaISO);
    const btnGuardarBoard = document.querySelector('#btnGuardarBoard');
    const labelNotice = document.querySelector('#labelNotice');

    // Obtener día, mes y año
    const dia = String(fecha.getDate()).padStart(2, '0');
    const mes = String(fecha.getMonth() + 1).padStart(2, '0'); // Los meses empiezan desde 0
    const anio = fecha.getFullYear();

    // Formato final
    return `${dia}/${mes}/${anio}`;
}

async function initBoard(fromDate, toDate) {
    var _token = $('input[name="_token"]').val();

    const result = await $.ajax({
        method: 'post',
        url: '/mec/planeaciones/monitor/board',
        data: { _token: _token, fromDate: fromDate },
        beforeSend: () => {
            if (dpReady) {
                dp.events.list = []; // Vacía la lista de eventos
                dp.update();
            }
        },
        success: (resp) => {
            dp.resources = resp.boardCentros;

            //Horizonte
            /*dp.separators = [
                {color: "red", location: info.Horizonte}
            ];*/
            //Mostrar los eventos planificados previamente

            allEvents = resp.extractor;
            //TarifasHilos = resp.TarifasHilo;
            //festivos = resp.festivos;
            let scrollToDate = null;
            if (allEvents != null) {
                resp.extractor.forEach((i) => {
                    let x = Math.floor(Math.random() * 8) + 1;
                    if (i.fecha_inicio !== null) {
                        scrollToDate = scrollToDate ?? new DayPilot.Date(i.fecha_inicio);
                        var e = {
                            start: new DayPilot.Date(i.fecha_inicio),
                            end: new DayPilot.Date(i.fecha_fin),
                            id: i.id_contenedor,
                            resource: parseInt(i.id_cliente), //(i.id_cliente != null) ? parseInt(i.id_proveedor.toString()+"7000") : parseInt(i.id_camion.toString()+"5000"), //<=======Este es el ID del recurso (maquina) donde se ha de colocar el servicio de viaje
                            text: i.num_contenedor,
                            bubbleHtml: i.num_contenedor,
                            barColor: barColor(x),
                            barBackColor: barBackColor(x),
                            backColor: barBackColor(x),
                            complete: 100,
                            tooltip: i.num_contenedor,
                        };
                    }
                    dp.events.list.push(e);
                    // const threads = array1.findIndex(element => element > 10);
                });
            }

            if (scrollToDate) {
                dp.startDate = scrollToDate.addDays(-2);
            } else {
                dp.startDate = new DayPilot.Date(fromDate);
            }

            if (dpReady) {
                dp.update();
            } else {
                dp.init();
                dpReady = true;
            }

            dp.scrollTo(new DayPilot.Date(fromDate));
        },
        error: (e) => {
            console.log('Ocurrio un error: ' + e);
        },
    });

    return result;
}

/**Configuracion de DayPilot */
var mySugesstions = [];
var allEvents = [];

var dp = new DayPilot.Scheduler('dp');

dp.startDate = $('#daterange').attr('data-start');
dp.days = 365;
dp.cellWidth = 185;
dp.rowMarginBottom = 10;
dp.rowMarginTop = 10;
dp.scale = 'Day';
dp.locale = 'es-mx';
dp.timeHeaders = [
    { groupBy: 'Month', format: 'MMMM yyyy' },
    { groupBy: 'Day', format: 'd' },
];

dp.crosshairType = 'Full';
dp.allowEventOverlap = true;

dp.showToolTip = false;
dp.bubble = new DayPilot.Bubble();

dp.durationBarMode = 'PercentComplete';

dp.contextMenu = new DayPilot.Menu({
    items: [
        {
            text: '... ',
            onClick: function (args) {
                Swal.fire({
                    title: '¿Desea ver las corrdenadas del viaje?',
                    showDenyButton: false,
                    showCancelButton: true,
                    confirmButtonText: 'Si, Actualizar!',
                    cancelButtonText: 'Cancelar!',
                    icon: 'question',
                }).then((result) => {
                    /* Read more about isConfirmed, isDenied below */
                    if (result.isConfirmed) {
                        var event = args.source.data.id;
                    }
                });
            },
        },

        {
            //text: "Ver Detalles", onClick: function (args) {}
        } /*,
        {
            text: "Select", onClick: function (args) {
                dp.multiselect.add(args.source);
            }
        }*/,
    ],
});

dp.treeEnabled = true;
dp.treePreventParentUsage = true;

dp.heightSpec = 'Max';
dp.height = 500;

dp.events.list = [];

dp.eventMovingStartEndEnabled = true;
dp.eventResizingStartEndEnabled = true;
dp.timeRangeSelectingStartEndEnabled = true;

/*dp.onBeforeCellRender = function (args) {
   var machines = $("#dp").data('allowmachines');
   //var machines = JSON.parse();

   for(i= 0; i< machines.length; i++){
       var disabled = true;
       if((machines[i]*1) === args.cell.resource){
           disabled = false;
           i = machines.length;
       }
       //console.log('machine: '+machines[i]+': '+disabled);
   }

   if (disabled == true) {
       args.cell.disabled = true;
       args.cell.backColor = "#ccc";
   }




   var fecha = ''+args.cell.start+'';
   fecha = fecha.substring(0,10);
   if(festivos.includes(fecha)){
       args.cell.disabled = true;
       args.cell.backColor = "#ea9999";
   }
};*/

function BoarCambio(args) {
    let inforAjustes = ajustes.filter((item) => {
        return item.text != args.e.data.text;
    });
    ajustes = inforAjustes ? [...inforAjustes, args.e.data] : [...ajustes, args.e.data];
    dp.message('Se cambió fecha del viaje: ' + args.e.text());
    if (ajustes.length > 0) {
        btnGuardarBoard.classList.remove('d-none');
        labelNotice.textContent = `Viajes con cambios sin confirmar: ${ajustes.length}`;
        labelNotice.classList.remove('d-none');
        btnGuardarBoard.classList.add('parpadeando');
        labelNotice.classList.add('parpadeando');
        setTimeout(() => {
            btnGuardarBoard.classList.remove('parpadeando');
            labelNotice.classList.remove('parpadeando');
        }, 2500); // 2.5 segundos
    }
}
// event moving
dp.onEventMoved = function (args) {
    BoarCambio(args);
};

dp.onEventMoving = function (args) {};

// event resizing
dp.onEventResized = function (args) {
    BoarCambio(args);
};

// event creating
dp.onTimeRangeSelected = function (args) {};

dp.onEventMove = function (args) {
    if (args.ctrl) {
        var newEvent = new DayPilot.Event({
            start: args.newStart,
            end: args.newEnd,
            text: args.e.text(),
            resource: args.newResource,
            id: 'id/' + args.e.id() + '/' + DayPilot.guid(), // generate random id
        });
        dp.events.add(newEvent);

        // notify the server about the action here
        args.preventDefault(); // prevent the default action - moving event to the new location
    }
};

dp.onRowClick = function (args) {
    //console.log(args);
};

dp.onEventClick = function (args) {
    getInfoViaje(args.e.data.start.value, args.e.data.end.value, args.e.data.text, args.e.data.id);
};

function abrirMapaEnNuevaPestana(contenedor, tipoS, origenRastreo) {
    const url = `/coordenadas/mapa_rastreo?contenedor=${contenedor}&tipoS=${encodeURIComponent(tipoS)}&origenRastreo=${encodeURIComponent(origenRastreo)}`;
    window.open(url, '_blank');
}

function getInfoViaje(startDate, endDate, numContenedor_, idContendor) {
    let fechaSalida = document.querySelector('#fechaSalida');
    fechaSalida.textContent = formatFecha(startDate);

    let fechaEntrega = document.querySelector('#fechaEntrega');
    fechaEntrega.textContent = formatFecha(endDate);

    let numContenedor = document.querySelector('#numContenedorSpan');
    numContenedor.textContent = numContenedor_;

    let nombreProveedor = document.querySelector('#nombreProveedor');
    // let nombreTransportista = document.querySelector('#nombreTransportista')
    let contactoEntrega = document.querySelector('#ContactoEntrega');
    let nombreOperador = document.querySelector('#nombreOperador');
    let telefonoOperador = document.querySelector('#telefonoOperador');

function getInfoViaje(startDate, endDate, numContenedor_, idContendor){
   let fechaSalida = document.querySelector('#fechaSalida')
  fechaSalida.textContent = formatFecha(startDate);

  let fechaEntrega  = document.querySelector('#fechaEntrega')
  fechaEntrega.textContent = formatFecha(endDate);

  let numContenedor = document.querySelector('#numContenedorSpan')
  numContenedor.textContent = numContenedor_


  let nombreProveedor = document.querySelector('#nombreProveedor')
 // let nombreTransportista = document.querySelector('#nombreTransportista')
  let contactoEntrega = document.querySelector('#ContactoEntrega')
  let nombreOperador = document.querySelector('#nombreOperador')
  let telefonoOperador = document.querySelector('#telefonoOperador')


  //let tipoViajeSpan = document.querySelector('#tipoViajeSpan')

  let origen = document.querySelector('#origen')
  let destino = document.querySelector('#destino')
  let nombreCliente = document.querySelector('#nombreCliente')
  let nombreSubcliente = document.querySelector('#nombreSubcliente')

  let id_equipo_camion = document.querySelector('#id_equipo_camion')
  let placas_camion = document.querySelector('#placas_camion')

  let marca_camion = document.querySelector('#marca_camion')
  let imei_camion = document.querySelector('#imei_camion')

  let id_equipo_chasis = document.querySelector('#id_equipo_chasis')
  let imei_chasis = document.querySelector('#imei_chasis')


   var _token = $('input[name="_token"]').val();
   $.ajax({
       url:'/planeaciones/monitor/board/info-viaje',
       type:'post',
       data:{_token:_token, id: idContendor},
       beforeSend:()=>{
           $("#cima-label").addClass('d-none')
           mostrarLoading('Espere un momento, cargando información del contenedor...')
           let docum = document.querySelectorAll('.documentos')
           docum.forEach((d) => {
               d.innerHTML = `--`
           })

           nombreProveedor.textContent = "--"
   //nombreTransportista.textContent = "--"
   contactoEntrega.textContent = "--"
   nombreOperador.textContent = "--"
   telefonoOperador.textContent = "--"


           tipoViajeSpan.textContent = ""




           origen.textContent = "--"
           destino.textContent = "--"
           nombreCliente.textContent = "--"
           nombreSubcliente.textContent = "--"

              placas_camion.textContent = "--"
                id_equipo_camion.textContent = "--"
                marca_camion.textContent = "--"
                imei_camion.textContent = "--"
                id_equipo_chasis.textContent = "--"
                imei_chasis.textContent = "--"
       },
       success:(response)=>{
           ocultarLoading()


             nombreProveedor.textContent = response.datosExtraviaje.empresa_beneficiario
   contactoEntrega.textContent =  response.datosExtraviaje.cp_contacto_entrega ?? "--"
   // nombreTransportista.textContent = response.datosExtraviaje.transportista_nombre ?? "--"
   nombreOperador.textContent =    response.datosExtraviaje.beneficiario_nombre

   telefonoOperador.textContent = response.datosExtraviaje.beneficiario_telefono ?? "--"




          // tipoViajeSpan.textContent = response.tipo



           origen.textContent = response.cotizacion.origen
           destino.textContent = response.cotizacion.destino
           nombreCliente.textContent = response.cliente.nombre
           nombreSubcliente.textContent =  response.subcliente?.nombre ?? ""

                placas_camion.textContent = response.documentos.placas_camion ?? "NA"
                id_equipo_camion.textContent = response.documentos.id_equipo_camion ?? "NA"
                marca_camion.textContent = response.documentos.marca_camion ?? "NA"
                imei_camion.textContent = response.documentos.imei_camion ?? "NA"
                id_equipo_chasis.textContent = response.documentos.id_equipo_chasis ?? "NA"
                imei_chasis.textContent = response.documentos.imei_chasis ?? "NA"

           if(response.tipo == "Viaje Propio"){
               $('#tipoViajeSpan').addClass('bg-gradient-success')
           }else{
               $('#tipoViajeSpan').addClass('bg-gradient-info')
           }
           let tipoS="Planeacion-> Contenedor:"
           //Once en true para que se ejecute una sola vez y se elimine el listener    onclick="('${params.data.contenedor}')
           //btnFinalizar.addEventListener('click', () => finalizarViaje(idContendor,numContenedor_), { once: true });
         //  btnDeshacer.addEventListener('click', () => anularPlaneacion(idContendor,numContenedor_), { once: true });
           btnRastreo.addEventListener('click', () => abrirMapaEnNuevaPestana(numContenedor_,tipoS,'MECBoard'), { once: true });




    let origen = document.querySelector('#origen');
    let destino = document.querySelector('#destino');
    let nombreCliente = document.querySelector('#nombreCliente');
    let nombreSubcliente = document.querySelector('#nombreSubcliente');

    let id_equipo_camion = document.querySelector('#id_equipo_camion');
    let placas_camion = document.querySelector('#placas_camion');

    let marca_camion = document.querySelector('#marca_camion');
    let imei_camion = document.querySelector('#imei_camion');

    let id_equipo_chasis = document.querySelector('#id_equipo_chasis');
    let imei_chasis = document.querySelector('#imei_chasis');

    var _token = $('input[name="_token"]').val();
    $.ajax({
        url: '/planeaciones/monitor/board/info-viaje',
        type: 'post',
        data: { _token: _token, id: idContendor },
        beforeSend: () => {
            $('#cima-label').addClass('d-none');
            mostrarLoading('Espere un momento, cargando información del contenedor...');
            let docum = document.querySelectorAll('.documentos');
            docum.forEach((d) => {
                d.innerHTML = `--`;
            });

            nombreProveedor.textContent = '--';
            //nombreTransportista.textContent = "--"
            contactoEntrega.textContent = '--';
            nombreOperador.textContent = '--';
            telefonoOperador.textContent = '--';

            tipoViajeSpan.textContent = '';

            origen.textContent = '--';
            destino.textContent = '--';
            nombreCliente.textContent = '--';
            nombreSubcliente.textContent = '--';

            placas_camion.textContent = '--';
            id_equipo_camion.textContent = '--';
            marca_camion.textContent = '--';
            imei_camion.textContent = '--';
            id_equipo_chasis.textContent = '--';
            imei_chasis.textContent = '--';
        },
        success: (response) => {
            ocultarLoading();

            nombreProveedor.textContent = response.datosExtraviaje.empresa_beneficiario;
            contactoEntrega.textContent = response.datosExtraviaje.cp_contacto_entrega ?? '--';
            // nombreTransportista.textContent = response.datosExtraviaje.transportista_nombre ?? "--"
            nombreOperador.textContent = response.datosExtraviaje.beneficiario_nombre;

            telefonoOperador.textContent = response.datosExtraviaje.beneficiario_telefono ?? '--';

            // tipoViajeSpan.textContent = response.tipo

            origen.textContent = response.cotizacion.origen;
            destino.textContent = response.cotizacion.destino;
            nombreCliente.textContent = response.cliente.nombre;
            nombreSubcliente.textContent = response.subcliente.nombre;

            placas_camion.textContent = response.documentos.placas_camion ?? 'NA';
            id_equipo_camion.textContent = response.documentos.id_equipo_camion ?? 'NA';
            marca_camion.textContent = response.documentos.marca_camion ?? 'NA';
            imei_camion.textContent = response.documentos.imei_camion ?? 'NA';
            id_equipo_chasis.textContent = response.documentos.id_equipo_chasis ?? 'NA';
            imei_chasis.textContent = response.documentos.imei_chasis ?? 'NA';

            if (response.tipo == 'Viaje Propio') {
                $('#tipoViajeSpan').addClass('bg-gradient-success');
            } else {
                $('#tipoViajeSpan').addClass('bg-gradient-info');
            }
            let tipoS = 'Planeacion-> Contenedor:';
            //Once en true para que se ejecute una sola vez y se elimine el listener    onclick="('${params.data.contenedor}')
            //btnFinalizar.addEventListener('click', () => finalizarViaje(idContendor,numContenedor_), { once: true });
            //  btnDeshacer.addEventListener('click', () => anularPlaneacion(idContendor,numContenedor_), { once: true });
            btnRastreo.addEventListener('click', () => abrirMapaEnNuevaPestana(numContenedor_, tipoS, 'MECBoard'), {
                once: true,
            });

            let documentos = response.documents;
            let docs = Object.keys(documentos);

            docs.forEach((doc) => {
                let documento = document.querySelector('#' + doc);
                valorDoc = documentos[doc];
                if (documento) {
                    documento.innerHTML =
                        valorDoc != false && valorDoc != null
                            ? `<i class="fas fa-circle-check text-success fa-lg"></i>`
                            : `<i class="fas fa-circle-xmark text-secondary fa-lg"></i>`;
                }

                if (doc == 'cima' && valorDoc == 1) {
                    $('#cima-label').removeClass('d-none');
                }
            });
        },
        error: () => {
            ocultarLoading();
        },
    });

    const modalElement = document.getElementById('viajeModal');
    const bootstrapModal = new bootstrap.Modal(modalElement);
    bootstrapModal.show();
}
function mostrarLoading(text = 'Espere un momento...') {
    let label = document.querySelector('#loading-text');
    label.textContent = text;

    document.getElementById('loading-overlay').style.display = 'flex';
}

function confirmarCambiosPlaneacion() {
    var _token = $('input[name="_token"]').val();
    let payload = { _token, ajustes: JSON.stringify(ajustes) };
    Swal.fire({
        title: `Guardar cambios para ${ajustes.length} ${ajustes.length > 1 ? 'Viajes' : 'Viaje'}`,
        text: `¿Desea guardar la programación de ${ajustes.length} viajes pendientes por confirmar?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, continuar',
        cancelButtonText: 'Cancelar',
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '/planeaciones/viajes/reprogramar',
                type: 'post',
                data: payload,
                beforeSend: () => {
                    mostrarLoading('Espere un momento, guardando cambios...');
                },
                success: (response) => {
                    ocultarLoading();
                    Swal.fire(response.Titulo, response.Mensaje, response.TMensaje);
                },
                error: (e) => {
                    ocultarLoading();
                    Swal.fire('Ha ocurrido un error', 'error', 'error');
                },
            });
        }
    });
}

function ocultarLoading() {
    document.getElementById('loading-overlay').style.display = 'none';
}
function abrirMapaEnNuevaPestana(numContenedor, tipoS, origenRastreo) {
    //const url = `/mapa-comparacion?latitud=${latitud}&longitud=${longitud}&latitud_seguimiento=${latitud_seguimiento}&longitud_seguimiento=${longitud_seguimiento}&contenedor=${contenedor}`;
    if (numContenedor) {
        const url = `/coordenadas/mapa_rastreo?contenedor=${numContenedor}&tipoS=${encodeURIComponent(tipoS)}&origenRastreo=${encodeURIComponent(origenRastreo)}`;
        window.open(url, '_blank');
    } else {
        Swal.fire('Validación', 'No se encontró información del contenedor.', 'warning');
        return;
    }
}

function encontrarContenedor(contenedor) {
    let busqueda = allEvents;
    const resultados = busqueda.filter((f) => f.num_contenedor?.includes(contenedor));
    if (resultados.length != 1) {
        Swal.fire(
            'No se encontró contenedor',
            `No existe ningún contenedor "PLANEADO" con el numero de contenedor proporcionado`,
            'warning',
        );
        return;
    }

    let fromDate = resultados[resultados.length - 1]?.fecha_inicio;
    let toDate = resultados[resultados.length - 1]?.fecha_fin;
    let numContenedor = resultados[resultados.length - 1]?.num_contenedor;
    let idContendor = resultados[resultados.length - 1]?.id_contenedor;

    if (dpReady) {
        dp.scrollTo(new DayPilot.Date(fromDate));
        getInfoViaje(fromDate, toDate, numContenedor, idContendor);
    }
}

/*
buscarContenedor.addEventListener('keypress',e => {
   if (e.key === 'Enter') {
   encontrarContenedor(e.target.value)
   }
})*/

function barColor(i) {
    var colors = ['#A9A9A9', '#6aa84f', '#f1c232', '#cc0000', '#C8A2C8', '#0057B8'];
    return colors[i % 6];
}

function barBackColor(i) {
    var colors = ['#CBCBCB', '#b6d7a8', '#ffe599', '#ea9999', '#D8BFD8', '#87CEEB'];
    return colors[i % 6];
}

function zooming(ev) {
    var cellwidth = parseInt(ev);
    document.querySelector('#labelZoom').innerText = cellwidth;
    var start = dp.getViewPort().start;

    dp.cellWidth = cellwidth;
    dp.update();
    dp.scrollTo(start);
}
