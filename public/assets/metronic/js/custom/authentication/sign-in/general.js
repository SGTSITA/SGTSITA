'use strict';
var KTSigninGeneral = (function () {
    var t, e, r;
    return {
        init: function () {
            ((t = document.querySelector('#kt_sign_in_form')),
                (e = document.querySelector('#kt_sign_in_submit')),
                (r = FormValidation.formValidation(t, {
                    fields: {
                        email: {
                            validators: {
                                regexp: {
                                    regexp: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
                                    message:
                                        'Lo sentimos, al parecer no esta escribiendo una dirección de correo electrónico válida',
                                },
                                notEmpty: {
                                    message: 'Se requiere dirección de correo electrónico',
                                },
                            },
                        },
                        password: {
                            validators: {
                                notEmpty: {
                                    message: 'El campo password es requerido',
                                },
                            },
                        },
                    },
                    plugins: {
                        trigger: new FormValidation.plugins.Trigger(),
                        bootstrap: new FormValidation.plugins.Bootstrap5({
                            rowSelector: '.fv-row',
                            eleInvalidClass: '',
                            eleValidClass: '',
                        }),
                    },
                })),
                !(function (t) {
                    try {
                        return (new URL(t), !0);
                    } catch (t) {
                        return !1;
                    }
                })(e.closest('form').getAttribute('action'))
                    ? e.addEventListener('click', function (i) {
                          (i.preventDefault(),
                              r.validate().then(function (r) {
                                  'Valid' == r
                                      ? (e.setAttribute('data-kt-indicator', 'on'), (e.disabled = !0))
                                      : Swal.fire({
                                            text: 'Datos incorrectos, el email y/o password.',
                                            icon: 'error',
                                            buttonsStyling: !1,
                                            confirmButtonText: 'Ok, Reintentar!',
                                            customClass: {
                                                confirmButton: 'btn btn-primary',
                                            },
                                        });
                              }));
                      })
                    : e.addEventListener('click', function (i) {
                          (i.preventDefault(),
                              r.validate().then(function (r) {
                                  'Valid' == r
                                      ? (e.setAttribute('data-kt-indicator', 'on'),
                                        (e.disabled = !0),
                                        axios
                                            .post(e.closest('form').getAttribute('action'), new FormData(t))
                                            .then(function (e) {
                                                if (e) {
                                                    t.reset();
                                                    const e = t.getAttribute('data-kt-redirect-url');
                                                    e && (location.href = e);
                                                } else
                                                    Swal.fire({
                                                        text: 'El usuario y/o password es incorrecto.',
                                                        icon: 'error',
                                                        buttonsStyling: !1,
                                                        confirmButtonText: 'Ok, reintentar!',
                                                        customClass: {
                                                            confirmButton: 'btn btn-primary',
                                                        },
                                                    });
                                            })
                                            .catch(function (t) {
                                                Swal.fire({
                                                    text: 'No fue posible iniciar sesión, usuario y/o password incorrecto, por favor intentelo de nuevo.',
                                                    icon: 'warning',
                                                    buttonsStyling: !1,
                                                    confirmButtonText: 'Ok, intentarlo de nuevo!',
                                                    customClass: {
                                                        confirmButton: 'btn btn-primary',
                                                    },
                                                });
                                            })
                                            .then(() => {
                                                (e.removeAttribute('data-kt-indicator'), (e.disabled = !1));
                                            }))
                                      : Swal.fire({
                                            text: 'No es posible iniciar sesión, usuario y password requerido.',
                                            icon: 'error',
                                            buttonsStyling: !1,
                                            confirmButtonText: 'Ok, intentarlo de nuevo!',
                                            customClass: {
                                                confirmButton: 'btn btn-primary',
                                            },
                                        });
                              }));
                      }));
        },
    };
})();
KTUtil.onDOMContentLoaded(function () {
    KTSigninGeneral.init();
});
