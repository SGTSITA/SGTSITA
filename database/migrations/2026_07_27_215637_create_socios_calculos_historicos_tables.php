<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('socios_calculos_periodos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_empresa')->constrained('empresas')->restrictOnDelete();
            $table->date('fecha_desde');
            $table->date('fecha_hasta');
            $table->decimal('total_utilidad_bruta_viajes', 15, 2)->default(0);
            $table->decimal('total_gastos_periodo', 15, 2)->default(0);
            $table->decimal('utilidad_neta_distribuible', 15, 2)->default(0);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['id_empresa', 'fecha_desde', 'fecha_hasta'], 'socio_periodo_idx');
        });

        Schema::create('socios_calculos_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('calculo_periodo_id')->constrained('socios_calculos_periodos')->cascadeOnDelete();
            $table->foreignId('socio_id')->constrained('socios')->cascadeOnDelete();
            $table->enum('tipo_pago', ['porcentaje', 'cuota_fija']);
            $table->decimal('valor_pactado', 15, 2);
            $table->decimal('monto_distribuido', 15, 2);
            $table->timestamps();
        });

        Schema::create('socios_calculos_viajes_historico', function (Blueprint $table) {
            $table->id();
            $table->foreignId('calculo_periodo_id')->constrained('socios_calculos_periodos')->cascadeOnDelete();
            $table->string('contenedor', 100);
            $table->string('cliente', 255)->nullable();
            $table->string('unidad', 80)->nullable();
            $table->decimal('utilidad_viaje', 15, 2)->default(0);
            $table->date('fecha_viaje');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('socios_calculos_viajes_historico');
        Schema::dropIfExists('socios_calculos_detalles');
        Schema::dropIfExists('socios_calculos_periodos');
    }
};
