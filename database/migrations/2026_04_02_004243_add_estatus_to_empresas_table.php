<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->tinyInteger('estatus')
                  ->default(1)
                  ->after('id_sat_regimen');
        });
    }

    public function down()
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn('estatus');
        });
    }
};
