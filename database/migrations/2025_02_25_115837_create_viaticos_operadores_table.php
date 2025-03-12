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
        Schema::create('viaticos_operadores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_cotizacion');
            $table->string('descripcion_gasto');
            $table->decimal('monto', 10, 2);
            $table->date('fecha_comprobante')->nullable();
            $table->string('comprobante')->nullable();
            $table->timestamps();

            $table->foreign('id_cotizacion')->references('id_cotizacion')->on('docum_cotizacion');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('viaticos_operadores');
    }
};
