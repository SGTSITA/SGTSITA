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
        Schema::create('comprobantes_gastos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_asignacion')->nullable();
            $table->foreign('id_asignacion')
                ->references('id')->on('asignaciones')
                ->inDelete('set null');

            $table->text('imagen')->nullable();
            $table->text('tipo')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('comprobantes_gastos');
    }
};
