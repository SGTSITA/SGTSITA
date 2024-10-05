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
        Schema::create('docum_cotizacion', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_cotizacion');
            $table->foreign('id_cotizacion')
                ->references('id')->on('cotizaciones')
                ->inDelete('set null');

            $table->text('num_contenedor')->nullable();
            $table->text('terminal')->nullable();
            $table->text('num_autorizacion')->nullable();
            $table->text('boleta_liberacion')->nullable();
            $table->text('doda')->nullable();
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
        Schema::dropIfExists('docum_cotizacion');
    }
};
