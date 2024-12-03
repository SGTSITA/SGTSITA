<div class="modal fade" id="kt_modal_fileuploader" tabindex="-1" aria-hidden="true">
    <!--begin::Modal dialog-->
    <div class="modal-dialog modal-dialog-centered mw-800px">
        <!--begin::Modal content-->
        <div class="modal-content">
            <!--begin::Modal header-->
            <div class="modal-header pb-0 border-0 justify-content-end">
                
                <!--begin::Close-->
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
                <!--end::Close-->
            </div>
            <!--begin::Modal header-->

            <!--begin::Modal body-->
            <div class="modal-body scroll-y pt-0 pb-15">
                <!--begin::Wrapper-->
                <div class="mw-lg-600px mx-auto">
                    <!--begin::Heading-->
                    <div class="mb-13 text-center">
                        
                        <div class="text-muted fw-semibold fs-5">
                       Cargar documentos:
                     

                        <!--begin::Title-->
                        <h1 class="mb-3" id="titleFileUploader"></h1>
                        <!--end::Title-->
                    </div>
                    </div>
                    <!--end::Heading-->

                    
                   <div class="scroll-y me-n7 pe-7 mt-5" id="kt_catalog_clients" data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_new_address_header" data-kt-scroll-wrappers="#kt_modal_new_address_scroll" data-kt-scroll-offset="300px" style="max-height: 447px;">
                        <input type="file" name="files">
                        <div id="proveedorResult" class="d-flex flex-stack flex-grow-1 "></div>
                 
                    </div>

                </div>
                <!--end::Wrapper-->               
            </div>
            <!--end::Modal body-->
        </div>
        <!--end::Modal content-->
    </div>
    <!--end::Modal dialog-->
</div>