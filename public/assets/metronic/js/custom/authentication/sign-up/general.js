"use strict";
var KTSignupGeneral = function() {
    var e, t, r, a, s = function() {
        return a.getScore() > 50
    };
    return {
        init: function() {
            e = document.querySelector("#kt_sign_up_form"), t = document.querySelector("#kt_sign_up_submit"), a = KTPasswordMeter.getInstance(e.querySelector('[data-kt-password-meter="true"]')), ! function(e) {
                try {
                    return new URL(e), !0
                } catch (e) {
                    return !1
                }
            }(t.closest("form").getAttribute("action")) ? (r = FormValidation.formValidation(e, {
                fields: {
                    "first-name": {
                        validators: {
                            notEmpty: {
                                message: "First Name is required"
                            }
                        }
                    },
                    "last-name": {
                        validators: {
                            notEmpty: {
                                message: "Last Name is required"
                            }
                        }
                    },
                    email: {
                        validators: {
                            regexp: {
                                regexp: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
                                message: "Lo sentimos, al parecer no esta escribiendo una dirección de correo electrónico válida"
                            },
                            notEmpty: {
                                message: "Se requiere dirección de correo electrónico"
                            }
                        }
                    },
                    password: {
                        validators: {
                            notEmpty: {
                                message: "El campo password es requerido"
                            },
                            callback: {
                                message: "Por favor introduzca un password valido",
                                callback: function(e) {
                                    if (e.value.length > 0) return s()
                                }
                            }
                        }
                    },
                    "confirm-password": {
                        validators: {
                            notEmpty: {
                                message: "Se requiere la confirmación del password"
                            },
                            identical: {
                                compare: function() {
                                    return e.querySelector('[name="password"]').value
                                },
                                message: "El password y su confirmación no coinciden"
                            }
                        }
                    },
                    toc: {
                        validators: {
                            notEmpty: {
                                message: "Usted debe aceptar los Terminos & Condiciones"
                            }
                        }
                    }
                },
                plugins: {
                    trigger: new FormValidation.plugins.Trigger({
                        event: {
                            password: !1
                        }
                    }),
                    bootstrap: new FormValidation.plugins.Bootstrap5({
                        rowSelector: ".fv-row",
                        eleInvalidClass: "",
                        eleValidClass: ""
                    })
                }
            }), t.addEventListener("click", (function(s) {
                var formData = new FormData(document.getElementById("kt_sign_up_form"));
                s.preventDefault(), r.revalidateField("password"), 
                r.validate().then((function(r) {
                    "Valid" == r ? ( 
                        $.ajax({
                            url:'/accounts/register',
                            type:'post',
                           data: formData,
                           data: formData,
                            processData: false,  
                            contentType: false,
                            beforeSend:()=>{
                                t.setAttribute("data-kt-indicator", "on"), t.disabled = !0
                            },
                            success:(response)=>{
                                t.removeAttribute("data-kt-indicator"), t.disabled = !1;
                            
                                Swal.fire({
                                    text: response.message,
                                    icon: (response.success) ? "success" : "warning",
                                    buttonsStyling: !1,
                                    allowOutsideClick:false,
                                    allowEscapeKey:false,
                                    confirmButtonText: "Ok, entendido!",
                                    customClass: {
                                        confirmButton: "btn btn-primary"
                                    }
                                }).then((function(t) {
                                    if (t.isConfirmed && response.success) {
                                        e.reset(), a.reset();
                                        var r = e.getAttribute("data-kt-redirect-url");
                                        r && (location.href = r)
                                    }
                                }))
                            },
                            error:(e)=>{

                                var response = e.responseJSON;
                                var errors = response.errors;
                                var errorText = "";
                                Object.keys(errors).forEach((field) => {
                                    
                                    errors[field].forEach((error) => {
                                        errorText = `Error: ${error}`;
                                    });
                                });

                                Swal.fire({
                                    text:errorText,
                                    icon:"error",
                                    confirmButtonText: "Ok, entendido!",
                                    customClass: {
                                        confirmButton: "btn btn-primary"
                                    }
                                });

                                t.removeAttribute("data-kt-indicator"), t.disabled = !1
                            }
                        })
                    ) : Swal.fire({
                        text: "Lo sentimos, no es posible continuar, el formulario tiene campos incorrectos",
                        icon: "error",
                        buttonsStyling: !1,
                        confirmButtonText: "Ok, entendido!",
                        customClass: {
                            confirmButton: "btn btn-primary"
                        }
                    })
                }))
            })), e.querySelector('input[name="password"]').addEventListener("input", (function() {
                this.value.length > 0 && r.updateFieldStatus("password", "NotValidated")
            }))) : (r = FormValidation.formValidation(e, {
                fields: {
                    name: {
                        validators: {
                            notEmpty: {
                                message: "Name is required"
                            }
                        }
                    },
                    email: {
                        validators: {
                            regexp: {
                                regexp: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
                                message: "The value is not a valid email address"
                            },
                            notEmpty: {
                                message: "Email address is required"
                            }
                        }
                    },
                    password: {
                        validators: {
                            notEmpty: {
                                message: "The password is required"
                            },
                            callback: {
                                message: "Please enter valid password",
                                callback: function(e) {
                                    if (e.value.length > 0) return s()
                                }
                            }
                        }
                    },
                    password_confirmation: {
                        validators: {
                            notEmpty: {
                                message: "The password confirmation is required"
                            },
                            identical: {
                                compare: function() {
                                    return e.querySelector('[name="password"]').value
                                },
                                message: "The password and its confirm are not the same"
                            }
                        }
                    },
                    toc: {
                        validators: {
                            notEmpty: {
                                message: "You must accept the terms and conditions"
                            }
                        }
                    }
                },
                plugins: {
                    trigger: new FormValidation.plugins.Trigger({
                        event: {
                            password: !1
                        }
                    }),
                    bootstrap: new FormValidation.plugins.Bootstrap5({
                        rowSelector: ".fv-row",
                        eleInvalidClass: "",
                        eleValidClass: ""
                    })
                }
            }), t.addEventListener("click", (function(a) {
                a.preventDefault(), r.revalidateField("password"), r.validate().then((function(r) {
                    "Valid" == r ? (t.setAttribute("data-kt-indicator", "on"), t.disabled = !0, axios.post(t.closest("form").getAttribute("action"), new FormData(e)).then((function(t) {
                        if (t) {
                            e.reset();
                            const t = e.getAttribute("data-kt-redirect-url");
                            t && (location.href = t)
                        } else Swal.fire({
                            text: "Lo sentimos, no es posible continuar, el formulario tiene campos incorrectos",
                            icon: "error",
                            buttonsStyling: !1,
                            confirmButtonText: "Ok, entendido!",
                            customClass: {
                                confirmButton: "btn btn-primary"
                            }
                        })
                    })).catch((function(e) {
                        Swal.fire({
                            text: "Lo sentimos, no es posible continuar, el formulario tiene campos incorrectos",
                            icon: "error",
                            buttonsStyling: !1,
                            confirmButtonText: "Ok, entendido!",
                            customClass: {
                                confirmButton: "btn btn-primary"
                            }
                        })
                    })).then((() => {
                        t.removeAttribute("data-kt-indicator"), t.disabled = !1
                    }))) : Swal.fire({
                        text: "Lo sentimos, no es posible continuar, el formulario tiene campos incorrectos",
                        icon: "error",
                        buttonsStyling: !1,
                        confirmButtonText: "Ok, entendido!",
                        customClass: {
                            confirmButton: "btn btn-primary"
                        }
                    })
                }))
            })), e.querySelector('input[name="password"]').addEventListener("input", (function() {
                this.value.length > 0 && r.updateFieldStatus("password", "NotValidated")
            })))
        }
    }
}();
KTUtil.onDOMContentLoaded((function() {
    KTSignupGeneral.init()
}));