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
        Schema::create('movimientos_bancarios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_banco');
            $table->boolean('tipo_movimiento');
            $table->decimal('monto',10,2);
            $table->date('fecha_movimiento');
            $table->boolean('is_active');
            $table->timestamps();

            $table->foreign('id_banco')->references('id')->on('bancos');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('movimientos_bancarios');
    }
};
