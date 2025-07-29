function mostrarLoading(text = "Espere un momento...") {
    
    let label = document.querySelector('#loading-text')
    label.textContent = text

    document.getElementById('loading-overlay').style.display = 'flex'
}

function ocultarLoading() {
document.getElementById('loading-overlay').style.display = 'none';
}

function simularEvento() {
mostrarLoading();

// Simula una operación
setTimeout(() => {
    
    ocultarLoading();
}, 2000);
}

async function whatsAppQrCode(id){
    try {
        const response = await fetch(`http://localhost:3000/whatsApp/qr-code/${id}`);
        
        if (!response.ok) { // 'response.ok' es true para status 200-299
            // Si el servidor envía un error HTTP (ej. 404, 500), lanza un error para que lo capture el 'catch'
            throw new Error(`Error HTTP: ${response.status} ${response.statusText}`);
        }

        const data = await response.json();

        let WhatsAppQrPicture = document.querySelector('#WhatsAppQrPicture');
        if (WhatsAppQrPicture && data.status === "qr") {
            WhatsAppQrPicture.src = data.qr;
        }
        return data.status; 
    } catch (error) {
        console.error('Error al obtener datos:', error);
        return "error"; // Retorna "error" si algo falla
    }

}

async function whatsAppConversations(id){
    try {
        const response = await fetch(`http://localhost:3000/whatsapp/${id}/conversations/list`);
        
        if (!response.ok) { // 'response.ok' es true para status 200-299
            // Si el servidor envía un error HTTP (ej. 404, 500), lanza un error para que lo capture el 'catch'
            throw new Error(`Error HTTP: ${response.status} ${response.statusText}`);
        }

        const data = await response.json();
        return data; 
    } catch (error) {
        console.error('Error al obtener datos:', error);
        return {status:"error", conversations: null}; // Retorna "error" si algo falla
    }
}

async function whatsAppSendMessage(id){
    try {
        const response = await fetch(`http://localhost:3000/whatsapp/${id}/messages/send`);
        
        if (!response.ok) { // 'response.ok' es true para status 200-299
            // Si el servidor envía un error HTTP (ej. 404, 500), lanza un error para que lo capture el 'catch'
            throw new Error(`Error HTTP: ${response.status} ${response.statusText}`);
        }

        const data = await response.json();
        return data; 
    } catch (error) {
        console.error('Error al obtener datos:', error);
        return {status:"error", conversations: null}; // Retorna "error" si algo falla
    }
}

function allowOnlyDecimals(event) {
    const input = event.target;
    const regex = /^[0-9]*\.?[0-9]*$/;

    if (!regex.test(input.value)) {
    input.value = input.value.slice(0, -1);
    }
}

function validarEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

function moneyFormat(moneyValue){
    const $formatMoneda = new Intl.NumberFormat('es-MX', {
    style: 'currency',
    currency: 'MXN',
    minimumFractionDigits: 2
    }).format(moneyValue);

    return $formatMoneda;
}

function reverseMoneyFormat(formatValue){
    var valorLimpio = formatValue.replace(/[\$,]/g, '');
    return valorLimpio;
}

function generateUUID() {
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
        const r = (Math.random() * 16) | 0;
        const v = c === 'x' ? r : (r & 0x3) | 0x8;
        return v.toString(16);
    });
}

function formatoConsecutivo(folio, lenFormat = 5) {
    const len = lenFormat - String(folio).length;
    let consecutivo = '';

    for (let x = 1; x <= len; x++) {
        consecutivo += '0';
    }

    consecutivo += folio;
    return consecutivo;
}

function changeTag(tagId,value){
 var tag = document.querySelector("#"+tagId);
 tag.textContent = value;
}

function obtenerFechaEnLetra(fecha) {
    const dia = conocerDiaSemanaFecha(fecha);
    const num = new Date(fecha).getDate();
    const anno = new Date(fecha).getFullYear();
    const meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
    const mes = meses[new Date(fecha).getMonth()];

    return `${dia}, ${num} de ${mes} del ${anno}`;
}

function conocerDiaSemanaFecha(fecha) {
    const dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
    const dia = new Date(fecha).getDay();
    return dias[dia];
}
