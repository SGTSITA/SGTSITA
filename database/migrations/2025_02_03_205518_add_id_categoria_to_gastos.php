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
        Schema::table('gastos_generales', function (Blueprint $table) {
            if (!Schema::hasColumn('gastos_generales', 'id_categoria')) {
                $table->unsignedBigInteger('id_categoria')->nullable()->after('id_empresa'); 
                $table->foreign('id_categoria')->references('id')->on('categorias_gastos');
            }

            $table->date('fecha_operacion')->after('fecha')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('gastos_generales', function (Blueprint $table) {
            //
        });
    }
};
