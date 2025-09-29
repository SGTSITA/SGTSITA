<?php
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\PermisosController;
use App\Http\Controllers\EmpresasController;
use App\Http\Controllers\ExternosController;
use App\Http\Controllers\CuentaGlobalController;
use App\Http\Controllers\GoogleLinkResolverController;
use App\Http\Controllers\MEP\CostosViajeMEPController;
use App\Http\Controllers\WhatsAppController;
use App\Http\Controllers\GpsController;
use App\Http\Controllers\GpsCompanyController;
use App\Http\Controllers\ContactoController;
use App\Http\Controllers\ReporteriaController;
use App\Http\Controllers\MepController;

use App\Models\User;


Route::group(["prefix" => "gps"],function(){
 Route::get('globalgps/ubicacion/by-imei/{imei}',[GpsController::class,'obtenerUbicacionByImei'])->name('ubicacion.byimei');
 Route::get('skyangel/ubicacion/',[GpsController::class,'getLocationSkyAngel'])->name('ubicacion.byimei');

 Route::get('jimi/api/test',[GpsCompanyController::class,'testGpsApi']);

 Route::get('setup',[GpsCompanyController::class,'setupGps'])->name('gps.setup');
 Route::get('config',[GpsCompanyController::class,'getConfig'])->name('gps.config');
 Route::post('config/store',[GpsCompanyController::class,'setConfig'])->name('gps.store');
});

Route::group(["prefix" => "mep"], function(){
 Route::get('viajes',[MepController::class, 'index'])->name('mep.index');
 Route::get('viajes/list',[MepController::class, 'getCotizacionesList'])->name('mep.viajes');
 Route::get('viajes/finalizadas',[MepController::class, 'getCotizacionesFinalizadas'])->name('mep.viajes');
 Route::post('viajes/operador/asignar',[MepController::class, 'asignarOperador'])->name('mep.asignaoperdor');
 Route::post('catalogos/operador-unidad',[MepController::class, 'getCatalogosMep'])->name('mep.catalogos');
});


Route::group(["prefix" => "whatsapp"],function(){
    Route::get('sendtext/{phone}/{text}',[WhatsAppController::class,'sendText'])->name('whatsapp.text');
    Route::get('webhook',[WhatsAppController::class,'webHook'])->name('whatsapp.webhook');
    Route::post('webhook',[WhatsAppController::class,'verifyWebHook'])->name('whatsapp.verify.webhook');

});


Route::post('/exportar-cxc', [ReporteriaController::class, 'export'])->name('exportar.cxc');
Route::post('sendfiles',[ExternosController::class,'sendFiles1'])->name('file-manager.sendfiles');



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
include('api.php');

Route::get('/', function () {
    return view('landing.index');
   // return view('auth.login');

});

Route::get('aviso-privacidad',function(){
    return view('landing.aviso-privacidad');
})->name('aviso-privacidad');

// =============== M O D U L O   login custom ===============================

// Route::get('dashboard', [App\Http\Controllers\CustomAuthController::class, 'dashboard']);
Route::get('login', [App\Http\Controllers\CustomAuthController::class, 'index'])->name('login');
Route::post('custom-login', [App\Http\Controllers\CustomAuthController::class, 'customLogin'])->name('login.custom');
Route::get('registration', [App\Http\Controllers\CustomAuthController::class, 'registration'])->name('register-user');
Route::post('custom-registration', [App\Http\Controllers\CustomAuthController::class, 'customRegistration'])->name('register.custom');
Route::get('signout', [App\Http\Controllers\CustomAuthController::class, 'signOut'])->name('signout');

//cambio empresa usuario 
Route::post('/cambiar-empresa', [App\Http\Controllers\UserController::class, 'cambiarEmpresa'])->name('usuario.cambiarEmpresa');
//

//google
Route::get('/auth/google', function () {
    return Socialite::driver('google')->redirect();
})->name('google.redirect');

