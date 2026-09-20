<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notificacion_regla_usuarios', function (Blueprint $table) {
            $table->id();

            $table->foreignId('notificacion_regla_id')
                ->constrained('notificacion_reglas')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['notificacion_regla_id', 'user_id'], 'notif_regla_user_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificacion_regla_usuarios');
    }
};
