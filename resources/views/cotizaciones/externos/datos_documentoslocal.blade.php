<div class="flex-grow-1">
<div class="fv-row row mb-3">
      <div class="col-12">
        <!--begin::Form-->
        <form class="form" action="#" method="post">
            <!--begin::Input group-->
            <div class="fv-row" data-bs-toggle="modal" data-bs-target="#kt_modal_fileuploader" id="fileUploadBoletaPatio">
                <!--begin::Dropzone-->
                <div class="dropzone" id="kt_dropzonejs_example_1">
                    <!--begin::Message-->
                    <div class="dz-message needsclick">
                    <i class="ki-duotone ki-file-up fs-3x text-primary"><span class="path1"></span><span class="path2"></span></i>

                        <!--begin::Info-->
                        <div class="ms-4">
                            <h3 class="fs-5 fw-bold text-gray-900 mb-1">¿Cuenta con Boleta de Patio?</h3>
                            <span class="fs-7 fw-semibold text-gray-500">Haga Click aquí para subir su archivo boleta de patio</span>
                        </div>
                        <!--end::Info-->

                    </div>
                </div>
                <!--end::Dropzone-->
            </div>
            <!--end::Input group-->
        </form>
        <!--end::Form-->
      </div>
   </div>
  <div class="mb-2"></div>
  <div class="fv-row row mb-3">
    <div class="col-xxl-4" data-select2-id="select2-data-120-0yyr">
      <div class="card  min-vh-75 mb-5 mb-xxl-10" data-select2-id="select2-data-119-c065">
      <div class="card-header">
          <div class="card-title align-items-start flex-column">
            <h4 class="text-gray-800 mb-0">Boleta de liberación</h4>
          </div>
          <div class="card-toolbar">
            <button type="button" id="btnFileBoletaLiberacion" class="btn btn-primary btn-icon flex-shrink-0" data-bs-toggle="modal" data-bs-target="#kt_modal_fileuploader">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path opacity="0.3" d="M5 16C3.3 16 2 14.7 2 13C2 11.3 3.3 10 5 10H5.1C5 9.7 5 9.3 5 9C5 6.2 7.2 4 10 4C11.9 4 13.5 5 14.3 6.5C14.8 6.2 15.4 6 16 6C17.7 6 19 7.3 19 9C19 9.4 18.9 9.7 18.8 10C18.9 10 18.9 10 19 10C20.7 10 22 11.3 22 13C22 14.7 20.7 16 19 16H5ZM8 13.6H16L12.7 10.3C12.3 9.89999 11.7 9.89999 11.3 10.3L8 13.6Z" fill="currentColor" />
                <path d="M11 13.6V19C11 19.6 11.4 20 12 20C12.6 20 13 19.6 13 19V13.6H11Z" fill="currentColor" />
              </svg>
            </button>
          </div>
        </div>
        <div class="card-body">
          <span class="fs-7 fw-semibold text-gray-600 pb-6 d-block">Documento por el cual se autoriza el retiro de las mercancías importadas o exportadas</span>
          <label class="form-label">Núm Boleta liberación</label>
          <div class="input-group mb-5">
            <input type="text" class="form-control form-control-sm" placeholder="Núm Boleta liberación" aria-label="Username" aria-describedby="basic-addon1" />
            <span class="input-group-text btn btn-primary btn-sm" id="basic-addon1">
              <i class="ki-duotone ki-check fs-1">
                <span class="path1"></span>
                <span class="path2"></span>
                <span class="path3"></span>
              </i>
            </span>
          </div>
     
        </div>
      </div>
    </div>
    <div class="col-xxl-4" data-select2-id="select2-data-120-0yyr">
      <!--begin::Invoices-->
      <div class="card  card-xxl-stretch mb-5 mb-xxl-10" data-select2-id="select2-data-119-c065">
        <!--begin::Header-->
        <div class="card-header">
          <div class="card-title align-items-start flex-column">
            <h4 class="text-gray-800 mb-0">DODA</h4>
          </div>
          <div class="card-toolbar">
            <button type="button" id="btnFileDODA" class="btn btn-primary btn-icon flex-shrink-0" data-bs-toggle="modal" data-bs-target="#kt_modal_fileuploader">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path opacity="0.3" d="M5 16C3.3 16 2 14.7 2 13C2 11.3 3.3 10 5 10H5.1C5 9.7 5 9.3 5 9C5 6.2 7.2 4 10 4C11.9 4 13.5 5 14.3 6.5C14.8 6.2 15.4 6 16 6C17.7 6 19 7.3 19 9C19 9.4 18.9 9.7 18.8 10C18.9 10 18.9 10 19 10C20.7 10 22 11.3 22 13C22 14.7 20.7 16 19 16H5ZM8 13.6H16L12.7 10.3C12.3 9.89999 11.7 9.89999 11.3 10.3L8 13.6Z" fill="currentColor" />
                <path d="M11 13.6V19C11 19.6 11.4 20 12 20C12.6 20 13 19.6 13 19V13.6H11Z" fill="currentColor" />
              </svg>
            </button>
          </div>
        </div>
        <!--end::Header-->
        <!--begin::Body-->
        <div class="card-body">
          <span class="fs-7 fw-semibold text-gray-600 pb-6 d-block">Documento generado para simplificar los trámites aduaneros y agilizar el despacho de mercancías</span>
          <!--begin::Left Section-->
          <label class="form-label">Núm DODA</label>
          <div class="input-group mb-5">
            <input type="text" class="form-control form-control-sm" placeholder="Número DODA" aria-label="Username" aria-describedby="basic-addon1" />
            <span class="input-group-text btn btn-primary btn-sm" id="basic-addon1">
              <i class="ki-duotone ki-check fs-1">
                <span class="path1"></span>
                <span class="path2"></span>
                <span class="path3"></span>
              </i>
            </span>
          </div>
          <!--end::Left Section-->
        </div>
        <!--end::Body-->
      </div>
      <!--end::Invoices-->
    </div>
    <div class="col-xxl-4">
      <!--begin::Invoices-->
      <div class="card   mb-5 mb-xxl-10">
        <!--begin::Header-->
        <div class="card-header">
          <div class="card-title align-items-start flex-column">
            <h4 class="text-gray-800 mb-0">Boleta Patio</h4>
          </div>
          <div class="card-toolbar">
            <button type="button" id="btnBoletaPatio" class="btn btn-primary btn-icon flex-shrink-0" data-bs-toggle="modal" data-bs-target="#kt_modal_fileuploader">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path opacity="0.3" d="M5 16C3.3 16 2 14.7 2 13C2 11.3 3.3 10 5 10H5.1C5 9.7 5 9.3 5 9C5 6.2 7.2 4 10 4C11.9 4 13.5 5 14.3 6.5C14.8 6.2 15.4 6 16 6C17.7 6 19 7.3 19 9C19 9.4 18.9 9.7 18.8 10C18.9 10 18.9 10 19 10C20.7 10 22 11.3 22 13C22 14.7 20.7 16 19 16H5ZM8 13.6H16L12.7 10.3C12.3 9.89999 11.7 9.89999 11.3 10.3L8 13.6Z" fill="currentColor" />
                <path d="M11 13.6V19C11 19.6 11.4 20 12 20C12.6 20 13 19.6 13 19V13.6H11Z" fill="currentColor" />
              </svg>
            </button>
          </div>
        </div>
        <!--end::Header-->
        <!--begin::Body-->
        <div class="card-body">
          <span class="fs-8 fw-semibold text-gray-600 pb-6 d-block">Documento fiscal que acompaña a las mercancías que se transportan por vías federales para acreditar la legitimidad de los bienes</span>
          <!--begin::Left Section-->
          <label class="form-label">Núm Carta Porte</label>
          <div class="input-group mb-5">
            <input type="text" class="form-control form-control-sm" placeholder="Núm carta porte" aria-label="Username" aria-describedby="basic-addon1" />
            <span class="input-group-text btn btn-primary btn-sm" id="basic-addon1">
              <i class="ki-duotone ki-check fs-1">
                <span class="path1"></span>
                <span class="path2"></span>
                <span class="path3"></span>
              </i>
            </span>
          </div>
          <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed  p-6">
            <!--begin::Icon-->
            <i class="ki-duotone ki-shield-tick fs-2tx text-primary me-4">
              <span class="path1"></span>
              <span class="path2"></span>
            </i>
            <!--end::Icon-->
            <!--begin::Wrapper-->
            <div class="d-flex flex-stack flex-grow-1 flex-wrap flex-md-nowrap">
              <!--begin::Content-->
              <div class="mb-3 mb-md-0 fw-semibold">
                <h4 class="fs-6 text-gray-900 fw-bold">Documento Cargado Exitosamente</h4>
                <div class="fs-7 text-gray-700 pe-7">El archivó se cargó correctamente en el sistema. Para visualizar haga click <a href="#" class="fw-bold">Aquí</a>
                </div>
              </div>
              <!--end::Content-->
              <!--begin::Action-->
              <!--end::Action-->
            </div>
            <!--end::Wrapper-->
          </div>
          <!--end::Left Section-->
        </div>
        <!--end::Body-->
      </div>
      <!--end::Invoices-->
    </div>
  </div>
</div>