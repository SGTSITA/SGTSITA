
let tagNumContenedor = document.querySelector("#numContenedor");
let numContenedor = tagNumContenedor.textContent;
const safeValue = encodeURIComponent(numContenedor); //agregue porq habia uno que traia // en el num de contenendor , validar en captura..

let urlGetFiles = `/viajes/file-manager/get-file-list/${safeValue}`;

let dt = $("#kt_datatable_example_1").DataTable({
    select: false,
    ajax: {
        url: urlGetFiles,
    },
    searchDelay: 500,

    order: [[5, 'desc']],
    select: {
        style: 'multi',
        selector: 'td:first-child input[type="checkbox"]',
        className: 'row-selected'
    },
    language: {
        "paginate": {
            "first": "Primero",
            "last": "Último",
            "next": "Siguiente",
            "previous": "Anterior"
        },
        select: {
            rows: {
                "1": "",
                "_": ""
            }
        },
        "decimal": ".",
        "emptyTable": "No hay datos disponibles en la tabla",
        "zeroRecords": "No se encontraron coincidencias",
        "info": "_START_ a _END_ de _TOTAL_ entradas",
        "infoFiltered": "(Filtrado de _MAX_ total de entradas)",
        "lengthMenu": "Mostrar _MENU_ entradas",
        "thousands": ",",
    },
    columns: [
        { data: null },
        { data: 'secondaryFileName' },
        { data: 'fileType' },
        { data: 'fileSize' },
        { data: 'fileDate' },
        { data: null },

    ],
    columnDefs: [
        {
            targets: 0,
            orderable: false,
            render: function (data) {

                return `
                    <div class="form-check form-check-sm form-check-custom form-check-solid">
                        <input class="form-check-input" type="checkbox" value="cotizaciones/cotizacion${data.identifier}/${data.filePath}" />
                    </div>`;
            }
        },
        {
            targets: 5,

            orderable: false,

            render: function (data, type, row) {

                return `
                        <a href="/cotizaciones/cotizacion${data.identifier}/${data.filePath}" target="_blank" class="btn btn-active-primary btn-sm">
                            Ver Archivo
                        </a>
                    `;
            },
        },
    ]
});


const filterSearch = document.querySelector('[data-kt-docs-table-filter="search"]');
filterSearch.addEventListener('keyup', function (e) {
    dt.search(e.target.value).draw();
});

dt.on('draw', function () {
    //
    toggleToolbars();
    //   handleDeleteRows();
    initToggleToolbar();
    KTMenu.createInstances();
});

var initToggleToolbar = function () {
    // Toggle selected action toolbar
    // Select all checkboxes
    const container = document.querySelector('#kt_datatable_example_1');
    const checkboxes = container.querySelectorAll('[type="checkbox"]');

    // Select elements
    const deleteSelected = document.querySelector('[data-kt-docs-table-select="delete_selected"]');

    // Toggle delete selected toolbar
    checkboxes.forEach(c => {
        // Checkbox on click event
        c.addEventListener('click', function () {
            setTimeout(function () {
                toggleToolbars();
            }, 50);
        });
    });

    // Deleted selected rows
    if (deleteSelected) {
        deleteSelected.addEventListener('click', function () {
            // SweetAlert2 pop up --- official docs reference: https://sweetalert2.github.io/
            Swal.fire({
                text: "¿Esta seguro que quiere eliminar los archivos seleccionados?",
                icon: "question",
                showCancelButton: true,
                buttonsStyling: false,
                showLoaderOnConfirm: true,
                confirmButtonText: "Si, Eliminar!",
                cancelButtonText: "No",
                customClass: {
                    confirmButton: "btn fw-bold btn-danger",
                    cancelButton: "btn fw-bold btn-active-light-primary"
                },
            }).then(function (result) {
                if (result.value) {
                    // Simulate delete request -- for demo purpose only
                    Swal.fire({
                        text: "Deleting selected customers",
                        icon: "info",
                        buttonsStyling: false,
                        showConfirmButton: false,
                        timer: 2000
                    }).then(function () {
                        Swal.fire({
                            text: "You have deleted all selected customers!.",
                            icon: "success",
                            buttonsStyling: false,
                            confirmButtonText: "Ok, got it!",
                            customClass: {
                                confirmButton: "btn fw-bold btn-primary",
                            }
                        }).then(function () {
                            // delete row data from server and re-draw datatable
                            dt.draw();
                        });

                        // Remove header checked box
                        const headerCheckbox = container.querySelectorAll('[type="checkbox"]')[0];
                        headerCheckbox.checked = false;
                    });
                } else if (result.dismiss === 'cancel') {

                }
            });
        });
    }
}

