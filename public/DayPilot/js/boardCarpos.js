 const fecha = new Date();
 //const APP_URL = document.querySelector('meta[name="APP_URL"]').getAttribute('content');

 var allEvents = null;
 var festivos = [];
 let dpReady = false;
 let buscarContenedor  = document.querySelector('#txtBuscarContenedor')

function formatFecha(fechaISO){
const fecha = new Date(fechaISO);

// Obtener día, mes y año
const dia = String(fecha.getDate()).padStart(2, '0');
const mes = String(fecha.getMonth() + 1).padStart(2, '0'); // Los meses empiezan desde 0
const anio = fecha.getFullYear();

// Formato final
return `${dia}/${mes}/${anio}`;
}
 

 async function initBoard(fromDate,toDate){

     var _token = $('input[name="_token"]').val();
    
     const result = await $.ajax({
         method:'post',
         url:'/planeaciones/monitor/board',
         data:{_token: _token, fromDate: fromDate},
         beforeSend:()=>{
            if ( dpReady) {
                dp.events.list = [];  // Vacía la lista de eventos
                dp.update();
            }
         },
         success:(resp)=>{
           
             
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
             if(allEvents != null){
                 resp.extractor.forEach((i)=>{
                 let x = Math.floor(Math.random() * 8) + 1;
                 if(i.fecha_inicio !== null){
                     scrollToDate = scrollToDate ?? new DayPilot.Date(i.fecha_inicio)
                     var e = {
                     start: new DayPilot.Date(i.fecha_inicio),
                     end: new DayPilot.Date(i.fecha_fin),
                     id:i.id_contenedor,
                     resource: parseInt(i.id_cliente),//(i.id_cliente != null) ? parseInt(i.id_proveedor.toString()+"7000") : parseInt(i.id_camion.toString()+"5000"), //<=======Este es el ID del recurso (maquina) donde se ha de colocar el servicio de viaje
                     text: i.num_contenedor,
                     bubbleHtml: i.num_contenedor,
                     barColor: barColor(x),
                     barBackColor: barBackColor(x),
                     backColor:  barBackColor(x),
                     complete: 100,
                     tooltip:i.num_contenedor
                     };
                 }
                 dp.events.list.push(e);
                // const threads = array1.findIndex(element => element > 10);
                
             });
             }

             dp.startDate = scrollToDate.addDays(-2)

             if ( dpReady) {
              
                dp.update();
            }else{
                dp.init();
                dpReady = true;
            }
             
             dp.scrollTo(new DayPilot.Date(fromDate));        

            
             
                        
         },
         error:(e)=>{
             console.log('Ocurrio un error: '+e);
         }
     })

     return result;
 }

 

 /**Configuracion de DayPilot */
 var mySugesstions = [];
 var allEvents = [];


 var dp = new DayPilot.Scheduler("dp");

 dp.startDate = $('#daterange').attr('data-start');
 dp.days = 365;

 dp.cellWidth = 160;

 dp.rowMarginBottom = 10;
 dp.rowMarginTop = 10;
 dp.scale = "Day";
 dp.locale = "es-mx";
 dp.timeHeaders = [
     {groupBy: "Month", format: "MMMM yyyy"},
     {groupBy: "Day", format: "d"}
 ];
   
 dp.crosshairType = "Full";
 dp.allowEventOverlap = true;

 dp.showToolTip = false;
 dp.bubble = new DayPilot.Bubble();

 dp.durationBarMode = "PercentComplete";
 
 dp.contextMenu = new DayPilot.Menu({
     items: [
         {
             text: "... ", onClick: function (args) {
                Swal.fire({
                    title: '¿Desea ver las corrdenadas del viaje?',
                    showDenyButton: false,
                    showCancelButton: true,
                    confirmButtonText: 'Si, Actualizar!',
                    cancelButtonText: 'Cancelar!',
                    icon: 'question'
                  }).then((result) => {
                    /* Read more about isConfirmed, isDenied below */
                    if (result.isConfirmed) {
                        //var resp = eliminarEvento();                       

                     //   var _token = $('input[name="_token"]').val();
                        var event = args.source.data.id;
                        
                      /*  $.ajax({
                        method:'post',
                        url:'/thread/programming/event/delete',
                        data:{_token: _token,event: event},
                        beforeSend:()=>{
                        },
                        success:(resp)=>{
                            Swal.fire(resp.Titulo, resp.Mensaje, resp.TMensaje);
                            if(resp.TMensaje === "success"){
                                dp.events.remove(args.source);
                            }
                        },
                        error:(err)=>{
                            console.log("error");
                        }
                    })*/
                    } 
                  })

               
                 
             }
         },
    
         {
             //text: "Ver Detalles", onClick: function (args) {}
         }/*,
         {
             text: "Select", onClick: function (args) {
                 dp.multiselect.add(args.source);
             }
         }*/
     ]
 });

 dp.treeEnabled = true;
 dp.treePreventParentUsage = true;

 dp.heightSpec = "Max";
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


 // event moving
 dp.onEventMoved = function (args) {
     //console.log(args.e);
     dp.message("Nuevo calculo: " + args.e.text());
     
 };

 dp.onEventMoving = function (args) {
    
    
 };

 // event resizing
 dp.onEventResized = function (args) {
    var MS_PER_MINUTE = 60000; 
    var EndDateTime =  Date.parse(args.newEnd);
    var myEndDate = new Date(EndDateTime - 1 * MS_PER_MINUTE);

    args.end =(myEndDate.getFullYear()+'-'+(myEndDate.getMonth()+1)+'-'+myEndDate.getDate()); ;
    dp.message("Programa Modificado: " + args.e.text());
 };

 // event creating
 dp.onTimeRangeSelected = function (args) {
   
 };

 dp.onEventMove = function (args) {
     if (args.ctrl) {
         var newEvent = new DayPilot.Event({
             start: args.newStart,
             end: args.newEnd,
             text: args.e.text(),
             resource: args.newResource,
             id: 'id/'+args.e.id()+'/'+ DayPilot.guid()  // generate random id
         });
         dp.events.add(newEvent);

         // notify the server about the action here

         args.preventDefault(); // prevent the default action - moving event to the new location
     }
 };

 dp.onRowClick = function (args){
    //console.log(args);
 }

 dp.onEventClick = function (args) {
   getInfoViaje(args.e.data.start.value,args.e.data.end.value, args.e.data.text, args.e.data.id);
 };

 function abrirMapaEnNuevaPestana( contenedor,tipoS) {
  
    const url = `/coordenadas/mapa_rastreo?contenedor=${contenedor}&tipoS=${encodeURIComponent(tipoS)}`;
    window.open(url, '_blank');
}

 function getInfoViaje(startDate, endDate, numContenedor_, idContendor){
    let fechaSalida = document.querySelector('#fechaSalida')
   fechaSalida.textContent = formatFecha(startDate);

   let fechaEntrega  = document.querySelector('#fechaEntrega')
   fechaEntrega.textContent = formatFecha(endDate);
   
   let numContenedor = document.querySelector('#numContenedorSpan')
   numContenedor.textContent = numContenedor_

   let nombreTransportista = document.querySelector('#nombreTransportista')
   let tipoViajeSpan = document.querySelector('#tipoViajeSpan')

   let origen = document.querySelector('#origen')
   let destino = document.querySelector('#destino')
   let nombreCliente = document.querySelector('#nombreCliente')
   let nombreSubcliente = document.querySelector('#nombreSubcliente')
   

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

            nombreTransportista.textContent = "--"
            tipoViajeSpan.textContent = "--"

            origen.textContent = "--"
            destino.textContent = "--"
            nombreCliente.textContent = "--"
            nombreSubcliente.textContent = "--"
        },
        success:(response)=>{
            ocultarLoading()
            nombreTransportista.textContent = response.nombre;
            tipoViajeSpan.textContent = response.tipo

            origen.textContent = response.cotizacion.origen
            destino.textContent = response.cotizacion.destino
            nombreCliente.textContent = response.cliente.nombre
            nombreSubcliente.textContent = response.subcliente.nombre

            if(response.tipo == "Viaje Propio"){
                $('#tipoViajeSpan').addClass('bg-gradient-success')
            }else{
                $('#tipoViajeSpan').addClass('bg-gradient-info')
            }
            let tipoS="Planeacion-> Contenedor:"
            //Once en true para que se ejecute una sola vez y se elimine el listener    onclick="('${params.data.contenedor}')
            btnFinalizar.addEventListener('click', () => finalizarViaje(idContendor,numContenedor_), { once: true });
            btnDeshacer.addEventListener('click', () => anularPlaneacion(idContendor,numContenedor_), { once: true });
            btnRastreo.addEventListener('click', () => abrirMapaEnNuevaPestana(numContenedor_,tipoS), { once: true });

            let documentos = response.documents
            let docs = Object.keys(documentos)
            docs.forEach(doc => {
                let documento = document.querySelector("#"+doc)
                valorDoc = documentos[doc]
                if(documento){
                    
                    documento.innerHTML = (valorDoc != false && valorDoc != null) ?
                     `<i class="fas fa-circle-check text-success fa-lg"></i>` :
                     `<i class="fas fa-circle-xmark text-secondary fa-lg"></i>`
                }

                if(doc == 'cima' && valorDoc == 1){
                    $("#cima-label").removeClass('d-none')

                }
                
            })
        },
        error:()=>{
            ocultarLoading()
        }
    })

    const modalElement = document.getElementById('viajeModal');
    const bootstrapModal = new bootstrap.Modal(modalElement);
    bootstrapModal.show();
 }

