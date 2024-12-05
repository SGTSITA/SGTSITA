function allowOnlyDecimals(event) {
    const input = event.target;
    const regex = /^[0-9]*\.?[0-9]*$/;

    if (!regex.test(input.value)) {
    input.value = input.value.slice(0, -1);
    }
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

function changeTag(tagId,value){
 var tag = document.querySelector("#"+tagId);
 tag.textContent = value;
}