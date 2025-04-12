 const fecha = new Date();
 //const APP_URL = document.querySelector('meta[name="APP_URL"]').getAttribute('content');

 var allEvents = null;
 var festivos = [];


 

 async function initBoard(){

     var _token = $('input[name="_token"]').val();
    
     const result = await $.ajax({
         method:'post',
         url:'/planeaciones/monitor/board',
         data:{_token: _token},
         beforeSend:()=>{
         },
         success:(resp)=>{
           
            
             dp.resources = resp.boardCentros;
             //Horizonte
             /*dp.separators = [
                 {color: "red", location: info.Horizonte}
             ];*/
             //Mostrar los eventos planificados previamente
             var x = 0;
             allEvents = resp.extractor;
             TarifasHilos = resp.TarifasHilo;
             festivos = resp.festivos;
             if(allEvents != null){
                 resp.extractor.forEach((i)=>{
                 if(i.fecha_inicio !== null){
                     var e = {
                     start: new DayPilot.Date(i.fecha_inicio),
                     end: new DayPilot.Date(i.fecha_fin),
                     id:i.id_contenedor,
                     resource: (i.id_proveedor != null) ? parseInt(i.id_proveedor.toString()+"7000") : parseInt(i.id_camion.toString()+"5000"), //<=======Este es el ID del recurso (maquina) donde se ha de colocar el servicio de viaje
                     text: i.num_contenedor,
                     bubbleHtml: i.num_contenedor,
                     barColor: barColor(1),
                     barBackColor: barBackColor(1),
                     backColor:  barBackColor(1),
                     complete: 100,
                     tooltip:i.num_contenedor
                     };
                 }
                 dp.events.list.push(e);
                // const threads = array1.findIndex(element => element > 10);
                 x = (x == 4) ? 0 : x + 1;
             });
             }


             dp.init();
             
             dp.scrollTo(new DayPilot.Date(resp.scrollDate));                   
         },
         error:(e)=>{
             console.log('Ocurrio un error: '+e);
         }
     })

     return result;
 }

 

 /**Configuracion de DayPilot */

 var dp = new DayPilot.Scheduler("dp");
 var mySugesstions = [];
 var allEvents = [];

 dp.startDate = "2025-01-01";
 dp.days = 365;
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
             text: "Actualizar Centro", onClick: function (args) {
                Swal.fire({
                    title: '¿Actualizar centro?',
                    showDenyButton: false,
                    showCancelButton: true,
                    confirmButtonText: 'Si, Actualizar!',
                    cancelButtonText: 'Cancelar!',
                    icon: 'question'
                  }).then((result) => {
                    /* Read more about isConfirmed, isDenied below */
                    if (result.isConfirmed) {
                        //var resp = eliminarEvento();                       

                        var _token = $('input[name="_token"]').val();
                        var event = args.source.data.id;
                        
                        $.ajax({
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
                    })
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
//   console.log(args.e.data.id);
var _token = $('input[name="_token"]').val();
$.ajax({
    url:'manager/monitor/board/getDataCentro',
    type:'post',
    data:{_token:_token, id: args.e.data.id},
    success:(response)=>{
        document.getElementById('labelCentro').innerHTML = response.centro;
        document.getElementById('labelUltimaConexion').innerHTML = response.ultima_conexion;
        document.getElementById('labelDesface').innerHTML = response.desface;
        document.getElementById('labelIntentos').innerHTML = response.intentos;
        document.getElementById('labelEstatus').innerHTML = response.estatus_extractor;
        
        $("#kt_modal_data").modal('show');

    },
    error:()=>{

    }
})
 };

 function buscarCentro(event,_sap){
    if (event.which === 13) {    
    var _token = $('input[name="_token"]').val();
    $.ajax({
        url:'manager/monitor/board/find/getDataCentro',
        type:'post',
        data:{_token:_token, sap: _sap},
        success:(response)=>{

            if (response.TMensaje == "success"){
                document.getElementById('labelCentro').innerHTML = response.centro;
                document.getElementById('labelUltimaConexion').innerHTML = response.ultima_conexion;
                document.getElementById('labelDesface').innerHTML = response.desface;
                document.getElementById('labelIntentos').innerHTML = response.intentos;
                document.getElementById('labelEstatus').innerHTML = response.estatus_extractor;                    
                $("#kt_modal_data").modal('show');
            }else{
                Swal.fire('',response.Mensaje,response.TMensaje);
            }
        },
        error:()=>{
    
        }
    }) 
   }
 }

 function barColor(i) {
     var colors = ["#A9A9A9", "#6aa84f", "#f1c232", "#cc0000"];
     return colors[i % 4];
 }

 function barBackColor(i) {
     var colors = ["#CBCBCB", "#b6d7a8", "#ffe599", "#ea9999"];
     return colors[i % 4];
 }

function zooming(ev) {
    var cellwidth = parseInt(ev);
    document.querySelector("#labelZoom").innerText = cellwidth;
    var start = dp.getViewPort().start;

    dp.cellWidth = cellwidth;
    dp.update();
    dp.scrollTo(start);
}


 $(document).ready(()=>{
    initBoard();
})
