<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('conboys_contenedores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conboy_id')->constrained('conboys')->onDelete('cascade');
            $table->string('id_contenedor');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conboys_contenedores');
    }
};