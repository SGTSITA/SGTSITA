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
            $table->unsignedBigInteger('sat_metodo_pago_id')->nullable()->after('id_empresa');
            $table->unsignedBigInteger('sat_forma_pago_id')->nullable()->after('id_empresa');
            $table->unsignedBigInteger('sat_uso_cfdi_id')->nullable()->after('id_empresa');
            $table->boolean('uso_recinto')->nullable();
            $table->text('direccion_recinto')->nullable();
            $table->text('direccion_entrega')->nullable();

            $table->foreign('sat_metodo_pago_id')->references('id')->on('sat_metodos_pago');
            $table->foreign('sat_forma_pago_id')->references('id')->on('sat_formas_pago');
            $table->foreign('sat_uso_cfdi_id')->references('id')->on('sat_usos_cfdi');

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
