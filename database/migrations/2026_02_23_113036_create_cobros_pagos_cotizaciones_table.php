<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cobros_pagos_cotizaciones', function (Blueprint $table) {
            $table->id();


            $table->unsignedBigInteger('cobro_pago_id');


            $table->unsignedBigInteger('cotizacion_id');


            $table->string('origen')->nullable();


            $table->decimal('monto', 15, 2);


            //  $table->unsignedBigInteger('user_id');

            $table->timestamps();


            $table->foreign('cobro_pago_id')
                  ->references('id')
                  ->on('cobros_pagos')
                  ->restrictOnDelete();

            $table->foreign('cotizacion_id')
                  ->references('id')
                  ->on('cotizaciones')
                  ->restrictOnDelete();


            $table->index(['cobro_pago_id', 'cotizacion_id']);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cobros_pagos_cotizaciones');
    }
};
