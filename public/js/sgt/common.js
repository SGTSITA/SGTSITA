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