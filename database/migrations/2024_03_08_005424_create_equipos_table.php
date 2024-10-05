<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('equipos', function (Blueprint $table) {
            $table->id();
            $table->text('tipo')->nullable();
            $table->text('pies')->nullable();
            $table->text('marca')->nullable();
            $table->text('year')->nullable();
            $table->text('motor')->nullable();
            $table->text('num_serie')->nullable();
            $table->text('modelo')->nullable();
            $table->text('acceso')->nullable();
            $table->text('tarjeta_circulacion')->nullable();
            $table->text('poliza_seguro')->nullable();
            $table->text('folio')->nullable();
            $table->date('fecha')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('equipos');
    }
};
