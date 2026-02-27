let selectProveedor = document.querySelector('#id_proveedor');
let selectTransport = document.querySelector('#id_transportista');

if (selectProveedor) {
    selectProveedor.addEventListener('change', () => {
        getTranspotistas();
    });
}

function getTranspotistas() {
    let _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    let proveedor = selectProveedor.value;
    $.ajax({
        type: 'post',
        url: '/mec/transportistas/list',
        data: { proveedor, _token },
        beforeSend: () => {},
        success: (response) => {
            let opciones = response;
            selectTransport.innerHTML = '';

            opciones.forEach((opcion) => {
                selectTransport.add(new Option(opcion.nombre, opcion.id));
            });
        },
        error: () => {},
    });
}

let selectProveedorLocal = document.querySelector('#id_proveedorlocal');
let selectTransportLocal = document.querySelector('#id_transportistalocal');

if (selectProveedorLocal) {
    selectProveedorLocal.addEventListener('change', () => {
        getTranspotistasLocal();
    });
}

function getTranspotistasLocal() {
    let _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    let proveedor = selectProveedorLocal.value;
    $.ajax({
        type: 'post',
        url: '/mec/transportistas/list-local',
        data: { proveedor, _token },
        beforeSend: () => {},
        success: (response) => {
            let opciones = response;
            selectTransportLocal.innerHTML = '';

            opciones.forEach((opcion) => {
                selectTransportLocal.add(new Option(opcion.nombre, opcion.id));
            });
        },
        error: () => {},
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const campos = ['dias_estadia', 'tarifa_estadia', 'dias_pernocta', 'tarifa_pernocta', 'Costomaniobra'];

    const recalcularTotales = () => {
        const diasE = parseFloat(document.getElementById('dias_estadia').value) || 0;
        const tarifaE = parseFloat(document.getElementById('tarifa_estadia').value) || 0;
        const diasP = parseFloat(document.getElementById('dias_pernocta').value) || 0;
        const tarifaP = parseFloat(document.getElementById('tarifa_pernocta').value) || 0;
        const costoManiobra = parseFloat(document.getElementById('Costomaniobra').value) || 0;

        const totalE = diasE * tarifaE;
        const totalP = diasP * tarifaP;
        const totalG = totalE + totalP;
        const totalConManiobra = totalG + costoManiobra;

        document.getElementById('total_estadia').value = totalE.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
        document.getElementById('total_pernocta').value = totalP.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
        document.getElementById('total_general').value = totalConManiobra.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
    };

    // Escucha cambios solo en los inputs que importan
    campos.forEach((id) => {
        const input = document.getElementById(id);
        //if (input) input.dispatchEvent(new Event('input', { bubbles: true }));
        if (input) input.addEventListener('input', recalcularTotales);
    });
    recalcularTotales();
});
