let map, marker;
let mapex, markerext;
const tasa_iva = 0.16;
const tasa_retencion = 0.04;
const catalogo_clientes = document.querySelector("#txtClientes");
const formCotizacion = document.querySelector('#cotizacionCreateMultiple')
const frmMode = (formCotizacion) ? formCotizacion.getAttribute("sgt-cotizacion-action") : null;

const formFields = [
    {'field':'origen', 'id':'origen','label':'Origen','required': true, "type":"text", "master": true},
    {'field':'destino', 'id':'destino','label':'Destino','required': true, "type":"text", "master": true},
    {'field':'num_contenedor', 'id':'num_contenedor','label':'Núm. Contenedor','required': true, "type":"text", "master": false},
    {'field':'tamano', 'id':'tamano','label':'Tamaño Contenedor','required': true, "type":"numeric", "master": false},
    {'field':'peso_reglamentario', 'id':'peso_reglamentario','label':'Peso Reglamentario','required': true, "type":"numeric", "master": true},
    {'field':'peso_contenedor', 'id':'peso_contenedor','label':'Peso Contenedor','required': true, "type":"numeric", "master": false},
    {'field':'precio_viaje', 'id':'precio_viaje','label':'Precio Viaje','required': true, "type":"money", "master": true},
    {'field':'base_factura', 'id':'base_factura','label':'Base 1','required': true, "type":"money", "master": true},
    {'field':'fecha_modulacion', 'id':'fecha_modulacion','label':'Fecha Modulación','required': false, "type":"text", "master": false},
    {'field':'fecha_entrega', 'id':'fecha_entrega','label':'Fecha Entrega','required': false, "type":"text", "master": false},
    {'field':'sobrepeso', 'id':'sobrepeso','label':'Sobrepeso','required': false, "type":"numeric", "master": false},
    {'field':'precio_sobre_peso', 'id':'precio_sobre_peso','label':'Precio Sobre Peso','required': false, "type":"money", "master": true},
    {'field':'precio_tonelada', 'id':'precio_tonelada','label':'Precio Tonelada','required': false, "type":"money", "master": false},
    {'field':'burreo', 'id':'burreo','label':'Burreo','required': false, "type":"money", "master": true},
    {'field':'maniobra', 'id':'maniobra','label':'Maniobra','required': false, "type":"money", "master": true},
    {'field':'estadia', 'id':'estadia','label':'Estadía','required': false, "type":"money", "master": true},
    {'field':'otro', 'id':'otro','label':'Otros','required': false, "type":"money", "master": true},
    {'field':'iva', 'id':'iva','label':'IVA','required': false, "type":"money", "master": true},
    {'field':'retencion', 'id':'retencion','label':'Retención','required': false, "type":"money", "master": true},
    {'field':'base_taref', 'id':'base_taref','label':'Base 2','required': false, "type":"money", "master": true},
    {'field':'total', 'id':'total','label':'Total','required': false, "type":"money", "master": true},  
    {'field':'terminal', 'id':'terminal','label':'Terminal','required': false, "type":"text", "master": false},  
    {'field':'num_autorizacion', 'id':'num_autorizacion','label':'Num. Autorización','required': false, "type":"text", "master": false},  
    {'field':'bloque', 'id':'bloque','label':'Block','required': false, "type":"text", "master": false},       
];

const editFormFields = [
    {'field':'terminal', 'id':'terminal','label':'Terminal','required': false, "type":"text", "master": false},
    {'field':'num_autorizacion', 'id':'num_autorizacion','label':'Num. Autorización','required': false, "type":"text", "master": false},
    {'field':'bloque', 'id':'bloque','label':'Block','required': false, "type":"text", "master": false},
    {'field':'bloque_hora_i', 'id':'bloque_hora_i','label':'Hora inicio bloque','required': false, "type":"text", "master": false},
    {'field':'bloque_hora_f', 'id':'bloque_hora_f','label':'Hora fin bloque','required': false, "type":"text", "master": false},
    {'field':'num_boleta_liberacion', 'id':'num_boleta_liberacion','label':'Núm. Boleta de Liberación','required': false, "type":"text", "master": false},
    {'field':'num_doda', 'id':'num_doda','label':'Num. Doda','required': false, "type":"text", "master": false},
    {'field':'num_carta_porte', 'id':'num_carta_porte','label':'Núm. Carta Porte','required': false, "type":"text", "master": false},
    {'field':'boleta_vacio', 'id':'boleta_vacio','label':'Boleta vacio','required': false, "type":"text", "master": false},
    {'field':'fecha_boleta_vacio', 'id':'fecha_boleta_vacio','label':'Fecha Boleta vacío','required': false, "type":"text", "master": false},
    {'field':'eir', 'id':'eir','label':'EIR','required': false, "type":"text", "master": false},
    {'field':'cima', 'id':'cima','label':'Administrado por CIMA','required': false, "type":"text", "master": false},    
    {'field':'ccp', 'id':'ccp','label':'Formato CCP','required': false, "type":"text", "master": false},    
    {'field':'direccion_recinto', 'id':'direccion_recinto','label':'Dirección recinto','required': false, "type":"text", "master": false},  
    {'field':'text_recinto', 'id':'text_recinto','label':'¿Va a recinto?','required': false, "type":"text", "master": false},  
    {'field':'fecha_modulacion', 'id':'fecha_modulacion','label':'Fecha modulación','required': false, "type":"text", "master": false},  
    {'field':'fecha_entrega', 'id':'fecha_entrega','label':'Fecha Entrega','required': false, "type":"text", "master": false},  
    {'field':'fecha_eir', 'id':'fecha_eir','label':'Fecha EIR','required': false, "type":"text", "master": false},  
    {'field':'total', 'id':'total','label':'Total + Gastos','required': false, "type":"money", "master": false}, 
    {'field':'direccion_entrega', 'id':'direccion_entrega','label':'Dirección de entrega','required': false, "type":"text", "master": false}, 
    
]

let Contenedores = [];
let ContenedorA = []
let ContenedorB = []

