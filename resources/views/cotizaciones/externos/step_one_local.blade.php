@extends('layouts.usuario_externo')

@section('WorkSpace')
<div class="card">
  <div class="card-header ">
    <h3 class="card-title align-items-start flex-column">
      <span class="card-label fw-bold text-gray-900">Seleccione una opción</span>
      <span class="text-gray-500 mt-1 fw-semibold fs-6">Solicitud de servicio de gestion de transporte</span>
    </h3>
  </div>
  <div class="card-body">
    <div class="d-flex flex-column flex-lg-row-fluid">
      <div class="d-flex flex-center flex-column flex-column-fluid">
        <div class="w-lg-650px w-xl-700px mx-auto">
          <form method="post" action="{{route('viajes.selectorlocal')}}" class="my-auto pb-5 fv-plugins-bootstrap5 fv-plugins-framework" novalidate="novalidate" id="kt_create_account_form">
            @csrf
            <div class="current" data-kt-stepper-element="content">
              <div class="w-100">
                <div class="fv-row">
                  <div class="row">
                    <div class="col-lg-6">
                      <input type="radio" class="btn-check" name="transac" value="simple" checked="checked" id="kt_create_account_form_transac_personal">
                      <label class="btn btn-outline btn-outline-dashed btn-active-light-primary p-7 d-flex align-items-center mb-10" for="kt_create_account_form_transac_personal">
                        <span class="svg-icon svg-icon-muted svg-icon-2hx">
                          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path opacity="0.3" d="M19 22H5C4.4 22 4 21.6 4 21V3C4 2.4 4.4 2 5 2H14L20 8V21C20 21.6 19.6 22 19 22ZM12.5 18C12.5 17.4 12.6 17.5 12 17.5H8.5C7.9 17.5 8 17.4 8 18C8 18.6 7.9 18.5 8.5 18.5L12 18C12.6 18 12.5 18.6 12.5 18ZM16.5 13C16.5 12.4 16.6 12.5 16 12.5H8.5C7.9 12.5 8 12.4 8 13C8 13.6 7.9 13.5 8.5 13.5H15.5C16.1 13.5 16.5 13.6 16.5 13ZM12.5 8C12.5 7.4 12.6 7.5 12 7.5H8C7.4 7.5 7.5 7.4 7.5 8C7.5 8.6 7.4 8.5 8 8.5H12C12.6 8.5 12.5 8.6 12.5 8Z" fill="currentColor"></path>
                            <rect x="7" y="17" width="6" height="2" rx="1" fill="currentColor"></rect>
                            <rect x="7" y="12" width="10" height="2" rx="1" fill="currentColor"></rect>
                            <rect x="7" y="7" width="6" height="2" rx="1" fill="currentColor"></rect>
                            <path d="M15 8H20L14 2V7C14 7.6 14.4 8 15 8Z" fill="currentColor"></path>
                          </svg>
                        </span>
                        <span class="d-block fw-semibold text-start">
                          <span class="text-dark fw-bold d-block fs-4 mb-2"> Solicitud local </span>
                          <span class="text-muted fw-semibold fs-6">Está es la opción adecuada si desea solicitar un solo viaje</span>
                        </span>
                      </label>
                    </div>
                    <div class="col-lg-6">
                      <input type="radio" class="btn-check" name="transac" value="multiple" id="kt_create_account_form_transac_TAE">
                      <label class="btn btn-outline btn-outline-dashed btn-active-light-primary p-7 d-flex align-items-center mb-10" for="kt_create_account_form_transac_TAE">
                        <span class="svg-icon svg-icon-muted svg-icon-2hx">
                          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path opacity="0.3" d="M19 22H5C4.4 22 4 21.6 4 21V3C4 2.4 4.4 2 5 2H14L20 8V21C20 21.6 19.6 22 19 22ZM12.5 18C12.5 17.4 12.6 17.5 12 17.5H8.5C7.9 17.5 8 17.4 8 18C8 18.6 7.9 18.5 8.5 18.5L12 18C12.6 18 12.5 18.6 12.5 18ZM16.5 13C16.5 12.4 16.6 12.5 16 12.5H8.5C7.9 12.5 8 12.4 8 13C8 13.6 7.9 13.5 8.5 13.5H15.5C16.1 13.5 16.5 13.6 16.5 13ZM12.5 8C12.5 7.4 12.6 7.5 12 7.5H8C7.4 7.5 7.5 7.4 7.5 8C7.5 8.6 7.4 8.5 8 8.5H12C12.6 8.5 12.5 8.6 12.5 8Z" fill="currentColor"></path>
                            <rect x="7" y="17" width="6" height="2" rx="1" fill="currentColor"></rect>
                            <rect x="7" y="12" width="10" height="2" rx="1" fill="currentColor"></rect>
                            <rect x="7" y="7" width="6" height="2" rx="1" fill="currentColor"></rect>
                            <path d="M15 8H20L14 2V7C14 7.6 14.4 8 15 8Z" fill="currentColor"></path>
                          </svg>
                        </span>
                        <span class="d-block fw-semibold text-start">
                          <span class="text-dark fw-bold d-block fs-4 mb-2"> Solicitud Multiple </span>
                          <span class="text-muted fw-semibold fs-6">¿Desea solicitar más de un viaje a la vez? Utilice esta opcioón</span>
                        </span>
                      </label>
                    </div>
                    <div class="offset-3 col-lg-6">
                      <input type="radio" class="btn-check" name="transac" value="documents" id="kt_create_account_form_transac_corporate">
                      <label class="btn btn-outline btn-outline-dashed btn-active-light-primary p-7 d-flex align-items-center" for="kt_create_account_form_transac_corporate">
                        
                          <span class="svg-icon svg-icon-3x me-5">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                              <path opacity="0.3" d="M20 15H4C2.9 15 2 14.1 2 13V7C2 6.4 2.4 6 3 6H21C21.6 6 22 6.4 22 7V13C22 14.1 21.1 15 20 15ZM13 12H11C10.5 12 10 12.4 10 13V16C10 16.5 10.4 17 11 17H13C13.6 17 14 16.6 14 16V13C14 12.4 13.6 12 13 12Z" fill="currentColor"></path>
                              <path d="M14 6V5H10V6H8V5C8 3.9 8.9 3 10 3H14C15.1 3 16 3.9 16 5V6H14ZM20 15H14V16C14 16.6 13.5 17 13 17H11C10.5 17 10 16.6 10 16V15H4C3.6 15 3.3 14.9 3 14.7V18C3 19.1 3.9 20 5 20H19C20.1 20 21 19.1 21 18V14.7C20.7 14.9 20.4 15 20 15Z" fill="currentColor"></path>
                            </svg>
                          </span>
                       
                          <span class="d-block fw-semibold text-start">
                            <span class="text-dark fw-bold d-block fs-4 mb-2">Subir documentación</span>
                            <span class="text-muted fw-semibold fs-6">Seleccione está opción para ver solicitudes con documentación pendiente</span>
                          </span>
                        
                      </label>
                    </div>
                  </div>
                </div>
              </div>
            </div>
        </div>
      </div>
    </div>
  </div>
  <div class="card-footer border-0 text-end">
    <div class="separator separator-dashed mb-8"></div>
    <button type="submit" class="btn btn-lg btn-primary" data-kt-stepper-action="next"> Continuar
      <!--begin::Svg Icon | path: icons/duotune/arrows/arr064.svg-->
      <span class="svg-icon svg-icon-4 ms-1">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <rect opacity="0.5" x="18" y="13" width="13" height="2" rx="1" transform="rotate(-180 18 13)" fill="currentColor"></rect>
          <path d="M15.4343 12.5657L11.25 16.75C10.8358 17.1642 10.8358 17.8358 11.25 18.25C11.6642 18.6642 12.3358 18.6642 12.75 18.25L18.2929 12.7071C18.6834 12.3166 18.6834 11.6834 18.2929 11.2929L12.75 5.75C12.3358 5.33579 11.6642 5.33579 11.25 5.75C10.8358 6.16421 10.8358 6.83579 11.25 7.25L15.4343 11.4343C15.7467 11.7467 15.7467 12.2533 15.4343 12.5657Z" fill="currentColor"></path>
        </svg>
      </span>
    </button>
    </form>
  </div>
</div>
@endsection

@push('javascript')
 <script>


    

 </script>
@endpush