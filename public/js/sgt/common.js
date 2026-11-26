function mostrarLoading(text = "Espere un momento...") {
    let label = document.querySelector("#loading-text");
    label.textContent = text;

    document.getElementById("loading-overlay").style.display = "flex";
}

function ocultarLoading() {
    document.getElementById("loading-overlay").style.display = "none";
}

function simularEvento() {
    mostrarLoading();

    // Simula una operación
    setTimeout(() => {
        ocultarLoading();
    }, 2000);
}

/* function allowOnlyDecimals(event) {
    const input = event.target;
    const regex = /^[0-9]*\.?[0-9]*$/;

    if (!regex.test(input.value)) {
        input.value = input.value.slice(0, -1);
    }
}
 */

function allowOnlyDecimals(event) {
    const input = event.target;

    let value = input.value;

    value = value.replace(/,/g, ".");

    value = value.replace(/[^0-9.]/g, "");

    const parts = value.split(".");

    if (parts.length > 2) {
        value = parts[0] + "." + parts.slice(1).join("");
    }

    input.value = value;
}
function validarEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

function moneyFormat(moneyValue) {
    if (moneyValue === null || moneyValue === undefined || moneyValue === "") {
        moneyValue = 0;
    }
    let valStr = String(moneyValue);
    if (valStr.includes("NaN")) {
        moneyValue = 0;
    } else {
        let clean = typeof moneyValue === "string" ? reverseMoneyFormat(valStr).trim() : moneyValue;
        moneyValue = parseFloat(clean);
    }
    let num = parseFloat(moneyValue);
    if (isNaN(num)) {
        num = 0;
    }
    const $formatMoneda = new Intl.NumberFormat("es-MX", {
        style: "currency",
        currency: "MXN",
        minimumFractionDigits: 2,
    }).format(num);

    return $formatMoneda;
}

function reverseMoneyFormat(formatValue) {
    if (formatValue === null || formatValue === undefined) {
        return "";
    }
    var valorLimpio = String(formatValue).replace(/NaN/gi, "").replace(/[\$,\s]/g, "");
    return valorLimpio;
}

function generateUUID() {
    return "xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx".replace(
        /[xy]/g,
        function (c) {
            const r = (Math.random() * 16) | 0;
            const v = c === "x" ? r : (r & 0x3) | 0x8;
            return v.toString(16);
        },
    );
}

function formatoConsecutivo(folio, lenFormat = 5) {
    const len = lenFormat - String(folio).length;
    let consecutivo = "";

    for (let x = 1; x <= len; x++) {
        consecutivo += "0";
    }

    consecutivo += folio;
    return consecutivo;
}

function changeTag(tagId, value) {
    var tag = document.querySelector("#" + tagId);
    if (tag) tag.textContent = value;
}

function obtenerFechaEnLetra(fecha) {
    const dia = conocerDiaSemanaFecha(fecha);
    const num = new Date(fecha).getDate();
    const anno = new Date(fecha).getFullYear();
    const meses = [
        "enero",
        "febrero",
        "marzo",
        "abril",
        "mayo",
        "junio",
        "julio",
        "agosto",
        "septiembre",
        "octubre",
        "noviembre",
        "diciembre",
    ];
    const mes = meses[new Date(fecha).getMonth()];

    return `${dia}, ${num} de ${mes} del ${anno}`;
}

function conocerDiaSemanaFecha(fecha) {
    const dias = [
        "Domingo",
        "Lunes",
        "Martes",
        "Miércoles",
        "Jueves",
        "Viernes",
        "Sábado",
    ];
    const dia = new Date(fecha).getDay();
    return dias[dia];
}

// =========================================================================
// Estrategia ágil de captura de números y moneda en SGT
// (Limpia al foco si es 0, selecciona todo si es > 0, formatea en blur)
// =========================================================================
(function ($) {
    if (!$ || window._sgtNumericCaptureInitialized) return;
    window._sgtNumericCaptureInitialized = true;

    let focusSelectTarget = null;

    // 1. Campos de moneda (.moneyformat)
    $(document).on("focus", ".moneyformat", function (e) {
        if (this.readOnly || this.disabled) return;
        if ($(this).hasClass("calculo-cotizacion") || $(this).hasClass("calculo-proveedor")) return;

        var val = this.value;
        var cleanVal = reverseMoneyFormat(val).trim();
        var num = parseFloat(cleanVal);

        if (!cleanVal || isNaN(num) || num === 0) {
            this.value = "";
            focusSelectTarget = null;
        } else {
            this.value = cleanVal;
            focusSelectTarget = this;
            setTimeout(() => {
                if (document.activeElement === this) {
                    try {
                        this.select();
                    } catch (err) {}
                }
            }, 10);
        }
    });

    $(document).on("blur", ".moneyformat", function (e) {
        if (this.readOnly || this.disabled) return;
        if ($(this).hasClass("calculo-cotizacion") || $(this).hasClass("calculo-proveedor")) return;

        focusSelectTarget = null;
        var cleanVal = reverseMoneyFormat(this.value).trim();
        var num = parseFloat(cleanVal);

        if (!cleanVal || isNaN(num) || num === 0) {
            this.value = moneyFormat(0);
        } else {
            this.value = moneyFormat(num);
        }
    });

    // 2. Campos numéricos y decimales (.numeric-input, type=number, litros, gastos montos)
    const numericSelectors = ".numeric-input, input[name='gasto_monto[]'], input[name='gasto_litros[]'], #litros_diesel, #litros_urea";

    $(document).on("focus", numericSelectors, function (e) {
        if (this.readOnly || this.disabled) return;

        var cleanVal = String(this.value || "").trim();
        var num = parseFloat(cleanVal);

        if (!cleanVal || isNaN(num) || num === 0) {
            this.value = "";
            focusSelectTarget = null;
        } else {
            focusSelectTarget = this;
            setTimeout(() => {
                if (document.activeElement === this) {
                    try {
                        this.select();
                    } catch (err) {}
                }
            }, 10);
        }
    });

    $(document).on("blur", numericSelectors, function (e) {
        if (this.readOnly || this.disabled) return;
        focusSelectTarget = null;
        var cleanVal = String(this.value || "").trim();
        var num = parseFloat(cleanVal);

        if (!cleanVal || isNaN(num)) {
            this.value = "";
        }
    });

    // Evitar que el mouseup deseleccione el texto recién enfocado
    $(document).on("mouseup", ".moneyformat, " + numericSelectors, function (e) {
        if (this.readOnly || this.disabled) return;
        if (focusSelectTarget === this) {
            focusSelectTarget = null;
            e.preventDefault();
            try {
                this.select();
            } catch (err) {}
        }
    });
})(window.jQuery || $);

