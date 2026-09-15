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
            $table->decimal('latitud_carga', 10, 8)->nullable()->after('latitud');
            $table->decimal('longitud_carga', 11, 8)->nullable()->after('longitud');
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
            $table->dropColumn(['latitud_carga', 'longitud_carga']);
        });
    }
};
