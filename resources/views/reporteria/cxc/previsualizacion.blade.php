@extends('layouts.app')

@section('template_title')
    Cuentas por Cobrar
@endsection

@section('css')
    <!-- Estilos específicos -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/fixedcolumns/5.0.1/css/fixedColumns.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/select/2.0.3/css/select.bootstrap5.min.css">

    <style>
        .container-box {
            margin: 30px auto;
            padding: 20px;
            max-width: 1200px; /* Ajuste de tamaño del cuadro */
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow-x: auto; /* Para tablas grandes */
        }

        .container-box h4 {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 10px;
            text-align: left;
        }

        .container-box table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 0.9rem; /* Ajuste de tamaño de fuente */
        }

        .container-box th, .container-box td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
            white-space: nowrap; /* Evita que el contenido se rompa en filas */
        }

        .container-box th {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        .totales {
            margin-top: 20px;
            font-size: 1rem;
        }

        .totales p {
            margin: 5px 0;
            font-weight: bold;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="container-box">
        <a  class="btn btn-primary">
                            Regresar
                            </a> 
            <h4>Empresa: Ejemplo S.A.</h4>
            <h4>Estado de cuenta</h4>
            <h4>Cliente: Cliente de Prueba</h4>

            <table>
                <thead>
                    <tr>
                        <th>Fecha inicio</th>
                        <th>Contratista</th>
                        <th>Contenedor</th>
                        <th>Facturado a</th>
                        <th>Destino</th>
                        <th>Peso</th>
                        <th>Tamaño</th>
                        <th>Burreo</th>
                        <th>Estadía</th>
                        <th>Sobrepeso</th>
                        <th>Otro</th>
                        <th>Precio venta</th>
                        <th>Precio viaje</th>
                        <th>Base factura</th>
                        <th>IVA</th>
                        <th>Retención</th>
                        <th>Base taref</th>
                        <th>Total oficial</th>
                        <th>Total no oficial</th>
                        <th>Importe VTA</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>01-12-2024</td>
                        <td>Contratista A</td>
                        <td>Contenedor123</td>
                        <td>Cliente A</td>
                        <td>Destino 1</td>
                        <td>1000 kg</td>
                        <td>40 ft</td>
                        <td>$500.00</td>
                        <td>$200.00</td>
                        <td>$50.00</td>
                        <td>$30.00</td>
                        <td>$3000.00</td>
                        <td>$1500.00</td>
                        <td>$2500.00</td>
                        <td>$400.00</td>
                        <td>$100.00</td>
                        <td>$250.00</td>
                        <td>$2850.00</td>
                        <td>$500.00</td>
                        <td>$3350.00</td>
                    </tr>
                </tbody>
            </table>

            <div class="totales">
                <p>Total oficial: <b>$2850.00</b></p>
                <p>Total no oficial: <b>$500.00</b></p>
                <p>Importe VTA: <b>$3350.00</b></p>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- Scripts específicos -->
    <script src="https://cdn.datatables.net/2.0.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/fixedcolumns/5.0.1/js/dataTables.fixedColumns.min.js"></script>
    <script src="https://cdn.datatables.net/select/2.0.3/js/dataTables.select.min.js"></script>
@endsection
