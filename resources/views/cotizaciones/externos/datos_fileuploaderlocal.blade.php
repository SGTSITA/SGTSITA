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
                        <button type="button" id="btnContinuar" class="btn btn-link text-primary fw-bold">
                            Aceptar y Continuar
                        </button>
                    </div>
                </div>
                <!--end::Content-->
            </div>
            <!--end::Wrapper-->
        </div>
    </div>
</div>

<div class="d-none" id="fileUploaderContainer">
    <div class="d-flex flex-column flex-xl-row gap-7 gap-lg-10">
        <!--begin::Order details-->

        <div class="card card-flush py-4 flex-row-fluid">
            <!--begin::Card header-->
            <div class="card-header">
                <div class="card-title">
                    <h2>Boleta patio</h2>
                </div>
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">
                @if ($action == 'editar')
                @endif
                <input type="file" name="files" id="BoletaPatio">
            </div>
            <!--end::Card body-->
        </div>

        <div class="card card-flush py-4 flex-row-fluid">
            <!--begin::Card header-->
            <div class="card-header">
                <div class="card-title">
                    <h2>Boleta liberación</h2>
                </div>
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
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
            {{--  <div class="card-body pt-0">
                @if ($action == 'editar')
                @endif
                <input type="file" name="files" id="BoletaLib">
            </div> --}}
            <!--end::Card body-->
        </div>

        <div class="card card-flush py-4 flex-row-fluid">
            <!--begin::Card header-->
            <div class="card-header">
                <div class="card-title">
                    <h2>Doda</h2>
                </div>
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            {{-- <div class="card-body pt-0">
                @if ($action == 'editar')
                @endif
                <input type="file" name="files" id="Doda">
            </div> --}}
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
            <!--end::Card body-->
        </div>
    </div>
</div>
