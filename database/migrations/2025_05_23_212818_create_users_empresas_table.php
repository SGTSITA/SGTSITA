<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users_empresas', function (Blueprint $table) {
            $table->id(); // id autoincremental
            $table->unsignedBigInteger('id_user');
            $table->integer('id_empresa');
            $table->integer('empresaInicial');
            $table->timestamps(); // created_at y updated_at

            // Opcional: claves foráneas si hay relación con tabla users o empresas
             $table->foreign('id_user')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users_empresas');
    }
};

