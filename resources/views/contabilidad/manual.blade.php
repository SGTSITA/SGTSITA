@extends('layouts.app')

@section('template_title')
    Manual Contable y Guía Contabilizadora CONAC / LGCG
@endsection

@section('content')
<div class="container-fluid py-4">

    {{-- HERO / ENCABEZADO --}}
    <div class="card shadow-sm border-0 mb-4 bg-gradient text-white" style="background: linear-gradient(135deg, #1e1e2d 0%, #2c3e50 100%);">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <div>
                    <span class="badge bg-warning text-dark px-3 py-2 fw-bold text-uppercase mb-2">
                        <i class="fas fa-balance-scale me-1"></i> Marco Normativo CONAC / LGCG &bull; SGTSITA ERP
                    </span>
                    <h2 class="fw-bolder mb-1 text-white">Manual Contable del Sistema y Guía Contabilizadora</h2>
                    <p class="text-white-50 mb-0 fs-6">
                        Fundamentación jurídica, momentos contables, catálogo armonizado de cuentas y manual operativo de procesos financieros para transporte terrestre y contenedores.
                    </p>
                </div>
                <div class="mt-3 mt-md-0 d-flex gap-2">
                    <a href="{{ route('contabilidad.balance-general.index') }}" class="btn btn-outline-light btn-sm fw-bold">
                        <i class="fas fa-file-invoice-dollar me-1"></i> Balance General
                    </a>
                    <a href="{{ route('contabilidad.poliza-apertura.index') }}" class="btn btn-success btn-sm fw-bold">
                        <i class="fas fa-folder-plus me-1"></i> Póliza de Apertura
                    </a>
                </div>
            </div>

            {{-- NAVEGACIÓN RÁPIDA --}}
            <hr class="border-secondary my-3">
            <div class="d-flex flex-wrap gap-2">
                <a href="#marco-legal" class="btn btn-sm btn-dark text-white-50"><i class="fas fa-gavel text-warning me-1"></i> 1. Marco Legal LGCG</a>
                <a href="#catalogo-cuentas" class="btn btn-sm btn-dark text-white-50"><i class="fas fa-sitemap text-info me-1"></i> 2. Plan de Cuentas</a>
                <a href="#momentos-contables" class="btn btn-sm btn-dark text-white-50"><i class="fas fa-stopwatch text-success me-1"></i> 3. Momentos Contables</a>
                <a href="#guia-eventos" class="btn btn-sm btn-dark text-white-50"><i class="fas fa-book-reader text-primary me-1"></i> 4. Guía Contabilizadora</a>
                <a href="#cxc-cxp-gastos" class="btn btn-sm btn-dark text-white-50"><i class="fas fa-calculator text-danger me-1"></i> 5. Fórmulas CxC, CxP y Gastos</a>
                <a href="#liquidaciones-prestamos" class="btn btn-sm btn-dark text-white-50"><i class="fas fa-id-card-alt text-warning me-1"></i> 6. Operadores y Socios</a>
                <a href="#hallazgos-pdf" class="btn btn-sm btn-dark text-white-50"><i class="fas fa-file-pdf text-danger me-1"></i> 7. Análisis TP 002 (PDF)</a>
                <a href="#comandos-consola" class="btn btn-sm btn-dark text-white-50"><i class="fas fa-terminal text-info me-1"></i> 8. Comandos Artisan</a>
            </div>
        </div>
    </div>

    {{-- SECCIÓN 1: MARCO LEGAL LGCG / CONAC --}}
    <div class="card shadow-sm border-0 mb-4" id="marco-legal">
        <div class="card-header bg-white py-3 border-bottom">
            <h5 class="card-title fw-bold text-primary mb-0">
                <i class="fas fa-gavel text-warning me-2"></i> 1. Marco Legal y Fundamentación Jurídica (LGCG &amp; CONAC)
            </h5>
        </div>
        <div class="card-body">
            <p class="text-muted">
                La contabilidad en SGTSITA está diseñada bajo los principios y lineamientos emitidos por el <strong>Consejo Nacional de Armonización Contable (CONAC)</strong> en apego estricto a la <strong>Ley General de Contabilidad Gubernamental (LGCG)</strong>, adoptando las mejores prácticas aplicadas a empresas permisionarias de autotransporte federal de carga y logística de contenedores.
            </p>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="p-3 border rounded bg-light h-100">
                        <h6 class="fw-bold text-dark"><i class="fas fa-check-circle text-success me-1"></i> Art. 34 LGCG &bull; Devengo Contable</h6>
                        <p class="small text-muted mb-0">
                            <em>"Los registros contables de los entes se llevarán con base acumulativa. La contabilización de las transacciones de gasto se registrará conforme a la fecha de su realización, independientemente de la de su pago, y la del ingreso cuando se realice el derecho de cobro."</em><br>
                            <strong>En SGTSITA:</strong> Toda cotización aprobada y todo gasto capturado generan su asiento de provisión al momento de concluirse o autorizarse, reconociendo el derecho de cobro (CxC) o la obligación de pago (CXP) antes del flujo de efectivo.
                        </p>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-3 border rounded bg-light h-100">
                        <h6 class="fw-bold text-dark"><i class="fas fa-check-circle text-success me-1"></i> Art. 38 LGCG &bull; Partida Doble</h6>
                        <p class="small text-muted mb-0">
                            <em>"El registro de las etapas del presupuesto de los entes se efectuará en las cuentas contables que, para tal efecto, establezca el consejo, las cuales deberán reflejar un estricto equilibrio entre cargos y abonos."</em><br>
                            <strong>En SGTSITA:</strong> Ninguna póliza (de Diario, Ingreso o Egreso) se registra si la suma del Debe no es matemáticamente idéntica a la suma del Haber (Diferencia = $0.00).
                        </p>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-3 border rounded bg-light h-100">
                        <h6 class="fw-bold text-dark"><i class="fas fa-check-circle text-success me-1"></i> Art. 40 LGCG &bull; Matriz de Conversión</h6>
                        <p class="small text-muted mb-0">
                            <em>"La transición de los registros presupuestarios y operativos a los registros contables se hará a través de la matriz de conversión o guía contabilizadora."</em><br>
                            <strong>En SGTSITA:</strong> Cada acción del usuario (aprobar viaje, pagar factura, registrar préstamo, transferir a socio) ejecuta automáticamente las reglas de la Guía Contabilizadora mapeando el evento a las cuentas específicas.
                        </p>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-3 border rounded bg-light h-100">
                        <h6 class="fw-bold text-dark"><i class="fas fa-check-circle text-success me-1"></i> Art. 41 LGCG &bull; Catálogo Armonizado</h6>
                        <p class="small text-muted mb-0">
                            <em>"Para el registro único de las operaciones presupuestarias y contables, los entes dispondrán de clasificadores presupuestarios, listas de cuentas y catálogos de bienes armonizados."</em><br>
                            <strong>En SGTSITA:</strong> El plan de cuentas se codifica a 5 dígitos organizados en 5 géneros universales (Activo, Pasivo, Patrimonio, Ingresos y Gastos).
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- SECCIÓN 2: PLAN DE CUENTAS ARMONIZADO --}}
    <div class="card shadow-sm border-0 mb-4" id="catalogo-cuentas">
        <div class="card-header bg-white py-3 border-bottom">
            <h5 class="card-title fw-bold text-primary mb-0">
                <i class="fas fa-sitemap text-info me-2"></i> 2. Estructura Armonizada del Plan de Cuentas (5 Géneros)
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle text-nowrap">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 120px;">Código</th>
                            <th>Nombre de la Cuenta</th>
                            <th style="width: 100px;">Nivel</th>
                            <th style="width: 120px;">Naturaleza</th>
                            <th style="width: 120px;">Afectable</th>
                            <th>Uso Operativo en SGTSITA</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="table-primary fw-bold">
                            <td>1</td>
                            <td colspan="5">ACTIVO (Bienes y Derechos de la Empresa)</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">1.1.1.2</td>
                            <td>Bancos / Tesorería</td>
                            <td>Nivel 4</td>
                            <td>Deudora</td>
                            <td>No</td>
                            <td>Cuenta de mayor para cuentas bancarias</td>
                        </tr>
                        <tr>
                            <td class="ps-4">1.1.1.2.001</td>
                            <td>BBVA Bancomer</td>
                            <td>Nivel 5</td>
                            <td>Deudora</td>
                            <td><span class="badge bg-success">Sí</span></td>
                            <td>Cuenta bancaria principal operativa</td>
                        </tr>
                        <tr>
                            <td class="ps-4">1.1.1.2.002</td>
                            <td>Banco Azteca</td>
                            <td>Nivel 5</td>
                            <td>Deudora</td>
                            <td><span class="badge bg-success">Sí</span></td>
                            <td>Cuenta de dispersión rápida / nómina</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">1.1.2.1.001</td>
                            <td>Clientes Nacionales por Cobrar (CXC)</td>
                            <td>Nivel 5</td>
                            <td>Deudora</td>
                            <td><span class="badge bg-success">Sí</span></td>
                            <td>Fletes devengados pendientes de cobro a clientes</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">1.1.2.3.001</td>
                            <td>Préstamos Otorgados a Operadores</td>
                            <td>Nivel 5</td>
                            <td>Deudora</td>
                            <td><span class="badge bg-success">Sí</span></td>
                            <td>Anticipos y préstamos entregados a choferes</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">1.1.2.3.002</td>
                            <td>Gastos por Comprobar</td>
                            <td>Nivel 5</td>
                            <td>Deudora</td>
                            <td><span class="badge bg-success">Sí</span></td>
                            <td>Fondos entregados a talleres/rutas pendientes de factura</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">1.1.2.3.003</td>
                            <td>Anticipos a Socios por Liquidar</td>
                            <td>Nivel 5</td>
                            <td>Deudora</td>
                            <td><span class="badge bg-success">Sí</span></td>
                            <td>Adelantos retirados por socios a cuenta de utilidades</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">1.2.4.1.001</td>
                            <td>Tractocamiones / Camiones</td>
                            <td>Nivel 5</td>
                            <td>Deudora</td>
                            <td><span class="badge bg-success">Sí</span></td>
                            <td>Activo fijo vehicular (Tractocamiones propios)</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">1.2.4.1.002</td>
                            <td>Remolques y Chasis</td>
                            <td>Nivel 5</td>
                            <td>Deudora</td>
                            <td><span class="badge bg-success">Sí</span></td>
                            <td>Activo fijo de arrastre (Chasis, portas y dollys)</td>
                        </tr>

                        <tr class="table-danger fw-bold">
                            <td>2</td>
                            <td colspan="5">PASIVO (Obligaciones y Deudas con Terceros)</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">2.1.1.2.001</td>
                            <td>Proveedores de Fletes Subcontratados</td>
                            <td>Nivel 5</td>
                            <td>Acreedora</td>
                            <td><span class="badge bg-success">Sí</span></td>
                            <td>Fletes adeudados a permisionarios externos (CXP)</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">2.1.1.2.002</td>
                            <td>Talleres, Refacciones y Mantenimiento</td>
                            <td>Nivel 5</td>
                            <td>Acreedora</td>
                            <td><span class="badge bg-success">Sí</span></td>
                            <td>Facturas pendientes con SAGAZA, refaccionarias, etc.</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">2.1.3.1.001</td>
                            <td>Préstamos Recibidos a Corto Plazo</td>
                            <td>Nivel 5</td>
                            <td>Acreedora</td>
                            <td><span class="badge bg-success">Sí</span></td>
                            <td>Créditos bancarios o de terceros recibidos por la empresa</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">2.1.9.1.002</td>
                            <td>Reparto de Utilidades por Pagar a Socios</td>
                            <td>Nivel 5</td>
                            <td>Acreedora</td>
                            <td><span class="badge bg-success">Sí</span></td>
                            <td>Utilidades calculadas en corte pendientes de pagar</td>
                        </tr>

                        <tr class="table-warning fw-bold">
                            <td>3</td>
                            <td colspan="5">HACIENDA PÚBLICA / PATRIMONIO (Capital y Resultados)</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">3.1.1.1.001</td>
                            <td>Capital Social Fijo / Aportaciones</td>
                            <td>Nivel 5</td>
                            <td>Acreedora</td>
                            <td><span class="badge bg-success">Sí</span></td>
                            <td>Patrimonio inicial aportado por socios / apertura</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">3.2.1.1</td>
                            <td>Resultado del Ejercicio</td>
                            <td>Nivel 4</td>
                            <td>Acreedora</td>
                            <td><span class="badge bg-success">Sí</span></td>
                            <td>Utilidad neta acumulada en el año en curso</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">3.2.2.1</td>
                            <td>Resultado de Ejercicios Anteriores</td>
                            <td>Nivel 4</td>
                            <td>Acreedora</td>
                            <td><span class="badge bg-success">Sí</span></td>
                            <td>Utilidades o pérdidas acumuladas de años previos</td>
                        </tr>

                        <tr class="table-success fw-bold">
                            <td>4</td>
                            <td colspan="5">INGRESOS Y OTROS BENEFICIOS</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">4.1.1.1</td>
                            <td>Ingresos por Fletes y Servicios</td>
                            <td>Nivel 4</td>
                            <td>Acreedora</td>
                            <td><span class="badge bg-success">Sí</span></td>
                            <td>Venta de fletes, burreo, estadías y maniobras</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">4.3.1.1</td>
                            <td>Intereses Ganados de Cuentas Bancarias</td>
                            <td>Nivel 4</td>
                            <td>Acreedora</td>
                            <td><span class="badge bg-success">Sí</span></td>
                            <td>Rendimientos financieros generados en cuentas de banco</td>
                        </tr>

                        <tr class="table-secondary fw-bold">
                            <td>5</td>
                            <td colspan="5">GASTOS Y OTRAS PÉRDIDAS (Costos y Operación)</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">5.1.1.1</td>
                            <td>Combustible (Diésel)</td>
                            <td>Nivel 4</td>
                            <td>Deudora</td>
                            <td><span class="badge bg-success">Sí</span></td>
                            <td>Cargas de diésel imputadas a tractocamiones</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">5.1.1.2</td>
                            <td>Casetas y Peajes</td>
                            <td>Nivel 4</td>
                            <td>Deudora</td>
                            <td><span class="badge bg-success">Sí</span></td>
                            <td>Tags y tickets de casetas en viajes</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">5.1.1.3</td>
                            <td>Maniobras y Burreo</td>
                            <td>Nivel 4</td>
                            <td>Deudora</td>
                            <td><span class="badge bg-success">Sí</span></td>
                            <td>Gastos de patio, accesos y maniobras externas</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">5.1.2.1</td>
                            <td>Mantenimiento, Llantas y Reparaciones</td>
                            <td>Nivel 4</td>
                            <td>Deudora</td>
                            <td><span class="badge bg-success">Sí</span></td>
                            <td>Servicios mecánicos, compra de llantas y partes</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">5.1.3.1</td>
                            <td>Gastos Administrativos y Generales</td>
                            <td>Nivel 4</td>
                            <td>Deudora</td>
                            <td><span class="badge bg-success">Sí</span></td>
                            <td>Software, oficinas, cuotas administrativas, trámites</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- SECCIÓN 3: MOMENTOS CONTABLES --}}
    <div class="card shadow-sm border-0 mb-4" id="momentos-contables">
        <div class="card-header bg-white py-3 border-bottom">
            <h5 class="card-title fw-bold text-primary mb-0">
                <i class="fas fa-stopwatch text-success me-2"></i> 3. Los Momentos Contables de Ingresos y Egresos
            </h5>
        </div>
        <div class="card-body">
            <div class="row g-4">
                {{-- INGRESOS --}}
                <div class="col-lg-6">
                    <div class="border rounded p-3 h-100 bg-light">
                        <h6 class="fw-bold text-success border-bottom pb-2">
                            <i class="fas fa-arrow-down me-1"></i> Momentos Contables del Ingreso (Art. 33 LGCG)
                        </h6>
                        <ol class="list-group list-group-numbered list-group-flush mb-0">
                            <li class="list-group-item bg-transparent">
                                <strong>Estimado:</strong> Monto cotizado inicialmente en la orden de servicio.
                            </li>
                            <li class="list-group-item bg-transparent">
                                <strong>Modificado:</strong> Ajustes por accesorios en ruta (demoras, burreo, sobrepeso).
                            </li>
                            <li class="list-group-item bg-transparent">
                                <strong>Devengado (CxC):</strong> Viaje concluido / entregado al cliente. Se emite la factura o queda firme el derecho de cobro.<br>
                                <span class="badge bg-primary text-white">Cargo: Clientes CXC &bull; Abono: Ingresos Fletes</span>
                            </li>
                            <li class="list-group-item bg-transparent">
                                <strong>Recaudado (Cobro):</strong> El cliente deposita en la cuenta bancaria.<br>
                                <span class="badge bg-success text-white">Cargo: Bancos &bull; Abono: Clientes CXC</span>
                            </li>
                        </ol>
                    </div>
                </div>

                {{-- EGRESOS --}}
                <div class="col-lg-6">
                    <div class="border rounded p-3 h-100 bg-light">
                        <h6 class="fw-bold text-danger border-bottom pb-2">
                            <i class="fas fa-arrow-up me-1"></i> Momentos Contables del Gasto (Art. 35 LGCG)
                        </h6>
                        <ol class="list-group list-group-numbered list-group-flush mb-0">
                            <li class="list-group-item bg-transparent">
                                <strong>Aprobado / Comprometido:</strong> Autorización de la compra, orden de taller o asignación a proveedor.
                            </li>
                            <li class="list-group-item bg-transparent">
                                <strong>Devengado (CXP):</strong> Recepción de la refacción o servicio realizado. Nace la deuda con el proveedor.<br>
                                <span class="badge bg-warning text-dark">Cargo: Gasto Operativo &bull; Abono: Proveedores CXP</span>
                            </li>
                            <li class="list-group-item bg-transparent">
                                <strong>Ejercido:</strong> Cuenta por pagar lista y programada para pago bancario.
                            </li>
                            <li class="list-group-item bg-transparent">
                                <strong>Pagado (Salida de Bancos):</strong> Desembolso real de efectivo o transferencia bancaria.<br>
                                <span class="badge bg-danger text-white">Cargo: Proveedores CXP &bull; Abono: Bancos</span>
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- SECCIÓN 4: GUÍA CONTABILIZADORA POR EVENTOS OPERATIVOS --}}
    <div class="card shadow-sm border-0 mb-4" id="guia-eventos">
        <div class="card-header bg-white py-3 border-bottom">
            <h5 class="card-title fw-bold text-primary mb-0">
                <i class="fas fa-book-reader text-primary me-2"></i> 4. Guía Contabilizadora de Eventos Operativos (Cuentas T)
            </h5>
        </div>
        <div class="card-body">
            <div class="row g-4">

                {{-- EVENTO 1: PROVISIÓN DE VIAJE --}}
                <div class="col-md-6">
                    <div class="card h-100 border">
                        <div class="card-header bg-primary text-white py-2">
                            <span class="badge bg-light text-primary me-1">Evento 1</span> <strong>PROVISION_VIAJE &bull; Cotización / Viaje Concluido</strong>
                        </div>
                        <div class="card-body p-3">
                            <p class="small text-muted mb-2">
                                <strong>Disparador:</strong> Cuando se captura o actualiza el importe de una cotización aprobada o viaje finalizado.
                            </p>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm text-center mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Cuenta Contable</th>
                                            <th>DEBE (Cargo)</th>
                                            <th>HABER (Abono)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="text-start"><code>1.1.2.1.001</code> Clientes Nacionales CXC</td>
                                            <td class="text-success fw-bold">$ TOTAL</td>
                                            <td>-</td>
                                        </tr>
                                        <tr>
                                            <td class="text-start"><code>4.1.1.1</code> Ingresos por Fletes</td>
                                            <td>-</td>
                                            <td class="text-primary fw-bold">$ TOTAL</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="alert alert-info py-1 px-2 mt-2 mb-0 small">
                                <strong>Efecto en Balance:</strong> Incrementa el Activo Circulante (CxC) e incrementa el Patrimonio vía Utilidad del Ejercicio.
                            </div>
                        </div>
                    </div>
                </div>

                {{-- EVENTO 2: COBRO CXC --}}
                <div class="col-md-6">
                    <div class="card h-100 border">
                        <div class="card-header bg-success text-white py-2">
                            <span class="badge bg-light text-success me-1">Evento 2</span> <strong>COBRO_CXC &bull; Cobro Recaudado de Clientes</strong>
                        </div>
                        <div class="card-body p-3">
                            <p class="small text-muted mb-2">
                                <strong>Disparador:</strong> Al aplicar un cobro en el módulo de Cuentas por Cobrar (<code>CuentasCobrarController</code>).
                            </p>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm text-center mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Cuenta Contable</th>
                                            <th>DEBE (Cargo)</th>
                                            <th>HABER (Abono)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="text-start"><code>1.1.1.2.{banco}</code> Bancos / Tesorería</td>
                                            <td class="text-success fw-bold">$ COBRO</td>
                                            <td>-</td>
                                        </tr>
                                        <tr>
                                            <td class="text-start"><code>1.1.2.1.001</code> Clientes Nacionales CXC</td>
                                            <td>-</td>
                                            <td class="text-danger fw-bold">$ COBRO</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="alert alert-success py-1 px-2 mt-2 mb-0 small">
                                <strong>Efecto en Balance:</strong> Sustitución de activos: el dinero entra a Bancos y disminuye la deuda del Cliente (CxC).
                            </div>
                        </div>
                    </div>
                </div>

                {{-- EVENTO 3: PROVISIÓN DE GASTO --}}
                <div class="col-md-6">
                    <div class="card h-100 border">
                        <div class="card-header bg-warning text-dark py-2">
                            <span class="badge bg-dark text-white me-1">Evento 3</span> <strong>PROVISION_GASTO &bull; Gasto Devengado (Sin Pagar)</strong>
                        </div>
                        <div class="card-body p-3">
                            <p class="small text-muted mb-2">
                                <strong>Disparador:</strong> Al registrar un gasto operativo con estatus <code>pendiente_pago</code>.
                            </p>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm text-center mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Cuenta Contable</th>
                                            <th>DEBE (Cargo)</th>
                                            <th>HABER (Abono)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="text-start"><code>5.1.1.X</code> Cuenta de Gasto (Diésel, Taller...)</td>
                                            <td class="text-danger fw-bold">$ MONTO</td>
                                            <td>-</td>
                                        </tr>
                                        <tr>
                                            <td class="text-start"><code>2.1.1.2.001</code> Proveedores CXP</td>
                                            <td>-</td>
                                            <td class="text-warning fw-bold">$ MONTO</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="alert alert-warning py-1 px-2 mt-2 mb-0 small">
                                <strong>Efecto en Balance:</strong> Incrementa el Pasivo Circulante (CXP) y reduce la Utilidad del Ejercicio en Patrimonio. Bancos NO se toca.
                            </div>
                        </div>
                    </div>
                </div>

                {{-- EVENTO 4: PAGO DE GASTO --}}
                <div class="col-md-6">
                    <div class="card h-100 border">
                        <div class="card-header bg-danger text-white py-2">
                            <span class="badge bg-light text-danger me-1">Evento 4</span> <strong>PAGO_GASTO &bull; Pago a Proveedores (Salida de Bancos)</strong>
                        </div>
                        <div class="card-body p-3">
                            <p class="small text-muted mb-2">
                                <strong>Disparador:</strong> Al registrar el pago bancario del gasto (<code>GastosService::pagar</code>).
                            </p>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm text-center mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Cuenta Contable</th>
                                            <th>DEBE (Cargo)</th>
                                            <th>HABER (Abono)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="text-start"><code>2.1.1.2.001</code> Proveedores CXP</td>
                                            <td class="text-primary fw-bold">$ PAGO</td>
                                            <td>-</td>
                                        </tr>
                                        <tr>
                                            <td class="text-start"><code>1.1.1.2.{banco}</code> Bancos / Tesorería</td>
                                            <td>-</td>
                                            <td class="text-danger fw-bold">$ PAGO</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="alert alert-danger py-1 px-2 mt-2 mb-0 small">
                                <strong>Efecto en Balance:</strong> Se cancela la deuda con el Proveedor (CXP) y sale el dinero de Bancos.
                            </div>
                        </div>
                    </div>
                </div>

                {{-- EVENTO 5: PRÉSTAMO A OPERADOR --}}
                <div class="col-md-6">
                    <div class="card h-100 border">
                        <div class="card-header bg-info text-white py-2">
                            <span class="badge bg-light text-info me-1">Evento 5</span> <strong>PRESTAMO_OPERADOR &bull; Préstamo / Adelanto Otorgado</strong>
                        </div>
                        <div class="card-body p-3">
                            <p class="small text-muted mb-2">
                                <strong>Disparador:</strong> Al registrar un préstamo o adelanto en <code>PrestamosController::store</code>.
                            </p>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm text-center mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Cuenta Contable</th>
                                            <th>DEBE (Cargo)</th>
                                            <th>HABER (Abono)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="text-start"><code>1.1.2.3.001</code> Préstamos a Operadores</td>
                                            <td class="text-success fw-bold">$ PRESTAMO</td>
                                            <td>-</td>
                                        </tr>
                                        <tr>
                                            <td class="text-start"><code>1.1.1.2.{banco}</code> Bancos / Tesorería</td>
                                            <td>-</td>
                                            <td class="text-danger fw-bold">$ PRESTAMO</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="alert alert-info py-1 px-2 mt-2 mb-0 small">
                                <strong>Efecto en Balance:</strong> No es un gasto devengado. Es un derecho de cobro a favor de la empresa dentro de Deudores Diversos.
                            </div>
                        </div>
                    </div>
                </div>

                {{-- EVENTO 6: LIQUIDACIÓN OPERADOR --}}
                <div class="col-md-6">
                    <div class="card h-100 border">
                        <div class="card-header bg-dark text-white py-2">
                            <span class="badge bg-light text-dark me-1">Evento 6</span> <strong>LIQUIDACION_OPERADOR &bull; Liquidación con Amortización</strong>
                        </div>
                        <div class="card-body p-3">
                            <p class="small text-muted mb-2">
                                <strong>Disparador:</strong> Al aplicar liquidación de viajes en <code>LiquidacionesController::aplicarPago</code>.
                            </p>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm text-center mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Cuenta Contable</th>
                                            <th>DEBE (Cargo)</th>
                                            <th>HABER (Abono)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="text-start"><code>5.1.1.X</code> Sueldos / Pagos de Operación</td>
                                            <td class="text-danger fw-bold">$ DEVENGADO</td>
                                            <td>-</td>
                                        </tr>
                                        <tr>
                                            <td class="text-start"><code>1.1.2.3.001</code> Préstamos a Operadores</td>
                                            <td>-</td>
                                            <td class="text-info fw-bold">$ AMORTIZADO</td>
                                        </tr>
                                        <tr>
                                            <td class="text-start"><code>1.1.1.2.{banco}</code> Bancos (Remanente)</td>
                                            <td>-</td>
                                            <td class="text-danger fw-bold">$ NETO PAGADO</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="alert alert-dark py-1 px-2 mt-2 mb-0 small">
                                <strong>Efecto en Balance:</strong> El sueldo entra al gasto, se recupera el activo del préstamo y sale de bancos solo el neto.
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- SECCIÓN 5: FÓRMULAS DE CXC, CXP Y TRATAMIENTO DE GASTOS --}}
    <div class="card shadow-sm border-0 mb-4" id="cxc-cxp-gastos">
        <div class="card-header bg-white py-3 border-bottom">
            <h5 class="card-title fw-bold text-primary mb-0">
                <i class="fas fa-calculator text-danger me-2"></i> 5. Reglas Matemáticas de Cálculo: CxC, CxP y Costos
            </h5>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="border rounded p-3 h-100">
                        <h6 class="fw-bold text-primary border-bottom pb-2">
                            <i class="fas fa-file-invoice text-primary me-1"></i> Total a Cobrar al Cliente (CxC)
                        </h6>
                        <div class="p-2 bg-light rounded text-monospace mb-3">
                            <strong>TOTAL CXC = (Flete Base + Burreo + Maniobra + Estadía + Otro + Sobrepeso + IVA 16%) - Retención 4%</strong>
                        </div>
                        <h6 class="fw-bold fs-7 text-dark mb-1">Conceptos que SÍ integran la CxC:</h6>
                        <ul class="small text-muted ps-3 mb-2">
                            <li><strong>precio_viaje / base_factura:</strong> Tarifa pactada del servicio de transporte.</li>
                            <li><strong>burreo, maniobra, estadia:</strong> Accesorios logísticos aprobados por el cliente.</li>
                            <li><strong>sobrepeso:</strong> Excedente de peso por tonelada (peso real - reglamentario).</li>
                            <li><strong>iva (16%):</strong> Impuesto al Valor Agregado sobre los conceptos gravables.</li>
                            <li><strong>retencion (4%):</strong> Retención de IVA por servicios de autotransporte (Art. 1-A LIVA).</li>
                        </ul>
                        <div class="alert alert-warning py-2 mb-0 small">
                            <i class="fas fa-exclamation-triangle me-1"></i> <strong>Conceptos que NO se cobran al cliente:</strong>
                            Combustible, peajes normales, viáticos, sueldo del chofer y composturas en ruta. Son <strong>costos directos</strong> de la empresa, no conceptos facturables al cliente a menos que se hayan estipulado como gastos recuperables adicionales.
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="border rounded p-3 h-100">
                        <h6 class="fw-bold text-success border-bottom pb-2">
                            <i class="fas fa-truck text-success me-1"></i> Cuentas por Pagar Proveedor Subcontratado (CXP)
                        </h6>
                        <div class="p-2 bg-light rounded text-monospace mb-3">
                            <strong>TOTAL CXP = (Precio Prov + Burreo + Maniobra + Estadía + Sobrepeso Prov + IVA Prov) - Retención Prov</strong>
                        </div>
                        <h6 class="fw-bold fs-7 text-dark mb-1">Criterio en SGTSITA:</h6>
                        <p class="small text-muted mb-2">
                            Aplica cuando la asignación del contenedor tiene <code>tipo_contrato = 'Subcontratado'</code>. El total resultante queda como pasivo en <code>2.1.1.2.001 Proveedores de Fletes</code> hasta que se disperse el pago en <code>CuentasPagarController</code>.
                        </p>
                        <h6 class="fw-bold fs-7 text-dark mb-1">Margen de Contribución:</h6>
                        <div class="p-2 bg-light rounded text-monospace small">
                            <strong>UTILIDAD BRUTA = Total Cobrado al Cliente - Total Pagado al Proveedor Subcontratado</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- SECCIÓN 6: ANÁLISIS DEL PDF REAL (TP 002 AL 30 JUL 2024) --}}
    <div class="card shadow-sm border-0 mb-4" id="hallazgos-pdf">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="card-title fw-bold text-danger mb-0">
                <i class="fas fa-file-pdf text-danger me-2"></i> 6. Análisis del Balance Modelo Real (PDF: "TP 002 AL 30 JUL 2024")
            </h5>
            <span class="badge bg-danger">Hallazgos y Conceptos Pendientes</span>
        </div>
        <div class="card-body">
            <p class="text-muted">
                Contrastando los registros del balance del PDF contra la estructura actual del ERP, se identificaron los siguientes conceptos existentes y las <strong>6 áreas que requieren definirse o implementarse en el sistema</strong>:
            </p>

            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Concepto en PDF</th>
                            <th>Monto en PDF</th>
                            <th>Rubro Contable</th>
                            <th>¿Existe actualmente en SGTSITA?</th>
                            <th>Diagnóstico y Recomendación</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="fw-bold">BANCOS (BBVA)</td>
                            <td class="text-end">$225,192.12</td>
                            <td>Activo Circulante (1.1.1.2)</td>
                            <td><span class="badge bg-success">SÍ EXISTE</span></td>
                            <td>Mapeado al catálogo bancario y conciliación diaria.</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">CXC (jun-24 y jul-24)</td>
                            <td class="text-end">$119,880.00</td>
                            <td>Activo Circulante (1.1.2.1)</td>
                            <td><span class="badge bg-success">SÍ EXISTE</span></td>
                            <td>Se nutre de cotizaciones aprobadas pendientes de cobro.</td>
                        </tr>
                        <tr class="table-warning">
                            <td class="fw-bold">INVERSIONES</td>
                            <td class="text-end">-</td>
                            <td>Activo Circulante (1.1.1.4)</td>
                            <td><span class="badge bg-danger">NO EXISTE</span></td>
                            <td>No hay módulo para registrar pagarés o inversiones bancarias a plazo.</td>
                        </tr>
                        <tr class="table-warning">
                            <td class="fw-bold">GASTOS POR COMPROBAR (mar-24 / Zagaza)</td>
                            <td class="text-end">$89,880.00</td>
                            <td>Activo Circulante (1.1.2.3.002)</td>
                            <td><span class="badge bg-danger">NO EXISTE</span></td>
                            <td>Son anticipos entregados a proveedores/talleres sin comprobante fiscal aún. Actualmente los gastos se registran como definitivos.</td>
                        </tr>
                        <tr class="table-warning">
                            <td class="fw-bold">EQUIPO DE TRANSPORTE Y CHASIS</td>
                            <td class="text-end">$1,653,000.00</td>
                            <td>Activo No Circulante (1.2.4.1)</td>
                            <td><span class="badge bg-secondary">PARCIAL</span></td>
                            <td>Existe el catálogo de tractos y chasis, pero NO tienen valor de adquisición contable ni depreciación acumulada.</td>
                        </tr>
                        <tr class="table-danger">
                            <td class="fw-bold">PASIVO: PRÉSTAMOS RECIBIDOS (mar-24 a jun-24, Yoshua)</td>
                            <td class="text-end">$1,846,963.34</td>
                            <td>Pasivo Circulante / LP (2.1.3 / 2.2.3)</td>
                            <td><span class="badge bg-danger">NO EXISTE</span></td>
                            <td><strong>Hallazgo crítico:</strong> El sistema tiene préstamos que la empresa <em>otorga</em> a operadores, pero <strong>NO TIENE un módulo de Préstamos o Financiamientos Recibidos por la Empresa</strong> (deudas con bancos o prestamistas como Yoshua).</td>
                        </tr>
                        <tr class="table-warning">
                            <td class="fw-bold">CUENTAS POR PAGAR ACTIVOS (Chasis, Mod Computadora AP002)</td>
                            <td class="text-end">$45,032.00</td>
                            <td>Pasivo Circulante (2.1.1.2 / 2.1.9)</td>
                            <td><span class="badge bg-danger">NO EXISTE</span></td>
                            <td>El módulo de CXP solo contempla fletes subcontratados. No permite registrar compras a crédito de chasis o equipo de cómputo (Acreedores Diversos).</td>
                        </tr>
                        <tr class="table-warning">
                            <td class="fw-bold">GASTOS PAGADOS POR ANTICIPADO (Diferidos)</td>
                            <td class="text-end">$121,223.60</td>
                            <td>Activo Circulante (1.1.5.1)</td>
                            <td><span class="badge bg-secondary">PARCIAL</span></td>
                            <td>Existe la tabla <code>gastos_diferidos_detalle</code>, pero no cuenta con amortización contable mensual automática a resultados.</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">PATRIMONIO (Resultado del Ejercicio)</td>
                            <td class="text-end">$360,216.05</td>
                            <td>Patrimonio (3.2.1 / 3.2.2)</td>
                            <td><span class="badge bg-success">SÍ EXISTE</span></td>
                            <td>Totalmente automatizado con el reporte de Balance General CONAC.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- SECCIÓN 7: COMANDOS ARTISAN Y PROCEDIMIENTO DE PRUEBAS --}}
    <div class="card shadow-sm border-0 mb-4" id="comandos-consola">
        <div class="card-header bg-white py-3 border-bottom">
            <h5 class="card-title fw-bold text-primary mb-0">
                <i class="fas fa-terminal text-info me-2"></i> 7. Comandos Artisan para Sincronización y Pruebas
            </h5>
        </div>
        <div class="card-body">
            <p class="text-muted">
                Para reconstruir la contabilidad a partir de los datos operativos existentes o limpiar para realizar pruebas controladas, se crearon dos comandos de consola:
            </p>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="p-3 border rounded bg-dark text-light h-100">
                        <h6 class="fw-bold text-success"><i class="fas fa-sync-alt me-1"></i> Sincronización Histórica</h6>
                        <pre class="bg-black text-white p-2 rounded small mb-2"><code>php artisan contabilidad:sync-historico</code></pre>
                        <p class="small text-white-50 mb-2">
                            Recorre todas las tablas operativas y genera las pólizas correspondientes con estricta partida doble:
                        </p>
                        <ul class="small text-white-50 ps-3 mb-2">
                            <li>Saldos iniciales de cuentas bancarias &rarr; Póliza de Apertura.</li>
                            <li>Cotizaciones con importe &rarr; Provisiones de Viajes (CxC).</li>
                            <li>Cobros aplicados &rarr; Recaudación en Bancos.</li>
                            <li>Gastos capturados &rarr; Provisiones de Gasto (CXP).</li>
                            <li>Pagos de gastos &rarr; Cancelación de CXP vs Bancos.</li>
                            <li>Préstamos a choferes &rarr; Deudores Diversos vs Bancos.</li>
                        </ul>
                        <span class="badge bg-secondary">Opción: --force (para reescribir)</span>
                        <span class="badge bg-secondary">Opción: --empresa=ID</span>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-3 border rounded bg-dark text-light h-100">
                        <h6 class="fw-bold text-danger"><i class="fas fa-trash-alt me-1"></i> Reinicio / Reset de Pólizas</h6>
                        <pre class="bg-black text-white p-2 rounded small mb-2"><code>php artisan contabilidad:reset-historico</code></pre>
                        <p class="small text-white-50 mb-2">
                            Elimina de forma segura las pólizas automáticas y sus asientos contables para permitir correr pruebas tantas veces como sea necesario.
                        </p>
                        <ul class="small text-white-50 ps-3 mb-2">
                            <li>Por defecto conserva las Pólizas de Apertura manuales.</li>
                            <li>Desvincula las pólizas de los registros operativos.</li>
                            <li>Permite probar variaciones de saldos y configuraciones.</li>
                        </ul>
                        <span class="badge bg-danger">Opción: --all (elimina también apertura)</span>
                        <span class="badge bg-secondary">Opción: --confirm (sin confirmación interactiva)</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
