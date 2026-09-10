(function () {
    let activeRequests = 0;

    function getActiveModal() {
        return document.querySelector(".modal.show .modal-content");
    }

    function createGlobalLoader() {
        let loader = document.getElementById("scb-global-loader");

        if (loader) return loader;

        loader = document.createElement("div");
        loader.id = "scb-global-loader";
        loader.innerHTML = `
            <div class="scb-loader-box">
                <div class="scb-loader-spinner"></div>
                <div class="scb-loader-text">Procesando solicitud...</div>
            </div>
        `;

        document.body.appendChild(loader);

        return loader;
    }

    function createModalLoader(modalContent) {
        let loader = modalContent.querySelector(".scb-modal-loader");

        if (loader) return loader;

        loader = document.createElement("div");
        loader.className = "scb-modal-loader";
        loader.innerHTML = `
            <div class="scb-loader-box">
                <div class="scb-loader-spinner"></div>
                <div class="scb-loader-text">Guardando información...</div>
            </div>
        `;

        modalContent.appendChild(loader);

        return loader;
    }

    function showLoader() {
        activeRequests++;

        const modalContent = getActiveModal();

        if (modalContent) {
            const loader = createModalLoader(modalContent);
            loader.style.display = "flex";
            return;
        }

        const loader = createGlobalLoader();
        loader.style.display = "flex";
    }

    function hideLoader() {
        activeRequests = Math.max(0, activeRequests - 1);

        if (activeRequests > 0) return;

        const globalLoader = document.getElementById("scb-global-loader");

        if (globalLoader) {
            globalLoader.style.display = "none";
        }

        document.querySelectorAll(".scb-modal-loader").forEach((loader) => {
            loader.style.display = "none";
        });
    }

    window.ScbLoading = {
        show: showLoader,
        hide: hideLoader,
    };

    /*
    |--------------------------------------------------------------------------
    | Interceptar fetch global
    |--------------------------------------------------------------------------
    */
    const originalFetch = window.fetch;

    window.fetch = async function (...args) {
        showLoader();

        try {
            return await originalFetch.apply(this, args);
        } finally {
            hideLoader();
        }
    };

    /*
    |--------------------------------------------------------------------------
    | Interceptar jQuery Ajax global
    |--------------------------------------------------------------------------
    */
    if (window.jQuery) {
        $(document).ajaxStart(function () {
            showLoader();
        });

        $(document).ajaxStop(function () {
            hideLoader();
        });
    }
})();
