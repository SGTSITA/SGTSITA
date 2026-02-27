/**
 *
 * Este archivo se utiliza para formar la sugerencia del programa de tejido de cada tela en demandas
 *
 **/
const fecha = new Date();
const APP_URL = document.querySelector('meta[name="APP_URL"]').getAttribute('content');

var festivos = [];

function GuardarProgTejido() {
    var forComplete = $('#qtyForComplete').data('forcomplete');
    if (forComplete > 0) {
        Swal.fire(
            'Cantidad incompleta!',
            'La cantidad requerida no está completa, por favor ajuste su programa',
            'warning',
        );
        return false;
    }
    var _token = $('input[name="_token"]').val();
    var events = dp.events.list;
    var requiredQty = $('#dp').data('requiredqty');
    var article = $('#dp').data('article');
    var article_id = $('#dp').data('articleid');
    var demandItems = $('#dp').data('demanda');
    var threadComposition = $('#threadComposition').val();
    var weavingProgramItems = $('#weavingProgramItems').val();
    $.ajax({
        url: '/weave/programming/save',
        type: 'post',
        data: {
            _token: _token,
            events: JSON.stringify(events),
            requiredQty: requiredQty,
            weavingProgramItems: weavingProgramItems,
            article: article,
            article_id: article_id,
            demandItems: demandItems,
            threadComposition: threadComposition,
        },
        beforeSend: () => {
            Swal.fire({
                title: 'Guardando información!',
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
            Swal.fire(response.Titulo, response.Mensaje, response.TMensaje).then((result) => {
                if (result.isConfirmed) {
                    bPreguntar = false;
                    window.location.replace(APP_URL + '/weave/planning/cloth-list');
                }
            });
        },
        error: (err) => {
            Swal.close();
        },
    });
}

function detalleDiario(tarifa, diasProd, startDate, numMachine) {
    var requiredQty = $('#dp').data('requiredqty');
    $.ajax({
        url: '/weave/planning/calculateDailyProduction',
        type: 'get',
        data: {
            tarifa: tarifa,
            num_days_production: diasProd,
            startDate: startDate,
            requiredQty: requiredQty,
            numMachine: numMachine,
        },
        beforeSend: () => {},
        success: (response) => {
            $('#tabSuggestions').hide();
            document.getElementById('detailSuggestion').innerHTML = response;
            $('#detailSuggestion').show();
        },
        error: (err) => {},
    });
}

function showProdByEachMachine() {
    var programByMachine = JSON.parse($('#weavingProgramItems').val());
    $('#tabclothProgram>tbody>tr').detach();
    if (programByMachine != null) {
        programByMachine.forEach((s) => {
            $('#tabclothProgram>tbody').append(
                '<tr><td>' +
                    s.maquina +
                    '</td><td>' +
                    s.Qty +
                    'Kg</td><td>' +
                    s.startDate +
                    '</td><td>' +
                    s.endDate +
                    '</td><td>' +
                    Math.ceil(s.daysForProduce) +
                    '</td></tr>',
            );
        });
    }

    $('#mclothProgramming').modal('show');
}

async function getMachines() {
    var methodProgram = $('input[name="methodProgram"]:checked').val();
    var article = $('#dp').data('article');
    var requiredQty = $('#dp').data('requiredqty');
    var threadComposition = $('#threadComposition').val();

    const result = await $.ajax({
        method: 'post',
        url: '/api/weave/getMachines',
        data: {
            startDate: dp.startDate,
            methodProgram: methodProgram,
            article: article,
            requiredQty: requiredQty,
            threadComposition: threadComposition,
        },
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
                Swal.close();
                return false;
            }
            dp.resources = info.Plantas;
            //Horizonte
            dp.separators = [{ color: 'red', location: info.Horizonte }];
            //Mostrar los eventos planificados previamente
            allEvents = info.eventos;
            if (allEvents != null) {
                info.eventos.forEach((i) => {
                    var complete = 100;

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
                            complete: complete,
                            tooltip: i.pprogtej_idpart,
                        };
                        dp.events.list.push(e);
                    }
                });
            }

            mySugesstions = info.allSuggestions;
            if (mySugesstions != null) {
                mySugesstions.forEach((s) => {
                    $('#tabSuggestions>tbody').append(
                        '<tr><td>' +
                            s.maquina +
                            '</td><td>' +
                            s.capacidad +
                            'Kg</td><td>' +
                            s.startDate +
                            '</td><td>' +
                            s.endDate +
                            '</td><td>' +
                            Math.ceil(s.daysForProduce) +
                            "</td><td><a href='#' onclick='detalleDiario(" +
                            s.capacidad +
                            ',' +
                            Math.ceil(s.daysForProduce) +
                            ',' +
                            '"' +
                            s.startDate +
                            '"' +
                            ',' +
                            s.maquina +
                            ")'>Detalles</a></td></tr>",
                    );
                });
            }

            //Construir la sugerencia creada por el sistema
            if (info.suggestion !== null || info.suggestion != []) {
                theSuggestion = info.suggestion.bloqueFechas;
                theSuggestion.forEach((sg) => {
                    var f = {
                        start: new DayPilot.Date(sg.startDate + 'T00:00:00'),
                        end: new DayPilot.Date(sg.endDate + 'T23:59:00'),
                        id: DayPilot.guid(),
                        resource: sg.pprogtej_idmaquina, //<=======Este es el ID del recurso (maquina) donde se ha de colocar la orden de trabajo
                        text: article,
                        bubbleHtml: 'Sugerencia para: ' + article,
                        barColor: barColor(8),
                        //barBackColor: barBackColor(3),
                        complete: 100,
                    };
                    dp.events.list.push(f);
                });
            }
            //barColor: 1=>Verde, 2=>Amarillo, 7=>Rojo, 8=>Azul

            $('#weavingProgramItems').val(JSON.stringify(info.weavingProgramItems));

            dp.init();
            dp.scrollTo(info.scrollDate);

            Swal.close();
        },
        error: (e) => {
            Swal.close();
            console.log('Ocurrio un error: ' + e);
        },
    });

    return result;
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
        /* {
                    text: "Dividir Producción", onClick: function (args) {
                        dp.events.edit(args.source);
                    }
                },*/
        {
            text: 'Ver Detalles',
            onClick: function (args) {
                //dp.events.remove(args.source);
                // dp.onEventClick();
            },
        },
        { text: '-' } /*,
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
    setProgramedProduction();
};

dp.onEventMoving = function (args) {
    //No permitir realizar cambios a fechas anteriores a la actual
    if (args.start < new DayPilot.Date(Date.now())) {
        args.allowed = false;
    }

    if (isNaN(args.e.data.id)) {
        var tarifa = 99;
        var weavingProgramItems = JSON.parse($('#weavingProgramItems').val());
        var requiredqty = 1;
        var daysProduction = 100;
        var itemFound = 0;

        var currentMachine = args.resource;
        weavingProgramItems.forEach((x) => {
            if (x.pprogtej_idmaquina == args.e.data.resource) {
                requiredqty = x.Qty;
            }
        });

        mySugesstions.forEach((i) => {
            if (itemFound === 0) {
                if (currentMachine == i.pprogtej_idmaquina) {
                    itemFound = 1;
                    daysProduction = requiredqty / i.capacidad;
                    daysProduction = Math.ceil(daysProduction);
                }
            }
        });

        args.end = args.start.addDays(daysProduction); // fixed duration
        args.left.enabled = true;
        args.left.html = 'Producir la tela en esta maquina tomará ' + daysProduction + ' dia.';
    }
};

// event resizing
dp.onEventResized = function (args) {
    //Calcular kilos programados y por programar
    setProgramedProduction();
};

function setProgramedProduction() {
    var events = JSON.stringify(dp.events.list);
    var requiredQty = $('#dp').data('requiredqty');
    var article_id = $('#dp').data('articleid');

    $.ajax({
        url: '/api/weave/setProgramedProduction',
        method: 'POST',
        data: {
            events: events,
            requiredQty: requiredQty,
            article_id: article_id,
        },
        success: function (ajax) {
            $('#weavingProgramItems').val(JSON.stringify(ajax.weavingProgramItems));
            $('#qtyCompleted').text(ajax.qtyCompleted + 'Kg');
            $('#qtyForComplete').text(ajax.forComplete + 'Kg');
            $('#qtyForComplete').data('forcomplete', ajax.forComplete.replaceAll(',', ''));
        },
    });
}

// event creating
/* dp.onTimeRangeSelected = function (args) {
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
function addDaysToDate(date, days) {
    var res = new Date(date);
    res.setDate(res.getDate() + days);
    var myDay = res.getDate() < 10 ? '0' + res.getDate() : res.getDate();
    var myMonth = res.getMonth() + 1;
    myMonth = myMonth < 10 ? '0' + myMonth : myMonth;
    var myYear = res.getFullYear();
    return myYear + '-' + myMonth + '-' + myDay + 'T00:00:00';
}

dp.onEventMove = function (args) {
    if (args.ctrl) {
        var forComplete = $('#qtyForComplete').data('forcomplete');
        if (forComplete <= 0) {
            Swal.fire(
                'Producción multiple no permitida',
                'No es posible agregar producción a una nueva maquina porque la cantidad requerida está completa',
                'warning',
            );
            return false;
        }

        var itemFound = 0;
        var daysForProduce = 0;
        mySugesstions.forEach((i) => {
            if (itemFound === 0) {
                if (args.newResource == i.pprogtej_idmaquina) {
                    daysForProduce = Math.ceil(forComplete / i.capacidad);
                    itemFound = 1;
                } else {
                    //Maquina sin tarifa
                }
            }
        });

        if (itemFound == 1) {
            var newEndDate = addDaysToDate(args.newStart, daysForProduce);
            var newEvent = new DayPilot.Event({
                start: args.newStart,
                end: newEndDate,
                text: args.e.text(),
                resource: args.newResource,
                id: DayPilot.guid(),
            });

            dp.events.add(newEvent);
            setProgramedProduction();
            args.preventDefault();
        }
    }
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
    getMachines();
});
