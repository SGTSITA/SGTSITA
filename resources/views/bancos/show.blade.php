@extends('layouts.app')

@section('template_title')
    Movimientos Bancarios
@endsection

@section('content')
<div class="d-none">
                <form action="{{ route('advance_bancos.buscador', $banco->id) }}" name="form-buscar" id="form-buscar" method="GET">
                    <div class="card-body" style="padding-left: 1.5rem; padding-top: 1rem;">
                        <h5>Buscador por fechas</h5>
                        <div class="row">
                            <div class="col-4">
                                <label for="user_id">Rango de fecha DE:</label>
                                <input class="form-control" type="date" id="fecha_de" name="fecha_de" required>
                            </div>
                            <div class="col-4">
                                <label for="user_id">Rango de fecha Hasta:</label>
                                <input class="form-control" type="date" id="fecha_hasta" name="fecha_hasta" required>
                            </div>
                            <div class="col-4">
                                <br><br>
                                <button class="btn btn-sm mb-0 mt-sm-0 mt-1" type="submit" style="background-color: #F82018; color: #ffffff;">Buscar</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
<div class="container-fluid my-5 py-2">
    
    <!--div class="row">
        <div class="col-12">
            
        </div-->
        
        <div class="col-12 card">
        <div class=" card-header m-2 ">
              <div class="row">
                <div class="col-7">
                  <h6 class="mb-0">Movimientos Bancarios</h6>
                </div>
                <div class="col-5 d-flex justify-content-end align-items-center">
                @if(Route::currentRouteName() == 'advance_bancos.buscador')
                        <form action="{{ route('pdf.print_banco', $banco->id) }}" method="GET">
                            <input class="form-control" type="hidden" id="fecha_de" name="fecha_de" value="{{$startOfWeek}}">
                            <input class="form-control" type="hidden" id="fecha_hasta" name="fecha_hasta" value="{{$endOfWeek }}">
                            <button class="btn btn-sm mb-0 d-none d-lg-block" type="submit" style="background-color: #F82018; color: #ffffff;">
                                <i class="fas fa-file-pdf text-lg me-1" aria-hidden="true"></i>
                                Reporte
                            </button>
                        </form>
                        @else
                        <form action="{{ route('pdf.print_banco', $banco->id) }}" method="GET">
                            <button class="btn btn-sm mb-0 d-none d-lg-block" type="submit" style="background-color: #F82018; color: #ffffff;">
                                <i class="fas fa-file-pdf text-lg me-1" aria-hidden="true"></i>
                                Reporte
                            </button> 
                        </form>
                    @endif
                   
                </div>
              </div>
            </div>
        <div class=" card-body" >
            <div class="row m-1 justify-content-center align-items-center">
              
              <div class="col-sm-auto col-8 my-auto">
                <div class="h-100">
                  <h5 class="mb-1 font-weight-bolder">
                  {{ucwords(mb_strtolower($banco->nombre_beneficiario, 'UTF-8'))}}
                  </h5>
                  <p class="mb-0 font-weight-bold text-sm">
                  {{$banco->nombre_banco}}
                  </p>
                  <p class="mb-0 font-weight-bold text-sm">
                  Núm Cuenta: {{$banco->cuenta_bancaria}}
                  </p>
                  <p class="mb-0 font-weight-bold text-sm">
                  Clabe: {{$banco->clabe}}
                  </p>
                  <p class="mb-0 font-weight-bold text-sm">
                  Saldo actual: <span class="font-weight-bolder @if ($banco->saldo < 0) text-danger @endif">${{number_format( $banco->saldo,2)}}</span>
                  </p>
                </div>
              </div>
              <div class="col-sm-auto ms-sm-auto mt-sm-0 mt-3 d-flex">
              <div class="border-dashed border-1 border-secondary border-radius-md p-3 ">
                    <div class="font-weight-bolder text-sm"><span class="small">Periodo</span></div>
                    <input type="text" id="daterange" readonly 
                    class="form-control bg-transparent form-control-sm min-w-100" 
                    style="border: none; box-shadow: none;"
                    value="{{ \Carbon\Carbon::parse($startOfWeek)->format('Y-m-d') }} AL {{ \Carbon\Carbon::parse($fecha)->format('Y-m-d') }}"
                    />

                </div>
               
              </div>
            </div>
          </div>
        </div>

      <div class="col-md-12 col-sm-12 mx-auto mt-3">
          <div class="card my-sm-5 my-lg-0">
            <div class="card-header text-center">
            <div class="row">
                <div class="col-lg-8 col-md-6 col-12">
                  <div class="d-flex align-items-center">
                
                    <div class="px-3">
                      <p class="text-body text-sm font-weight-bold mb-3">
                      <span class="text-body text-xs opacity-8">
                        Periodo del 
                      </span>
                      {{ \Carbon\Carbon::parse($startOfWeek)->translatedFormat('j \d\e F') }} al {{ \Carbon\Carbon::parse($fecha)->translatedFormat('j \d\e F') }}
                      </p>
                     
                    </div>
                  </div>
                </div>
          
               
                
              </div>
              
                <div class="row justify-content-md-between">
                    @can('bancos-configuracion')
                        <div class="col-md-4 text-start">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal" style="background-color: #030303; color: #ffffff;">Configuracion</button>
                        </div>
                    @endcan
                    <div class="col-md-12 row">
         <div class="col-lg-3 col-6 text-center">
            <div class="border-dashed border-1 border-secondary border-radius-md py-3">
               <h6 class="text-primary mb-0">Saldo Inicial</h6>
               <h4 class="font-weight-bolder"><span class="small" id="saldo-inicial">${{ number_format($saldoInicial, 2, '.', ',') }} </span></h4>
            </div>
         </div>
         <div class="col-lg-3 col-6 text-center">
            <div class="border-dashed border-1 border-secondary border-radius-md py-3">
               <h6 class="text-primary mb-0">Cobros</h6>
               <h4 class="font-weight-bolder"><span class="small" id="collections">$0.00</span></h4>
            </div>
         </div>
         <div class="col-lg-3 col-6 text-center">
            <div class="border-dashed border-1 border-secondary border-radius-md py-3">
               <h6 class="text-primary mb-0">Pagos</h6>
               <h4 class="font-weight-bolder"><span class="small" id="payment">$0.00</span></h4>
            </div>
         </div>
         <div class="col-lg-3 col-6 text-center">
            <div class="border-dashed border-1 border-secondary border-radius-md py-3 border-success" id="borderBalance">
               <h6 class="text-primary mb-0">Saldo Final</h6>
               <h4 class="font-weight-bolder"><span class="small" id="diferenciaColumna2">$0.00</span></h4>
            </div>
         </div>
         </div>
                    <!--div class="col-lg-3 col-md-7 text-md-end text-start mt-5">
                        <h6 class="d-block mt-2 mb-0">Saldo inicial:</h6>
                        <h6 class="text-secondary" id="saldo-inicial">${{ number_format($saldoInicial, 2, '.', ',') }} </h6>
                    </div>
                    <div class="col-lg-3 col-md-7 text-md-end text-start mt-5">
                        <h6 class="d-block mt-2 mb-0">Total en Banco:</h6>
                        <h6 class="text-secondary">${{ number_format($saldoFinal, 2, '.', ',') }} </h6>
                    </div-->
                </div>
              <br>
              <div class="row justify-content-md-between">
                <div class="col-md-4 mt-auto">
                 
                </div>
                <!--iv class="col-lg-5 col-md-7 mt-auto">
                  <div class="row mt-md-5 mt-4 text-md-end text-start">
                    <div class="col-md-6">
                      <h6 class="text-secondary mb-0">Inicio de Semana:</h6>
                    </div>
                    <div class="col-md-6">
                      <h6 class="text-dark mb-0">{{ \Carbon\Carbon::parse($startOfWeek)->translatedFormat('j \d\e F') }}</h6>
                    </div>
                  </div>
                  <div class="row text-md-end text-start">
                    <div class="col-md-6">
                      <h6 class="text-secondary mb-0">Dia actual:</h6>
                    </div>
                    <div class="col-md-6">
                      <h6 class="text-dark mb-0">{{ \Carbon\Carbon::parse($startOfWeek)->format('Y-m-d') }} AL {{ \Carbon\Carbon::parse($fecha)->format('Y-m-d') }}</h6>
                    </div>
                  </div>
                </div-->
              </div>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-12">
                  <div class="table-responsive border-radius-lg">
                    <table class="table text-right">
                      <thead class="bg-default">
                        <tr>
                          <th scope="col" class="pe-2 text-start ps-2 text-white">Fecha</th>
                          <th scope="col" class="pe-2 text-white">Contenedor</th>
                          <th scope="col" class="pe-2 text-white" colspan="2">Cobros</th>
                          <th scope="col" class="pe-2 text-white">Pagos</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach ($combined as $item)
                            @if(isset($item->fecha_pago))
                                <tr>
                                    <td class="ps-4">{{ \Carbon\Carbon::parse($item->fecha_pago)->translatedFormat('j \d\e F') }}</td>
                                    <td class="text-start">
                                        @if(!isset($item->id_operador))
                                            @if(isset($item->DocCotizacion))
                                                <a href="{{ route('edit.cotizaciones', $item->id) }}">
                                                    {{ $item->DocCotizacion->num_contenedor }} <br>
                                                    <b style="color: #c22237">{{ $item->Cliente->nombre }}</b>
                                                </a>
                                            @elseif(isset($item->Cliente))
                                                {{-- Provisional --}}
                                                @if($item->tipo == 'Salida')
                                                    <a data-bs-toggle="collapse" href="#pagesEntrada{{ $item->id }}" aria-controls="pagesEntrada" role="button" aria-expanded="false">
                                                        Varios <br> <b style="color: #22c2ba">{{ $item->Cliente2->nombre }}</b>
                                                    </a>
                                                    @if ($item->contenedores != null)
                                                        <div class="collapse " id="pagesEntrada{{ $item->id }}">
                                                            Contenedores y Abonos
                                                            <ul>
                                                                @php
                                                                    $contenedoresAbonos = json_decode($item->contenedores, true);
                                                                @endphp
                                                                @foreach ($contenedoresAbonos as $contenedorAbono)
                                                                    <li>{{ $contenedorAbono['num_contenedor'] }} - ${{ number_format($contenedorAbono['abono'], 2, '.', ',') }}</li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    @endif
                                                @else
                                                    <a data-bs-toggle="collapse" href="#pagesEntrada{{ $item->id }}" aria-controls="pagesEntrada" role="button" aria-expanded="false">
                                                        Varios <br> <b>{{ $item->Cliente->nombre }}</b>
                                                    </a>
                                                    @if ($item->contenedores != null)
                                                        <div class="collapse " id="pagesEntrada{{ $item->id }}">
                                                            Contenedores y Abonos
                                                            <ul>
                                                                @php
                                                                    $contenedoresAbonos = json_decode($item->contenedores, true);
                                                                @endphp
                                                                @foreach ($contenedoresAbonos as $contenedorAbono)
                                                                    <li>{{ $contenedorAbono['num_contenedor'] }} - ${{ number_format($contenedorAbono['abono'], 2, '.', ',') }}</li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    @endif
                                                @endif
                                            @elseif(isset($item->Proveedor))
                                                <a data-bs-toggle="collapse" href="#pagesEntrada{{ $item->id }}" aria-controls="pagesEntrada" role="button" aria-expanded="false">
                                                    Varios <br> <b style="color: #22c2ba">{{ $item->Proveedor->nombre }}</b>
                                                </a>
                                                @if ($item->contenedores != null)
                                                    <div class="collapse " id="pagesEntrada{{ $item->id }}">
                                                        Contenedores y Abonos
                                                        <ul>
                                                            @php
                                                                $contenedoresAbonos = json_decode($item->contenedores, true);
                                                            @endphp
                                                            @foreach ($contenedoresAbonos as $contenedorAbono)
                                                                <li>{{ $contenedorAbono['num_contenedor'] }} - ${{ number_format($contenedorAbono['abono'], 2, '.', ',') }}</li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                @endif
                                            @endif
                                        @else
                                            @if(isset($item->contenedores))
                                                <a data-bs-toggle="collapse" href="#pagesOperadores{{ $item->id }}" aria-controls="pagesOperadores" role="button" aria-expanded="false">
                                                    {{$item->descripcion_gasto}}<br> <b style="color: #226dc2">{{ $item->Operador->nombre }}</b>
                                                </a>
                                                <div class="collapse " id="pagesOperadores{{ $item->id }}">
                                                    Contenedores y Abonos
                                                    <ul>
                                                        @php
                                                            $contenedoresAbonos = json_decode($item->contenedores, true);
                                                        @endphp
                                                        @foreach ($contenedoresAbonos as $contenedorAbono)
                                                            <li>{{ $contenedorAbono['num_contenedor'] }} - ${{ number_format($contenedorAbono['abono'], 2, '.', ',') }}</li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @elseif(isset($item->id_cotizacion))
                                                <a  href="{{ route('edit.cotizaciones', $item->id_cotizacion) }}">
                                                    {{ $item->Asignacion->Contenedor->num_contenedor }}
                                                    <br> <b style="color: #226dc2">{{ $item->Operador->nombre }}</b>
                                                </a>
                                            @endif
                                        @endif
                                    </td>
                                    <td class="ps-4 penultima-columna" colspan="2">
                                        @if(!isset($item->id_operador))
                                            @if(!isset($item->tipo))
                                                @if (isset($item->id_banco1) && $item->id_banco1 == $banco->id)
                                                    $ {{ number_format($item->monto1, 0, '.', ',') }}
                                                @else
                                                    $ {{ number_format($item->monto2, 0, '.', ',') }}
                                                @endif
                                            @else
                                                @if ($item->tipo == 'Entrada')
                                                    @if (isset($item->id_banco1) && $item->id_banco1 == $banco->id)
                                                        $ {{ number_format($item->monto1, 0, '.', ',') }}
                                                    @else
                                                        $ {{ number_format($item->monto2, 0, '.', ',') }}
                                                    @endif
                                                @endif
                                            @endif
                                        @endif
                                    </td>
                                    <td class="ps-4 ultima-columna">
                                        @if(isset($item->id_operador))
                                            @if (isset($item->id_banco1) && $item->id_banco1 == $banco->id)
                                                $ {{ number_format($item->monto1, 0, '.', ',') }}
                                            @else
                                                $ {{ number_format($item->monto2, 0, '.', ',') }}
                                            @endif
                                        @else
                                            @if ($item->tipo == 'Salida')
                                                @if (isset($item->id_banco1) && $item->id_banco1 == $banco->id)
                                                    $ {{ number_format($item->monto1, 0, '.', ',') }}
                                                @else
                                                    $ {{ number_format($item->monto2, 0, '.', ',') }}
                                                @endif
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @elseif(isset($item->fecha_pago_proveedor))
                                <tr>
                                    <td class="ps-4">{{ \Carbon\Carbon::parse($item->fecha_pago_proveedor)->translatedFormat('j \d\e F') }}</td>
                                    <td class="text-start">
                                        <a href="{{ route('edit.cotizaciones', $item->id) }}">
                                            {{ $item->DocCotizacion->num_contenedor }} <br>
                                            <b style="color: #22c2ba">{{ $item->DocCotizacion->Asignaciones->Proveedor->nombre }}</b>
                                        </a>
                                    </td>
                                    <td class="ps-4" colspan="2"></td>
                                    <td class="ps-4 ultima-columna">
                                        @if (isset($item->id_prove_banco1) && $item->id_prove_banco1 == $banco->id)
                                            $ {{ number_format($item->prove_monto1, 0, '.', ',') }}
                                        @else
                                            $ {{ number_format($item->prove_monto2, 0, '.', ',') }}
                                        @endif
                                    </td>
                                </tr>
                            @elseif(isset($item->fecha))
                                <tr>
                                    <td class="ps-4">{{ \Carbon\Carbon::parse($item->fecha)->translatedFormat('j \d\e F') }}</td>
                                    <td class="text-start"><b style="color: #c24f22">{{ $item->motivo }}</b></td>
                                    <td class="ps-4" colspan="2"></td>
                                    <td class="ps-4 ultima-columna"> $ {{ number_format($item->monto1, 0, '.', ',') }}</td>
                                </tr>
                            @endif
                        @endforeach
                      </tbody>
                      <tfoot>
                        <tr>
                          <th></th>
                          <th class="h5 ps-4" colspan="2">SubTotal</th>
                          <td id="totalPenultimaColumna" colspan="1" class="text-right h5 ps-4"></td>
                          <td id="totalUltimaColumna" colspan="1" class="text-right h5 ps-4"></td>
                        </tr>
                        <tr>
                            <th></th>
                            <th></th>
                            <th class="h5 ps-4" colspan="2">Total</th>
                            <td id="diferenciaColumna" colspan="1" class="text-right h5 ps-4"></td>
                          </tr>
                      </tfoot>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
      </div>
    </div>
