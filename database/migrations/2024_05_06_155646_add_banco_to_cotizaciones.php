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
            $table->unsignedBigInteger('id_banco1')->nullable();
            $table->foreign('id_banco1')
                ->references('id')->on('bancos')
                ->inDelete('set null');

            $table->unsignedBigInteger('id_banco2')->nullable();
            $table->foreign('id_banco2')
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
