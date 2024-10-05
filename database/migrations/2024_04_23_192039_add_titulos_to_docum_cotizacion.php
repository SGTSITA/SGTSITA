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
        Schema::table('docum_cotizacion', function (Blueprint $table) {
            $table->text('num_boleta_liberacion')->nullable();
            $table->text('num_doda')->nullable();
            $table->text('num_carta_porte')->nullable();
            $table->text('boleta_vacio')->nullable();
            $table->date('fecha_boleta_vacio')->nullable();
            $table->text('eir')->nullable();
            $table->text('doc_eir')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('docum_cotizacion', function (Blueprint $table) {
            //
        });
    }
};
