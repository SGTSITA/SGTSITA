"use strict";

var KTAppInboxCompose = function() {
    const configurarCcBcc = e => {
        const cc = e.querySelector('[data-kt-inbox-form="cc"]'),
              ccBtn = e.querySelector('[data-kt-inbox-form="cc_button"]'),
              ccClose = e.querySelector('[data-kt-inbox-form="cc_close"]'),
              bcc = e.querySelector('[data-kt-inbox-form="bcc"]'),
              bccBtn = e.querySelector('[data-kt-inbox-form="bcc_button"]'),
              bccClose = e.querySelector('[data-kt-inbox-form="bcc_close"]');

        ccBtn.addEventListener("click", e => {
            e.preventDefault();
            cc.classList.remove("d-none");
            cc.classList.add("d-flex");
        });

        ccClose.addEventListener("click", e => {
            e.preventDefault();
            cc.classList.add("d-none");
            cc.classList.remove("d-flex");
        });

        bccBtn.addEventListener("click", e => {
            e.preventDefault();
            bcc.classList.remove("d-none");
            bcc.classList.add("d-flex");
        });

        bccClose.addEventListener("click", e => {
            e.preventDefault();
            bcc.classList.add("d-none");
            bcc.classList.remove("d-flex");
        });
    };

    const inicializarQuill = e => {
        // 💡 Guardamos la instancia en una variable global
        window.quillEditor = new Quill("#kt_inbox_form_editor", {
            modules: {
                toolbar: [
                    [{ header: [1, 2, false] }],
                    ["bold", "italic", "underline"]
                ]
            },
            placeholder: "Escriba un mensaje para el destinatario...",
            theme: "snow"
        });

        // Opcional: ajustar estilos del toolbar
        const toolbar = e.querySelector(".ql-toolbar");
        if (toolbar) {
            const estilos = ["px-5", "border-top-0", "border-start-0", "border-end-0"];
            toolbar.classList.add(...estilos);
        }
    };

    return {
        init: function() {
            const formulario = document.querySelector("#kt_inbox_compose_form");
            if (!formulario) return;

            configurarCcBcc(formulario);
            inicializarQuill(formulario);
        }
    };
}();

KTUtil.onDOMContentLoaded(function() {
    KTAppInboxCompose.init();
});