const formFieldsBloque = [
    {'field':'bloque','id':'bloque','label':'Block','required': false, "type":"text", "trigger":"none"},
    {'field':'bloque_hora_i','id':'bloque_hora_i','label':'Hora Inicio','required': false, "type":"text", "trigger":"bloque"},
    {'field':'bloque_hora_f','id':'bloque_hora_f','label':'Hora Fin','required': false, "type":"text", "trigger":"bloque"},
]

const formFieldsMec = [
    {'field':'text_recinto','id':'text_recinto','label':'recinto','required': false, "type":"text", "trigger":"none"},
    {'field':'direccion_entrega','id':'direccion_entrega','label':'Dirección Entrega','required': true, "type":"text", "trigger":"none"},
    {'field':'direccion_recinto','id':'direccion_recinto','label':'Dirección recinto','required': false, "type":"text", "trigger":"text_recinto"},
    {'field':'id_proveedor','id':'id_proveedor','label':'Proveedor','required': true, "type":"text", "trigger":"none"},
    {'field':'id_transportista','id':'id_transportista','label':'Transportista','required': true, "type":"text", "trigger":"none"}


]

const formFieldsFacturacion = [
    {'field':'id_uso_cfdi','id':'id_uso_cfdi','label':'Seleccione Uso del CFDI','required': true, "type":"text", "trigger":"none"},
    {'field':'id_forma_pago','id':'id_forma_pago','label':'Selecciones Forma de Pago','required': true, "type":"text", "trigger":"none"},
    {'field':'id_metodo_pago','id':'id_metodo_pago','label':'Seleccione Método de Pago','required': true, "type":"text", "trigger":"none"}

]

const formFieldsProveedor = [
    {'field':'id_proveedor','id':'id_proveedor','label':'Proveedor','required': true, "type":"text"},
    {'field':'precio_viaje','id':'precio_proveedor','label':'Costo Viaje','required': true, "type":"money"},
    {'field':'burreo','id':'burreo_proveedor','label':'Burreo Proveedor','required': true, "type":"money"},
    {'field':'maniobra','id':'maniobra_proveedor','label':'Maniobra Proveedor','required': true, "type":"money"},
    {'field':'estadia','id':'estadia_proveedor','label':'Estadía','required': true, "type":"money"},
    {'field':'sobrepeso','id':'cantidad_sobrepeso_proveedor','label':'Sobre Peso','required': true, "type":"text"},
    {'field':'precio_sobre_peso','id':'sobrepeso_proveedor','label':'Precio Sobre Peso','required': true, "type":"money"},
    {'field':'precio_tonelada','id':'total_tonelada','label':'Total Tonelada','required': false, "type":"money"},
    {'field':'base_factura','id':'base1_proveedor','label':'Base 1','required': true, "type":"money"},
    {'field':'base_taref','id':'base2_proveedor','label':'Base 2','required': true, "type":"money"},
    {'field':'otro','id':'otro_proveedor','label':'Otros','required': false, "type":"money"},
    {'field':'iva','id':'iva_proveedor','label':'IVA','required': false, "type":"money"},
    {'field':'retencion','id':'retencion_proveedor','label':'Retención','required': false, "type":"money"},
    {'field':'total','id':'total_proveedor','label':'Total','required': false, "type":"money"},
]

$(".moneyformat").on("focus",(e)=>{
var val = e.target.value;
e.target.value = reverseMoneyFormat(val);
})

$(".moneyformat").on("blur",(e) =>{
var val = e.target.value;
e.target.value =  moneyFormat(val);
})

function calcularTotal(modulo = 'crear') {
    const fields = (modulo == 'crear') ? formFields : formFieldsProveedor;

    const field_precio_viaje = fields.find( i => i.field == "precio_viaje");
    const field_burreo = fields.find( i => i.field == "burreo");
    const field_otro = fields.find( i => i.field == "otro");
    const field_estadia = fields.find( i => i.field == "estadia");
    const field_maniobra = fields.find( i => i.field == "maniobra");

    const precio_viaje = parseFloat(reverseMoneyFormat(document.getElementById(field_precio_viaje.id).value)) || 0;
    const burreo = parseFloat(reverseMoneyFormat(document.getElementById(field_burreo.id).value)) || 0;
    const otro = parseFloat(reverseMoneyFormat(document.getElementById(field_otro.id).value)) || 0;
    const estadia = parseFloat(reverseMoneyFormat(document.getElementById(field_estadia.id).value)) || 0;
    const maniobra = parseFloat(reverseMoneyFormat(document.getElementById(field_maniobra.id).value)) || 0;

    const subTotal = precio_viaje + burreo + maniobra + estadia + otro;

    const field_base_factura = fields.find( i => i.field == "base_factura");

    const baseFactura = parseFloat(reverseMoneyFormat(document.getElementById(field_base_factura.id).value)) || 0;
    const iva = (baseFactura * tasa_iva);
    const retencion = (baseFactura * tasa_retencion);

    const field_iva = fields.find( i => i.field == "iva");
    const field_retencion = fields.find( i => i.field == "retencion");

    document.getElementById(field_iva.id).value = moneyFormat(iva);
    document.getElementById(field_retencion.id).value = moneyFormat(retencion);

    // Restar el valor de Retención del total
    const totalSinRetencion = precio_viaje + burreo + iva + otro + estadia + maniobra;
    const totalConRetencion = totalSinRetencion - retencion;

    // Obtener el valor de Precio Tonelada
    //const field_precio_tonelada = fields.find( i => i.field == "precio_tonelada");
    const precioTonelada = (modulo != "proveedores") 
    ? parseFloat(reverseMoneyFormat(document.getElementById('total_sobrepeso_viaje').value)) ||0 
    : parseFloat(reverseMoneyFormat(document.getElementById('total_tonelada').value));

    // Sumar el valor de Precio Tonelada al total
    const totalFinal = totalConRetencion + precioTonelada;
    

    if((modulo != "proveedores") && document.querySelector("#txtSumGastos")){
        let SumGastos = parseFloat(reverseMoneyFormat(document.querySelector("#txtSumGastos").value)) || 0;
        let txtResultGastos  = document.querySelectorAll(".txtResultGastos");
        txtResultGastos.forEach((r) => r.value = moneyFormat(totalFinal + SumGastos))
    }
    

    let totalCotizacion  = (modulo == "proveedores") ? document.querySelectorAll(".total-cotizacion-proveedor") : document.querySelectorAll(".total-cotizacion");
    totalCotizacion.forEach((r) =>{
         r.value = moneyFormat(totalFinal)
        
    })
    //baseTaref Corresponde a Base 2
    const baseTaref = (totalFinal - baseFactura - iva) + retencion;
    // Mostrar el resultado en el input de base_taref
    const field_base_taref = fields.find( i => i.field == "base_taref");
    console.log(field_base_taref.id)
    document.getElementById(field_base_taref.id).value = moneyFormat(baseTaref);

    // Formatear el total con comas
    //const totalFormateado = moneyFormat(totalFinal);
    //const field_total = fields.find( i => i.field == "total");
   // document.getElementById(field_total.id).value = totalFormateado;
  //  console.log(totalFormateado)

}

