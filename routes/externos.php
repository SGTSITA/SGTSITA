<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExternosController;
use App\Http\Controllers\CotizacionesController;
use App\Http\Controllers\ClientController;

Route::group(["prefix" => "viajes"], function(){
    Route::post('selector', [ExternosController::class,'selector'])->name('viajes.selector');
    Route::get('solicitud/simple',[ExternosController::class,'solicitudSimple'])->name('viajes.simple');

    Route::get('solicitud/multiple',[ExternosController::class,'solicitudMultiple'])->name('viajes.multiple');
    Route::post('solicitud/multiple',[CotizacionesController::class,'storeMultiple'])->name('viajes.multiple.create');

    Route::get('documents',[ExternosController::class,'viajesDocuments'])->name('viajes.documents');
    Route::post('documents/pending',[ExternosController::class,'getContenedoresPendientes'])->name('documents.pending');
});

Route::group(["prefix" => "contenedores"], function(){
    Route::post('files/upload',[CotizacionesController::class, 'adjuntarDocumentos']);
});

Route::group(["prefix" => "clientes"], function(){
    Route::get('/crear-nuevo',[ClientController::class,'index_subcliente'])->name('subcliente.index');
});