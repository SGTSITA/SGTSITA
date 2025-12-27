let waElements = document.querySelectorAll('.waElements');
let waContacts = [];
var waStatus = null;
let waTextStatus = document.getElementById('waTextStatus');
let waIconStatus = document.getElementById('waIconStatus');
let waStatusResponse = null;

async function whatsAppQrCode(id) {
    try {
        const response = await fetch(`${waHost}/whatsApp/qr-code/${id}`);

        if (!response.ok) {
            // 'response.ok' es true para status 200-299
            // Si el servidor envía un error HTTP (ej. 404, 500), lanza un error para que lo capture el 'catch'
            throw new Error(`Error HTTP: ${response.status} ${response.statusText}`);
        }

        const data = await response.json();

        let WhatsAppQrPicture = document.querySelector('#WhatsAppQrPicture');
        if (WhatsAppQrPicture && data.status === 'qr') {
            WhatsAppQrPicture.src = data.qr;
        }
        return data.status;
    } catch (error) {
        console.error('Error al obtener datos:', error);
        return 'error'; // Retorna "error" si algo falla
    }
}

async function whatsAppConversations(id) {
    try {
        const response = await fetch(`${waHost}/whatsapp/${id}/conversations/list`);

        if (!response.ok) {
            // 'response.ok' es true para status 200-299
            // Si el servidor envía un error HTTP (ej. 404, 500), lanza un error para que lo capture el 'catch'
            throw new Error(`Error HTTP: ${response.status} ${response.statusText}`);
        }

        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error al obtener datos:', error);
        return { status: 'error', conversations: null }; // Retorna "error" si algo falla
    }
}

async function whatsAppSendMessage(id, contacts, message, files = []) {
    try {
        let datamessage = { message: message, groupName: contacts, files: files };
        const response = await fetch(`${waHost}/whatsapp/${id}/messages/send`, {
            method: 'POST', // Specify the HTTP method as POST
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(datamessage),
        });

        if (!response.ok) {
            // 'response.ok' es true para status 200-299
            // Si el servidor envía un error HTTP (ej. 404, 500), lanza un error para que lo capture el 'catch'
            throw new Error(`Error HTTP: ${response.status} ${response.statusText}`);
        }

        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error al obtener datos:', error);
        return { status: 'error', conversations: null }; // Retorna "error" si algo falla
    }
}

const statusMap = {
    error: () => document.getElementById('waNotice').classList.remove('d-none'),
    qr: () => document.getElementById('whatsAppLogin').classList.remove('d-none'),
    loading: () => document.getElementById('whatsAppLoding').classList.remove('d-none'),
    ready: () => waReadyComponents(),
};

function waReadyComponents() {
    let whatsAppMessageCompose = document.getElementById('whatsAppMessageCompose');
    if (whatsAppMessageCompose) whatsAppMessageCompose.classList.remove('d-none');

    waTextStatus.textContent = 'Conectado';
    waIconStatus.classList.remove('btn-color-gray-700');
    waIconStatus.classList.add('btn-color-success');
}

// setInterval(async () => {
//     await getWaQr();
// }, 15000);

async function getWaQr() {
    waStatusResponse = (await whatsAppQrCode(waClient)) || 'error';
}

// setInterval(() => {
//     if (waStatus != waStatusResponse) {
//         waElements.forEach((el) => {
//             el.classList.add('d-none');
//         });

//         waStatus = waStatusResponse;
//         statusMap[waStatusResponse]();
//     }
// }, 4000);

let waTagify = null;
let btnSendWhatsApp = document.querySelector('#kt_whatsapp');
let textMessageWhatsApp = document.querySelector('#kt_whatsapp_text_input');

// setInterval(async () => {
//     var tagifyUsers = document.querySelector('#kt_tagify_users');
//     if (waStatus == 'ready' && waContacts.length == 0) {
//         waTextStatus.textContent = 'Sincronizando...';
//         var waConversations = await whatsAppConversations(waClient);

//         if (waConversations.status === 'syncing') return false;
//         waConversationsResult = waConversations.conversations;

//         waTextStatus.textContent = 'Conectado';
//         waConversationsResult.forEach((c) => {
//             let participants = c.participants;
//             waContacts = [
//                 ...waContacts,
//                 {
//                     value: c.id,
//                     name: c.name,
//                     avatar: null,
//                     email: !c.isGroup ? c.id : `Grupo de WhatsApp: ${participants.length} miembros`,
//                 },
//             ];
//         });
//     }

//     if (waTagify == null && tagifyUsers && waContacts.length > 0) {
//         waTagify = tagifyInit(waContacts);
//     }
// }, 5000);

async function sendWhatsApp() {
    let usersWhatsApp = waTagify.value;
    if (usersWhatsApp.length == 0) {
        Swal.fire('Seleccione contacto', 'Por favor selecciona al menos un contacto', 'warning');
        return false;
    }

    if (textMessageWhatsApp.value.length == 0) {
        Swal.fire('Escribir mensaje', 'Por favor escribe un mensaje para el contacto', 'warning');
        return false;
    }

    let waFiles = [];
    let waNumContenedor = localStorage.getItem('numContenedor');

    document.querySelectorAll('input[name="waFiles"]:checked').forEach((cb) => {
        waFiles = [
            ...waFiles,
            {
                fileUrl: `https://sgt.gologipro.com/${cb.value}`,
                fileName: `${cb.dataset.wafile}: ${waNumContenedor}`,
            },
        ];
    });

    var sendingMessage = await whatsAppSendMessage(waClient, usersWhatsApp[0].name, textMessageWhatsApp.value, waFiles);
    if (sendingMessage.status === 'Mensaje enviado') {
        waTagify.removeAllTags();
        textMessageWhatsApp.value = '';
        toastr.options = {
            closeButton: true,
            debug: false,
            newestOnTop: false,
            progressBar: true,
            positionClass: 'toastr-bottom-center',
            preventDuplicates: false,
            onclick: null,
            showDuration: '1500',
            hideDuration: '1000',
            timeOut: '5000',
            extendedTimeOut: '1000',
            showEasing: 'swing',
            hideEasing: 'linear',
            showMethod: 'fadeIn',
            hideMethod: 'fadeOut',
        };

        toastr.success(`El mensaje WhatsApp se envió correctamente a los contactos seleccionados `, `Mensaje Enviado`);
    }
}

btnSendWhatsApp.addEventListener('click', sendWhatsApp);
