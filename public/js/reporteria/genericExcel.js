function getReportName(report) {
    var reportNames = ['cuentas_por_pagar', 'reporte_viajes', 'reporte_utilidad', 'liquidados_cxc', 'liquidados_cxp'];
    return reportNames.length - 1 < report ? 'generic_report' : reportNames[report];
}

function getReportHeaders(report) {
    var reportHeaders = [
        ['Numero', 'Origen', 'Destino', 'Contenedor', 'Estatus'],
        ['Cliente', 'SubCliente', 'Origen', 'Destino', 'Contenedor', 'Fecha Salida', 'Fecha Llegada', 'Estatus'],
        ['Cliente', 'SubCliente', 'Origen', 'Destino', 'Contenedor', 'Utilidad'],
        ['Cliente', 'SubCliente', 'Origen', 'Destino', 'Contenedor', 'Estatus'],
        ['Origen', 'Destino', 'Contenedor', 'Estatus'],
    ];
    return reportHeaders.length - 1 < report ? [] : reportHeaders[report];
}

$('#exportButtonGenericExcel').on('click', () => {
    var _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    var dataExport = $('#txtDataGenericExcel').val();
    var reportNumber = $('#exportButtonGenericExcel').data('report');
    var reportName = getReportName(reportNumber);
    var reportHeaders = JSON.stringify(getReportHeaders(reportNumber));
    $.ajax({
        url: '/reporteria/excel/export',
        type: 'post',
        data: { _token, reportNumber, reportHeaders, dataExport },
        xhrFields: {
            responseType: 'blob',
        },
        beforeSend: () => {},
        success: (response) => {
            var blob = new Blob([response], { type: 'application/xlsx' });
            var url = URL.createObjectURL(blob);
            var a = document.createElement('a');
            a.style.display = 'none';
            a.href = url;
            a.download = reportName + '.xlsx';
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
        },
        error: () => {},
    });
});
