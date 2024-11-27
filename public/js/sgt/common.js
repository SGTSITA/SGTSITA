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