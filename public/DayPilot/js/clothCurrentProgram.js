/**
 * Este archivo se utiliza para formar la sugerencia del programa de tejido de cada tela en demandas
 */
const fecha = new Date();
var TarifasTelas = null;

var festivos = [];

function GuardarProgTejido() {
    var _token = $('input[name="_token"]').val();
    var events = dp.events.list;
    var requiredQty = $('#dp').data('requiredqty');
    var article = $('#dp').data('article');
    var article_id = $('#dp').data('articleid');
    var demandItems = $('#dp').data('demanda');
    $.ajax({
        url: '/weave/programming/save',
        type: 'post',
        data: {
            _token: _token,
            events: JSON.stringify(events),
            requiredQty: requiredQty,
            article: article,
            article_id: article_id,
            demandItems: demandItems,
        },
        beforeSend: () => {
            Swal.fire({
                title: 'Actualizando el programa!',
                html: 'Por favor espere un momento...',
                allowOutsideClick: false,
                timerProgressBar: true,
                didOpen: () => {
                    Swal.showLoading();
                    const b = Swal.getHtmlContainer().querySelector('b');
                },
            });
        },
        success: (response) => {
            Swal.fire(response.Titulo, response.Mensaje, response.TMensaje);
        },
        error: (err) => {
            Swal.fire(
                'Error',
                'No hemos podido procesar su solicitud, es posible que no se hayan registrado los cambios ',
                'error',
            );
        },
    });
}

async function getCurrentProgram() {
    const result = await $.ajax({
        method: 'post',
        url: '/api/weave/getCurrentProgram',
        beforeSend: () => {
            Swal.fire({
                title: 'Obteniendo información!',
                html: 'Por favor espere un momento...',
                allowOutsideClick: false,
                timerProgressBar: true,
                didOpen: () => {
                    Swal.showLoading();
                    const b = Swal.getHtmlContainer().querySelector('b');
                },
            });
        },
        success: (resp) => {
            var info = JSON.parse(resp);

            festivos = info.festivos;
            if (info.eventos == null) {
                $('#itemsNotFound').show();
                return false;
            }

            dp.resources = info.Plantas;
            //Horizonte
            dp.separators = [{ color: 'red', location: info.Horizonte }];
            //Mostrar los eventos planificados previamente
            allEvents = info.eventos;
            if (allEvents != null) {
                info.eventos.forEach((i) => {
                    if (i.startDate !== null) {
                        var e = {
                            start: new DayPilot.Date(i.startDate + 'T00:00:00'),
                            end: new DayPilot.Date(i.endDate + 'T23:59:00'),
                            id: i.pprogtej_id,
                            resource: i.pprogtej_idmaquina, //<=======Este es el ID del recurso (maquina) donde se ha de colocar la orden de trabajo
                            text: i.pprogtej_part,
                            bubbleHtml: i.pprogtej_ordenado + 'Kg',
                            barColor: barColor(1),
                            barBackColor: barBackColor(1),
                            complete: 100,
                            tooltip: i.pprogtej_idpart,
                        };
                    }
                    dp.events.list.push(e);
                });
            }

            getTarifas(info.TelasPrograma);

            //barColor: 1=>Verde, 2=>Amarillo, 7=>Rojo, 8=>Azul

            dp.init();
            dp.scrollTo(new DayPilot.Date(info.scrollDate));
        },
        error: (e) => {
            console.log('Ocurrio un error: ' + e);
        },
    });

    return result;
}

function getTarifas(clothList) {
    $.ajax({
        url: '/api/weave/getTarifas',
        method: 'post',
        data: { clothList: clothList },
        beforeSend: () => {
            Swal.fire({
                title: 'Obteniendo tarifas!',
                html: 'Por favor espere un momento...',
                allowOutsideClick: false,
                timerProgressBar: true,
                didOpen: () => {
                    Swal.showLoading();
                    const b = Swal.getHtmlContainer().querySelector('b');
                },
            });
        },
        success: (response) => {
            TarifasTelas = response.Tarifas;
            Swal.close();
        },
        error: (e) => {
            Swal.close();
            Swal.fire(
                'Error: ' + e.statusText,
                'Ha ocurrido un error, no pudimos procesar su solicitud. Es posible que el modulo no funcione correctamete',
                'error',
            );
        },
    });
}

/**Configuracion de DayPilot */

var dp = new DayPilot.Scheduler('dp');
var mySugesstions = [];
var allEvents = [];

dp.startDate = '2022-07-01';
dp.days = 365;
dp.scale = 'Day';
dp.locale = 'es-mx';
dp.timeHeaders = [
    { groupBy: 'Month', format: 'MMMM yyyy' },
    { groupBy: 'Day', format: 'd' },
];

dp.crosshairType = 'Full';
dp.allowEventOverlap = false;

dp.showToolTip = false;
dp.bubble = new DayPilot.Bubble();

dp.durationBarMode = 'PercentComplete';

