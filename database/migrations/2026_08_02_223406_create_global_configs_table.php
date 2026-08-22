<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
     public function up()
    {
        Schema::create('global_configs', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // Ej: 'operador_documentos'
            $table->text('value')->nullable(); // Ej: '["Doda", "Boleta liberacion"]'
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('global_configs');
    }
};
