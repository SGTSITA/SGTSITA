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
        Schema::create('pagos_prestamos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_liquidacion');
            $table->unsignedBigInteger('id_prestamo');
            $table->decimal('saldo_anterior',10,2);
            $table->decimal('monto_pago',10,2);
            $table->timestamps();

            $table->foreign('id_liquidacion')->references('id')->on('liquidaciones');
            $table->foreign('id_prestamo')->references('id')->on('prestamos');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pagos_prestamos');
    }
};
