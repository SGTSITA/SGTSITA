<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUrlConexionToGpsCompanyTable extends Migration
{
    public function up()
    {
        Schema::table('gps_company', function (Blueprint $table) {
            $table->string('url_conexion')->nullable()->after('url');
        });
    }

    public function down()
    {
        Schema::table('gps_company', function (Blueprint $table) {
            $table->dropColumn('url_conexion');
        });
    }
}

