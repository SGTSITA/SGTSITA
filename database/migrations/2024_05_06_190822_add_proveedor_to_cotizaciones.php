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
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->float('prove_restante')->nullable();
            $table->float('prove_monto1')->nullable();
            $table->text('prove_metodo_pago1')->nullable();
            $table->text('prove_comprobante_pago1')->nullable();

            $table->float('prove_monto2')->nullable();
            $table->text('prove_metodo_pago2')->nullable();
            $table->text('prove_comprobante_pago2')->nullable();

            $table->unsignedBigInteger('id_prove_banco1')->nullable();
            $table->foreign('id_prove_banco1')
                ->references('id')->on('bancos')
                ->inDelete('set null');

            $table->unsignedBigInteger('id_prove_banco2')->nullable();
            $table->foreign('id_prove_banco2')
                ->references('id')->on('bancos')
                ->inDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cotizaciones', function (Blueprint $table) {
            //
        });
    }
};
