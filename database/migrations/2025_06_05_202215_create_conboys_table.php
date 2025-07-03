<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('conboys', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->timestamp('fecha_inicio')->nullable();
             $table->timestamp('fecha_fin')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); 
            $table->timestamps(); // incluye created_at y updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conboys');
    }
};
