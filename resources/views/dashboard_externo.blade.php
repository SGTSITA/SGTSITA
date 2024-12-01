@extends('layouts.usuario_externo')

@section('WorkSpace')
<div class="row gx-5 gx-xl-10">
  <div class="col-sm-12 mb-5 mb-xl-10">
    <div class="card card-flush h-lg-100">
      <div class="card-header ">
        <h3 class="card-title align-items-start flex-column">
          <span class="card-label fw-bold text-gray-900">Servicio de transporte</span>
          <span class="text-gray-500 mt-1 fw-semibold fs-6">Solicitud de servicio de gestion de transporte</span>
        </h3>
        <div class="card-toolbar">
          <button class="btn btn-icon btn-color-gray-500 btn-active-color-primary justify-content-end" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end" data-kt-menu-overflow="true">
            <i class="ki-duotone ki-dots-square fs-1">
              <span class="path1"></span>
              <span class="path2"></span>
              <span class="path3"></span>
              <span class="path4"></span>
            </i>
          </button>
          <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg-light-primary fw-semibold w-200px" data-kt-menu="true">
            <!--begin::Menu item-->
            <div class="menu-item px-3">
              <div class="menu-content fs-6 text-gray-900 fw-bold px-3 py-4">Opciones</div>
            </div>
            <!--end::Menu item-->
            <!--begin::Menu separator-->
            <div class="separator mb-3 opacity-75"></div>
            <!--end::Menu separator-->
            <!--begin::Menu item-->
            <div class="menu-item px-3 mb-3">
              <a href="#" class="menu-link px-3"> Vista previa </a>
            </div>
            <!--end::Menu item-->
          </div>
        </div>
      </div>
      <div class="card-body">
        <div class="d-flex flex-column flex-md-row p-1">
          <ul class="nav nav-tabs nav-pills border-0 flex-row flex-md-column me-5 mb-3 mb-md-0 fs-6" role="tablist">
            <li class="nav-item w-100 me-0 mb-md-2">
              <a class="nav-link w-100 active btn btn-flex btn-color-muted btn-active-primary border border-active-primary border-dashed border-gray-300 rounded" data-bs-toggle="tab" href="#kt_vtab_pane_1">
   
                <span class="d-flex flex-column align-items-start">
                  <span class="fs-4 fw-bold">Datos Generales</span>
                  <span class="fs-7">Información del contenedor</span>
                </span>
              </a>
            </li>
            <li class="nav-item w-100 me-0 mb-md-2">
              <a class="nav-link w-100 btn btn-flex btn-color-muted btn-active-primary border border-active-primary border-dashed border-gray-300 rounded" data-bs-toggle="tab" href="#kt_vtab_pane_2">
             
                <span class="d-flex flex-column align-items-start">
                  <span class="fs-4 fw-bold">Bloque</span>
                  <span class="fs-7">Datos bloque de entrada</span>
                </span>
              </a>
            </li>
            <li class="nav-item w-100 me-0 mb-md-2">
              <a class="nav-link w-100 btn btn-flex btn-color-muted btn-active-primary border border-active-primary border-dashed border-gray-300 rounded" data-bs-toggle="tab" href="#kt_vtab_pane_3">
               
                <span class="d-flex flex-column align-items-start">
                  <span class="fs-4 fw-bold">Documentos</span>
                  <span class="fs-7 text-start">Información y carga de documentos</span>
                </span>
              </a>
            </li>
          </ul>

          <div class="tab-content d-flex flex-column h-100" id="myTabContent">
            <div class="tab-pane fade show active h-100" id="kt_vtab_pane_1" role="tabpanel">
              @include('cotizaciones.externos.datos_generales')
            </div>
            <div class="tab-pane fade h-100" id="kt_vtab_pane_2" role="tabpanel"> 
              @include('cotizaciones.externos.datos_bloque')
            </div>
            <div class="tab-pane fade d-flex flex-column h-100" id="kt_vtab_pane_3" role="tabpanel"> 
            @include('cotizaciones.externos.datos_documentos')
            </div>
          </div>

        </div>
      </div>
      <div class="card-footer text-end">
      <div class="separator separator-dashed mb-8"></div>
        <button class="btn btn-primary">Solicitar servicio</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('')
<script>
  $(document).ready(()=>{
    
  })
</script>
@endpush