// Callback de Google
Route::get('/auth/google/callback', function () {
    $googleUser = Socialite::driver('google')->stateless()->user();

    $user = \App\Models\User::where('email', $googleUser->getEmail())->first();

    if ($user) {
        // Solo actualiza los datos de Google, sin tocar la contraseña
        $user->update([
            'google_id' => $googleUser->getId(),
            'avatar' => $googleUser->getAvatar(),
            'name' => $googleUser->getName(),
        ]);
    } else {
        // Usuario nuevo desde Google
        $user = \App\Models\User::create([
            'email' => $googleUser->getEmail(),
            'name' => $googleUser->getName(),
            'google_id' => $googleUser->getId(),
            'avatar' => $googleUser->getAvatar(),
            'password' => Hash::make(Str::random(24)),
            'id_empresa' => 1,
            'id_cliente' => 0,
        ]);
    }

    Auth::login($user);
    return redirect('/dashboard');
});
//
Auth::routes();

Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');


// ==================== C O O R D E N A D A S ====================

Route::get('coordenadas/questions/{id}/{tc}', [App\Http\Controllers\CoordenadasController::class, 'index'])->name('index.cooredenadas');
Route::post('coordenadas/edit/{id}', [App\Http\Controllers\CoordenadasController::class, 'edit'])->name('edit.cooredenadas');
//R
Route::get('/coordenadas/cotizaciones/get/{id}', [App\Http\Controllers\CotizacionesController::class, 'getCotizacionesId']);
Route::post('coordenadas/cotizaciones/mail-coordenadas', [App\Http\Controllers\CotizacionesController::class, 'enviarCorreo'])->name('correo.CoordenadaCompartirMail');
Route::post('coordenadas/compartir/save', [App\Http\Controllers\CoordenadasController::class, 'store'])->name('guardar.CoordenadaCompartir');
Route::post('coordenadas/guardarresp', [App\Http\Controllers\CoordenadasController::class, 'guardarRespuesta'])->name('guardar.respuestaCoordenada');
Route::get('coordenadas/mapas', [App\Http\Controllers\CoordenadasController::class, 'indexMapa'])->name('ver.coordenadamapa');
Route::get('coordenadas/busqueda', [App\Http\Controllers\CoordenadasController::class, 'indexSeach'])->name('seach.coordenadas');
Route::get('/coordenadas/contenedor/search', [App\Http\Controllers\CoordenadasController::class, 'getcoorcontenedor'])->name('getcoorcontenedor');

Route::get('/coordenadas/rastrear', [App\Http\Controllers\CoordenadasController::class, 'rastrearIndex'])->name('rastrearContenedor');

Route::post('/coordenadas/archivo', [App\Http\Controllers\CoordenadasController::class, 'subirArchivo'])->name('coordenadas.archivo');
Route::get('/coordenadas/contenedor/searchEquGps', [App\Http\Controllers\CoordenadasController::class, 'getEquiposGps'])->name('getEquiposGps');
Route::post('/coordenadas/ubicacion-vehiculo', [App\Http\Controllers\GpsController::class, 'obtenerUbicacionByImei'])->name('coordenadas.ubicacion');

//coordenadas -> conboys virtuales

Route::get('coordenadas/conboys', [App\Http\Controllers\ConboysController::class, 'index'])->name('index.conboys');
Route::get('coordenadas/conboys/create', [App\Http\Controllers\ConboysController::class, 'create'])->name('create.conboys');
Route::post('coordenadas/conboys/store', [App\Http\Controllers\ConboysController::class, 'store'])->name('store.conboys');
Route::get('coordenadas/conboys/edit/{id}', [App\Http\Controllers\ConboysController::class, 'edit'])->name('edit.conboys');
Route::post('coordenadas/conboys/update', [App\Http\Controllers\ConboysController::class, 'update'])->name('update.conboys');
Route::delete('coordenadas/conboys/delete', [App\Http\Controllers\ConboysController::class, 'destroy'])->name('destroy.conboys');
Route::delete('coordenadas/conboys/eliminar-contenedor/{contenedor}/{convoy}', [App\Http\Controllers\ConboysController::class, 'eliminarContenedor']);
Route::post('/coordenadas/conboys/estatus', [App\Http\Controllers\ConboysController::class, 'updateEstatus']);

Route::get('coordenadas/conboys/getconboys', [App\Http\Controllers\ConboysController::class, 'getConboys'])->name('getConboys.conboys');
Route::get('coordenadas/conboys/getconboysFinalizados', [App\Http\Controllers\ConboysController::class, 'getconboysFinalizados'])->name('getconboysFinalizados.conboys');

Route::get('/coordenadas/conboys/getHistorialUbicaciones', [App\Http\Controllers\ConboysController::class, 'getHistorialUbicaciones'])->name('getHistorialUbicaciones.conboys');