</div>
@include('bancos.modal')
@endsection
@section('datatable')

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Calcular el total de la penúltima columna
        let totalPenultima = 0;
        document.querySelectorAll('.penultima-columna').forEach(function(cell) {
            let text = cell.textContent.trim().replace('$', '').replace(/,/g, '');
            totalPenultima += parseFloat(text) || 0;
        });
        document.getElementById('totalPenultimaColumna').textContent = `$ ${totalPenultima.toLocaleString('en-US')}`;
        document.getElementById('collections').textContent = `$ ${totalPenultima.toLocaleString('en-US')}`;

        
        // Calcular el total de la última columna
        let totalUltima = 0;
        document.querySelectorAll('.ultima-columna').forEach(function(cell) {
            let text = cell.textContent.trim().replace('$', '').replace(/,/g, '');
            totalUltima += parseFloat(text) || 0;
        });
        document.getElementById('totalUltimaColumna').textContent = `$ ${totalUltima.toLocaleString('en-US')}`;
        document.getElementById('payment').textContent = `$ ${totalUltima.toLocaleString('en-US')}`;


        // Calcular la diferencia y mostrarla
        let saldoInicial = document.getElementById('saldo-inicial').textContent;
        saldoInicial = saldoInicial.replace(/[\$,]/g, '');
        let diferencia = parseFloat(saldoInicial) + totalPenultima - totalUltima ;
        document.getElementById('diferenciaColumna').textContent = `$ ${diferencia.toLocaleString('en-US')}`;
        document.getElementById('diferenciaColumna2').textContent = `$ ${diferencia.toLocaleString('en-US')}`;
    });
</script>
<!-- CSS de Date Range Picker -->
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<!-- Moment.js -->
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
<!-- JS de Date Range Picker -->
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script>
$(document).ready(function() {
    $('#daterange').daterangepicker({
        opens: 'left',
        locale: {
            format: 'YYYY-MM-DD', // Formato de fecha
            separator: " AL ", // Separador entre la fecha inicial y final
            applyLabel: "Aplicar",
            cancelLabel: "Cancelar",
            fromLabel: "Desde",
            toLabel: "Hasta",
            customRangeLabel: "Personalizado",
            daysOfWeek: ["Do", "Lu", "Ma", "Mi", "Ju", "Vi", "Sa"],
            monthNames: ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"],
            firstDay: 1
        },
        maxDate: moment()
    }, 
        function(start, end, label) {
        // Callback para el botón "Aplicar"
        $("#fecha_de").val(start.format('YYYY-MM-DD'));
        $("#fecha_hasta").val(end.format('YYYY-MM-DD'));
        document.getElementById("form-buscar").submit();
  
    });
});
</script>


@endsection
