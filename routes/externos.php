<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExternosController;
use App\Http\Controllers\CotizacionesController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\UserController;

Route::get('gps', function () {
    return view('gps.magnitracking');
});

Route::group(['prefix' => 'mec'], function () {
    Route::post('planeaciones/monitor/board', [ExternosController::class,'initBoard']);
    Route::post('transportistas/list', [ExternosController::class,'transportistasList']);
    Route::post('transportistas/list-local', [ExternosController::class,'transportistasListLocal']);
});

Route::group(["prefix" => "viajes"], function () {
    Route::post('selector', [ExternosController::class,'selector'])->name('viajes.selector');
    Route::get('solicitar', [ExternosController::class,'solicitarIndex'])->name('viajes.solicitar');

    Route::post('editar', [ExternosController::class,'editForm'])->name('viajes.edit-form');

    Route::get('solicitud/simple', [ExternosController::class,'solicitudSimple'])->name('viajes.simple');

    Route::get('solicitud/multiple', [ExternosController::class,'solicitudMultiple'])->name('viajes.multiple');
    Route::post('solicitud/multiple', [CotizacionesController::class,'storeMultiple'])->name('viajes.multiple.create');

    Route::get('documents', [ExternosController::class,'viajesDocuments'])->name('viajes.documents');
    Route::post('documents/pending', [ExternosController::class,'getContenedoresPendientes'])->name('documents.pending');

    Route::post('cancelar', [ExternosController::class,'cancelarViaje'])->name('viajes.cancelar');

    Route::get('mis-viajes', [ExternosController::class,'misViajes'])->name('mis.viajes');
    Route::post('file-manager', [ExternosController::class,'fileManager'])->name('mis.file-manager');

    Route::post('file-manager/cfdi-files', [ExternosController::class,'CfdiToZip'])->name('cfdi.file-manager');
    Route::get('file-manager/cfdi-files/{zipFile}', [ExternosController::class,'ZipDownload'])->name('cfdi.file-manager');
    Route::get('file-manager/get-file-list/{numContenedor}', [ExternosController::class,'getFilesProperties'])->name('viajes.files');

    Route::post('/get-asignables', [ExternosController::class,'getContenedoresAsignables'])->name('viajes.asignables');

    //solicitud viaje local
    Route::get('/viajes-local', [ExternosController::class,'solicitarIndexlocal'])->name('viajes.local');


    Route::post('selector-local', [ExternosController::class,'selectorlocal'])->name('viajes.selectorlocal');
    Route::post('editar-local', [ExternosController::class,'editFormlocal'])->name('viajes.edit-formlocal');
    Route::get('solicitud/simple-local', [ExternosController::class,'solicitudSimplelocal'])->name('viajes.simplelocal');
    Route::get('mis-viajes-local', [ExternosController::class,'misViajeslocal'])->name('mis.viajeslocal');
    Route::get('/patio-local', [ExternosController::class,'listPatio'])->name('mis.patiolocal');
    Route::post('documents/pending-local-patio', [ExternosController::class,'getlistPatio'])->name('lista.patiolocal');

    Route::get('solicitud/multiple-local', [ExternosController::class,'solicitudMultiplelocal'])->name('viajes.multiplelocal');
    Route::post('solicitud/multiple-local', [CotizacionesController::class,'storeMultiplelocal'])->name('viajes.multiple.local');

    Route::get('documents-local', [ExternosController::class,'viajesDocumentslocal'])->name('viajes.documentslocal');
    Route::post('documents/pending-local', [ExternosController::class,'getContenedoreslocalesPendientes'])->name('documents.pendinglocal');
    Route::post('file-manager-local', [ExternosController::class,'fileManagerlocal'])->name('mis.file-managerlocal');
    Route::post('/maniobras/cambiar-estatus', [ExternosController::class,'cambiarestatuslocal'])->name('maniobras.cambiarestatuslocal');
    Route::get('/maniobras/{maniobraId}/historial-estatus', [ExternosController::class,'historialEstatus'])->name('maniobras.historial-estatus');

});

Route::group(["prefix" => "contenedores"], function () {
    Route::post('files/upload', [CotizacionesController::class, 'adjuntarDocumentos']);
    Route::get('files/get-file', [CotizacionesController::class, 'adjuntarDocumentos']);
});

