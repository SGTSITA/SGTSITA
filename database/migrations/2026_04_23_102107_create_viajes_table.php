<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('viajes', function (Blueprint $table) {
            $table->id();

            $table->enum('tipo', ['full', 'sencillo'])->default('sencillo');
            $table->enum('estado', ['activo', 'cancelado'])->default('activo');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('viajes');
    }
};
