<div class="row mt-10" id="noticeFileUploader">
    <div class="col-6 offset-3">
        <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed mb-9 p-6">
            <!--begin::Icon-->
            <i class="ki-duotone ki-design-1 fs-2tx text-primary me-4"></i>
            <!--end::Icon-->
            <!--begin::Wrapper-->
            <div class="d-flex flex-stack flex-grow-1">
                <!--begin::Content-->
                <div class="fw-semibold">
                    <div class="fs-6 text-gray-700">
                        Para adjuntar archivos, primero debe guardar la información de la solicitud de viaje. Esto
                        garantiza que los documentos se asocien correctamente al registro y que el proceso continúe de
                        forma segura y ordenada.
                        <br />
                        <br />
                        <button type="submit" class="btn btn-link text-primary fw-bold">Aceptar y Continuar</button>
                    </div>
                </div>
                <!--end::Content-->
            </div>
            <!--end::Wrapper-->
        </div>
    </div>
</div>

<div class="d-none" id="fileUploaderContainer">

    <div class="row g-7">

        <!-- Carta Porte -->
        <div class="col-md-6 col-xl-4">
            <div class="card card-flush py-4 h-100">

                <div class="card-header">
                    <div class="card-title">
                        <h2>Formato Carta Porte</h2>
                    </div>
                </div>

                <div class="card-body pt-0">
                    <div class="row g-3">

                        {{--  <div class="col-12">
                            <label class="form-label fw-semibold">Número de documento</label>
                            <input type="text" name="numCartaPorte" class="form-control" placeholder="Ej: CP-123456"
                                @if ($action == 'editar') value="{{ $cotizacion?->DocCotizacion?->num_boleta_liberacion ?? '' }}" @endif>
                        </div> --}}

                        {{-- <div class="col-12">
                            <label class="form-label fw-semibold">Fecha</label>
                            <input type="date" name="fechaCartaPorte" class="form-control">
                        </div> --}}

                        <div class="col-12">
                            <label class="form-label fw-semibold">Archivo</label>
                            <input type="file" name="fileCartaPorte" id="CCP" class="form-control">
                        </div>

                    </div>
                </div>

            </div>
        </div>

        <!-- Boleta -->
        <div class="col-md-6 col-xl-4">
            <div class="card card-flush py-4 h-100">

                <div class="card-header">
                    <div class="card-title">
                        <h2>Boleta de Liberación</h2>
                    </div>
                </div>

                <div class="card-body pt-0">
                    <div class="row g-3">

                        <div class="col-12">
                            <label class="form-label fw-semibold">Folio</label>
                            <input type="text" name="numBoleta" id="numBoleta" class="form-control"
                                placeholder="Ej: BL-123456"
                                @if ($action == 'editar') value="{{ $cotizacion?->DocCotizacion?->num_boleta_liberacion ?? '' }}" @endif>
                        </div>

                        {{-- <div class="col-12">
                            <label class="form-label fw-semibold">Fecha</label>
                            <input type="date" name="fechaBoleta" class="form-control">
                        </div> --}}

                        <div class="col-12">
                            <label class="form-label fw-semibold">Archivo</label>
                            <input type="file" name="fileBoleta" id="BoletaLib" class="form-control">
                        </div>

                    </div>
                </div>

            </div>
        </div>

        <!-- DODA -->
        <div class="col-md-6 col-xl-4">
            <div class="card card-flush py-4 h-100">

                <div class="card-header">
                    <div class="card-title">
                        <h2>DODA</h2>
                    </div>
                </div>

                <div class="card-body pt-0">
                    <div class="row g-3">

                        <div class="col-12">
                            <label class="form-label fw-semibold">Folio</label>
                            <input type="text" name="numDoda" id="numDoda" class="form-control"
                                placeholder="Ej: DODA-123456"
                                @if ($action == 'editar') value="{{ $cotizacion?->DocCotizacion?->num_doda ?? '' }}" @endif>
                        </div>

                        {{-- <div class="col-12">
                            <label class="form-label fw-semibold">Fecha</label>
                            <input type="date" name="fechaDoda" class="form-control">
                        </div> --}}

                        <div class="col-12">
                            <label class="form-label fw-semibold">Archivo</label>
                            <input type="file" name="fileDoda" id="Doda" class="form-control">
                        </div>

                    </div>
                </div>

            </div>
        </div>

    </div>

</div>
{{--   <div class="d-flex flex-stack flex-wrapr">
        <!--begin::Users-->
        <div class="symbol-group symbol-hover my-1"></div>
        <!--end::Users-->
        <!--begin::Stats-->
        <div class="d-flex my-1">
            <!--begin::Stat-->
            <button type="button" name="btnWhatsApp" id="btnWhatsApp" data-bs-toggle="modal"
                data-bs-target="#kt_modal_whatsapp-files"
                class="btn border border-dashed border-gray-300 btn-outline-success rounded d-flex align-items-center py-2 px-3"
                style="cursor: pointer">
                <i class="ki-duotone ki-whatsapp fs-3">
                    <span class="path1"></span>
                    <span class="path2"></span>
                </i>
                <span class="ms-1 fs-7 fw-bold text-gray-600">WhatsApp</span>
            </button>
            <!--end::Stat-->
            <!--begin::Stat-->
            <a href="#" class="border border-dashed border-gray-300 rounded d-flex align-items-center py-2 px-3"
                style="cursor: pointer">
                <i class="ki-duotone ki-paper-clip fs-3"></i>
                <span class="ms-1 fs-7 fw-bold text-gray-600">Más documentos</span>
            </a>
            <!--end::Stat-->
        </div>
        <!--end::Stats-->
    </div> --}}
