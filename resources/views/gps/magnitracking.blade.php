@extends('layouts.usuario_externo')

@section('WorkSpace')
    <h1>GPS Magnitracking</h1>
    <h4>
        Latitud:
        <span id="lat"></span>
    </h4>
    <h4>
        Longitud:
        <span id="long"></span>
    </h4>
@endsection

@push('javascript')
    <script>
        var statics = {};
        (function (send) {
            XMLHttpRequest.prototype.send = function (data) {
                this.setRequestHeader('session_username', 'josehemsani');
                this.setRequestHeader('session_token', 'ubUmCFTZnoZlfy6lfY7OylebmUXcGqor');
                send.call(this, data);
            };
        })(XMLHttpRequest.prototype.send);

        // vars
        var la = [];
        var map;
        var mapLayers = new Array();
        var mapMarkerIcons = new Array();
        var mapPopup,
            baseLayers = {};
        var objectsGeocoderCache = {};
        var timer_objectFollow;
        var objectsData = new Array();
        var settingsUserData = new Array();
        var settingsObjectData = new Array();

        function load() {
            loadLanguage(function (response) {
                loadSettings('server', function (response) {
                    loadSettings('user', function (response) {
                        loadSettings('objects', function (response) {
                            load2();
                        });
                    });
                });
            });
        }

        function load2() {
            initMap();
            initGui();
            initGrids();

            objectFollow('866381051024450');

            document.getElementById('loading_panel').style.display = 'none';
        }

        function unload() {}

        function transformToObjectData(data) {
            var result = [];
            result['data'] = [];

            result['visible'] = data['v'];
            result['follow'] = data['f'];
            result['of_trip'] = data['of_trip'];
            result['selected'] = data['s'];
            result['event'] = data['evt'];
            result['event_arrow_color'] = data['evtac'];
            result['event_ohc_color'] = data['evtohc'];
            result['address'] = data['a'];
            result['layers'] = data['l'];

            result['status'] = data['st'];
            result['status_string'] = data['ststr'];
            result['protocol'] = data['p'];
            result['connection'] = data['cn'];
            result['odometer'] = data['o'];
            result['engine_hours'] = data['eh'];
            result['service'] = data['sr'];

            if (data['d'] != '') {
                result['data'].push({
                    dt_server: data['d'][0][0],
                    dt_tracker: data['d'][0][1],
                    lat: data['d'][0][2],
                    lng: data['d'][0][3],
                    altitude: data['d'][0][4],
                    angle: data['d'][0][5],
                    speed: data['d'][0][6],
                    params: data['d'][0][7],
                });
            }

            result['task'] = data['task'];

            return result;
        }

        function objectFollow(imei = '866381051024450') {
            //  clearTimeout(timer_objectFollow);

            var data = {
                cmd: 'load_object_data',
                imei: imei,
            };

            $.ajax({
                type: 'POST',
                url: 'https://magnitracking.net/api/v1/main/fn_objects.php',
                data: data,
                dataType: 'json',
                cache: false,
                error: function (statusCode, errorThrown) {
                    // shedule next object reload
                    //  timer_objectFollow = setTimeout("objectFollow('" + imei + "');", gsValues['map_refresh']*1000);
                },
                success: function (result) {
                    // console.log(result)
                    // convert tracking route to normal format
                    for (var imei in result) {
                        console.log(result[imei]['d']);
                        result[imei] = transformToObjectData(result[imei]);
                    }

                    if (Object.keys(objectsData).length != Object.keys(result).length) {
                        objectsData = result;
                    } else {
                        for (var imei in result) {
                            objectsData[imei]['conn_valid'] = result[imei]['conn_valid'];
                            objectsData[imei]['loc_valid'] = result[imei]['loc_valid'];
                            objectsData[imei]['odometer'] = result[imei]['odometer'];
                            objectsData[imei]['status'] = result[imei]['status'];
                            objectsData[imei]['status_string'] = result[imei]['status_string'];
                            objectsData[imei]['engine_hours'] = result[imei]['engine_hours'];
                            objectsData[imei]['service'] = result[imei]['service'];

                            if (objectsData[imei]['data'] == '') {
                                objectsData[imei]['data'] = result[imei]['data'];
                            } else {
                                if (objectsData[imei]['data'].length >= settingsObjectData[imei]['tail_points']) {
                                    objectsData[imei]['data'].pop();
                                }
                                objectsData[imei]['data'].unshift(result[imei]['data'][0]);
                            }
                        }
                    }

                    var lat = objectsData[imei]['data'][0]['lat'];
                    var lng = objectsData[imei]['data'][0]['lng'];

                    let labelLat = document.querySelector('#lat');
                    labelLat.textContent = lat;

                    let labelLng = document.querySelector('#long');
                    labelLng.textContent = lng;

                    // shedule next object reload
                    //  timer_objectFollow = setTimeout("objectFollow('" + imei + "');", gsValues['map_refresh']*1000);
                },
            });
        }

        objectFollow();
    </script>
@endpush
