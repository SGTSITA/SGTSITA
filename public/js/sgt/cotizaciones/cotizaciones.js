const tasa_iva = 0.16;
const tasa_retencion = 0.04;
const catalogo_clientes = document.querySelector("#txtClientes");

$(".moneyformat").on("focus",(e)=>{
var val = e.target.value;
e.target.value = reverseMoneyFormat(val);
})

$(".moneyformat").on("blur",(e) =>{
var val = e.target.value;
e.target.value =  moneyFormat(val);
})

function calcularTotal() {
    const precio_viaje = parseFloat(reverseMoneyFormat(document.getElementById('precio_viaje').value)) || 0;
    const burreo = parseFloat(reverseMoneyFormat(document.getElementById('burreo').value)) || 0;
    const otro = parseFloat(reverseMoneyFormat(document.getElementById('otro').value)) || 0;
    const estadia = parseFloat(reverseMoneyFormat(document.getElementById('estadia').value)) || 0;
    const maniobra = parseFloat(reverseMoneyFormat(document.getElementById('maniobra').value)) || 0;

    const subTotal = precio_viaje + burreo + maniobra + estadia + otro;
    
    //calcularImpuestos(subTotal);
    const baseFactura = parseFloat(reverseMoneyFormat(document.getElementById('base_factura').value)) || 0;
    const iva = (baseFactura * tasa_iva);
    const retencion = (baseFactura * tasa_retencion);

    document.getElementById('iva').value = moneyFormat(iva);
    document.getElementById('retencion').value = moneyFormat(retencion);

    const baseTaref = (subTotal - baseFactura - iva) + retencion;
    // Mostrar el resultado en el input de base_taref
    document.getElementById('base_taref').value = moneyFormat(baseTaref);

   // const retencion = parseFloat(reverseMoneyFormat(document.getElementById('retencion').value)) || 0;
    //const iva = parseFloat(reverseMoneyFormat(document.getElementById('iva').value)) || 0;
    // Restar el valor de Retención del total
    const totalSinRetencion = precio_viaje + burreo + iva + otro + estadia + maniobra;
    const totalConRetencion = totalSinRetencion - retencion;

    // Obtener el valor de Precio Tonelada
    const precioTonelada = parseFloat(reverseMoneyFormat(document.getElementById('precio_tonelada').value)) || 0;

    // Sumar el valor de Precio Tonelada al total
    const totalFinal = totalConRetencion + precioTonelada;

    // Formatear el total con comas
    const totalFormateado = moneyFormat(totalFinal);

    document.getElementById('total').value = totalFormateado;

}

document.addEventListener('DOMContentLoaded', function () {
    // Obtener elementos del DOM
    var pesoReglamentarioInput = document.getElementById('peso_reglamentario');
    var pesoContenedorInput = document.getElementById('peso_contenedor');
    var sobrepesoInput = document.getElementById('sobrepeso');

    var precioSobrePesoInput = document.getElementById('precio_sobre_peso');
    var precioToneladaInput = document.getElementById('precio_tonelada');

    // Agregar evento de cambio a los inputs
    pesoReglamentarioInput.addEventListener('input', calcularSobrepeso);
    pesoContenedorInput.addEventListener('input', calcularSobrepeso);
    pesoContenedorInput.addEventListener('input', calcularSobrepeso);
    pesoContenedorInput.addEventListener('input', valorSobrePrecio);


    function valorSobrePrecio(){
        // Obtener el valor de Sobrepeso
        var sobrepeso = parseFloat(sobrepesoInput.value.replace(/,/g, '')) || 0;
   
        // Obtener el valor de Precio Sobre Peso
        var precioSobrePeso = parseFloat(precioSobrePesoInput.value.replace(/,/g, '')) || 0;
   
        // Calcular el resultado de la multiplicación
        var resultado = sobrepeso * precioSobrePeso;
   
        // Mostrar el resultado en el campo "Precio Tonelada"
        precioToneladaInput.value = moneyFormat(resultado); //resultado.toLocaleString('en-US');
   
        // Calcular el total
        calcularTotal();
   }
    // Función para calcular el sobrepeso
    function calcularSobrepeso() {
        var pesoReglamentario = parseFloat(pesoReglamentarioInput.value) || 0;
        var pesoContenedor = parseFloat(pesoContenedorInput.value) || 0;

        // Calcular sobrepeso
        var sobrepeso = Math.max(pesoContenedor - pesoReglamentario, 0);

        // Mostrar sobrepeso en el input correspondiente con dos decimales
        sobrepesoInput.value = sobrepeso.toFixed(2);
    }

    // Agregar evento de entrada al campo "Precio Sobre Peso"
    precioSobrePesoInput.addEventListener('input', function () {
        valorSobrePrecio();
    });

    // Calcular sobrepeso inicialmente al cargar la página
    calcularSobrepeso();

    // Función para calcular base_taref
   /* function calcularBaseTaref() {
        // Obtener los valores de los inputs
        const total = parseFloat(document.getElementById('total').value.replace(/,/g, '')) || 0;
        const precio_viaje = parseFloat(document.getElementById('precio_viaje').value.replace(/,/g, '')) || 0;
        const baseFactura = parseFloat(document.getElementById('base_factura').value) || 0;

        //Calculamos IVA y retencion
        const iva = (baseFactura * tasa_iva);
        const retencion = (baseFactura * tasa_retencion);

        //calcularImpuestos();

        // Realizar el cálculo
        const baseTaref = (total - baseFactura - iva) + retencion;

        // Mostrar el resultado en el input de base_taref
        document.getElementById('base_taref').value = baseTaref.toFixed(2);
    }*/

    // Agregar eventos de cambio a los inputs para calcular automáticamente
   // document.getElementById('total').addEventListener('input', calcularBaseTaref);
    document.getElementById('base_factura').addEventListener('input', calcularTotal);
    var inputMoneyFormat = $('.moneyformat');
    inputMoneyFormat.on('input',calcularTotal)
   // document.getElementById('iva').addEventListener('input', calcularBaseTaref);
  //  document.getElementById('retencion').addEventListener('input', calcularBaseTaref);
});


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
        } else {
            $('#id_subcliente').empty();
            $('#id_subcliente').append('<option value="">Seleccionar subcliente</option>');
        }
    });


