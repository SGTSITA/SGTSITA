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
            $table->unsignedBigInteger('id_cuenta_prov')->nullable();
            $table->foreign('id_cuenta_prov')
                ->references('id')->on('cuentas_bancarias')
                ->inDelete('set null');

            $table->text('dinero_cuenta_prov')->nullable();

            $table->unsignedBigInteger('id_cuenta_prov2')->nullable();
            $table->foreign('id_cuenta_prov2')
                ->references('id')->on('cuentas_bancarias')
                ->inDelete('set null');

            $table->text('dinero_cuenta_prov2')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cotizacion', function (Blueprint $table) {
            //
        });
    }
};
