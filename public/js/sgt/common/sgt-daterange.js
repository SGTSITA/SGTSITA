/**
 * SGT DateRangePicker — Control Propio y Centralizado para Módulo Central SGT
 * 
 * Delimita automáticamente los ejercicios válidos a partir de cotizaciones en la base de datos
 * (ejercicio_ini_bd: ej. 2024 hasta ejercicio_fin_bd: año actual + 1)
 * garantizando cero desfasamiento en años futuros sin modificar código.
 */

(function (window, $) {
    'use strict';

    window.SgtDateRange = {
        /**
         * Obtiene la configuración de periodos de BD sincrónica o asíncronamente
         */
        getConfig: function () {
            if (window.SGT_DATE_CONFIG && window.SGT_DATE_CONFIG.ejercicio_ini_bd) {
                return window.SGT_DATE_CONFIG;
            }

            // Intentar recuperar de sessionStorage
            try {
                var cached = sessionStorage.getItem('sgt_date_config');
                if (cached) {
                    window.SGT_DATE_CONFIG = JSON.parse(cached);
                    return window.SGT_DATE_CONFIG;
                }
            } catch (e) {
                // Ignore storage error
            }

            // Fallback seguro dinámico basado en el año del sistema
            var currentYear = new Date().getFullYear();
            var fallback = {
                ejercicio_ini_bd: 2024,
                ejercicio_fin_bd: currentYear + 1,
                anios: [],
                min_date: '2024-01-01',
                max_date: (currentYear + 1) + '-12-31'
            };
            for (var y = currentYear + 1; y >= 2024; y--) {
                fallback.anios.push(y);
            }
            window.SGT_DATE_CONFIG = fallback;
            return fallback;
        },

        /**
         * Carga asíncrona vía Ajax global si se desea refrescar
         */
        fetchConfigAsync: function (callback) {
            if (window.SGT_DATE_CONFIG && window.SGT_DATE_CONFIG.ejercicio_ini_bd) {
                if (typeof callback === 'function') callback(window.SGT_DATE_CONFIG);
                return;
            }

            $.ajax({
                url: '/sgt/fechas/periodos-cotizaciones',
                method: 'GET',
                dataType: 'json',
                success: function (data) {
                    if (data && data.ejercicio_ini_bd) {
                        window.SGT_DATE_CONFIG = data;
                        try {
                            sessionStorage.setItem('sgt_date_config', JSON.stringify(data));
                        } catch (e) {}
                    }
                    if (typeof callback === 'function') callback(window.SgtDateRange.getConfig());
                },
                error: function () {
                    if (typeof callback === 'function') callback(window.SgtDateRange.getConfig());
                }
            });
        },

        /**
         * Genera los rangos de acciones rápidas para daterangepicker
         * Muestra únicamente: 'Últimos 7 días', 'Últimos 30 días', 'Este mes', 'Mes anterior'
         * y 'Personalizado' (manejado automáticamente por el plugin con customRangeLabel).
         */
        generarRangos: function () {
            return {
                'Últimos 7 días': [moment().subtract(6, 'days').startOf('day'), moment().endOf('day')],
                'Últimos 30 días': [moment().subtract(29, 'days').startOf('day'), moment().endOf('day')],
                'Este mes': [moment().startOf('month'), moment().endOf('month')],
                'Mes anterior': [
                    moment().subtract(1, 'month').startOf('month'),
                    moment().subtract(1, 'month').endOf('month')
                ]
            };
        },

        /**
         * Genera el locale estandarizado en español
         */
        getLocale: function (customSeparator) {
            return {
                format: 'YYYY-MM-DD',
                separator: customSeparator || ' AL ',
                applyLabel: 'Aplicar',
                cancelLabel: 'Cancelar',
                fromLabel: 'Desde',
                toLabel: 'Hasta',
                customRangeLabel: 'Personalizado',
                weekLabel: 'S',
                daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
                monthNames: [
                    'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
                ],
                firstDay: 1
            };
        },

        /**
         * Inicializa un DateRangePicker con los estándares y límites de BD de SGT
         */
        init: function (selector, userOptions, userCallback) {
            var $el = $(selector);
            if (!$el.length) return null;

            var config = window.SgtDateRange.getConfig();
            userOptions = userOptions || {};

            var separator = (userOptions.locale && userOptions.locale.separator) ? userOptions.locale.separator : ' AL ';
            var customLocale = $.extend(true, {}, window.SgtDateRange.getLocale(separator), userOptions.locale || {});

            var defaults = {
                opens: 'right',
                showDropdowns: true,
                linkedCalendars: false,
                showCustomRangeLabel: true,
                minYear: parseInt(config.ejercicio_ini_bd, 10),
                maxYear: parseInt(config.ejercicio_fin_bd, 10),
                minDate: moment(config.min_date),
                maxDate: moment(config.max_date),
                locale: customLocale,
                ranges: window.SgtDateRange.generarRangos()
            };

            var finalOptions = $.extend(true, {}, defaults, userOptions);

            // Asegurar que si el usuario definió minDate o maxDate personalizados, se respeten
            if (userOptions.minDate) finalOptions.minDate = userOptions.minDate;
            if (userOptions.maxDate) finalOptions.maxDate = userOptions.maxDate;

            $el.daterangepicker(finalOptions, userCallback);
            return $el.data('daterangepicker');
        }
    };

    // Alias global accesible en cualquier script
    window.initSgtDateRange = function (selector, options, callback) {
        return window.SgtDateRange.init(selector, options, callback);
    };

})(window, window.jQuery || $);
