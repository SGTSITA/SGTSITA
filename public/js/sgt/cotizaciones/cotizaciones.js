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

function setFormFields(currentFormFields){
 formFields = currentFormFields;
}

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

    const baseTaref = (subTotal - baseFactura - iva) + retencion;

    // Mostrar el resultado en el input de base_taref
    const field_base_taref = fields.find( i => i.field == "base_taref");
    document.getElementById(field_base_taref.id).value = moneyFormat(baseTaref);

    // Restar el valor de Retención del total
    const totalSinRetencion = precio_viaje + burreo + iva + otro + estadia + maniobra;
    const totalConRetencion = totalSinRetencion - retencion;

    // Obtener el valor de Precio Tonelada
    const field_precio_tonelada = fields.find( i => i.field == "precio_tonelada");
    const precioTonelada = parseFloat(reverseMoneyFormat(document.getElementById(field_precio_tonelada.id).value)) || 0;

    // Sumar el valor de Precio Tonelada al total
    const totalFinal = totalConRetencion + precioTonelada;

    // Formatear el total con comas
    const totalFormateado = moneyFormat(totalFinal);
    const field_total = fields.find( i => i.field == "total");
    document.getElementById(field_total.id).value = totalFormateado;

}

document.addEventListener('DOMContentLoaded', function () {
    // Obtener elementos del DOM
    var pesoReglamentarioInput = document.getElementById('peso_reglamentario');
    var pesoContenedorInput = document.getElementById('peso_contenedor');
    var sobrepesoInput = document.getElementById('sobrepeso');

    var precioSobrePesoInput = document.getElementById('precio_sobre_peso');
    var precioToneladaInput = document.getElementById('precio_tonelada');

    var precioSobrePesoProveedor = document.getElementById('sobrepeso_proveedor')
    var sobrePesoProveedor = document.getElementById('cantidad_sobrepeso_proveedor');
    var precioToneladaProveedor = document.getElementById('total_tonelada');

    // Agregar evento de cambio a los inputs
    pesoReglamentarioInput.addEventListener('input', calcularSobrepeso);
    pesoContenedorInput.addEventListener('input', calcularSobrepeso);
    pesoContenedorInput.addEventListener('input', calcularSobrepeso);
    pesoContenedorInput.addEventListener('input', valorSobrePrecio);


    function valorSobrePrecio(){
        // Obtener el valor de Sobrepeso
        var sobrepeso = parseFloat(sobrepesoInput.value.replace(/,/g, '')) || 0;
   
        // Obtener el valor de Precio Sobre Peso
        var precioSobrePeso = parseFloat(reverseMoneyFormat(precioSobrePesoInput.value)) || 0;
   
        // Calcular el resultado de la multiplicación
        var resultado = sobrepeso * precioSobrePeso;
   
        // Mostrar el resultado en el campo "Precio Tonelada"
        precioToneladaInput.value = moneyFormat(resultado); 
   
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
            sobrepesoInput.value = sobrepeso.toFixed(2);
        }
       
        var sobrePesoProveedor = document.getElementById('cantidad_sobrepeso_proveedor');
        if(sobrePesoProveedor){
            sobrePesoProveedor.value = sobrepeso.toFixed(2);
            
        }
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
    

});