function anularPlaneacion(idCotizacion, numContenedor){
    $("#viajeModal").modal('hide')
    var _token = $('input[name="_token"]').val();
    Swal.fire({
        title: `Quitar programación ${numContenedor}`,
        text: `¿Desea cancelar la programación actual del viaje? Una vez realizada esta acción no se podrá deshacer`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, continuar',
        cancelButtonText: 'Cancelar',
      }).then((result) => {
        if (result.isConfirmed) {

            fetch(`/planeaciones/viaje/programa/anular`,
            {
                method: 'POST',  
                headers: {
                    'Content-Type': 'application/json', 
                },
                body: JSON.stringify({
                    _token: _token,
                    idCotizacion: idCotizacion,
                    numContenedor:numContenedor
                })
            })
            .then(response => response.json())
            .then(data => {
                Swal.fire(data.Titulo,data.Mensaje,data.TMensaje)
                if(data.TMensaje == "success"){
                    dp.events.remove(idCotizacion); //Eliminar del board
               }
            })
            .catch(error => {
                Swal.fire('Error', 'No pudimos anular el programa del viaje', 'error');
            });
        } 
      });
}

function finalizarViaje(idCotizacion, numContenedor){
    $("#viajeModal").modal('hide')
    var _token = $('input[name="_token"]').val();
    Swal.fire({
        title: `Finalizar viaje ${numContenedor}`,
        text: `¿Se encuentra seguro que desea finalizar el viaje? Una vez realizada esta acción no se podrá deshacer`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, continuar',
        cancelButtonText: 'Cancelar',
      }).then((result) => {
        if (result.isConfirmed) {

            fetch(`/planeaciones/viaje/finalizar`,
            {
                method: 'POST',  
                headers: {
                    'Content-Type': 'application/json', 
                },
                body: JSON.stringify({
                    _token: _token,
                    idCotizacion: idCotizacion
                })
            })
            .then(response => response.json())
            .then(data => {
                Swal.fire(data.Titulo,data.Mensaje,data.TMensaje)
                if(data.TMensaje == "success"){
                     dp.events.remove(idCotizacion); //Eliminar del board
                }

            })
            .catch(error => {
                Swal.fire('Error', 'No pudimos finalizar el viaje', 'error');
            });
        } 
      });
      
}

