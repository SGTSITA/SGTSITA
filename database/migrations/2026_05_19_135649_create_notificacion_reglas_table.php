<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notificacion_reglas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('notificacion_tipo_id')
                ->constrained('notificacion_tipos')
                ->cascadeOnDelete();

            $table->foreignId('empresa_id')
                ->nullable()
                ->constrained('empresas')
                ->nullOnDelete();


            $table->boolean('notificar_empresa')->default(false);
            $table->boolean('notificar_cliente')->default(false);
            $table->boolean('notificar_proveedor')->default(false);

                      $table->boolean('activo')->default(true);

            $table->timestamps();

            $table->index(['notificacion_tipo_id', 'empresa_id']);
            $table->index('activo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificacion_reglas');
    }
};