function getClientes(clienteId){
    $.ajax({
        type: 'GET',
        url: '/subclientes/' + clienteId,
        success: function(data) {
            $('#id_subcliente').empty();
            $('#id_subcliente').append('<option value="">Seleccionar subcliente</option>');
            $.each(data, function(key, subcliente) {
                $('#id_subcliente').append('<option value="' + subcliente.id + '">' + subcliente.nombre + '</option>');
            });
            $('#id_subcliente').select2();
        }
    });
}


 


let geocoder;

/* function cargarmapas() {
    const modal = document.getElementById('mapModal');

    modal.addEventListener('shown.bs.modal', function () {
        if (!map) {
            // Inicializa el mapa
            map = new google.maps.Map(document.getElementById('map'), {
                center: { lat: 19.4326, lng: -99.1332 }, // CDMX por defecto
                zoom: 12,
            });

            geocoder = new google.maps.Geocoder();

            // Click para agregar marcador
            map.addListener('click', function (e) {
                const lat = e.latLng.lat();
                const lng = e.latLng.lng();

                if (marker) marker.setMap(null);

                marker = new google.maps.Marker({
                    position: { lat, lng },
                    map: map,
                });

                document.getElementById('latitud').value = lat.toFixed(6);
                document.getElementById('longitud').value = lng.toFixed(6);

                // Geocodificación inversa
                geocoder.geocode({ location: { lat, lng } }, (results, status) => {
                    if (status === "OK" && results[0]) {
                        document.getElementById('direccion_mapa').value = results[0].formatted_address;
                    } else {
                        console.error("No se pudo obtener la dirección:", status);
                    }
                });
            });
        } else {
           
        }

        // Si hay dirección inicial pero no lat/lng, geocodificar
        const latVal = document.getElementById('latitud').value;
        const lngVal = document.getElementById('longitud').value;
        const direccion = document.getElementById('direccion_entrega').value;

        if ((!latVal || !lngVal) && direccion) {
            geocoder.geocode({ address: direccion }, (results, status) => {
                if (status === "OK" && results[0]) {
                    const location = results[0].geometry.location;
                    map.setCenter(location);
                    map.setZoom(16);

                    if (marker) marker.setMap(null);
                    marker = new google.maps.Marker({
                        position: location,
                        map: map,
                    });

                    document.getElementById('latitud').value = location.lat().toFixed(6);
                    document.getElementById('longitud').value = location.lng().toFixed(6);
                    document.getElementById('direccion_mapa').value = direccion;
                } else {
                    alert("No se pudo localizar la dirección. Verifica el formato o selecciona manualmente.");
                }
            });
        }
    });

    // Buscar por input
    document.getElementById('searchInput').addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            let query = this.value;

            geocoder.geocode({ address: query }, (results, status) => {
                if (status === "OK" && results[0]) {
                    const location = results[0].geometry.location;
                    map.setCenter(location);
                    map.setZoom(16);
                } else {
                    console.error("No se encontró la dirección:", status);
                }
            });
        }
    });
} */
document.addEventListener('DOMContentLoaded', function () {
   // cargarmapas();

    const catalogo_clientes = document.querySelector("#txtClientes");
        const formCotizacion = document.querySelector('#cotizacionCreateMultiple');

        let frmMode = null;

        if (formCotizacion && formCotizacion.hasAttribute('sgt-cotizacionCreate-action')) {
            frmMode = formCotizacion.getAttribute('sgt-cotizacionCreate-action');
        }
    // Obtener elementos del DOM
    var pesoReglamentarioInput = document.getElementById('peso_reglamentario');
    var pesoContenedorInput = document.getElementById('peso_contenedor');
    var sobrepesoInput = document.getElementById('sobrepeso');

    var precioSobrePesoInput = document.getElementById('precio_sobre_peso');
    var precioToneladaInput = document.getElementById('precio_tonelada');
    var precioToneladaViajeInput = document.getElementById('total_sobrepeso_viaje');

    var precioSobrePesoProveedor = document.getElementById('sobrepeso_proveedor')
    var sobrePesoProveedor = document.getElementById('cantidad_sobrepeso_proveedor');
    var precioToneladaProveedor = document.getElementById('total_tonelada');

    // Agregar evento de cambio a los inputs
    pesoReglamentarioInput.addEventListener('input', calcularSobrepeso);
    pesoContenedorInput.addEventListener('input', calcularSobrepeso);
    pesoContenedorInput.addEventListener('input', calcularSobrepeso);
    pesoContenedorInput.addEventListener('input', valorSobrePrecio);

    var sobrepesoViajeInput = document.getElementById('sobrepeso_viaje');


    function valorSobrePrecio(){
        // Obtener el valor de Sobrepeso
        var sobrepeso = parseFloat(sobrepesoInput.value.replace(/,/g, '')) || 0;
   
        // Obtener el valor de Precio Sobre Peso
        var precioSobrePeso = parseFloat(reverseMoneyFormat(precioSobrePesoInput.value)) || 0;
   
        // Calcular el resultado de la multiplicación
        var resultado = sobrepeso * precioSobrePeso;
   
        // Mostrar el resultado en el campo "Precio Tonelada"
        precioToneladaInput.value = moneyFormat(resultado); 

        //Tomar en cuenta el sobrepeso de todos los contenedores. Para obtener el sobrepeso del viaje
        let sobrePeso = reverseMoneyFormat(sobrepesoViajeInput.value)
        precioToneladaViajeInput.value = moneyFormat(sobrePeso * precioSobrePeso)
   
        // Calcular el total
        calcularTotal();
   }

   function valorSobrePrecioProveedor(){
    // Obtener el valor de Sobrepeso
    var sobrepeso = parseFloat(sobrePesoProveedor.value.replace(/,/g, '')) || 0;

    // Obtener el valor de Precio Sobre Peso
    var precioSobrePeso = parseFloat(reverseMoneyFormat(precioSobrePesoProveedor.value)) || 0;

    // Calcular el resultado de la multiplicación
    var resultado = sobrepeso * precioSobrePeso;

    // Mostrar el resultado en el campo "Precio Tonelada"
    precioToneladaProveedor.value = moneyFormat(resultado); 

    // Calcular el total
    calcularTotal('proveedores');
    }
    // Función para calcular el sobrepeso
    function calcularSobrepeso() {
     
        var pesoReglamentario = parseFloat(pesoReglamentarioInput.value) || 0;
        var pesoContenedor = parseFloat(pesoContenedorInput.value) || 0;

        // Calcular sobrepeso
        var sobrepeso = Math.max(pesoContenedor - pesoReglamentario, 0);

        // Mostrar sobrepeso en el input correspondiente con dos decimales

        if(sobrepesoInput){
            sobrepesoInput.value = sobrepeso.toFixed(4);
        }
       

       
        sobrePesoViaje()
        // Calcular el total
        calcularTotal();
    }

    // Agregar evento de entrada al campo "Precio Sobre Peso"
    if(precioSobrePesoInput){
        precioSobrePesoInput.addEventListener('input', ()=> {
            valorSobrePrecio();
        });
    }


    if(precioSobrePesoProveedor){
        precioSobrePesoProveedor.addEventListener('input', ()=> {
            valorSobrePrecioProveedor();
        });
    }
    

    // Calcular sobrepeso inicialmente al cargar la página
    calcularSobrepeso();

    // Agregar eventos de cambio a los inputs para calcular automáticamente
    document.getElementById('base_factura').addEventListener('input', ()=>{calcularTotal()});
    var inputMoneyFormat = $('.calculo-cotizacion');
    inputMoneyFormat.on('input',()=>{calcularTotal()})
    var inputMoneyFormatProveedores = $('.calculo-proveedor');
    inputMoneyFormatProveedores.on('input',()=>{calcularTotal('proveedores')})


 var modal = document.getElementById('mapModal');
    modal.addEventListener('shown.bs.modal', function () {
        if (!map) {
            // Inicializar Google Maps
            map = new google.maps.Map(document.getElementById('map'), {
                center: { lat: 19.4326, lng: -99.1332 }, // CDMX por defecto
                zoom: 12,
            });

            // Click en el mapa
            map.addListener('click', function (e) {
                const lat = e.latLng.lat().toFixed(6);
                const lng = e.latLng.lng().toFixed(6);

                if (marker) marker.setMap(null);
                marker = new google.maps.Marker({
                    position: { lat: parseFloat(lat), lng: parseFloat(lng) },
                    map: map
                });

                document.getElementById('latitud').value = lat;
                document.getElementById('longitud').value = lng;

                fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
                    .then(res => res.json())
                    .then(data => {
                        const direccion = data.display_name;
                        document.getElementById('direccion_entrega').value = direccion;
                        document.getElementById('direccion_mapa').value = direccion;
                    });
            });
        }

        const lat = parseFloat(document.getElementById('latitud').value);
        const lng = parseFloat(document.getElementById('longitud').value);
        const direccion = document.getElementById('direccion_entrega').value;

        if ((!lat || !lng) && direccion) {
            // Geocoding con dirección
            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(direccion)}`)
                .then(res => res.json())
                .then(results => {
                    if (results.length > 0) {
                        const latitud = parseFloat(results[0].lat);
                        const longitud = parseFloat(results[0].lon);

                        map.setCenter({ lat: latitud, lng: longitud });
                        map.setZoom(16);

                        if (marker) marker.setMap(null);
                        marker = new google.maps.Marker({
                            position: { lat: latitud, lng: longitud },
                            map: map
                        });

                        document.getElementById('latitud').value = latitud.toFixed(6);
                        document.getElementById('longitud').value = longitud.toFixed(6);
                        document.getElementById('direccion_mapa').value = direccion;
                    } else {
                        alert("No se pudo localizar la dirección. Verifica el formato o selecciona manualmente.");
                    }
                })
                .catch(err => {
                    console.error("Error al buscar dirección:", err);
                    alert("Error al contactar el servicio de mapas.");
                });
        }
    });

   document.getElementById('searchInput').addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            let query = this.value;
            if (esShortUrlGoogleMaps(query)) {
                resolverUrlMapa(query);
            } else {
                query = formatearDireccion(query);
                fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}`)
                    .then(res => res.json())
                    .then(results => {
                        if (results.length > 0) {
                            const lat = parseFloat(results[0].lat);
                            const lng = parseFloat(results[0].lon);
                            map.setCenter({ lat, lng });
                            map.setZoom(16);
                            geocodificardireccion(lat, lng);
                        }
                    });
            }
        }
    });
});

