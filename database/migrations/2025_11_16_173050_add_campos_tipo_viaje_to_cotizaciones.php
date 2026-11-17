<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->enum('tipo_viaje_seleccion', ['local', 'foraneo', 'local_to_foraneo'])
                  ->default('foraneo')
                  ->after('cp_comentarios'); // Ubícalo donde te convenga
        });
    }

    public function down()
    {
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->dropColumn('tipo_viaje_seleccion');
        });
    }
};