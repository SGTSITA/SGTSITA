"use strict";
var KTAppInboxCompose = function() {
    const e = e => {
            const t = e.querySelector('[data-kt-inbox-form="cc"]'),
                a = e.querySelector('[data-kt-inbox-form="cc_button"]'),
                n = e.querySelector('[data-kt-inbox-form="cc_close"]'),
                o = e.querySelector('[data-kt-inbox-form="bcc"]'),
                r = e.querySelector('[data-kt-inbox-form="bcc_button"]'),
                l = e.querySelector('[data-kt-inbox-form="bcc_close"]');
            a.addEventListener("click", (e => {
                e.preventDefault(), t.classList.remove("d-none"), t.classList.add("d-flex")
            })), n.addEventListener("click", (e => {
                e.preventDefault(), t.classList.add("d-none"), t.classList.remove("d-flex")
            })), r.addEventListener("click", (e => {
                e.preventDefault(), o.classList.remove("d-none"), o.classList.add("d-flex")
            })), l.addEventListener("click", (e => {
                e.preventDefault(), o.classList.add("d-none"), o.classList.remove("d-flex")
            }))
        },

        
        n = e => {
            new Quill("#kt_inbox_form_editor", {
                modules: {
                    toolbar: [
                        [{
                            header: [1, 2, !1]
                        }],
                        ["bold", "italic", "underline"],
                        
                    ]
                },
                placeholder: "Escriba un mensaje para el destinatario...",
                theme: "snow"
            });
            const t = e.querySelector(".ql-toolbar");
            if (t) {
                const e = ["px-5", "border-top-0", "border-start-0", "border-end-0"];
                t.classList.add(...e)
            }
        }
    return {
        init: function() {
            (() => {
                const r = document.querySelector("#kt_inbox_compose_form"),
                    l = r.querySelectorAll('[data-kt-inbox-form="tagify"]');
                 n(r)
            })()
        }
    }
}();
KTUtil.onDOMContentLoaded((function() {
    KTAppInboxCompose.init()
}));