function resolverUrlMapa(url) {
    const _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    fetch('/coordenadas/resolver-link-google', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': _token,
            'Accept': 'application/json',
        },
        body: JSON.stringify({ shortUrl: url.trim() })
    })
        .then(res => res.json())
        .then(data => {
            if (data.lat && data.lng) {
                map.setCenter({ lat: data.lat, lng: data.lng });
                map.setZoom(16);

                if (marker) marker.setMap(null);
                marker = new google.maps.Marker({
                    position: { lat: data.lat, lng: data.lng },
                    map: map
                });

                geocodificardireccion(data.lat, data.lng);
              //  document.getElementById('latitud').value = data.lat.toFixed(6);
                //document.getElementById('longitud').value = data.lng.toFixed(6);
              //  document.getElementById('direccion_entrega').value = data.formatted_address || 'Ubicación desde Google Maps';
               // document.getElementById('direccion_mapa').value = data.formatted_address || 'Ubicación desde Google Maps';
            } else {
                alert('No se pudo obtener la ubicación desde el enlace.');
            }
        })
        .catch(err => {
            console.error('Error en la petición:', err);
            alert('Error al contactar al servidor.');
        });
}

function geocodificardireccion(lat, long){
    const geocoder = new google.maps.Geocoder();
    geocoder.geocode({ location: { lat: parseInt(lat), lng: parseInt(long) } }, (results, status) => {
        if (status === "OK") {
            if (results[0]) {
                // results[0].formatted_address tiene la dirección
            let direccion = results[0].formatted_address;
            document.getElementById('latitud').value = lat.toFixed(6);
            document.getElementById('longitud').value = long.toFixed(6);
            // O si quieres mostrarla en un div aparte:
          // document.getElementById('direccion_mapa').value= direccion;
            document.getElementById('direccion_entrega').value = direccion;
        } else {
            console.log("No se encontró dirección");
        }
    } else {
        console.error("Error en Geocoder:", status);
    }
});
}