Route::group(["prefix" => "clientes"], function () {
    Route::get('/crear-nuevo', [ClientController::class,'index_subcliente'])->name('subcliente.index');
    Route::get('/list', [ClientController::class,'subcliente_list'])->name('client.subcliente.list');
    Route::post('/list', [ClientController::class,'subcliente_get_list'])->name('subcliente.getlist');
    Route::post('/edit', [ClientController::class,'show_edit'])->name('subcliente.getlist');
    Route::post('/update', [ClientController::class,'update_subclientes'])->name('upadate.subcliente');
});

Route::group(['prefix' => 'manager'], function () {
    Route::get('/usuarios/crear', [UserController::class,'index_externos'])->name('usuario.create');
    Route::post('/usuarios/store', [UserController::class,'store'])->name('usuario.store');

});

// Route::post('/whatsapp/send', [WhatsAppController::class, 'enviarGrupo'])->name('whatsapp.enviarGrupo');


Route::group(['prefix' => 'coordenadas'], function () {
    //externos coordenadas MEC
    Route::get('coordenadas/extmapas', [App\Http\Controllers\CoordenadasController::class, 'extindexMapa'])->name('ver.extcoordenadamapa');
    Route::get('coordenadas/extbusqueda', [App\Http\Controllers\CoordenadasController::class, 'extindexSeach'])->name('seach.extcoordenadas');
    Route::get('coordenadas/extcompartir', [App\Http\Controllers\CoordenadasController::class, 'extcompartir'])->name('extcompartircoor');
    Route::post('coordenadas/extsearchDoctos', [App\Http\Controllers\CoordenadasController::class, 'encontrarURLfoto'])->name('extsearchDoctos');
    //ext
    Route::get('/coordenadas/exrastrear', [App\Http\Controllers\CoordenadasController::class, 'exrastrearIndex'])->name('exrastrearContenedor');
    Route::get('coordenadas/exconboys', [App\Http\Controllers\ConboysController::class, 'exindex'])->name('exindex.conboys');
    Route::get('coordenadas/conboys/ex-encontrar/', [App\Http\Controllers\ConboysController::class, 'exindexconvoy'])->name('exfind-convoy');
    Route::get('/coordenadas/ext/historialUbi', [App\Http\Controllers\ConboysController::class, 'extHistorialUbicaciones'])->name('extHistorialUbicaciones');


});


Route::middleware('auth')->group(function () {
    Route::post('/cotizacion/{id}/acceso', [App\Http\Controllers\CotizacionAccesoController::class, 'generar']);
    Route::post('/cotizacion/{id}/acceso/revocar', [App\Http\Controllers\CotizacionAccesoController::class, 'revocar']);
});

Route::group(['prefix' => 'externos'], function () {
    Route::get('/ver-documentos/{token}', [App\Http\Controllers\DocsController::class, 'formPassword']);
    Route::post('/ver-documentos/{token}', [App\Http\Controllers\DocsController::class, 'validarPassword'])->name('externos.validarPassword');
    Route::post('/acceso/validar/{token}', [App\Http\Controllers\DocsController::class, 'validarRevocacion'])->name('externos.validarRevocacion');
    Route::get('/acceso/revocado', [App\Http\Controllers\DocsController::class, 'accesoRevocado'])->name('externos.acceso.revocado');
    Route::get('/documentos/{token}/download/{docId}/{archivo}', [App\Http\Controllers\DocsController::class, 'download'])->name('externos.documentos.download');
    Route::post('/documentos/{token}/download-zip', [App\Http\Controllers\DocsController::class, 'downloadZip'])->name('externos.documentos.zip');
    Route::get('/documentos/{token}/download-full', [App\Http\Controllers\DocsController::class, 'downloadFull'])->name('externos.documentos.download.full');
});



Route::group(['prefix' => 'documentos_ext'], function () {

    Route::get('reporteria/documentos', [App\Http\Controllers\ExternosController::class, 'ext_index_documentos'])->name('ext_index_documentos.reporteria');
    Route::get('reporteria/documentos/buscador', [App\Http\Controllers\ExternosController::class, 'ext_advance_documentos'])->name('ext_advance_documentos.buscador');
    Route::post('reporteria/documentos/export', [App\Http\Controllers\ExternosController::class, 'ext_export_documentos'])->name('ext_export_documentos.export');


});
