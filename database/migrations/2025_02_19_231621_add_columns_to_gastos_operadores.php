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
        Schema::table('gastos_operadores', function (Blueprint $table) {
            $table->unsignedBigInteger('id_banco')->after('id_cotizacion')->nullable();
            $table->boolean('pago_inmediato')->after('comprobante')->nullable();
            $table->date('fecha_pago')->after('comprobante')->nullable();
            $table->string('estatus')->after('comprobante')->nullable();
            
            

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('gastos_operadores', function (Blueprint $table) {
            //
        });
    }
};
