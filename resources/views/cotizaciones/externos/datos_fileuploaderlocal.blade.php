<div class="row mt-10" id="noticeFileUploader">
  <div class="col-6 offset-3">
    <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed mb-9 p-6">
      <!--begin::Icon-->
      <i class="ki-duotone ki-design-1 fs-2tx text-primary me-4"></i>
      <!--end::Icon-->
      <!--begin::Wrapper-->
      <div class="d-flex flex-stack flex-grow-1 ">
        <!--begin::Content-->
        <div class=" fw-semibold">
          <div class="fs-6 text-gray-700 ">Para adjuntar archivos, primero debe guardar la información de la solicitud de viaje. Esto garantiza que los documentos se asocien correctamente al registro y que el proceso continúe de forma segura y ordenada. <br>
            <br>
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
    <div class="d-flex flex-column flex-xl-row gap-7 gap-lg-10" >
    <!--begin::Order details-->
    {{-- <div class="card card-flush py-4 flex-row-fluid">
        <!--begin::Card header-->
        <div class="card-header">
        <div class="card-title">
            <h2>Boleta patio</h2>
        </div>
        </div>
        <!--end::Card header-->
        <!--begin::Card body-->
        <div class="card-body pt-0">
        @if($action=="editar")  @endif
        <input type="file" name="files" id="BoletaPatio" >
        </div>
        <!--end::Card body-->
    </div> --}}
    <!--end::Order details-->
    <!--begin::Customer details-->
    <div class="card card-flush py-4  flex-row-fluid">
        <!--begin::Card header-->
        <div class="card-header">
        <div class="card-title">
            <h2>Boleta de Liberación</h2>
        </div>
        </div>
        <!--end::Card header-->
        <!--begin::Card body-->
        <div class="card-body pt-0">
        <input type="file" name="files" id="BoletaLib">
        </div>
        <!--end::Card body-->
    </div>
    <!--end::Customer details-->
    <!--begin::Documents-->
    <div class="card card-flush py-4  flex-row-fluid">
        <!--begin::Card header-->
        <div class="card-header">
        <div class="card-title">
            <h2>Doda</h2>
        </div>
        </div>
        <!--end::Card header-->
        <!--begin::Card body-->
        <div class="card-body pt-0">
        <input type="file" name="files" id="Doda">
        </div>
        <!--end::Card body-->
    </div>
    <!--end::Documents-->
    </div>
    <div class="d-flex flex-stack flex-wrapr">
    <!--begin::Users-->
    <div class="symbol-group symbol-hover my-1"></div>
    <!--end::Users-->
    <!--begin::Stats-->
    <div class="d-flex my-1">
        <!--begin::Stat-->
        <button type="button" name="btnWhatsApp" id="btnWhatsApp" data-bs-toggle="modal" data-bs-target="#kt_modal_whatsapp-files" class="btn border border-dashed border-gray-300 btn-outline-success rounded d-flex align-items-center py-2 px-3" style="cursor:pointer">
        <i class="ki-duotone ki-whatsapp fs-3"><span class="path1"></span><span class="path2"></span></i>
        <span class="ms-1 fs-7 fw-bold text-gray-600">WhatsApp</span>
        </button>
        <!--end::Stat-->
        <!--begin::Stat-->
        <a href="#" class="border border-dashed border-gray-300 rounded d-flex align-items-center py-2 px-3" style="cursor:pointer">
        <i class="ki-duotone ki-paper-clip fs-3"></i>
        <span class="ms-1 fs-7 fw-bold text-gray-600">Más documentos</span>
        </a>
        <!--end::Stat-->
    </div>
    <!--end::Stats-->
    </div>
</div>