function encontrarContenedor(contenedor){
    let busqueda = allEvents
    const resultados = busqueda.filter(f => f.num_contenedor?.includes(contenedor))
    if(resultados.length != 1){
        Swal.fire("No se encontró contenedor", `No existe ningún contenedor "PLANEADO" con el numero de contenedor proporcionado`,'warning')
        return
    }

    let fromDate = resultados[resultados.length - 1]?.fecha_inicio;
    let toDate = resultados[resultados.length - 1]?.fecha_fin;
    let numContenedor = resultados[resultados.length - 1]?.num_contenedor;
    let idContendor = resultados[resultados.length - 1]?.id_contenedor;

    if ( dpReady) {
        dp.scrollTo(new DayPilot.Date(fromDate));    
        getInfoViaje(fromDate, toDate, numContenedor, idContendor)
    }
     
}

buscarContenedor.addEventListener('keypress',e => {
    if (e.key === 'Enter') {
    encontrarContenedor(e.target.value)
    }
})

 function barColor(i) {
     var colors = ["#A9A9A9", "#6aa84f", "#f1c232", "#cc0000","#C8A2C8","#0057B8"];
     return colors[i % 6];
 }

 function barBackColor(i) {
     var colors = ["#CBCBCB", "#b6d7a8", "#ffe599", "#ea9999","#D8BFD8","#87CEEB"];
     return colors[i % 6];
 }

function zooming(ev) {
    var cellwidth = parseInt(ev);
    document.querySelector("#labelZoom").innerText = cellwidth;
    var start = dp.getViewPort().start;

    dp.cellWidth = cellwidth;
    dp.update();
    dp.scrollTo(start);
}

