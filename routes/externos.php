<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExternosController;
use App\Http\Controllers\CotizacionesController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\UserController;
Route::get('gps',function(){
 return view('gps.magnitracking');
});



Route::group(["prefix" => "viajes"], function(){
    Route::post('selector', [ExternosController::class,'selector'])->name('viajes.selector');
    Route::get('solicitud/simple',[ExternosController::class,'solicitudSimple'])->name('viajes.simple');

    Route::get('solicitud/multiple',[ExternosController::class,'solicitudMultiple'])->name('viajes.multiple');
    Route::post('solicitud/multiple',[CotizacionesController::class,'storeMultiple'])->name('viajes.multiple.create');

    Route::get('documents',[ExternosController::class,'viajesDocuments'])->name('viajes.documents');
    Route::post('documents/pending',[ExternosController::class,'getContenedoresPendientes'])->name('documents.pending');

    Route::post('cancelar',[ExternosController::class,'cancelarViaje'])->name('viajes.cancelar');

    Route::get('mis-viajes',[ExternosController::class,'misViajes'])->name('mis.viajes');
    Route::post('file-manager',[ExternosController::class,'fileManager'])->name('mis.file-manager');
   

    Route::post('file-manager/cfdi-files',[ExternosController::class,'CfdiToZip'])->name('cfdi.file-manager');
    Route::get('file-manager/cfdi-files/{zipFile}',[ExternosController::class,'ZipDownload'])->name('cfdi.file-manager');
    Route::get('file-manager/get-file-list/{numContenedor}',[ExternosController::class,'getFilesProperties'])->name('viajes.files');

    Route::post('/get-asignables',[ExternosController::class,'getContenedoresAsignables'])->name('viajes.asignables');
});

Route::group(["prefix" => "contenedores"], function(){
    Route::post('files/upload',[CotizacionesController::class, 'adjuntarDocumentos']);
});

Route::group(["prefix" => "clientes"], function(){
    Route::get('/crear-nuevo',[ClientController::class,'index_subcliente'])->name('subcliente.index');
    Route::get('/list',[ClientController::class,'subcliente_list'])->name('client.subcliente.list');
    Route::post('/list',[ClientController::class,'subcliente_get_list'])->name('subcliente.getlist');
    Route::post('/edit',[ClientController::class,'show_edit'])->name('subcliente.getlist');
    Route::post('/update',[ClientController::class,'update_subclientes'])->name('upadate.subcliente');
});

Route::group(['prefix' => 'manager'], function(){
    Route::get('/usuarios/crear',[UserController::class,'index_externos'])->name('usuario.create');
    Route::post('/usuarios/store',[UserController::class,'store'])->name('usuario.store');

});


Route::group(['prefix' => 'coordenadas'], function(){
    //externos coordenadas MEC
Route::get('coordenadas/extmapas', [App\Http\Controllers\CoordenadasController::class, 'extindexMapa'])->name('ver.extcoordenadamapa');
Route::get('coordenadas/extbusqueda', [App\Http\Controllers\CoordenadasController::class, 'extindexSeach'])->name('seach.extcoordenadas');
Route::get('coordenadas/extcompartir', [App\Http\Controllers\CoordenadasController::class, 'extcompartir'])->name('extcompartircoor');

   

});