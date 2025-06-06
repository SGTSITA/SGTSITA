
let tagNumContenedor = document.querySelector("#numContenedor");
let numContenedor = tagNumContenedor.textContent;
let urlGetFiles = `/viajes/file-manager/get-file-list/${numContenedor}`;

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
        { data: 'fileName' },
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
        if(deleteSelected){
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
        });}
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
    const buttonSendMail = document.querySelector('[data-kt-inbox-form="sendmail"]');
    const mainEmail = document.querySelector('#compose_to');
    const ccEmail = document.querySelector('#compose_cc');
    const emailCC = document.querySelector('[data-kt-inbox-form="cc"]');
    const subject = document.querySelector('#compose_subject')
    const messageMail = document.querySelector('#kt_inbox_form_editor')

    function goToUploadDocuments(){
        //let contenedor = apiGrid.getSelectedRows();
/*
        let numContenedor = null;
        contenedor.forEach(c => numContenedor = c.NumContenedor)*/

        let titleFileUploader = document.querySelector("#titleFileUploader");
        titleFileUploader.textContent = numContenedor.toUpperCase();
        localStorage.setItem('numContenedor',numContenedor);
        const modalElement = document.getElementById('kt_modal_fileuploader');
        const bootstrapModal = new bootstrap.Modal(modalElement);
        bootstrapModal.show();
   }

   function modalEmail(){
    subject.value = `Documentos Contenedor ${numContenedor}`
    const modalElement = document.getElementById('modal-enviar-correo');
    const bootstrapModal = new bootstrap.Modal(modalElement);
    bootstrapModal.show();
    
   }

   function sendEmail(){

    if(!validarEmail(mainEmail.value)){
        Swal.fire('Dirección invalida','Lo sentimos, el dato en el campo Para no es un correo electrónico','warning')
        return false;
    }

    emailCC.classList.forEach(c => {
        if(c == "d-flex"){
            if(!validarEmail(ccEmail.value)){
                Swal.fire('Dirección invalida en Cc (Copiar a)','Lo sentimos, el dato en el campo Cc no es un correo electrónico','warning')
                return false;
            }
        }
    })

   if(subject.value.length == 0) {
    Swal.fire('Escribir Asunto','Por favor introduzca asunto','warning')
    return false;
   }

   if(messageMail.textContent.length == 0){
    Swal.fire('Escribir mensaje','Por favor escriba un breve mensaje para el receptor','warning')
    return false;
   }


   enviarCorreo();    
   }

   function enviarCorreo(){

    let attachmentFiles = [];
    const allCheckboxes = document.querySelectorAll('tbody [type="checkbox"]');
    let checkedState = false;
    let count = 0;

    allCheckboxes.forEach(c => {
        if (c.checked) {
            checkedState = true;
            count++;
            let tmpFile = {"file":c.value}
            attachmentFiles = [...attachmentFiles, tmpFile]
        }
    });
    buttonSendMail.setAttribute("data-kt-indicator", "on")
    let _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    fetch('/sendfiles', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({_token: _token, email : mainEmail.value, secondaryEmail : ccEmail.value, subject: subject.value, message: messageMail.textContent, attachmentFiles: attachmentFiles, numContenedor: numContenedor })
    })
    .then(response => response.json())
    .then(data => {
        Swal.fire(data.Titulo,data.Mensaje,data.TMensaje)
    })
    .catch(error => console.error('Error:', error))
    .finally(() => {
        buttonSendMail.removeAttribute("data-kt-indicator")
        $('#modal-enviar-correo').modal('hide')
        mainEmail.value = '';
        ccEmail.value = '';
        messageMail.textContent = '';

    });

}

   

   btnDocumets.addEventListener('click',goToUploadDocuments)
   btnAdjuntos.addEventListener('click', modalEmail)
   buttonSendMail.addEventListener("click", sendEmail)