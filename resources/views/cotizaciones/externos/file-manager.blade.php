@extends('layouts.usuario_externo')

@section('WorkSpace')

<div class="card">
    <div class="card-body">

<!--begin::Wrapper-->
<div class="d-flex flex-stack mb-5">
    <!--begin::Search-->
    <div class="d-flex align-items-center position-relative my-1">
        <i class="ki-duotone ki-magnifier fs-1 position-absolute ms-6"><span class="path1"></span><span class="path2"></span></i>
        <input type="text" data-kt-docs-table-filter="search" class="form-control form-control-solid w-250px ps-15" placeholder="Buscar archivo"/>
    </div>
    <!--end::Search-->

    <!--begin::Toolbar-->
    <div class="d-flex justify-content-end" data-kt-docs-table-toolbar="base">
        <!--begin::Filter-->
        <button type="button" class="btn btn-sm btn-light-primary me-3" data-bs-toggle="tooltip" title="Coming Soon">
            <i class="ki-duotone ki-filter fs-2"><span class="path1"></span><span class="path2"></span></i>
            Filter
        </button>
        <!--end::Filter-->

        <!--begin::Add customer-->
        <button type="button" class="btn btn-sm btn-primary" >
        <i class="ki-duotone ki-folder-up fs-2"><span class="path1"></span><span class="path2"></span></i>
            Agregar documento
        </button>
        <!--end::Add customer-->
    </div>
    <!--end::Toolbar-->

    <!--begin::Group actions-->
    <div class="d-flex justify-content-end align-items-center d-none" data-kt-docs-table-toolbar="selected">
        <div class="fw-bold me-5">
            <span class="me-2" data-kt-docs-table-select="selected_count"></span> Selected
        </div>

        <button type="button" class="btn btn-sm btn-danger" data-kt-docs-table-select="delete_selected">
            Eliminar selección
        </button>
    </div>
    <!--end::Group actions-->
</div>
<!--end::Wrapper-->

<!--begin::Datatable-->
<table id="kt_datatable_example_1" class="table align-middle table-row-dashed fs-6 gy-5">
    <thead>
    <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
        <th class="w-10px pe-2">
            <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                <input class="form-check-input" type="checkbox" data-kt-check="true" data-kt-check-target="#kt_datatable_example_1 .form-check-input" value="1"/>
            </div>
        </th>
        <th>Archivo</th>
        <th>Tipo</th>
        <th>Tamaño</th>
        <th>Fecha documento</th>
        <th>Created Date</th>
        <th class="text-end min-w-100px">Actions</th>
    </tr>
    </thead>
    <tbody class="text-gray-600 fw-semibold">
    </tbody>
</table>
<!--end::Datatable-->
    </div>
</div>

@endsection

@push('javascript')
<script src="{{ asset('js/sgt/cotizaciones/file-manager.js') }}?v={{ filemtime(public_path('js/sgt/cotizaciones/file-manager.js')) }}"></script>

@endpush