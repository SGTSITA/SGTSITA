<!DOCTYPE html>
    <html>
        <style>
            body{
              font-family: sans-serif;
            }

            @page {
              margin: 160px 50px;
            }

            header { position: fixed;
              left: 0px;
              top: -160px;
              right: 0px;
              height: 100px;
              background-color: #47A0CD;
              color: #fff;
              text-align: center;
            }

            header h1{
              margin: 10px 0;
            }

            header h2{
              margin: 0 0 10px 0;
            }

            footer {
              position: fixed;
              left: 0px;
              bottom: -50px;
              right: 0px;
              height: 40px;
              border-bottom: 2px solid #47A0CD;
            }

            footer .page:after {
              content: counter(page);
            }

            footer table {
              width: 100%;
            }

            footer p {
              text-align: right;
            }

            footer .izq {
              text-align: left;
            }

            table {
            font-family: arial, sans-serif;
            border-collapse: collapse;
            width: 100%;
            border-radius: 6px;

            }

            td, th {
            text-align: center;
            padding: 8px;
            }

            tr:nth-child(even) {
            background-color: #47A0CD;
            }
        </style>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>

        <body>
            <header>
                    <img alt="Bootstrap Image Preview" src="{{ asset('img/logo.jpg') }}" style="width:25%; ">
            </header>

            <footer>
                <table>
                  <tr>
                    <td>
                        <p class="izq">
                            Fecha: {{$fechaCarbon->format('d \d\e F \d\e\l Y')}}
                        </p>
                    </td>
                    <td>
                      <p class="page">
                        Página
                      </p>
                    </td>
                  </tr>
                </table>
            </footer>

            <div id="content">
                <div class="row">
                    <h3 style="color: #47A0CD;font-size: 40px; text-align: center;">Recibo</h3>

                    <div style="width: 50%; float:left">
                        <blockquote class="blockquote">
                            <p class="display-4 from" style="color: #47A0CD;font-size: 25px;">
                                <strong>Datos Generales </strong>
                            </p>
                            <p class="text-right  text-white" style="color: #000;font-size: 18px;">
                                Empresa: {{$configuracion->nombre_sistema}}
                            </p>
                            <p class="text-right  text-white" style="color: #000;font-size: 18px;">
                                Telefono: 5500000000
                            </p>
                            <p class="text-right  text-white" style="color: #000;font-size: 18px;">
                                Correo: <br> sita@gmail.com
                            </p>
                            <p class="text-right  text-white" style="color: #000;font-size: 18px;">
                                Fecha:  {{$cotizacion->created_at->format('d \d\e F \d\e\l Y')}}
                            </p>

                        </blockquote>
                    </div>

                    <div style="width: 50%; float:right">
                        <blockquote class="blockquote">
                            <p class="display-4 from" style="color: #47A0CD;font-size: 25px;">
                                <strong>Datos del cliente </strong>
                            </p>

                                <p class="text-footer text-white para" style="color: #000;font-size: 18px;">
                                    Nombre: {{ $cotizacion->Cliente->nombre }}
                                </p>

                                <p class="text-footer text-white para" style="color: #000;font-size: 18px;">
                                    Telefono: {{ $cotizacion->Cliente->telefono }}
                                </p>

                                <p class="text-footer text-white para" style="color: #000;font-size: 18px;">
                                    Correo: {{ $cotizacion->Cliente->correo }}
                                </p>

                                <p class="text-footer text-white para" style="color: #000;font-size: 18px;">
                                    Facturado A:
                                    @if ($cotizacion->id_subcliente == NULL)
                                        {{ $cotizacion->Cliente->nombre }}
                                    @else
                                        {{ $cotizacion->Subcliente->nombre }}
                                    @endif
                                </p>
                        </blockquote>
                    </div>
                </div>

                <h2 style="page-break-before: always; text-align: center;"></h2>

                <div class="row mt-5">
                    <div class="col-md-12">
                        <h3 style="text-align: center;">Datos del Contenedor</h3>
                        <table id="ejemplo" class="table text-white tabla-completa" style="color: #000;width: 100%;padding: 30px;">
                            <thead class="tabla-azul" style="padding: 100px;">
                                <tr class="tr" style="background-color: #47A0CD;height: 40px; color: #ffffff;">
                                    <th >
                                        Contenedor
                                    </th>

                                    <th >
                                        Destino
                                    </th>

                                    <th >
                                        Peso
                                    </th>

                                    <th>
                                        Tamaño Contenedor
                                    </th>
                                </tr>
                            </thead>

                            <tbody style="text-align: center;font-size: 120%;">
                                <th>
                                    {{$documentacion->num_contenedor}}
                                </th>

                                <th>
                                    {{$cotizacion->destino}}
                                </th>

                                <th>
                                    {{$cotizacion->peso_contenedor}}
                                </th>

                                <th>
                                    {{$cotizacion->tamano}}
                                </th>
                            </tbody>
                        </table>

                        <h3 style="text-align: center;">Costos del Contenedor</h3>

                        <table id="ejemplo" class="table text-white tabla-completa" style="color: #000;width: 100%;padding: 30px;">
                            <thead class="tabla-azul" style="padding: 100px;">
                                <tr class="tr" style="background-color: #47A0CD;height: 40px; color: #ffffff;">
                                    <th >
                                        Burreo
                                    </th>

                                    <th >
                                        Estadia
                                    </th>

                                    <th>
                                        Sobre peso
                                    </th>

                                    <th>
                                        Otro
                                    </th>

                                    <th>
                                        Precio de Venta
                                    </th>
                                </tr>
                            </thead>

                            <tbody style="text-align: center;font-size: 120%;">
                                <th>
                                    $ {{ number_format($cotizacion->burreo, 2, '.', ',')}}
                                </th>

                                <th>
                                    $ {{ number_format($cotizacion->estadia, 2, '.', ',')}}
                                </th>

                                <th>
                                    $ {{ number_format($cotizacion->precio_sobre_peso, 2, '.', ',')}}
                                </th>

                                <th>
                                    $ {{ number_format($cotizacion->cot_otro, 2, '.', ',')}}
                                </th>

                                <th>
                                    $ {{ number_format($cotizacion->precio_viaje, 2, '.', ',')}}
                                </th>
                            </tbody>

                        </table>


                        <h3 style="text-align: center;">Factura</h3>

                        <table id="ejemplo" class="table text-white tabla-completa" style="color: #000;width: 100%;padding: 10px;">
                                <thead class="tabla-azul" >
                                    <tr class="tr" style="background-color: #47A0CD;height: 40px; color: #ffffff;">
                                        <th >
                                            Base Factura
                                        </th>

                                        <th >
                                            IVA
                                        </th>

                                        <th>
                                            Retencion
                                        </th>

                                        <th>
                                            Base Taref
                                        </th>
                                    </tr>
                                </thead>

                            <tbody style="text-align: center;font-size: 120%;">
                                <th>
                                    $ {{ number_format($cotizacion->base_factura, 1, '.', ',')}}
                                </th>

                                <th>
                                    $ {{ number_format($cotizacion->iva, 1, '.', ',')}}
                                </th>

                                <th>
                                    $ {{ number_format($cotizacion->retencion, 1, '.', ',')}}
                                </th>

                                <th>
                                    $ {{ number_format($cotizacion->base_taref, 1, '.', ',')}}
                                </th>

                            </tbody>
                        </table>

                        <h3 style="text-align: center;">Totales</h3>

                        <table id="ejemplo" class="table text-white tabla-completa" style="color: #000;width: 100%;padding: 10px;">
                                <thead class="tabla-azul" >
                                    <tr class="tr" style="background-color: #143E90;height: 40px; color: #ffffff;">
                                        <th>
                                            Total Oficial
                                        </th>

                                        <th>
                                            Total no Oficial
                                        </th>

                                        <th>
                                            Importe VTA
                                        </th>
                                    </tr>
                                </thead>

                            <tbody style="text-align: center;font-size: 120%;">
                                <th>
                                    @php
                                        $total_oficial = ($cotizacion->base_factura + $cotizacion->iva) - $cotizacion->retencion;
                                    @endphp
                                    $ {{ number_format($total_oficial, 1, '.', ',')}}
                                </th>

                                <th>
                                    $ {{ number_format($cotizacion->base_taref, 1, '.', ',')}}
                                </th>

                                <th>
                                    @php
                                        $importe_vta = $cotizacion->base_taref + $total_oficial;
                                    @endphp
                                    $ {{ number_format($importe_vta, 1, '.', ',')}}
                                </th>

                            </tbody>
                        </table>

                    </div>
                </div>

                <h2 style="page-break-before: always; text-align: center;"></h2>
                <div class="row mt-5">
                    <div class="col-md-6">
                        <h3 style="color: #47A0CD;font-size: 40px; text-align: center;">Datos de cuentas</h3>
                        <address class="text-white datos-contacto" style="color: #000;font-size: 20px;text-decoration: none;">
                            <p>
                                <ul style="color: #000">
                                    <p class="display-4 from" style="color: #47A0CD;font-size: 25px;">
                                        <strong>Oficial</strong>
                                    </p>
                                    @foreach ($bancos_oficiales as $banco_oficial)
                                        <li>Nombre: {{$banco_oficial->nombre_beneficiario}}</li>
                                        <li>Banco: {{$banco_oficial->nombre_banco}}</li>
                                        <li>No. CLABE: {{$banco_oficial->clabe}}</li>
                                        <li>No. CTA : {{$banco_oficial->cuenta_bancaria}}</li>
                                    @endforeach
                                </ul>
                            </p>

                            <br>
                            <p>
                                <ul style="color: #000">
                                    <p class="display-4 from" style="color: #47A0CD;font-size: 25px;">
                                        <strong>No Oficial</strong>
                                    </p>
                                    @foreach ($bancos_no_oficiales as $banco_oficial)
                                        <li>Nombre: {{$banco_oficial->nombre_beneficiario}}</li>
                                        <li>Banco: {{$banco_oficial->nombre_banco}}</li>
                                        <li>No. CLABE: {{$banco_oficial->clabe}}</li>
                                        <li>No. CTA : {{$banco_oficial->cuenta_bancaria}}</li>
                                    @endforeach
                                </ul>
                                </p><br>
                        </address>
                    </div>
                </div>
                <div class="contenedor-azul"style="background-color:#47A0CD;position: absolute;width: 60%;height:1%;left: 20%;right: 20%;">
                </div>

                <div class="row footer">
                    <div class="col-md-12">
                        <h3 class="text-center text-white " style="color: #000">
                            <a  class="text-center text-white pag" href="https://checkn-go.com.mx" target="blank" title="pagina eago" style="position: absolute;text-decoration: none;color: #fff;left: 40%;display:block;">checkn-go.com.mx
                            </a>
                        </h3>
                    </div>
                </div>
            </div>
        </body>
    </html>
