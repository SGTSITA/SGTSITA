<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('operadores', function (Blueprint $table) {

            $table->dropUnique('operadores_curp_unique');


            $table->unique(['curp', 'id_empresa'], 'operadores_curp_empresa_unique');
        });
    }

    public function down()
    {
        Schema::table('operadores', function (Blueprint $table) {

            $table->dropUnique('operadores_curp_empresa_unique');
            $table->unique('curp', 'operadores_curp_unique');
        });
    }
};
