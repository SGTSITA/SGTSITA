"use strict";

var KTSigninGeneral = (function () {
    var form, submitButton, validator;

    function mostrarMensajeError(status, data) {
        let titulo = "Error de acceso";
        let icono = "error";
        let mensaje =
            data?.mensaje || data?.message || "No fue posible iniciar sesión.";

        if (status === 401) {
            titulo = "Credenciales incorrectas";
            icono = "error";
        }

        if (status === 403) {
            titulo = "Acceso restringido";
            icono = "warning";
            mensaje =
                data?.mensaje ||
                "Tu usuario no tiene acceso a este sistema. Verifica que estés entrando al sistema correcto.";
        }

        if (status === 419) {
            titulo = "Sesión expirada";
            icono = "warning";
            mensaje =
                "La sesión expiró o el token de seguridad no es válido. Recarga la página e intenta nuevamente.";
        }

        Swal.fire({
            text: mensaje,
            icon: icono,
            title: titulo,
            buttonsStyling: false,
            confirmButtonText: "Ok, entendido",
            customClass: {
                confirmButton: "btn btn-primary",
            },
        });
    }

    function bloquearBoton() {
        submitButton.setAttribute("data-kt-indicator", "on");
        submitButton.disabled = true;
    }

    function desbloquearBoton() {
        submitButton.removeAttribute("data-kt-indicator");
        submitButton.disabled = false;
    }

    return {
        init: function () {
            form = document.querySelector("#kt_sign_in_form");
            submitButton = document.querySelector("#kt_sign_in_submit");

            validator = FormValidation.formValidation(form, {
                fields: {
                    email: {
                        validators: {
                            regexp: {
                                regexp: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
                                message:
                                    "Lo sentimos, al parecer no está escribiendo una dirección de correo electrónico válida",
                            },
                            notEmpty: {
                                message:
                                    "Se requiere dirección de correo electrónico",
                            },
                        },
                    },
                    password: {
                        validators: {
                            notEmpty: {
                                message: "El campo password es requerido",
                            },
                        },
                    },
                },
                plugins: {
                    trigger: new FormValidation.plugins.Trigger(),
                    bootstrap: new FormValidation.plugins.Bootstrap5({
                        rowSelector: ".fv-row",
                        eleInvalidClass: "",
                        eleValidClass: "",
                    }),
                },
            });

            submitButton.addEventListener("click", function (event) {
                event.preventDefault();

                validator.validate().then(function (status) {
                    if (status !== "Valid") {
                        Swal.fire({
                            text: "No es posible iniciar sesión, usuario y password requerido.",
                            icon: "error",
                            buttonsStyling: false,
                            confirmButtonText: "Ok, intentarlo de nuevo",
                            customClass: {
                                confirmButton: "btn btn-primary",
                            },
                        });

                        return;
                    }

                    bloquearBoton();

                    axios
                        .post(form.getAttribute("action"), new FormData(form), {
                            headers: {
                                Accept: "application/json",
                            },
                        })
                        .then(function (response) {
                            const data = response.data || {};

                            if (data.success === false) {
                                mostrarMensajeError(response.status, data);
                                return;
                            }

                            const redirectUrl =
                                data.redirect ||
                                form.getAttribute("data-kt-redirect-url");

                            if (redirectUrl) {
                                location.href = redirectUrl;
                                return;
                            }

                            Swal.fire({
                                text: data.mensaje || "Acceso correcto.",
                                icon: "success",
                                buttonsStyling: false,
                                confirmButtonText: "Continuar",
                                customClass: {
                                    confirmButton: "btn btn-primary",
                                },
                            });
                        })
                        .catch(function (error) {
                            const status = error?.response?.status;
                            const data = error?.response?.data || {};

                            mostrarMensajeError(status, data);
                        })
                        .finally(function () {
                            desbloquearBoton();
                        });
                });
            });
        },
    };
})();

KTUtil.onDOMContentLoaded(function () {
    KTSigninGeneral.init();
});

function togglePassword() {
    const password = document.getElementById("password");
    const eyeIcon = document.getElementById("eyeIcon");

    if (password.type === "password") {
        password.type = "text";
        eyeIcon.classList.remove("bi-eye");
        eyeIcon.classList.add("bi-eye-slash");
    } else {
        password.type = "password";
        eyeIcon.classList.remove("bi-eye-slash");
        eyeIcon.classList.add("bi-eye");
    }
}
