<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('notificacion_tipo_id')
                ->nullable()
                ->constrained('notificacion_tipos')
                ->nullOnDelete();

            $table->foreignId('empresa_id')
                ->nullable()
                ->constrained('empresas')
                ->nullOnDelete();

            $table->string('titulo');
            $table->text('mensaje')->nullable();


            $table->string('modelo_type')->nullable();
            $table->unsignedBigInteger('modelo_id')->nullable();

            $table->string('url')->nullable();

            $table->json('data')->nullable();


            $table->timestamp('leida_at')->nullable();


            $table->timestamp('vista_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'leida_at']);
            $table->index(['empresa_id', 'leida_at']);
            $table->index(['notificacion_tipo_id']);
            $table->index(['modelo_type', 'modelo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificaciones');
    }
};
