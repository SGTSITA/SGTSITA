@extends('layouts.usuario_externo')

@section('WorkSpace')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Listado de Contactos</h3>
            <div class="card-toolbar">
                <a href="{{ route('contactos.create') }}" class="btn btn-sm btn-primary">Agregar Contacto</a>
            </div>
        </div>
        <div class="card-body">
            <div id="myGrid" style="height: 500px;"></div>
        </div>
    </div>
@endsection

@push('javascript')
    <script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>
    <script src="{{ asset('js/sgt/contactos/contactos.js') }}"></script>
@endpush
