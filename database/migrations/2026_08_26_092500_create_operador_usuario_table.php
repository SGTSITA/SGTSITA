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
        Schema::create('operador_usuario', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('id_operador');
            $table->timestamps();

            // Claves foráneas y unicidad para evitar duplicados
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('id_operador')->references('id')->on('operadores')->onDelete('cascade');
            $table->unique(['user_id', 'id_operador']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('operador_usuario');
    }
};
