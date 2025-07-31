<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToConboysContenedoresTabl extends Migration
{
    public function up()
    {
        Schema::table('conboys_contenedores', function (Blueprint $table) {
            $table->boolean('es_primero')->default(false)->after('id_contenedor');
            $table->string('imei')->after('es_primero');
            $table->string('usuario')->after('imei');
        });
    }

    public function down()
    {
        Schema::table('conboys_contenedores', function (Blueprint $table) {
            $table->dropColumn(['es_primero', 'imei', 'usuario']);
        });
    }
}