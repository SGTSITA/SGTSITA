<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
        public function up()
        {
            Schema::table('coordenadas', function (Blueprint $table) {
                $table->string('toma_foto_patio')->nullable()->after('cargado_patio_datetime'); 
                $table->dateTime('toma_foto_patio_datetime')->nullable()->after('toma_foto_patio');
            });
        }

        public function down()
        {
            Schema::table('coordenadas', function (Blueprint $table) {
                $table->dropColumn(['toma_foto_patio', 'toma_foto_patio_datetime']);
            });
        }
};