// Toggle toolbars
var toggleToolbars = function () {
    // Define variables
    const container = document.querySelector('#kt_datatable_example_1');
    const toolbarBase = document.querySelector('[data-kt-docs-table-toolbar="base"]');
    const toolbarSelected = document.querySelector('[data-kt-docs-table-toolbar="selected"]');
    const selectedCount = document.querySelector('[data-kt-docs-table-select="selected_count"]');

    // Select refreshed checkbox DOM elements
    const allCheckboxes = container.querySelectorAll('tbody [type="checkbox"]');

    // Detect checkboxes state & count
    let checkedState = false;
    let count = 0;

    // Count checked boxes
    allCheckboxes.forEach(c => {
        if (c.checked) {
            checkedState = true;
            count++;
        }
    });

    // Toggle toolbars
    if (checkedState) {
        selectedCount.innerHTML = count;
        toolbarBase.classList.add('d-none');
        toolbarSelected.classList.remove('d-none');
    } else {
        toolbarBase.classList.remove('d-none');
        toolbarSelected.classList.add('d-none');
    }
}

const btnDocumets = document.querySelector('#btnDocs');
const btnAdjuntos = document.querySelector("#btnAdjuntos");
const btnWhatsApp = document.querySelector("#btnWhatsApp");
const buttonSendMail = document.querySelector('[data-kt-inbox-form="sendmail"]');
const mainEmail = document.querySelector('#compose_to');
const phoneWhatsApp = document.querySelector('#phone_wa');
const ccEmail = document.querySelector('#compose_cc');
const emailCC = document.querySelector('[data-kt-inbox-form="cc"]');
const subject = document.querySelector('#compose_subject')
const messageMail = document.querySelector('#kt_inbox_form_editor')
let selectContenedores = document.querySelector("#selectContenedores")

selectContenedores.addEventListener('change',(e)=>{
 localStorage.setItem('numContenedor', e.target.value);
})

function goToUploadDocuments() {
    let titleFileUploader = document.querySelector("#titleFileUploader");
    let contenedores = numContenedor.replace(/\s+/g, '*');
    contenedores = contenedores.split('*')

    while (selectContenedores.options.length > 0) {
        selectContenedores.remove(0);
    }

    contenedores.forEach(c => {
        let option = document.createElement('option');
        option.value = c;
        option.text = c;
        selectContenedores.appendChild(option);
    })

    titleFileUploader.textContent = contenedores[0].toUpperCase();
    localStorage.setItem('numContenedor', contenedores[0]);
    const modalElement = document.getElementById('kt_modal_fileuploader');
    const bootstrapModal = new bootstrap.Modal(modalElement);
    bootstrapModal.show();
}

function modalEmail() {
    subject.value = `Documentos Contenedor ${numContenedor}`
    const tag = document.getElementById("tagEnvioDocumentos")
    $("#emailAddress").removeClass('d-none')
    $("#phoneNumber").addClass('d-none')
    buttonSendMail.setAttribute("data-kt-inbox-form", "sendmail")
    tag.textContent = 'Enviar documentos vía Correo Electrónico'
    const modalElement = document.getElementById('modal-enviar-correo');
    const bootstrapModal = new bootstrap.Modal(modalElement);
    bootstrapModal.show();

}

const inputTelefono = document.getElementById('telefono_wa_input');
const dropdown = document.getElementById('contactos_dropdown');
const wrapper = document.getElementById('telefono_wa_wrapper');
const hiddenInput = document.getElementById('telefonos_wa');

let contactosWhatsapp = [];
let contactosSeleccionados = [];

async function cargarContactosWhatsapp() {
    try {
        const res = await fetch('/contactos/list'); // usa tu ruta real
        contactosWhatsapp = await res.json();
        mostrarDropdown('');
    } catch (err) {
        console.error("Error cargando contactos:", err);
    }
}

function renderTags() {
    // Elimina todos menos el input
    [...wrapper.querySelectorAll('.tag')].forEach(el => el.remove());

    contactosSeleccionados.forEach(c => {
        const tag = document.createElement('div');
        tag.dataset.telefono = c.telefono
        tag.className = 'tag d-flex align-items-center bg-light rounded-pill px-2 py-1 gap-2';
        tag.innerHTML = `
            <img src="${c.foto ?? '/assets/images/faces/default-avatar.png'}" class="rounded-circle" width="25" height="25" />
            <span>${c.nombre}</span>
            <span class="text-danger cursor-pointer" style="font-weight: bold;">&times;</span>
        `;
        tag.querySelector('span.text-danger').onclick = () => {
            contactosSeleccionados = contactosSeleccionados.filter(x => x.telefono !== c.telefono);
            renderTags();
        };
        wrapper.insertBefore(tag, inputTelefono);
    });

    // Actualizar input oculto
    hiddenInput.value = contactosSeleccionados.map(c => c.telefono).join(',');
}

