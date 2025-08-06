<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <label class="form-label fw-semibold mb-0">Permisos Personalizados:</label>
    </div>

    <div id="tablaPermisosAGGrid" class="ag-theme-alpine" style="height: 400px; overflow: auto;"></div>
    <input type="hidden" name="custom_permissions_json" id="custom_permissions_json" />
</div>

{{-- Solo se usa para editar --}}
@include('roles.permisos_modal')

{{-- Carga los permisos desde backend --}}
<script>
    window.PERMISOS_EXISTENTES = @json($permission);
</script>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>
    <script src="{{ asset('js/sgt/permisos/permisos_list.js') }}"></script>
@endpush
