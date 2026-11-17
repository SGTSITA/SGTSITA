<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('socios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_empresa')->constrained('empresas')->restrictOnDelete();
            $table->string('nombre', 255);
            $table->string('rfc', 20)->nullable();
            $table->string('telefono', 50)->nullable();
            $table->string('email', 150)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['id_empresa', 'activo']);
        });

        Schema::create('socios_configuraciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_empresa')->constrained('empresas')->restrictOnDelete();
            $table->foreignId('socio_id')->constrained('socios')->cascadeOnDelete();
            $table->foreignId('equipo_id')->constrained('equipos')->cascadeOnDelete(); // Tractos / Camiones
            $table->enum('tipo_pago', ['porcentaje', 'cuota_fija']);
            $table->decimal('valor', 15, 2);
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['id_empresa', 'socio_id', 'equipo_id'], 'socio_config_lookup');
            $table->index(['fecha_inicio', 'fecha_fin']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('socios_configuraciones');
        Schema::dropIfExists('socios');
    }
};
