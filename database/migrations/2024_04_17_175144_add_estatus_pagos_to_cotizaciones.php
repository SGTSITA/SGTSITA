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
            $table->text('carta_porte')->nullable();
            $table->text('estatus_pago')->nullable();
            $table->float('restante')->nullable();
            $table->float('monto1')->nullable();
            $table->text('metodo_pago1')->nullable();
            $table->text('comprobante_pago1')->nullable();
            $table->float('monto2')->nullable();
            $table->text('metodo_pago2')->nullable();
            $table->text('comprobante_pago2')->nullable();

            $table->unsignedBigInteger('id_subcliente')->nullable();
            $table->foreign('id_subcliente')
                ->references('id')->on('subclientes')
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
