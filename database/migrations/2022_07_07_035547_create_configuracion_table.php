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
        Schema::create('configuracion', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_sistema');
            $table->string('color_principal');
            $table->string('logo', 900)->nullable();
            $table->string('favicon', 900)->nullable();
            $table->string('color_iconos_sidebar')->nullable();
            $table->string('color_iconos_cards')->nullable();
            $table->string('color_boton_add')->nullable();
            $table->string('icon_boton_add')->nullable();
            $table->string('color_boton_save')->nullable();
            $table->string('icon_boton_save')->nullable();
            $table->string('color_boton_close')->nullable();
            $table->string('icon_boton_close')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('configuracion');
    }
};
