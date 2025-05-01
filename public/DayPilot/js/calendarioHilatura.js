var dp = new DayPilot.Month("dp");
dp.locale = "es-mx";
// behavior and appearance
dp.cellMarginBottom = 20;

dp.bubble = new DayPilot.Bubble({
    onLoad: function (args) {
        var ev = args.source;
        args.html = " " + ev.text();
    }
});
//dp.eventHoverHandling = "Disabled";

// view
var today =  new Date;
dp.startDate = today.toISOString();  // or just dp.startDate = "2022-10-25";


// event moving
dp.onEventMoved = function (args) {
    dp.message("Se ha movido de posición el evento : " + args.e.text());
};

// event resizing
dp.onEventResized = function (args) {
    //dp.message("Resized: " + args.e.text());
};

// event creating
dp.onTimeRangeSelected = async function (args) {
    const modal = await DayPilot.Modal.prompt("Introduzca nombre del evento:", "");
    dp.clearSelection();
    if (!modal.result) return;
    var e = new DayPilot.Event({
        start: args.start,
        end: args.end,
        id: DayPilot.guid(),
        text: modal.result,
        barColor: barColor(1)
    });
    dp.events.add(e);
    dp.message("Evento agregado. No olvide guardar los cambios");
};
dp.eventDeleteHandling = "Update";
dp.onEventClicked = function (args) {
    dp.events.edit(args.source);
    //args.e.text='Nuevo texto';
};

/*dp.onHeaderClicked = function (args) {
    alert("day: " + args.header.dayOfWeek);
};*/

dp.onEventDeleted = async function (args) {
    args.async = true;

    var _token = $('input[name="_token"]').val();

    DayPilot.Http.ajax({
        url:'/maintenance/calendars/threading/deleteEvent',
        method: "POST",
        data: {
            id: args.e.id(),
            _token:_token
        },
        success: function(ajax) {
            if (ajax.data.TMensaje == "success") {
                Swal.fire(ajax.data.Titulo, ajax.data.Mensaje, ajax.data.TMensaje);
            }
        }
    });
};

dp.contextMenu = new DayPilot.Menu({
    items: [
        {
            text: "Evento General", onClick: function (args) {
                var e = args.source;
                cambiarClasificacion(1,e.id());
            }
        },
        {
            text: "Evento de Hilatura", onClick: function (args) {
                var e = args.source;
                cambiarClasificacion(2,e.id());

            }
        },
        {
            text: "Evento de tejido", onClick: function (args) {
                var e = args.source;
                cambiarClasificacion(3,e.id());

            }
        }
    ]
});

function cambiarClasificacion(type_id, event_id){
    if(isNaN(event_id)) {changeTheColor(type_id, event_id);return false;}

    var _token = $('input[name="_token"]').val();

    $.ajax({
        url:'/maintenance/calendars/classify',
        type:'post',
        data:{_token:_token,type_id: type_id, event_id: event_id},
        success:()=>{
            changeTheColor(type_id, event_id)
        },
        error:()=>{
            dp.message('No fue posible cambiar la clasificación del evento');
        }
    })    
}

function changeTheColor(type_id, event_id){
    dp.events.list.forEach(function(i){
        if(i.id == event_id){
            i.barColor = barColor(type_id);
            dp.update();
        }
    })
}

function getMonthName(){
    var meses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
    var LabelDate = meses[dp.startDate.getMonth()]+ ' '+dp.startDate.getYear();
    $("#labelDate").text(LabelDate);
}

function getEvents(){
    $.ajax({
        url:'/maintenance/calendars/threading/getEvents',
        method:'get',
        success:(r)=>{           

            r.forEach(element => {
                console.log(element.type_id);
                var e = new DayPilot.Event({
                    start: new DayPilot.Date(element.start+'T00:00:00'),
                    end: new DayPilot.Date(element.end+'T23:59:00'),
                    id: element.id,
                    text: element.title,
                    barColor: barColor(element.type_id),
                    //barBackColor: barBackColor(element.type_id)
                });
                dp.events.add(e);
            });
        },
        error:()=>{
            console.log('No se pudo cargar información de los eventos actuales.')
        }
    });

    getMonthName();
}

function barColor(i) {
    var colors = [ "#f1c232","#0cbc87","#3c78d8", "#cc0000"];
    return colors[i];
}

function guardarCalHilatura(){
    
    var events = dp.events.list;
   
    var _token = $('input[name="_token"]').val();
    $.ajax({
        url:'/maintenance/calendars/threading/saveEvents',
        method:'post',
        data:{_token:_token, events: JSON.stringify(events)},
        beforeSend:()=>{
            Swal.fire({
                title: 'Actualizando el calendario!',
                html: 'Por favor espere un momento...',
                allowOutsideClick: false,
                timerProgressBar: true,
                didOpen: () => {
                    Swal.showLoading()
                    const b = Swal.getHtmlContainer().querySelector('b')
                }
                });
        },
        success:(i)=>{
            Swal.fire(i.Titulo,i.Mensaje,i.TMensaje);
            if(i.TMensaje == 'success'){
                //getEvents();
            }
        },
        error:(e)=>{
            Swal.fire('Error','Mensaje del sistema: '+e.responseJSON.message,'error');
        }
    });
}

dp.init();
