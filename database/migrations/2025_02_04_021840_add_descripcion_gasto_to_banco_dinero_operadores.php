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
        Schema::table('banco_dinero_operadores', function (Blueprint $table) {
            $table->string('descripcion_gasto')->after('id_banco1');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('banco_dinero_operadores', function (Blueprint $table) {
            //
        });
    }
};
