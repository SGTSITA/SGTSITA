<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoordenadasHistorialTable extends Migration
{
    public function up()
    {
        Schema::create('coordenadas_historial', function (Blueprint $table) {
            $table->id();
            $table->morphs('ubicacionable'); //id convoy o id contenedor 
            $table->string('tipo'); //imei global, imei skyangel(servicios) o convoys 
            $table->decimal('latitud', 10, 7);
            $table->decimal('longitud', 10, 7);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamp('registrado_en')->useCurrent();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('coordenadas_historial');
    }
}
