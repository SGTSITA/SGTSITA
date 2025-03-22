<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\PermisosController;
use App\Http\Controllers\EmpresasController;
use App\Http\Controllers\ExternosController;

Route::post('/exportar-cxc', [ReporteriaController::class, 'export'])->name('exportar.cxc');
Route::post('sendfiles',[ExternosController::class,'sendFiles1'])->name('file-manager.sendfiles');

use App\Http\Controllers\ReporteriaController;

// Ruta para mostrar el formulario de búsqueda
Route::get('/reporteria', [ReporteriaController::class, 'index'])->name('reporteria.index');

// Ruta para manejar la búsqueda de cotizaciones
Route::get('/reporteria/advance', [ReporteriaController::class, 'advance'])->name('reporteria.advance');

Route::get('/reporteria/cxp/advance', [ReporteriaController::class, 'advance_cxp'])->name('ruta_advance_cxp');

Route::get('exportar-cxc', [ReporteriaController::class, 'exportarCxc']);

Route::post('/exportar-cxc', [ReporteriaController::class, 'export'])->name('exportar.cxc');


//////////////
/*Route::get('/index-cxc', function () {
    return view('reporteria.cxc.index');
})->name('ruta.index');*/
///////////////

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
include('externos.php');

Route::get('/', function () {
    return view('auth.login');
});

// =============== M O D U L O   login custom ===============================

// Route::get('dashboard', [App\Http\Controllers\CustomAuthController::class, 'dashboard']);
Route::get('login', [App\Http\Controllers\CustomAuthController::class, 'index'])->name('login');
Route::post('custom-login', [App\Http\Controllers\CustomAuthController::class, 'customLogin'])->name('login.custom');
Route::get('registration', [App\Http\Controllers\CustomAuthController::class, 'registration'])->name('register-user');
Route::post('custom-registration', [App\Http\Controllers\CustomAuthController::class, 'customRegistration'])->name('register.custom');
Route::get('signout', [App\Http\Controllers\CustomAuthController::class, 'signOut'])->name('signout');


Auth::routes();

Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');


// ==================== C O O R D E N A D A S ====================

Route::get('coordenadas/{id}', [App\Http\Controllers\CoordenadasController::class, 'index'])->name('index.cooredenadas');
Route::post('coordenadas/edit/{id}', [App\Http\Controllers\CoordenadasController::class, 'edit'])->name('edit.cooredenadas');