Route::post('coordenadas/rastrear/savehistori', [App\Http\Controllers\ConboysController::class, 'guardarCoordenadasseguimintos'])->name('rastrear.savehistori');
Route::get('coordenadas/conboys/encontrar/', [App\Http\Controllers\ConboysController::class, 'indexconvoy'])->name('find-convoy');


Route::get('/coordenadas/conboys/getconvoy/{numero}', [App\Http\Controllers\ConboysController::class, 'buscarPorNumero'])->name('findbyNumber');
Route::post('/coordenadas/conboys/agregar', [App\Http\Controllers\ConboysController::class, 'addContenedores'])->name('updateConvoyEmpresas');
Route::get('/coordenadas/conboys/historialUbi', [App\Http\Controllers\ConboysController::class, 'HistorialUbicaciones'])->name('HistorialUbicaciones');

Route::post('/coordenadas/resolver-link-google', [App\Http\Controllers\GoogleLinkResolverController::class, 'resolver']);

Route::get('/mapa-comparacion', [App\Http\Controllers\ConboysController::class, 'rastreohistorialUbicaciones'])->name('rastreohistorialUbicaciones');


Route::get('/coordenadas/mapa_rastreo', function () {
    return view('coordenadas.mapa_rastreo');
});
Route::get('/configurar-geocerca', function () {
    return view('conboys.geocerca');
});
Route::get('/coordenadas/mapa_rastreo_varios', [App\Http\Controllers\ConboysController::class, 'mapaRastreoVarios'])->name('rastreoVariosConvoys');




Route::get('/scheduler/index', [App\Http\Controllers\RastreoIntervalController::class, 'index'])->name('scheduler.index');
Route::put('/scheduler/edit', [App\Http\Controllers\RastreoIntervalController::class, 'update'])->name('scheduler.update');

//NUEVO SERVICIO DE GPS 
Route::get('/gps/{imei}/detalle', [GpsController::class, 'detalleDispositivo']);

//R

//prueba refactorizacion de coordenadas

Route::get('/coordenadas/rastrearTabs', [App\Http\Controllers\CoordenadasController::class, 'RastreoTabs'])->name('rastrearTabs');


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
    Route::patch('/cuentas-bancarias/{id}/prioridad', [App\Http\Controllers\ProveedorController::class, 'definirCuentaPrioridad']);

    // ==================== E Q U I P O S ====================
     Route::get('equipos/index', [App\Http\Controllers\EquiposController::class, 'index'])->name('index.equipos');
     Route::post('equipos/create', [App\Http\Controllers\EquiposController::class, 'store'])->name('store.equipos');
     Route::patch('equipos/update/{id}', [App\Http\Controllers\EquiposController::class, 'update'])->name('update.equipos');
    Route::patch('equipos/desactivar/{id}', [App\Http\Controllers\EquiposController::class, 'desactivar'])->name('desactivar.equipos');
Route::post('/equipos/asignar-gps/{id}', [App\Http\Controllers\EquiposController::class, 'asignarGps'])->name('equipos.asignarGps');

    Route::get('/equipos/data', [App\Http\Controllers\EquiposController::class, 'data'])->name('equipos.data');


    // ==================== O P E R A D O R E S ====================
    Route::get('operadores', [App\Http\Controllers\OperadorController::class, 'index'])->name('index.operadores');
    Route::post('operadores/create', [App\Http\Controllers\OperadorController::class, 'store'])->name('store.operadores');
    Route::patch('operadores/update/{id}', [App\Http\Controllers\OperadorController::class, 'update'])->name('update.operadores');

    Route::get('operadores/show/{id}', [App\Http\Controllers\OperadorController::class, 'show'])->name('show.operadores');
    Route::patch('operadores/pago/update/{id}', [App\Http\Controllers\OperadorController::class, 'update_pago'])->name('update_pago.operadores');
    Route::get('operadores/show/pagos/{id}', [App\Http\Controllers\OperadorController::class, 'show_pagos'])->name('show_pagos.operadores');
    // Ruta para dar de baja (soft delete) a un operador
Route::delete('operadores/{id}', [App\Http\Controllers\OperadorController::class, 'destroy'])->name('operadores.destroy');