function mostrarDropdown(filtro) {
    filtro = filtro?.trim().toLowerCase() || ''; // Asegura que sea string sin espacios

    const coincidencias = contactosWhatsapp.filter(c => {
        const yaSeleccionado = contactosSeleccionados.some(s => s.telefono === c.telefono);
        const nombre = (c.nombre || '').toLowerCase();
        const telefono = (c.telefono || '');
        const filtroLower = (filtro || '').toLowerCase();

        return !yaSeleccionado && (
            nombre.includes(filtroLower) ||
            telefono.includes(filtroLower)
        );
    });


    dropdown.innerHTML = '';
    if (coincidencias.length === 0) {
        dropdown.classList.remove('show');
        return;
    }

    coincidencias.forEach(c => {
        const item = document.createElement('a');
        item.className = 'dropdown-item d-flex align-items-center';
        item.href = '#';
        item.innerHTML = `
            <img src="${c.foto ?? '/assets/images/faces/default-avatar.png'}" class="rounded-circle me-2" width="30" height="30" />
            <div>
                <div class="fw-semibold">${c.nombre}</div>
                <small class="text-muted">${c.telefono}</small>
            </div>
        `;
        item.onclick = (e) => {
            e.preventDefault();
            contactosSeleccionados.push(c);
            renderTags();
            inputTelefono.value = '';
            mostrarDropdown(''); //  refresca el dropdown después de agregar
        };
        dropdown.appendChild(item);
    });

    dropdown.classList.add('show');
}


// Eventos
inputTelefono.addEventListener('input', (e) => {
    mostrarDropdown(e.target.value);
});

inputTelefono.addEventListener('focus', () => {
    mostrarDropdown(inputTelefono.value);
});

inputTelefono.addEventListener('blur', () => {
    setTimeout(() => dropdown.classList.remove('show'), 150);
});

$('#modal-enviar-correo').on('shown.bs.modal', () => {
    cargarContactosWhatsapp();
});


function modalWhatsApp() {
    const tag = document.getElementById("tagEnvioDocumentos")
    $("#phoneNumber").removeClass('d-none')
    $("#emailAddress").addClass('d-none')
    buttonSendMail.setAttribute("data-kt-inbox-form", "WhatsApp")

    tag.textContent = 'Enviar documentos vía WhatsApp'
    subject.value = `Documentos Contenedor ${numContenedor}`
    const modalElement = document.getElementById('modal-enviar-correo');
    const bootstrapModal = new bootstrap.Modal(modalElement);
    bootstrapModal.show();
}



function sendEmail() {

    let channel = buttonSendMail.getAttribute("data-kt-inbox-form")

    if (!validarEmail(mainEmail.value) && channel == "sendmail") {
        Swal.fire('Dirección invalida', 'Lo sentimos, el dato en el campo Para no es un correo electrónico', 'warning')
        return false;
    }

    emailCC.classList.forEach(c => {
        if (c == "d-flex") {
            if (!validarEmail(ccEmail.value)) {
                Swal.fire('Dirección invalida en Cc (Copiar a)', 'Lo sentimos, el dato en el campo Cc no es un correo electrónico', 'warning')
                return false;
            }
        }
    })

    if (subject.value.length == 0) {
        Swal.fire('Escribir Asunto', 'Por favor introduzca asunto', 'warning')
        return false;
    }

    if (messageMail.textContent.length == 0) {
        Swal.fire('Escribir mensaje', 'Por favor escriba un breve mensaje para el receptor', 'warning')
        return false;
    }


    enviarCorreo();
}

function enviarCorreo() {

    let attachmentFiles = [];
    const allCheckboxes = document.querySelectorAll('tbody [type="checkbox"]');
    let checkedState = false;
    let count = 0;

    allCheckboxes.forEach(c => {
        if (c.checked) {
            checkedState = true;
            count++;

            const row = c.closest('tr');
            const labelText = row.querySelector('td:nth-child(2)').textContent.trim();

            let tmpFile = { "file": c.value, "documentSubject": labelText }
            attachmentFiles = [...attachmentFiles, tmpFile]
        }
    });

    const tags = document.querySelectorAll("#telefono_wa_wrapper .tag");
    const telefonos = Array.from(tags).map(tag => tag.dataset.telefono);

    buttonSendMail.setAttribute("data-kt-indicator", "on")
    let channel = buttonSendMail.getAttribute("data-kt-inbox-form")
    let _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    fetch('/sendfiles', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            _token: _token,
            channel,
            wa_phone: telefonos,
            email: mainEmail.value,
            secondaryEmail: ccEmail.value,
            subject: subject.value,
            message: messageMail.textContent,
            attachmentFiles: attachmentFiles,
            numContenedor: numContenedor
        })
    })
        .then(response => response.json())
        .then(data => {
            Swal.fire(data.Titulo, data.Mensaje, data.TMensaje)
        })
        .catch(error => console.error('Error:', error))
        .finally(() => {
            buttonSendMail.removeAttribute("data-kt-indicator")
            $('#modal-enviar-correo').modal('hide')
            mainEmail.value = '';
            ccEmail.value = '';
            if (window.quillEditor) {
                window.quillEditor.setText(""); // Limpia el contenido sin desactivar el editor
            }

        });

}



btnDocumets.addEventListener('click', goToUploadDocuments)
btnAdjuntos.addEventListener('click', modalEmail)
btnWhatsApp.addEventListener('click', modalWhatsApp)
buttonSendMail.addEventListener("click", sendEmail)
