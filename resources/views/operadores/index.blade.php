@extends('layouts.app')

@section('template_title')
    Operadores
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span id="card_title">
                            <i class="fas fa-users me-1"></i>
                            Operadores
                        </span>
                        <div class="float-right">
                            @can('operadores-create')
                                <button
                                    type="button"
                                    class="btn bg-gradient-info btn-xs mb-2"
                                    data-bs-toggle="modal"
                                    data-bs-target="#operadoresModal"
                                >
                                    <i class="fas fa-plus"></i>
                                    Crear
                                </button>
                            @endcan
                        </div>
                    </div>

                    <div class="card-body">
                        <div id="operadoresGrid" class="ag-theme-alpine" style="height: 600px; width: 100%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modales --}}
    @include('operadores.modal_create')
    @foreach ($operadores as $operador)
        @include('operadores.modal_edit')
    @endforeach

    {{-- Formularios ocultos --}}
    <form id="form-eliminar" method="POST" style="display: none">
        @csrf
        @method('DELETE')
    </form>

    <form id="form-restaurar" method="POST" style="display: none">
        @csrf
    </form>
@endsection

@section('datatable')
    {{-- SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- AG Grid --}}
    <script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>

    {{-- Script personalizado para AG Grid --}}
    <script>
        const operadoresData = @json($operadores);
        const pagosPendientes = @json($pagos_pendientes);
    </script>
    <script src="{{ asset('js/sgt/operadores/operadores_list.js') }}"></script>

    @if (Session::has('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: '{{ Session::get('success') }}',
                confirmButtonColor: '#3085d6',
            });
        </script>
    @endif

    @if (session('operador_con_restante'))
        <script>
            Swal.fire({
                icon: 'warning',
                title: 'No se puede dar de baja',
                html: `{!! addslashes(session('operador_con_restante')) !!}`,
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#d33',
            });
        </script>
    @endif
@endsection