function esShortUrlGoogleMaps(url) {
    const regex = /^https?:\/\/maps\.app\.goo\.gl\/.+$/i;
    return regex.test(url);
}

function formatearDireccion(raw) {
    if (!raw || typeof raw !== 'string') return '';

    let dir = raw.trim().toLowerCase();

    const reemplazos = [
        { regex: /\bcp\b/gi, texto: '' },
        { regex: /\bedo de mexico\b/gi, texto: 'Estado de México' },
        { regex: /\bsan\b/gi, texto: 'San' },
        { regex: /\bprolongacion\b/gi, texto: 'Prolongación' },
        { regex: /\blecheria\b/gi, texto: 'Lechería' },
        { regex: /\btulantongo\b/gi, texto: 'Tulantongo' },
        { regex: /\bjuarez\b/gi, texto: 'Juárez' }
    ];

    reemplazos.forEach(r => dir = dir.replace(r.regex, r.texto));
    dir = dir.replace(/\s{2,}/g, ' ').replace(/,+/g, ',').replace(/(^,|,$)/g, '');
    dir = dir.split(' ').map(p => p.charAt(0).toUpperCase() + p.slice(1)).join(' ');
    if (!dir.toLowerCase().includes('méxico')) dir += ', México';
    return dir;
}

var pesoReglamentarioInput = document.getElementById('peso_reglamentario');
var sobrepesoInput = document.getElementById('sobrepeso');
var pesoContenedorInput = document.getElementById('peso_contenedor');
var sobrePesoViajeInput = document.querySelector('#sobrepeso_viaje')
var totalSobrePesoViaje = document.querySelector('#total_sobrepeso_viaje')
var precioSobrePesoInpu = document.getElementById('precio_sobre_peso');
pesoContenedorInput.addEventListener('input', calcularSobrepeso);

function sobrePesoViaje(){
    let tabSelected = document.querySelector('input[name="contenedorTabs"]:checked');
    initContenedores(tabSelected.value)

    let tipoViajeSelected = document.querySelector('input[name="plan"]:checked');

    let sobrePesoB = (tipoViajeSelected.value == "Sencillo") ? 0 : parseFloat( ContenedorB['sobrepeso'] || 0)
    let viajeSobrePeso = parseFloat( ContenedorA['sobrepeso'] || 0) + sobrePesoB
    sobrePesoViajeInput.value = viajeSobrePeso
    let precio = reverseMoneyFormat(precioSobrePesoInpu.value)

    totalSobrePesoViaje.value = moneyFormat( parseFloat( viajeSobrePeso || 0) * parseFloat( precio || 0))

    var sobrePesoProveedor = document.getElementById('cantidad_sobrepeso_proveedor');
    if(sobrePesoProveedor){
        sobrePesoProveedor.value = viajeSobrePeso.toFixed(4);
        
    }
}

function calcularSobrepeso() {
    var pesoReglamentario = parseFloat(pesoReglamentarioInput.value) || 0;
    var pesoContenedor = parseFloat(pesoContenedorInput.value) || 0;

    // Calcular sobrepeso
    var sobrepeso = Math.max(pesoContenedor - pesoReglamentario, 0);

    // Mostrar sobrepeso en el input correspondiente con dos decimales
    if(sobrepesoInput){
        sobrepesoInput.value = sobrepeso.toFixed(4);
    }
   
    var sobrePesoProveedor = document.getElementById('cantidad_sobrepeso_proveedor');
    if(sobrePesoProveedor){
        sobrePesoProveedor.value = sobrepeso.toFixed(4);
        
    }

    sobrePesoViaje()
    // Calcular el total
    calcularTotal();
}

$('#id_cliente').change(function() {
    var clienteId = $(this).val();
    if (clienteId) {
        var dataClientes = JSON.parse(catalogo_clientes.value);
        dataClientes.forEach((i)=>{
            if(i.id == clienteId){
                $("#telClient").text(i.telefono)
                $("#mailClient").text(i.correo.toLowerCase())

            }
        })
        getClientes(clienteId);
    } else {
        $('#id_subcliente').empty();
        $('#id_subcliente').append('<option value="">Seleccionar subcliente</option>');
    }
   
});