dp.contextMenu = new DayPilot.Menu({
    items: [
        {
            text: 'Ver Detalles',
            onClick: function (args) {
                //dp.events.remove(args.source);
                // dp.onEventClick();
            },
        },
        /*{text: "-"},
         {
             text: "Select", onClick: function (args) {
                 dp.multiselect.add(args.source);
             }
         }*/
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

dp.onBeforeCellRender = function (args) {
    var fecha = '' + args.cell.start + '';
    fecha = fecha.substring(0, 10);
    if (festivos.includes(fecha)) {
        args.cell.disabled = true;
        args.cell.backColor = '#ea9999';
    }
};
// event moving
dp.onEventMoved = function (args) {
    //  dp.message("Nuevo calculo: " + args.e.text());
};

dp.onEventMoving = function (args) {
    //No permitir realizar cambios a fechas anteriores a la actual
    if (args.start < new DayPilot.Date(Date.now())) {
        args.allowed = false;
    }

    //Obtener tela_id el cual se almacena en la propiedad "tooltip" del evento
    var tela_id = args.e.data.tooltip;
    var tarifasTela = TarifasTelas.filter((id) => id.tela_id === tela_id);
    var validMachine = tarifasTela.find((maquina) => maquina.maquina_id === args.resource);

    if (validMachine == undefined) {
        //Si la maquina no es compatible, no se permite asignar la tela a la maquina en cuestion
        args.left.enabled = false;
        args.right.html = 'La tela no se puede producir en esta maquina';
        args.allowed = false;
        return false;
    }

    var qtyProduction = args.e.data.bubbleHtml.substring(0, args.e.data.bubbleHtml.length - 2);
    var tarifaDiaria = validMachine.capacidad;
    var daysProduction = Math.ceil(qtyProduction / tarifaDiaria);

    args.end = args.start.addDays(daysProduction); // fixed duration
    args.left.enabled = true;
    args.left.html = 'La producción esta maquina tomará ' + daysProduction + ' día(s).';
};

// event resizing
dp.onEventResized = function (args) {
    dp.message('Programa modificado: ' + args.e.text());
};

// event creating
/*dp.onTimeRangeSelected = function (args) {
     DayPilot.Modal.prompt("New event name:", "New Event").then(function (modal) {
         dp.clearSelection();
         var name = modal.result;
         if (!name) return;
         var e = new DayPilot.Event({
             start: args.start,
             end: args.end,
             id: DayPilot.guid(),
             resource: args.resource,
             text: name
         });
         dp.events.add(e);
         dp.message("Created");
     });
 };*/

dp.onEventMove = async function (args) {
    if (args.ctrl) {
        var newEvent = new DayPilot.Event({
            start: args.newStart,
            end: args.newEnd,
            text: args.e.text(),
            resource: args.newResource,
            id: DayPilot.guid() + '|id' + args.e.id(), // generate random id
        });
        dp.events.add(newEvent);

        // notify the server about the action here

        args.preventDefault(); // prevent the default action - moving event to the new location
        return false;
    }

    args.async = true;

    DayPilot.Http.ajax({
        url: '/api/weave/validation/cloth-machine',
        method: 'POST',
        data: {
            machine_id: args.newResource,
            id: args.e.id(),
        },
        success: function (ajax) {
            if (ajax.data.TMensaje != 'success') {
                Swal.fire(ajax.data.Titulo, ajax.data.Mensaje, ajax.data.TMensaje);
                args.preventDefault();
            }
            args.loaded();
        },
    });
};

dp.onRowClick = function (args) {
    args.async = true;

    DayPilot.Http.ajax({
        url: '/api/information/machine',
        method: 'POST',
        data: {
            machine_id: args.resource.data.id,
        },
        success: function (ajax) {
            console.log(ajax.data);
            document.getElementById('detailInformationMachine').innerHTML = ajax.data.render;
            $('#mInformationMachine').modal('show');
        },
    });
};

dp.onEventClick = function (args) {
    allEvents.forEach((i) => {
        if (i.pprogtej_id == args.e.data.id) {
            $('#eventTitle').text(i.pprogtej_part + ' | ' + i.descripcion);
            $('#valPlanta').text(i.planta + ' ' + i.planta_name);
            $('#valMaquina').text(i.maquina);
            $('#valCantidad').text(i.pprogtej_ordenado + 'Kg');
            $('#valFInicio').text(i.startDate);
            $('#valFFinal').text(i.endDate);
            $('#valTarifa').text(i.pprogtej_tarifa);
            $('#mDetailEvent').modal('show');
        }
    });
};

/*
 dp.init();

 dp.scrollTo("2022-08-01");*/

function barColor(i) {
    var colors = ['#3c78d8', '#6aa84f', '#f1c232', '#cc0000'];
    return colors[i % 4];
}

function barBackColor(i) {
    var colors = ['#a4c2f4', '#b6d7a8', '#ffe599', '#ea9999'];
    return colors[i % 4];
}

$(document).ready(() => {
    getCurrentProgram();
});
