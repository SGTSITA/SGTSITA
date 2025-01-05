@extends('layouts.app')

@section('template_title')
Gastos Generales
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <h2 id="card_title">
                                Gastos Generales
                            </h2>

                              @can('gastos-create')
                             <div class="float-right">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal" style="background: {{$configuracion->color_boton_add}}; color: #ffff">
                                    <i class="fa fa-fw fa-plus"></i>  Crear
                                </button>
                              </div>
                              @endcan

                        </div>
                    </div>

                    <div class="card-body"style=" padding: 0rem 1.5rem 1.5rem 1.5rem;">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover table_id" id="datatable-search">
                                <thead class="thead">
                                    <tr>
                                        <th>No</th>
										<th>Motivo</th>
										<th>Monto</th>
										<th>Metodo</th>
                                        <th>Banco</th>
                                        <th>Fecha</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($gastos as $gasto)
                                        <tr>
                                            <td>{{ $gasto->id }}</td>

											<td>{{ $gasto->motivo }}</td>
											<td>
                                               <b> ${{ number_format($gasto->monto1, 2, '.', ',') }} </b>
                                            </td>
											<td>{{ $gasto->metodo_pago1 }}</td>
                                            <td>{{ $gasto->Banco1->nombre_banco }}</td>
                                            <td>{{ $gasto->fecha }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@include('gastos_generales.create')
@endsection

@section('datatable')

<script>
    const dataTableSearch = new simpleDatatables.DataTable("#datatable-search", {
      searchable: true,
      fixedHeight: false
    });

</script>

@endsection