async function getContenedoresOnFull(){
    let _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    let referencia = document.querySelector('#referencia_full')

    let uuid = referencia.textContent

    if(uuid.length == 0) return false
    let ContenedorFields = ContenedorA

await $.ajax({
    url:'/cotizaciones/full',
    type:'post',
    data:{_token, uuid},
    beforeSend:()=>{},
    success:(response)=>{
        
        ContenedorA = response[0]
        ContenedorB = response[1]

        sobrePesoViaje()
        calcularTotal()
        
    },
    error:()=>{

    }
})
}

function initContenedores(Contenedor, action = 'create'){
    const formData = {};
    let specificFields = formFields.filter((f) => f.master == false )
    
    specificFields.forEach((item) =>{
     var input = item.field;
     var inputValue = document.getElementById(input);
     if(inputValue){
         if(item.type == "money"){
             formData[input] = (inputValue.value.length > 0) ? parseFloat(reverseMoneyFormat(inputValue.value)) : 0;
         }else{
             formData[input] = inputValue.value;
         }
     }
    });

    //Agregar manualmente 2 campos
    if(action == "edit"){
        editFormFields.forEach((item) =>{
            var input = item.field;
            var inputValue = document.getElementById(input);
            if(inputValue){
                if(item.type == "money"){
                    formData[input] = (inputValue.value.length > 0) ? parseFloat(reverseMoneyFormat(inputValue.value)) : 0;
                }else{
                    formData[input] = inputValue.value;
                }
            }
        })
    }

   formData['sobrepeso'] = sobrepesoInput.value;
   //formData['precio_sobre_peso'] = precioSobrePesoInput.value;

    formData['Contenedor'] = Contenedor
    if(Contenedor == "Contenedor-A"){
        formData['jerarquia'] = 'Principal'
        ContenedorA = {...formData}
        
    }else{
        formData['jerarquia'] = 'Secundario'
        ContenedorB = {...formData}
      
    }
    
}

function valorSobrePrecioContenedor(){
    var precioSobrePesoInput = document.getElementById('precio_sobre_peso');
    var precioToneladaInput = document.getElementById('precio_tonelada');
    var sobrepesoInput = document.getElementById('sobrepeso');
    // Obtener el valor de Sobrepeso
    var sobrepeso = parseFloat(sobrepesoInput.value.replace(/,/g, '')) || 0;

    // Obtener el valor de Precio Sobre Peso
    var precioSobrePeso = parseFloat(reverseMoneyFormat(precioSobrePesoInput.value)) || 0;

    // Calcular el resultado de la multiplicación
    var resultado = sobrepeso * precioSobrePeso;

    // Mostrar el resultado en el campo "Precio Tonelada"
    precioToneladaInput.value = moneyFormat(resultado); 

    // Calcular el total
   // calcularTotal();
}

function showInfoContenedor(Contenedor){
    
    //Guardamos los datos del contenedor activo
    let contenedorActivo = (Contenedor == 'Contenedor-A')  ? 'Contenedor-B' : 'Contenedor-A';
    initContenedores(contenedorActivo,frmMode)
    //Cargamos los datos del contenedor que se desea visualizar
    let fieldsContenedor = (Contenedor == 'Contenedor-A')  ? ContenedorA : ContenedorB;

    let specificFields = formFields.filter((f) => f.master == false )
    
    specificFields.forEach((item) =>{
        var input = item.field;
        var htmlField = document.getElementById(input);
        if(htmlField){
            if(item.type == "money"){
                htmlField.value = moneyFormat(fieldsContenedor[input]);
            }else{
                htmlField.value = fieldsContenedor[input];
            }
        }
       });

       let labelNumContedor = document.querySelectorAll('.labelNumContedor')
       labelNumContedor.forEach((c) =>{
        let cont = document.getElementById(c.id)
        if(cont) cont.textContent = fieldsContenedor['num_contenedor']
       })

       localStorage.setItem('numContenedor',fieldsContenedor['num_contenedor'])
       getFilesContenedor()
       
       calcularTotal()
       valorSobrePrecioContenedor()
}

async function  validarContenedores (Contenedor) {
    let fieldsContenedor = (Contenedor == 'Contenedor-A')  ? ContenedorA : ContenedorB;
    let specificFields = formFields.filter((f) => f.master == false )
    
    var passValidation = specificFields.every((item) => {
        var field = fieldsContenedor[item.field];
       
            if(item.required === true && field.length == 0){
                Swal.fire(`Lo sentimos, el campo "${item.label}" de "${Contenedor}" es obligatorio.`,"Parece que no ha proporcionado información en el campo "+item.label,"warning");
                return false;
            }
        
        return true;
    })

   if(!passValidation) return passValidation;

   //Validaciones con condicionantes
   /*let sobrePeso = fieldsContenedor['sobrepeso'];
   let precioSobrePeso = fieldsContenedor['precio_sobre_peso'];
   if(sobrePeso > 0 && precioSobrePeso.length <= 0){
        Swal.fire(`Lo sentimos, el campo "Precio Sobre Peso" de "${Contenedor}" es obligatorio`,"Parece que no ha proporcionado información en el campo Precio Sobre Peso","warning");
        return false;
   }*/

   return true;
  
}