Route::group(['middleware' => ['auth']], function() {
    Route::resource('roles', RoleController::class);
    Route::resource('permisos', PermisosController::class);
    Route::resource('users', UserController::class);

    // ==================== E M P R E S A S ====================
    Route::resource('empresas', EmpresasController::class);
    Route::post('empresas/create', [App\Http\Controllers\EmpresasController::class, 'store'])->name('store.empresas');
    Route::patch('empresas/update/{id}', [App\Http\Controllers\EmpresasController::class, 'update'])->name('update.empresas');

    // ==================== C L I E N T E S ====================
    Route::resource('clients', ClientController::class);
    Route::post('clients/get-list',[App\Http\Controllers\ClientController::class,'get_list'])->name('clients.get');
    Route::post('clients/create', [App\Http\Controllers\ClientController::class, 'create'])->name('create.clients');
    //
    Route::post('clients/store', [App\Http\Controllers\ClientController::class, 'store'])->name('store.clients');
    Route::post('clients/edit', [App\Http\Controllers\ClientController::class, 'edit'])->name('edit.clients');
    Route::post('clients/confirm-update', [App\Http\Controllers\ClientController::class, 'update'])->name('update.client');
   // Route::patch('clients/update/{id}', [App\Http\Controllers\ClientController::class, 'update'])->name('update.clients');
    Route::post('subclientes/crear-nuevo',[ClientController::class,'new_subcliente'])->name('new.subcliente');
    Route::post('subclientes/list',[ClientController::class,'subcliente_list_internal'])->name('subcliente.list');
    Route::get('subclientes/edit/{id}', [App\Http\Controllers\ClientController::class, 'edit_subclientes'])->name('edit_subclientes.clients');
    Route::patch('subclientes/update/{id}', [App\Http\Controllers\ClientController::class, 'update_subclientes'])->name('update_subclientes.clients');
    Route::post('clients/subclientes/create', [App\Http\Controllers\ClientController::class, 'store_subclientes'])->name('store_subclientes.clients');

    // ==================== P R O V E E D O R E S ====================
    Route::get('proveedores', [App\Http\Controllers\ProveedorController::class, 'index'])->name('index.proveedores');
    Route::get('proveedores/list', [App\Http\Controllers\ProveedorController::class, 'getProveedoresList'])->name('list.proveedores');
    Route::post('proveedores/create', [App\Http\Controllers\ProveedorController::class, 'store'])->name('store.proveedores');
    Route::post('proveedores/create/cuenta', [App\Http\Controllers\ProveedorController::class, 'cuenta'])->name('cuenta.proveedores');
    Route::patch('proveedores/update/{id}', [App\Http\Controllers\ProveedorController::class, 'update'])->name('update.proveedores');
    Route::delete('proveedores/cuentas/{id}', [App\Http\Controllers\ProveedorController::class, 'destroy'])->name('cuentas.borrar');
    Route::get('proveedores/{id}/edit', [App\Http\Controllers\ProveedorController::class, 'edit'])->name('edit.proveedores');
    Route::get('proveedores/{id}/cuentas', [App\Http\Controllers\ProveedorController::class, 'getCuentasBancarias'])->name('cuentas.proveedores');
    Route::patch('cuentas-bancarias/{id}/restore', [App\Http\Controllers\ProveedorController::class, 'restore'])->name('cuentas.restore');
    Route::patch('cuentas-bancarias/{id}/estado', [App\Http\Controllers\ProveedorController::class, 'cambiarEstadoCuenta'])->name('cambiar.estado.cuentas');
    Route::get('proveedores/validar-rfc', [App\Http\Controllers\ProveedorController::class, 'validarRFC'])->name('validar.rfc');
    Route::get('cuentas-bancarias/validar-clabe', [App\Http\Controllers\ProveedorController::class, 'validarCLABE'])->name('validar.clabe');

    // ==================== E Q U I P O S ====================
    Route::get('equipos/index', [App\Http\Controllers\EquiposController::class, 'index'])->name('index.equipos');
    Route::post('equipos/create', [App\Http\Controllers\EquiposController::class, 'store'])->name('store.equipos');
    Route::patch('equipos/update/{id}', [App\Http\Controllers\EquiposController::class, 'update'])->name('update.equipos');
    Route::patch('equipos/desactivar/{id}', [App\Http\Controllers\EquiposController::class, 'desactivar'])->name('desactivar.equipos');

    // ==================== O P E R A D O R E S ====================
    Route::get('operadores', [App\Http\Controllers\OperadorController::class, 'index'])->name('index.operadores');
    Route::post('operadores/create', [App\Http\Controllers\OperadorController::class, 'store'])->name('store.operadores');
    Route::patch('operadores/update/{id}', [App\Http\Controllers\OperadorController::class, 'update'])->name('update.operadores');

    Route::get('operadores/show/{id}', [App\Http\Controllers\OperadorController::class, 'show'])->name('show.operadores');
    Route::patch('operadores/pago/update/{id}', [App\Http\Controllers\OperadorController::class, 'update_pago'])->name('update_pago.operadores');
    Route::get('operadores/show/pagos/{id}', [App\Http\Controllers\OperadorController::class, 'show_pagos'])->name('show_pagos.operadores');

    // ==================== C O T I Z A C I O N E S  E X T E R N A S====================
    Route::get('/cotizaciones/index/externo', [App\Http\Controllers\CotizacionesController::class, 'index_externo'])->name('index.cotizaciones_manual');
    Route::get('/cotizaciones/solicitudes', [App\Http\Controllers\CotizacionesController::class, 'solicitudesEntrantes'])->name('cotizaciones.entrantes');
    Route::get('cotizaciones/externo/create', [App\Http\Controllers\CotizacionesController::class, 'create_externo'])->name('create.cotizaciones_externo');
    Route::get('cotizaciones/externo/edit/{id}', [App\Http\Controllers\CotizacionesController::class, 'edit_externo'])->name('edit.cotizaciones_externo');

    // ==================== C O T I Z A C I O N E S ====================
    Route::get('/cotizaciones/index', [App\Http\Controllers\CotizacionesController::class, 'index'])->name('index.cotizaciones');

    Route::get('/cotizaciones/busqueda', [App\Http\Controllers\CotizacionesController::class, 'find'])->name('busqueda.cotizaciones');
    Route::post('/cotizaciones/busqueda', [App\Http\Controllers\CotizacionesController::class, 'findExecute'])->name('exec.busqueda.cotizaciones');

    Route::get('/cotizaciones/index_finzaliadas', [App\Http\Controllers\CotizacionesController::class, 'index_finzaliadas'])->name('index_finzaliadas.cotizaciones');
    Route::get('/cotizaciones/index_espera', [App\Http\Controllers\CotizacionesController::class, 'index_espera'])->name('index_espera.cotizaciones');
    Route::get('/cotizaciones/index_aprobadas', [App\Http\Controllers\CotizacionesController::class, 'index_aprobadas'])->name('index_aprobadas.cotizaciones');
    Route::get('/cotizaciones/index_canceladas', [App\Http\Controllers\CotizacionesController::class, 'index_canceladas'])->name('index_canceladas.cotizaciones');



    Route::get('cotizaciones/create', [App\Http\Controllers\CotizacionesController::class, 'create'])->name('create.cotizaciones');
    Route::any('cotizaciones/store', [App\Http\Controllers\CotizacionesController::class, 'store'])->name('store.cotizaciones');
    Route::get('cotizaciones/edit/{id}', [App\Http\Controllers\CotizacionesController::class, 'edit'])->name('edit.cotizaciones');
    Route::patch('cotizaciones/update/{id}', [App\Http\Controllers\CotizacionesController::class, 'update'])->name('update.cotizaciones');

    Route::get('cotizaciones/pdf/{id}', [App\Http\Controllers\CotizacionesController::class, 'pdf'])->name('pdf.cotizaciones');

    Route::patch('cotizaciones/update/estatus/{id}', [App\Http\Controllers\CotizacionesController::class, 'update_estatus'])->name('update_estatus.cotizaciones');
    Route::patch('cotizaciones/update/tipo/{id}', [App\Http\Controllers\CotizacionesController::class, 'update_cambio'])->name('update_cambio.cotizaciones');

    Route::get('subclientes/{clienteId}', [App\Http\Controllers\CotizacionesController::class, 'getSubclientes']);

    Route::patch('cotizaciones/cambiar/empresa/{id}', [App\Http\Controllers\CotizacionesController::class, 'cambiar_empresa'])->name('cambiar_empresa.cotizaciones');
    Route::post('cotizaciones/asignar/empresa', [App\Http\Controllers\CotizacionesController::class, 'asignar_empresa'])->name('asignar_empresa.cotizaciones');
    Route::post('cotizaciones/gastos/registrar',[App\Http\Controllers\CotizacionesController::class, 'agregar_gasto_cotizacion'])->name('gastos.cotizaciones');
    Route::post('cotizaciones/gastos/get',[App\Http\Controllers\CotizacionesController::class, 'get_gastos'])->name('gastos.cotizaciones');
    Route::post('cotizaciones/gastos/eliminar',[App\Http\Controllers\CotizacionesController::class, 'eliminar_gasto_cotizacion'])->name('gastos.eliminar');

    Route::post('cotizaciones/gastos-operador/registrar',[App\Http\Controllers\CotizacionesController::class, 'agregar_gasto_operador'])->name('gastos.cotizaciones');
    Route::post('cotizaciones/gastos-operador/get',[App\Http\Controllers\CotizacionesController::class, 'get_gastos_operador'])->name('gastos.cotizaciones');
    Route::post('cotizaciones/gastos-operador/pagar',[App\Http\Controllers\CotizacionesController::class, 'pagar_gasto_operador'])->name('pagar.gastos');



    // ==================== P L A N E A C I O N ====================
    Route::get('planeaciones', [App\Http\Controllers\PlaneacionController::class, 'index'])->name('index.planeaciones');
    Route::post('planeaciones/create', [App\Http\Controllers\PlaneacionController::class, 'store'])->name('store.planeaciones');
    Route::patch('planeaciones/update/{id}', [App\Http\Controllers\PlaneacionController::class, 'update'])->name('update.planeaciones');
    Route::get('/planeaciones/equipos', [App\Http\Controllers\PlaneacionController::class, 'equipos'])->name('equipos.planeaciones');
    Route::post('planeaciones/asignacion/create', [App\Http\Controllers\PlaneacionController::class, 'asignacion'])->name('asignacion.planeaciones');
    Route::post('planeaciones/cambio/fecha', [App\Http\Controllers\PlaneacionController::class, 'edit_fecha'])->name('asignacion.edit_fecha');
    Route::get('planeaciones/buscador', [App\Http\Controllers\PlaneacionController::class, 'advance_planeaciones'])->name('advance_planeaciones.buscador');

    Route::get('planeaciones/buscador/faltantes', [App\Http\Controllers\PlaneacionController::class, 'advance_planeaciones_faltantes'])->name('advance_planeaciones_faltantes.buscador');

    // ==================== B A N C O S ====================

    Route::group(['prefix'=>'bancos','middleware' => 'finanzas:3'],function(){
        Route::get('/', [App\Http\Controllers\BancosController::class, 'index'])->name('index.bancos')->middleware('finanzas:3');
        Route::post('/create', [App\Http\Controllers\BancosController::class, 'store'])->name('store.bancos');
       // Route::get('bancos/edit/{id}', [App\Http\Controllers\BancosController::class, 'edit'])->name('edit.bancos');
        Route::patch('/update/{id}', [App\Http\Controllers\BancosController::class, 'update'])->name('update.bancos');

        Route::post('/movimientos/registrar', [App\Http\Controllers\BancosController::class, 'registrar_movimiento'])->name('movimientos.bancos');
    
        Route::get('/show/{id}', [App\Http\Controllers\BancosController::class, 'edit'])->name('edit.bancos');
        Route::get('/imprimir/{id}', [App\Http\Controllers\BancosController::class, 'pdf'])->name('pdf.print_banco');
        Route::get('/buscador/{id}', [App\Http\Controllers\BancosController::class, 'advance_bancos'])->name('advance_bancos.buscador');
        Route::put('/bancos/{id}/estado', [App\Http\Controllers\BancosController::class, 'cambiarEstado'])->name('bancos.estado');
    });
   
    // ==================== C U E N T A S  P O R  C O B R A R ====================
    Route::get('cuentas/cobrar', [App\Http\Controllers\CuentasCobrarController::class, 'index'])->name('index.cobrar');
   // Route::get('cuentas/cobrar/show/{id}', [App\Http\Controllers\CuentasCobrarController::class, 'show'])->name('show.cobrar');
    Route::get('cuentas/cobrar/show/{id}', [App\Http\Controllers\CuentasCobrarController::class, 'cobranza_v2'])->name('show.cobrar');
    Route::post('cuentas/cobrar/por_liquidar', [App\Http\Controllers\CuentasCobrarController::class, 'viajes_por_liquidar'])->name('por_liquidar.cobrar');
    Route::post('cuentas/cobrar/confirmar_pagos', [App\Http\Controllers\CuentasCobrarController::class, 'aplicar_pagos'])->name('confirmar.cobrar');
    Route::patch('cuentas/cobrar/update/{id}', [App\Http\Controllers\CuentasCobrarController::class, 'update'])->name('update.cobrar');
    Route::post('cuentas/cobrar/update/varios', [App\Http\Controllers\CuentasCobrarController::class, 'update_varios'])->name('update_varios.cobrar');

    // ==================== C U E N T A S  P O R  P A G A R ====================
    Route::get('cuentas/pagar', [App\Http\Controllers\CuentasPagarController::class, 'index'])->name('index.pagar');
    Route::get('cuentas/pagar/show/{id}', [App\Http\Controllers\CuentasPagarController::class, 'show'])->name('show.pagar');
    Route::patch('cuentas/pagar/update/{id}', [App\Http\Controllers\CuentasPagarController::class, 'update'])->name('update.pagar');
    Route::post('cuentas/pagar/update/varios', [App\Http\Controllers\CuentasPagarController::class, 'update_varios'])->name('update_varios.pagar');
    Route::post('cuentas/pagar/por_liquidar', [App\Http\Controllers\CuentasPagarController::class, 'viajes_por_liquidar'])->name('por_liquidar.pagar');
    Route::post('cuentas/pagar/confirmar_pagos', [App\Http\Controllers\CuentasPagarController::class, 'aplicar_pagos'])->name('confirmar.pagos');

    // ==================== R E P O R T E R I A ====================
    Route::get('reporteria/cotizaciones/cxc', [App\Http\Controllers\ReporteriaController::class, 'index'])->name('index.reporteria');
    Route::get('reporteria/cotizaciones/cxc/buscador', [App\Http\Controllers\ReporteriaController::class, 'advance'])->name('advance_search.buscador');
    Route::post('reporteria/cotizaciones/cxc/export', [App\Http\Controllers\ReporteriaController::class, 'export'])->name('cotizaciones.export');
    Route::post('reporteria/cotizaciones/cxc/export/excel', [App\Http\Controllers\ReporteriaController::class, 'exportExcel'])->name('cotizaciones.export-excel');

    Route::get('reporteria/cotizaciones/cxp', [App\Http\Controllers\ReporteriaController::class, 'index_cxp'])->name('index_cxp.reporteria');
    Route::get('reporteria/cotizaciones/cxp/buscador', [App\Http\Controllers\ReporteriaController::class, 'advance_cxp'])->name('advance_search_cxp.buscador');
    Route::post('reporteria/cotizaciones/cxp/export', [App\Http\Controllers\ReporteriaController::class, 'export_cxp'])->name('cotizaciones_cxp.export');
    Route::get('/subclientes/{clienteId}', [App\Http\Controllers\ReporteriaController::class, 'getSubclientes']);

    Route::get('reporteria/viajes', [App\Http\Controllers\ReporteriaController::class, 'index_viajes'])->name('index_viajes.reporteria');
    Route::get('reporteria/viajes/buscador', [App\Http\Controllers\ReporteriaController::class, 'advance_viajes'])->name('advance_viajes.buscador');
    Route::post('reporteria/viajes/export', [App\Http\Controllers\ReporteriaController::class, 'export_viajes'])->name('export_viajes.viajes');

    Route::get('reporteria/utilidad', [App\Http\Controllers\ReporteriaController::class, 'index_utilidad'])->name('index_utilidad.reporteria')->middleware('finanzas:3');
    Route::post('reporteria/utilidad/ver-utilidad' ,[App\Http\Controllers\ReporteriaController::class, 'getContenedorUtilidad']);
    Route::get('reporteria/utilidad/buscador', [App\Http\Controllers\ReporteriaController::class, 'advance_utilidad'])->name('advance_utilidad.buscador')->middleware('finanzas:3');
    Route::post('reporteria/utilidad/export', [App\Http\Controllers\ReporteriaController::class, 'export_utilidad'])->name('export_utilidad.export')->middleware('finanzas:3');

    Route::get('reporteria/documentos', [App\Http\Controllers\ReporteriaController::class, 'index_documentos'])->name('index_documentos.reporteria');
    Route::get('reporteria/documentos/buscador', [App\Http\Controllers\ReporteriaController::class, 'advance_documentos'])->name('advance_documentos.buscador');
    Route::post('reporteria/documentos/export', [App\Http\Controllers\ReporteriaController::class, 'export_documentos'])->name('export_documentos.export');

    Route::get('reporteria/liquidados/cxc', [App\Http\Controllers\ReporteriaController::class, 'index_liquidados_cxc'])->name('index_liquidados_cxc.reporteria');
    Route::get('reporteria/liquidados/cxc/buscador', [App\Http\Controllers\ReporteriaController::class, 'advance_liquidados_cxc'])->name('advance_liquidados.buscador');
    Route::post('reporteria/liquidados/cxc/export', [App\Http\Controllers\ReporteriaController::class, 'export_liquidados_cxc'])->name('liquidados_cxc.export');

    Route::get('reporteria/liquidados/cxp', [App\Http\Controllers\ReporteriaController::class, 'index_liquidados_cxp'])->name('index_liquidados_cxp.reporteria');
    Route::get('reporteria/liquidados/cxp/buscador', [App\Http\Controllers\ReporteriaController::class, 'advance_liquidados_cxp'])->name('advance_liquidados_cxp.buscador');
    Route::post('reporteria/liquidados/cxp/export', [App\Http\Controllers\ReporteriaController::class, 'export_liquidados_cxp'])->name('liquidados_cxp.export');

    Route::post('reporteria/excel/export',[App\Http\Controllers\ReporteriaController::class, 'exportGenericExcel'])->name('generic_excel');
    // ==================== L I Q U I D A C I O N E S ====================
    Route::get('liquidaciones', [App\Http\Controllers\LiquidacionesController::class, 'index'])->name('index.liquidacion');
    Route::get('liquidaciones/historial', [App\Http\Controllers\LiquidacionesController::class, 'historialPagos'])->name('historial.liquidacion');

    Route::post('liquidaciones/historial/data',[App\Http\Controllers\LiquidacionesController::class, 'historialPagosData'])->name('historialdata.liquidacion');
    Route::post('liquidaciones/historial/pagos/comprobante',[App\Http\Controllers\LiquidacionesController::class, 'comprobantePago'])->name('comprobante.liquidacion');
    Route::post('liquidaciones/viajes/pagos-operadores', [App\Http\Controllers\LiquidacionesController::class, 'getPagosOperadores'])->name('operadores.liquidacion');
    Route::post('liquidaciones/viajes/operador', [App\Http\Controllers\LiquidacionesController::class, 'getViajesOperador'])->name('operador.viajes');
    Route::post('liquidaciones/viajes/aplicar-pago', [App\Http\Controllers\LiquidacionesController::class, 'aplicarPago'])->name('pagar.viajes');
    Route::post('liquidaciones/viajes/gastos/justificar', [App\Http\Controllers\LiquidacionesController::class, 'justificarGastos'])->name('justifica.gastos');

    Route::get('liquidaciones/show/{id}', [App\Http\Controllers\LiquidacionesController::class, 'show'])->name('show.liquidacion');
    Route::patch('liquidaciones/update/{id}', [App\Http\Controllers\LiquidacionesController::class, 'update'])->name('update.liquidacion');
    Route::post('liquidaciones/update/varios', [App\Http\Controllers\LiquidacionesController::class, 'update_varios'])->name('update_varios.liquidacion');

    // ==================== G A S T O S  ====================
    Route::get('gastos/generales',[App\Http\Controllers\GastosGeneralesController::class, 'index'])->name('index.gastos_generales');
    Route::post('gastos/generales/get',[App\Http\Controllers\GastosGeneralesController::class, 'getGastos'])->name('get.gastos_generales');
    Route::post('gastos/generales/create', [App\Http\Controllers\GastosGeneralesController::class, 'store'])->name('store.gastos_generales');
    Route::post('gastos/diferir',[App\Http\Controllers\GastosGeneralesController::class, 'diferir'])->name('diferir.gastos_generales');
    Route::get('gastos/operativos',[App\Http\Controllers\GastosGeneralesController::class, 'index'])->name('index.gastos_operativos');

    // ==================== C A T A L O G O ====================
    Route::get('catalogo', [App\Http\Controllers\CatalogoController::class, 'index'])->name('index.catalogo');
    Route::get('catalogo/create', [App\Http\Controllers\CatalogoController::class, 'create'])->name('create.catalogo');
    Route::post('catalogo/store', [App\Http\Controllers\CatalogoController::class, 'store'])->name('store.catalogo');
    Route::get('catalogo/pdf/{id}', [App\Http\Controllers\CatalogoController::class, 'pdf'])->name('pdf.catalogo');
});

//Route Hooks - Do not delete//
Route::view('/especialists', 'livewire.especialists.index')->middleware('auth');

/*|--------------------------------------------------------------------------
|Configuracion
|--------------------------------------------------------------------------*/
Route::get('/configuracion/{id}', [App\Http\Controllers\ConfiguracionController::class, 'index'])->name('index.configuracion');
Route::patch('/configuracion/update/{id}', [App\Http\Controllers\ConfiguracionController::class, 'update'])->name('update.configuracion');

// En routes/web.php
Route::get('/descargar-db', [App\Http\Controllers\DatabaseController::class, 'descargarBaseDeDatos'])->name('descargar.db');

use App\Http\Controllers\CorreoController;

// Ruta para la vista principal de correos
Route::get('/correo', [App\Http\Controllers\CorreoController::class, 'index'])->name('correo.index');

Route::post('/correo', [App\Http\Controllers\CorreoController::class, 'update'])->name('correo.update');

Route::get('/configmec', [App\Http\Controllers\ConfigMecController::class, 'index'])->name('configmec');
Route::post('/configmec', [App\Http\Controllers\ConfigMecController::class, 'update'])->name('configmec.update');