// Ruta para restaurar (reactivar) un operador
Route::post('operadores/{id}/restaurar', [App\Http\Controllers\OperadorController::class, 'restore'])->name('operadores.restore');

    // ==================== C O T I Z A C I O N E S  E X T E R N A S====================
    Route::get('/cotizaciones/index/externo', [App\Http\Controllers\CotizacionesController::class, 'index_externo'])->name('index.cotizaciones_manual');
    Route::get('/cotizaciones/solicitudes', [App\Http\Controllers\CotizacionesController::class, 'solicitudesEntrantes'])->name('cotizaciones.entrantes');
    Route::get('cotizaciones/externo/create', [App\Http\Controllers\CotizacionesController::class, 'create_externo'])->name('create.cotizaciones_externo');
    Route::get('cotizaciones/externo/edit/{id}', [App\Http\Controllers\CotizacionesController::class, 'edit_externo'])->name('edit.cotizaciones_externo');

    // ==================== C O T I Z A C I O N E S ====================
    Route::get('/cotizaciones/index', [App\Http\Controllers\CotizacionesController::class, 'index'])->name('index.cotizaciones');
    Route::get('/cotizaciones/list', [App\Http\Controllers\CotizacionesController::class, 'getCotizacionesList'])->name('cotizaciones.list');
    Route::get('/cotizaciones/by-status', [App\Http\Controllers\CotizacionesController::class, 'getCotizacionesByStatus'])->name('cotizaciones.byStatus');


    Route::get('/cotizaciones/finalizadas', [App\Http\Controllers\CotizacionesController::class, 'getCotizacionesFinalizadas']);
    Route::get('/cotizaciones/espera', [App\Http\Controllers\CotizacionesController::class, 'getCotizacionesEnEspera']);
    Route::get('/cotizaciones/aprobadas', [App\Http\Controllers\CotizacionesController::class, 'getCotizacionesAprobadas']);
    Route::get('/cotizaciones/canceladas', [App\Http\Controllers\CotizacionesController::class, 'getCotizacionesCanceladas']);

    // Route::get('/cotizaciones/get/{id}', [App\Http\Controllers\CotizacionesController::class, 'getCotizacionesId']);
    // Route::post('/cotizaciones/mail-coordenadas', [App\Http\Controllers\CotizacionesController::class, 'enviarCorreo']);
    // Route::post('/coordenadas/save', [App\Http\Controllers\CoordenadasController::class, 'store']);
    // Route::post('/guardarresp', [App\Http\Controllers\CoordenadasController::class, 'guardarRespuesta'])->name('guardar.respuesta');


    Route::get('/cotizaciones/busqueda', [App\Http\Controllers\CotizacionesController::class, 'find'])->name('busqueda.cotizaciones');
    Route::post('/cotizaciones/full', [App\Http\Controllers\CotizacionesController::class, 'cotizacionesFull'])->name('cotizaciones.full');
    Route::post('/cotizaciones/transformar/full', [App\Http\Controllers\CotizacionesController::class, 'convertirFull'])->name('cotizaciones.transform.full');

    Route::post('/cotizaciones/busqueda', [App\Http\Controllers\CotizacionesController::class, 'findExecute'])->name('exec.busqueda.cotizaciones');
    Route::get('/cotizaciones/documentos/{id}', [App\Http\Controllers\CotizacionesController::class, 'getDocumentos']);

    /*
    Route::get('/cotizaciones/index_finzaliadas', [App\Http\Controllers\CotizacionesController::class, 'index_finzaliadas'])->name('index_finzaliadas.cotizaciones');
    Route::get('/cotizaciones/index_espera', [App\Http\Controllers\CotizacionesController::class, 'index_espera'])->name('index_espera.cotizaciones');
    Route::get('/cotizaciones/index_aprobadas', [App\Http\Controllers\CotizacionesController::class, 'index_aprobadas'])->name('index_aprobadas.cotizaciones');
    Route::get('/cotizaciones/index_canceladas', [App\Http\Controllers\CotizacionesController::class, 'index_canceladas'])->name('index_canceladas.cotizaciones');
    */



    Route::get('cotizaciones/create', [App\Http\Controllers\CotizacionesController::class, 'create'])->name('create.cotizaciones');
    Route::any('cotizaciones/store', [App\Http\Controllers\CotizacionesController::class, 'store'])->name('store.cotizaciones');
    Route::any('cotizaciones/store/v2', [App\Http\Controllers\CotizacionesController::class, 'storeV2'])->name('v2store.cotizaciones');
    Route::get('cotizaciones/edit/{id}', [App\Http\Controllers\CotizacionesController::class, 'edit'])->name('edit.cotizaciones');
    Route::post('cotizaciones/update/{id}', [App\Http\Controllers\CotizacionesController::class, 'update'])->name('update.cotizaciones');
    Route::post('cotizaciones/single/update/{id}', [App\Http\Controllers\CotizacionesController::class, 'singleUpdate'])->name('update.single');


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
    Route::post('cotizaciones/gastos-operador/eliminar',[App\Http\Controllers\CotizacionesController::class, 'eliminar_gasto_operador'])->name('eliminar.gastos');




    // ==================== P L A N E A C I O N ====================
    Route::group(["prefix" => "planeaciones"], function(){
        Route::get('/', [App\Http\Controllers\PlaneacionController::class, 'index'])->name('index.planeaciones');
        Route::post('create', [App\Http\Controllers\PlaneacionController::class, 'store'])->name('store.planeaciones');
        Route::patch('update/{id}', [App\Http\Controllers\PlaneacionController::class, 'update'])->name('update.planeaciones');
        Route::get('equipos', [App\Http\Controllers\PlaneacionController::class, 'equipos'])->name('equipos.planeaciones');
        Route::post('viaje/programar', [App\Http\Controllers\PlaneacionController::class, 'asignacion'])->name('asignacion.planeaciones');

        Route::post('viaje/programa/anular', [App\Http\Controllers\PlaneacionController::class, 'anularPlaneacion'])->name('anular.planeaciones');

        Route::post('viaje/finalizar', [App\Http\Controllers\PlaneacionController::class, 'finalizarViaje'])->name('finalizar.planeaciones');
        Route::post('cambio/fecha', [App\Http\Controllers\PlaneacionController::class, 'edit_fecha'])->name('asignacion.edit_fecha');
        Route::get('buscador', [App\Http\Controllers\PlaneacionController::class, 'advance_planeaciones'])->name('advance_planeaciones.buscador');
    
        Route::get('buscador/faltantes', [App\Http\Controllers\PlaneacionController::class, 'advance_planeaciones_faltantes'])->name('advance_planeaciones_faltantes.buscador');
        Route::post('monitor/board',[App\Http\Controllers\PlaneacionController::class, 'initBoard'])->name('planeacion.board');
        Route::post('monitor/board/info-viaje',[App\Http\Controllers\PlaneacionController::class, 'infoViaje'])->name('planeacion.info');
        Route::get('/programar-viaje',[App\Http\Controllers\PlaneacionController::class, 'programarViaje'])->name('planeacion.programar');
    });
   

    // ==================== B A N C O S ====================


    Route::group(['prefix'=>'bancos','middleware' => 'finanzas:3'],function(){
        Route::get('/', [App\Http\Controllers\BancosController::class, 'index'])->name('index.bancos')->middleware('finanzas:3');
        Route::post('/create', [App\Http\Controllers\BancosController::class, 'store'])->name('store.bancos');
        Route::get('list', [App\Http\Controllers\BancosController::class, 'list'])->name('list.bancos');
        Route::patch('/update/{id}', [App\Http\Controllers\BancosController::class, 'update'])->name('update.bancos');

        Route::post('/movimientos/registrar', [App\Http\Controllers\BancosController::class, 'registrar_movimiento'])->name('movimientos.bancos');
    
        Route::get('/show/{id}', [App\Http\Controllers\BancosController::class, 'edit'])->name('edit.bancos');
        Route::get('/imprimir/{id}', [App\Http\Controllers\BancosController::class, 'pdf'])->name('pdf.print_banco');
        Route::get('/buscador/{id}', [App\Http\Controllers\BancosController::class, 'advance_bancos'])->name('advance_bancos.buscador');
        Route::put('/bancos/{id}/estado', [App\Http\Controllers\BancosController::class, 'cambiarEstado'])->name('bancos.estado');
        Route::post('/cambiar-cuenta-global/{id}', [App\Http\Controllers\BancosController::class, 'cambiarCuentaGlobal'])->name('bancos.cambiarCuentaGlobal');
        Route::post('/cambiar-banco1/{id}', [App\Http\Controllers\BancosController::class, 'cambiarBanco1'])->name('bancos.cambiarBanco1');
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
    Route::get('/reporteria/viajes/data', [App\Http\Controllers\ReporteriaController::class, 'getViajesFiltrados'])->name('viajes.data');



    Route::get('reporteria/utilidad', [App\Http\Controllers\ReporteriaController::class, 'index_utilidad'])->name('index_utilidad.reporteria')->middleware('finanzas:3');
    Route::post('reporteria/utilidad/ver-utilidad' ,[App\Http\Controllers\ReporteriaController::class, 'getContenedorUtilidad']);
    Route::get('reporteria/utilidad/buscador', [App\Http\Controllers\ReporteriaController::class, 'advance_utilidad'])->name('advance_utilidad.buscador')->middleware('finanzas:3');
    Route::post('reporteria/utilidad/export', [App\Http\Controllers\ReporteriaController::class, 'export_utilidad'])->name('export_utilidad.export')->middleware('finanzas:3');
    Route::post('/reporteria/utilidad/descargar-gastos', [App\Http\Controllers\ReporteriaController::class, 'descargarGastos']);


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

    Route::get('reporteria/gastos-pagar', [App\Http\Controllers\ReporteriaController::class, 'index_gxp'])->name('index_gxp.reporteria');
    Route::get('reporteria/gastos-pagar/data', [App\Http\Controllers\ReporteriaController::class, 'getGastosPorPagarData'])->name('gxp.data');
    Route::post('reporteria/gastos-pagar/export', [App\Http\Controllers\ReporteriaController::class, 'exportGastosPorPagar'])->name('gxp.export');



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
    Route::get('gastos/viajes',[App\Http\Controllers\GastosContenedoresController::class, 'indexGastosViaje'])->name('index.gastos_viajes');
    Route::post('gastos/viajes/list',[App\Http\Controllers\GastosContenedoresController::class, 'gastosViajesList']);
   
    Route::post('gastos/viajes/confirmar-gastos',[App\Http\Controllers\GastosContenedoresController::class, 'confirmarGastos']);
    

    Route::post('gastos/generales/get',[App\Http\Controllers\GastosGeneralesController::class, 'getGastos'])->name('get.gastos_generales');
    Route::post('gastos/generales/create', [App\Http\Controllers\GastosGeneralesController::class, 'store'])->name('store.gastos_generales');
    Route::post('gastos/generales/delete', [App\Http\Controllers\GastosGeneralesController::class, 'eliminarGasto'])->name('delete.gastos_generales');
    Route::post('gastos/diferir',[App\Http\Controllers\GastosGeneralesController::class, 'aplicarGastos'])->name('diferir.gastos_generales');
    Route::get('gastos/por-pagar',[App\Http\Controllers\GastosContenedoresController::class, 'IndexPayment'])->name('index.gastos_por_pagar');
    Route::post('gastos/getGxp',[App\Http\Controllers\GastosContenedoresController::class, 'getGxp'])->name('get.gastos_por_pagar');
    Route::post('gastos/payGxp',[App\Http\Controllers\GastosContenedoresController::class, 'PagarGastosMultiple'])->name('pay.gastos_por_pagar');
     Route::post('gastos/exportar', [App\Http\Controllers\GastosContenedoresController::class, 'exportarSeleccionados'])->name('gastos.exportar');


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

//Rotas para la gestion de cuentas bancaria global

Route::get('/cuenta-global', [App\Http\Controllers\CuentaGlobalController::class, 'show']);
Route::post('/cuenta-global/update', [App\Http\Controllers\CuentaGlobalController::class, 'update']);

    Route::get('/gps', [App\Http\Controllers\GpsCompanyController::class, 'index'])->name('gps.index');
    Route::get('/gps/data', [App\Http\Controllers\GpsCompanyController::class, 'data']);
    Route::post('/gps/store', [App\Http\Controllers\GpsCompanyController::class, 'store']);
    Route::put('/gps/{id}', [App\Http\Controllers\GpsCompanyController::class, 'update']);
    Route::delete('/gps/{id}', [App\Http\Controllers\GpsCompanyController::class, 'destroy']);
    Route::post('/gps/restore/{id}', [App\Http\Controllers\GpsCompanyController::class, 'restore']);



Route::get('/contactos', [App\Http\Controllers\ContactoController::class, 'index'])->name('contactos.index');
Route::get('/contactos/create', [App\Http\Controllers\ContactoController::class, 'create'])->name('contactos.create');
Route::post('/contactos', [App\Http\Controllers\ContactoController::class, 'store'])->name('contactos.store');
Route::get('/contactos/list', [App\Http\Controllers\ContactoController::class, 'list'])->name('contactos.list');
Route::delete('/contactos/{id}', [App\Http\Controllers\ContactoController::class, 'inactivar'])->name('contactos.inactivar');
Route::put('/contactos/{id}/restore', [App\Http\Controllers\ContactoController::class, 'activar'])->name('contactos.activar');
Route::get('/contactos/editar/{id}', [App\Http\Controllers\ContactoController::class, 'edit'])->name('contactos.edit');
Route::put('/contactos/{id}', [App\Http\Controllers\ContactoController::class, 'update'])->name('contactos.update');
Route::get('/contactos/editar/{id}', [App\Http\Controllers\ContactoController::class, 'edit'])->name('contactos.edit');



Route::put('/permisos/{id}/editar-json', [App\Http\Controllers\PermisosController::class, 'updateAjax'])->name('permisos.update.ajax');




Route::get('costos/mep/', [App\Http\Controllers\MEP\CostosViajeMEPController::class, 'index'])->name('index.costos_mep');
Route::get('costos/mep/data', [App\Http\Controllers\MEP\CostosViajeMEPController::class, 'getCostosViajes'])->name('data.costos_mep');
Route::post('costos/mep/guardar-cambio', [App\Http\Controllers\MEP\CostosViajeMEPController::class, 'guardarCambio'])->name('guardar_cambio.costos_mep');
Route::get('costos/mep/pendientes',[App\Http\Controllers\MEP\CostosViajeMEPController::class, 'getPendientes'])->name('pendientes.costos_mep');
Route::get('costos/mep/pendientes/vista', [App\Http\Controllers\MEP\CostosViajeMEPController::class, 'vistaPendientes'])->name('vista_pendientes.costos_mep');
Route::get('costos/mep/pendientes/{id}/comparacion', [App\Http\Controllers\MEP\CostosViajeMEPController::class, 'compararCostos']);
Route::get('mep/pendientes/count', [App\Http\Controllers\MEP\CostosViajeMEPController::class, 'contarPendientes'])->name('mep.pendientes.count');
Route::get('costos/mep/dashboard', [App\Http\Controllers\MEP\CostosViajeMEPController::class, 'dashboard'])->name('dashboard.costos_mep');
Route::get('costos/mep/conteos', [App\Http\Controllers\MEP\CostosViajeMEPController::class, 'contarPorEstatus']) ->name('conteos.costos_mep');
Route::post('costos/mep/pendientes/{id}/aceptar', [App\Http\Controllers\MEP\CostosViajeMEPController::class, 'aceptarCambio']);
Route::post('costos/mep/pendientes/{id}/rechazar', [App\Http\Controllers\MEP\CostosViajeMEPController::class, 'rechazarCambio']);
Route::get('costos/mep/viajes', [App\Http\Controllers\MEP\CostosViajeMEPController::class, 'vistaViajesCambios'])->name('viajes.costos_mep');
Route::get('costos/mep/cambios/data', [App\Http\Controllers\MEP\CostosViajeMEPController::class, 'getCambios'])->name('cambios.costos_mep');
Route::get('/costos/mep/cambios/{id}/detalle', [\App\Http\Controllers\MEP\CostosViajeMEPController::class, 'detalleCambio']);
Route::post('/costos/mep/cambios/{id}/reenviar', [App\Http\Controllers\MEP\CostosViajeMEPController::class, 'reenviarCambio'])->name('costos_mep.reenviar');


Route::get('reporteria/viajes-por-cobrar', [App\Http\Controllers\ReporteriaController::class, 'indexVXC'])->name('index_vxc.reporteria');
Route::get('/reporteria/viajes-por-cobrar/data', [App\Http\Controllers\ReporteriaController::class, 'dataVXC'])->name('reporteria.vxc.data');
Route::post('/reporteria/viajes-por-cobrar/exportar', [App\Http\Controllers\ReporteriaController::class, 'exportarVXC']);