$("#cotizacionCreateMultiple").on("submit", async function(e){
    e.preventDefault();

    const actionFrm = this.getAttribute("sgt-cotizacion-action");

    var form = $(this);
    var url = form.attr('action');
    let input = document.querySelector('input[name="plan"]:checked');
    let tipoCotizacion = input.value
    
    let tabSelected = document.querySelector('input[name="contenedorTabs"]:checked');
   
    initContenedores(tabSelected.value,actionFrm)
    
   let isValidForm = await validarContenedores('Contenedor-A');
   
   if(tipoCotizacion == "Full" && isValidForm){
    isValidForm = await validarContenedores('Contenedor-B');
   }
   
   if(!isValidForm) return false

    if($("#id_cliente").val() == ""){
        Swal.fire("Seleccione Cliente","Aún no ha seleccionado Cliente, este es un campo requerido","warning");
        return false;
    }

    const selectSubClient = document.getElementById("id_subcliente");
    const subClientQty = selectSubClient.options.length;

    if(subClientQty > 1 && $("#id_subcliente").val() == ""){
        Swal.fire("Seleccione SubCliente","Aún no ha seleccionado SubCliente, este es un campo requerido","warning");
        return false;
    }

    let contenedores = [];
    contenedores = [...contenedores, ContenedorA]
    
    if(tipoCotizacion == "Full" ){
        contenedores = [...contenedores, ContenedorB]
    }
   
/** */
    var passValidation = formFields.every((item) => {
        var field = document.getElementById(item.field);
        if(field && item.master == true){
            if(item.required === true && field.value.length == 0){
                Swal.fire("El campo "+item.label+" es obligatorio","Parece que no ha proporcionado información en el campo "+item.label,"warning");
                return false;
            }
        }
        return true;
    })

    if(!passValidation) return passValidation;

    //Validaciones con condicionantes
    let sobrePeso = document.getElementById('sobrepeso').value;
    let precioSobrePeso = document.getElementById('precio_sobre_peso').value;
    if(sobrePeso > 0 && precioSobrePeso.length <= 0){
        Swal.fire("El campo Precio Sobre Peso es obligatorio","Parece que no ha proporcionado información en el campo Precio Sobre Peso","warning");
        return false;
    }

    const formData = {};

    formFields.forEach((item) =>{
    var input = item.field;
    var inputValue = document.getElementById(input);
    if(inputValue && item.master == true){
        if(item.type == "money"){
            formData[input] = (inputValue.value.length > 0) ? parseFloat(reverseMoneyFormat(inputValue.value)) : 0;
        }else{
            formData[input] = inputValue.value;
        }
    }
    });

/**Si estamos editando y tiene asignado un proveedor... validar campos de proveedor */
    if(actionFrm == "edit"){
        
        var passValidation = formFieldsProveedor.every((item) => {
            var field = document.getElementById(item.field);
            if(field){
                if(item.required === true && field.value.length == 0){
                    Swal.fire("El campo "+item.label+" es obligatorio","Parece que no ha proporcionado información en el campo "+item.label,"warning");
                    return false;
                }
            }
            return true;
        })
    
        if(!passValidation) return passValidation;

        formFieldsProveedor.forEach((item) =>{
            var input = item.id;
            var inputValue = document.getElementById(input);
            if(inputValue){
                if(item.type == "money"){
                    formData[input] = (inputValue.value.length > 0) ? parseFloat(reverseMoneyFormat(inputValue.value)) : 0;
                }else{
                    formData[input] = inputValue.value;
                }
            }
            });
        
    }

    formData["_token"] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    formData["id_cliente"] = $("#id_cliente").val();
    formData["id_subcliente"] = selectSubClient.value;
    formData["Contenedores"] = contenedores
    formData["TipoCotizacion"] = tipoCotizacion
    formData["sobrePeso"] = sobrePeso
    formData["precioSobrePeso"] = reverseMoneyFormat(precioSobrePeso)
 //coordenadas para comparacion
    formData["latitud"] = document.getElementById("latitud")?.value ?? null;
    formData["longitud"] = document.getElementById("longitud")?.value ?? null;
    formData["direccion_mapa"] = document.getElementById("direccion_mapa")?.value ?? null;
    formData["fecha_seleccion"] = document.getElementById("fecha_seleccion")?.value ?? null;


    formData["latitud"] = document.getElementById("latitud")?.value ?? null;
    formData["longitud"] = document.getElementById("longitud")?.value ?? null;
    formData["direccion_mapa"] = document.getElementById("direccion_mapa")?.value ?? null;
    formData["fecha_seleccion"] = document.getElementById("fecha_seleccion")?.value ?? null;


    $.ajax({
        url: url,
        type: "post",
        data: formData,
        beforeSend:function(){
        
        },
        success:function(data){
                Swal.fire(data.Titulo,data.Mensaje,data.TMensaje).then(function() {
                    if(data.TMensaje == "success"){
                       
                            location.reload();
                        
                    
                    }
                });
        },
        error:function(){       
        Swal.fire("Error","Ha ocurrido un error, intentelo nuevamente","error");
        }
    })
   
    
});

