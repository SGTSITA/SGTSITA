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
        Schema::table('bitacora_viajes_operadores', function (Blueprint $table) {
            $table->timestamp('apertura_contenedor')->nullable()->after('fotos_carga');
            $table->text('fotos_apertura')->nullable()->after('apertura_contenedor');
            $table->string('latitud_apertura', 100)->nullable()->after('fotos_apertura');
            $table->string('longitud_apertura', 100)->nullable()->after('latitud_apertura');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bitacora_viajes_operadores', function (Blueprint $table) {
            $table->dropColumn([
                'apertura_contenedor',
                'fotos_apertura',
                'latitud_apertura',
                'longitud_apertura',
            ]);
        });
    }
};
