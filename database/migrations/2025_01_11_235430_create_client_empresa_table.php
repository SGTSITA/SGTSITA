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
        Schema::create('client_empresa', function (Blueprint $table) {
            $table->unsignedBigInteger('id_client')->nullable();
            $table->unsignedBigInteger('id_empresa')->nullable();

            $table->foreign('id_client')->references('id')->on('clients');
            $table->foreign('id_empresa')->references('id')->on('empresas');

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
        Schema::dropIfExists('client_empresa');
    }
};
