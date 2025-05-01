<div class="modal fade" id="modal-enviar-correo" tabindex="-1" role="dialog" aria-labelledby="modal-form" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-body p-0">
        <div class="card">
          <div class="card-header d-flex align-items-center justify-content-between py-3">
            <h2 class="card-title m-0"> Escribir mensaje </h2>
            <!--begin::Toggle-->
            <a href="#" class="btn btn-sm btn-icon btn-color-primary btn-light btn-active-light-primary d-lg-none" data-bs-toggle="tooltip" data-bs-dismiss="click" data-bs-placement="top" title="Toggle inbox menu" id="kt_inbox_aside_toggle">
              <i class="ki-duotone ki-burger-menu-2 fs-3 m-0">
                <span class="path1"></span>
                <span class="path2"></span>
                <span class="path3"></span>
                <span class="path4"></span>
                <span class="path5"></span>
                <span class="path6"></span>
                <span class="path7"></span>
                <span class="path8"></span>
                <span class="path9"></span>
                <span class="path10"></span>
              </i>
            </a>
            <!--end::Toggle-->
          </div>
          <div class="card-body p-0">
            <!--begin::Form-->
            <div id="kt_inbox_compose_form">
              <!--begin::Body-->
              <div class="d-block">
                <!--begin::To-->
                <div class="d-flex align-items-center border-bottom px-8 min-h-50px">
                  <!--begin::Label-->
                  <div class="text-gray-900 fw-bold w-75px"> Para: </div>
                  <!--end::Label-->
                  <!--begin::Input-->
                  <input type="text" class="form-control form-control-transparent border-0" name="compose_to" id="compose_to" />
                  <!--end::Input-->
                  <!--begin::CC & BCC buttons-->
                  <div class="ms-auto w-75px text-end">
                    <span class="text-muted fs-bold cursor-pointer text-hover-primary me-2" data-kt-inbox-form="cc_button">Cc</span>
                    <span class="d-none text-muted fs-bold cursor-pointer text-hover-primary" data-kt-inbox-form="bcc_button">Bcc</span>
                  </div>
                  <!--end::CC & BCC buttons-->
                </div>
                <!--end::To-->
                <!--begin::CC-->
                <div class="d-none align-items-center border-bottom ps-8 pe-5 min-h-50px" data-kt-inbox-form="cc">
                  <!--begin::Label-->
                  <div class="text-gray-900 fw-bold w-75px"> Cc: </div>
                  <!--end::Label-->
                  <!--begin::Input-->
                  <input type="text" class="form-control form-control-transparent border-0" name="compose_cc" id="compose_cc" />
                  <!--end::Input-->
                  <!--begin::Close-->
                  <span class="btn btn-clean btn-xs btn-icon" data-kt-inbox-form="cc_close">
                    <i class="ki-duotone ki-cross fs-5">
                      <span class="path1"></span>
                      <span class="path2"></span>
                    </i>
                  </span>
                  <!--end::Close-->
                </div>
                <!--end::CC-->
                <!--begin::BCC-->
                <div class="d-none align-items-center border-bottom inbox-to-bcc ps-8 pe-5 min-h-50px" data-kt-inbox-form="bcc">
                  <!--begin::Label-->
                  <div class="text-gray-900 fw-bold w-75px"> Bcc: </div>
                  <!--end::Label-->
                  <!--begin::Input-->
                  <input type="text" class="form-control form-control-transparent border-0" name="compose_bcc" />
                  <!--end::Input-->
                  <!--begin::Close-->
                  <span class="btn btn-clean btn-xs btn-icon" data-kt-inbox-form="bcc_close">
                    <i class="ki-duotone ki-cross fs-5">
                      <span class="path1"></span>
                      <span class="path2"></span>
                    </i>
                  </span>
                  <!--end::Close-->
                </div>
                <!--end::BCC-->
                <!--begin::Subject-->
                <div class="border-bottom">
                  <input class="form-control form-control-transparent border-0 px-8 min-h-45px" name="compose_subject" id="compose_subject" placeholder="Asunto" />
                </div>
                <!--end::Subject-->
                <!--begin::Message-->
                <div id="kt_inbox_form_editor" class="bg-transparent border-0 h-350px px-3"></div>
                <!--end::Message-->
                
              </div>
              <!--end::Body-->
              <!--begin::Footer-->
              <div class="d-flex flex-stack flex-wrap gap-2 py-5 ps-8 pe-5 border-top">
                <!--begin::Actions-->
                <div class="d-flex align-items-center me-3">
                  <!--begin::Send-->
                  <div class="btn-group me-4">
                    <!--begin::Submit-->
                    <span class="btn btn-primary fs-bold px-6" data-kt-inbox-form="sendmail">
                      <span class="indicator-label"> Enviar </span>
                      <span class="indicator-progress"> Enviando... <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                      </span>
                    </span>
                    <!--end::Submit-->
                  </div>
                  <!--end::Send-->
                </div>
                <!--end::Actions-->
              </div>
              <!--end::Footer-->
</div>
            <!--end::Form-->
          </div>
        </div>
      </div>
    </div>
  </div>
</div>