$("#cotizacionCreate").on("submit", function(e){
    e.preventDefault();
    var form = $(this);
    var url = form.attr('action');

    //Validaciones a campos obligatorios
    if($("#id_cliente").val() == ""){
        Swal.fire("Seleccione Cliente","Aún no ha seleccionado Cliente, este es un campo requerido","warning");
        return false;
    }

    const selectSubClient = document.getElementById("id_subcliente");
    const subClientQty = selectSubClient.options.length;

    if(subClientQty > 1 && $("#id_subcliente").val() == ""){
        Swal.fire("Seleccione SubCliente","Aún no ha seleccionado SubCliente, este es un campo requerido","warning");
        return false;
    }

    var passValidation = formFields.every((item) => {
        var field = document.getElementById(item.field);
        if(field){
            if(item.required === true && field.value.length == 0){
                Swal.fire("El campo "+item.label+" es obligatorio","Parece que no ha proporcionado información en el campo "+item.label,"warning");
                return false;
            }
        }
        return true;
    })

   if(!passValidation) return passValidation;

   //Validaciones con condicionantes
   let sobrePeso = document.getElementById('sobrepeso').value;
   let precioSobrePeso = document.getElementById('precio_sobre_peso').value;
   if(sobrePeso > 0 && precioSobrePeso.length <= 0){
        Swal.fire("El campo Precio Sobre Peso es obligatorio","Parece que no ha proporcionado información en el campo Precio Sobre Peso","warning");
        return false;
   }

   const formData = {};

   formFields.forEach((item) =>{
    var input = item.field;
    var inputValue = document.getElementById(input);
    if(inputValue){
        if(item.type == "money"){
            formData[input] = (inputValue.value.length > 0) ? parseFloat(reverseMoneyFormat(inputValue.value)) : 0;
        }else{
            formData[input] = inputValue.value;
        }
    }
   });

   formData["_token"] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
   formData["id_cliente"] = $("#id_cliente").val();
   formData["id_subcliente"] = selectSubClient.value;

   
  //
    formData["latitud"] = document.getElementById("latitud")?.value ?? null;
    formData["longitud"] = document.getElementById("longitud")?.value ?? null;
    formData["direccion_mapa"] = document.getElementById("direccion_mapa")?.value ?? null;
    formData["fecha_seleccion"] = document.getElementById("fecha_seleccion")?.value ?? null;

   var uuid = localStorage.getItem('uuid');
   //Validaciones MEC
   if(uuid != null){
    formData["uuid"] = uuid;

    passValidation = formFieldsBloque.every((item) => {
        let trigger = item.trigger;
        let field = document.getElementById(item.field);

        if(trigger != "none"){
            let primaryField = document.getElementById(trigger);
            if(primaryField.value.length > 0 && field.value.length == 0){
                Swal.fire("El campo "+item.label+" es obligatorio","Parece que no ha proporcionado información en el campo "+item.label,"warning");
                return false;
            }
        }
        
        if(field){
            if(item.required === true && field.value.length == 0){
                Swal.fire("El campo "+item.label+" es obligatorio","Parece que no ha proporcionado información en el campo "+item.label,"warning");
                return false;
            }
            formData[item.field] = field.value;
        }
        
        
        return true;

    });

    if(!passValidation) return passValidation;

    //formFieldsMec
    passValidation = formFieldsMec.every((item) => {
        let trigger = item.trigger;
        let field = document.getElementById(item.field);

        if(trigger != "none"){
            let primaryField = document.getElementById(trigger);
            if(primaryField.value.length > 0 && field.value.length == 0){
                Swal.fire("El campo "+item.label+" es obligatorio","Parece que no ha proporcionado información en el campo "+item.label,"warning");
                return false;
            }
        }
        
        if(field){
            if(item.required === true && field.value.length == 0){
                Swal.fire("El campo "+item.label+" es obligatorio","Parece que no ha proporcionado información en el campo "+item.label,"warning");
                return false;
            }

            formData[item.field] = field.value;
        }

        
        return true;

    });

    if(!passValidation) return passValidation;

    //Validaciones Facturacion 
    passValidation = formFieldsFacturacion.every((item) => {
        let trigger = item.trigger;
        let field = document.getElementById(item.field);

        if(trigger != "none"){
            let primaryField = document.getElementById(trigger);
            if(primaryField.value.length > 0 && field.value.length == 0){
                Swal.fire("El campo "+item.label+" es obligatorio","Parece que no ha proporcionado información en el campo "+item.label,"warning");
                return false;
            }
        }
        
        if(field){
            if(item.required === true && field.value.length == 0){
                Swal.fire("El campo "+item.label+" es obligatorio","Parece que no ha proporcionado información en el campo "+item.label,"warning");
                return false;
            }
        }

        formData[item.field] = field.value;
        return true;

    });

    if(!passValidation) return passValidation;
    
   }

   $.ajax({
        url: url,
        type: "post",
        data: formData,
        beforeSend:function(){
        
        },
        success:function(data){
                Swal.fire(data.Titulo,data.Mensaje,data.TMensaje).then(function() {
                    if(data.TMensaje == "success"){
                        localStorage.setItem('numContenedor',formData['num_contenedor']); 
                        var uuid = localStorage.getItem('uuid');
                        if(uuid){
                            initFileUploader()
                            setTimeout(()=>{
                                document.getElementById('noticeFileUploader').classList.add('d-none')
                                document.getElementById('fileUploaderContainer').classList.remove('d-none')    
                            },3000);
                            
                            if(form.data('sgtCotizacionAction') != 'editar'){

                                form.attr('action', `/cotizaciones/single/update/${data.folio}`)
                                form.data('sgtCotizacionAction','editar')
                            }
                            

                        }else{
                            location.reload();
                        }
                    
                    }
                });
        },
        error:function(){       
        Swal.fire("Error","Ha ocurrido un error, intentelo nuevamente","error");
        }
    })

  
});


$("#cotizacionesUpdate").on("submit",(e)=>{
    e.preventDefault();
    //Validaciones a campos obligatorios
    if($("#id_cliente").val() == ""){
        Swal.fire("Seleccione Cliente","Aún no ha seleccionado Cliente, este es un campo requerido","warning");
        return false;
    }

    const selectSubClient = document.getElementById("id_subcliente");
    const subClientQty = selectSubClient.options.length;

    if(subClientQty > 1 && $("#id_subcliente").val() == ""){
        Swal.fire("Seleccione SubCliente","Aún no ha seleccionado SubCliente, este es un campo requerido","warning");
        return false;
    }

    var passValidation = formFields.every((item) => {
        var field = document.getElementById(item.field);
        
        if(item.required === true && field.value.length == 0){
            Swal.fire("El campo "+item.label+" es obligatorio","Parece que no ha proporcionado información en el campo "+item.label,"warning");
            return false;
        }

        return true;
    })

   if(!passValidation) return passValidation;

   //Validaciones con condicionantes
   let sobrePeso = document.getElementById('sobrepeso').value;
   let precioSobrePeso = document.getElementById('precio_sobre_peso').value;
   if(sobrePeso > 0 && precioSobrePeso.length <= 0){
        Swal.fire("El campo Precio Sobre Peso es obligatorio","Parece que no ha proporcionado información en el campo Precio Sobre Peso","warning");
        return false;
   }

   //Eliminamos el formato moneda de todos los campos antes de enviar al backend
   formFields.forEach((item) =>{
    if(item.type == "money") {
        var field = document.getElementById(item.field);
        field.value = reverseMoneyFormat(field.value);
    }
   });

   formFieldsProveedor.forEach((item) =>{
    if(item.type == "money") {
        var field = document.getElementById(item.id);
        if(field){
          field.value = reverseMoneyFormat(field.value);
        }
    }
   });

   e.target.submit();
})




