<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('gasto_conceptos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categoria_gasto_id')
                ->nullable()
                ->constrained('categorias_gastos')
                ->nullOnDelete();
            $table->string('nombre', 150);
            $table->string('clave', 80)->nullable();
            $table->enum('tipo_default', [
                'general',
                'periodo',
                'unidad',
                'viaje',
                'cotizacion',
                'contenedor',
                'operador',
            ])->default('general');
            $table->boolean('afecta_utilidad')->default(true);
            $table->boolean('permite_diferir')->default(false);
            $table->boolean('es_recuperable_cliente')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('tipo_default');
            $table->unique(['categoria_gasto_id', 'clave'], 'gasto_conceptos_categoria_clave_unique');
        });

        Schema::create('gastos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_empresa')
                ->constrained('empresas')
                ->restrictOnDelete();
            $table->foreignId('categoria_gasto_id')
                ->nullable()
                ->constrained('categorias_gastos')
                ->nullOnDelete();
            $table->foreignId('gasto_concepto_id')
                ->nullable()
                ->constrained('gasto_conceptos')
                ->nullOnDelete();
            $table->string('folio', 80)->nullable();
            $table->string('concepto', 255);
            $table->text('descripcion')->nullable();
            $table->decimal('monto_total', 15, 2)->default(0);
            $table->string('moneda', 10)->default('MXN');
            $table->date('fecha_gasto');
            $table->date('fecha_operacion')->nullable();
            $table->enum('tipo_gasto', [
                'general',
                'periodo',
                'unidad',
                'viaje',
                'cotizacion',
                'contenedor',
                'operador',
            ])->default('general');
            $table->enum('metodo_imputacion', [
                'periodo',
                'directo',
                'prorrateo',
                'manual',
                'solo_referencia',
                'diferido',
            ])->default('directo');
            $table->enum('estatus', [
                'borrador',
                'pendiente_pago',
                'pagado_parcial',
                'pagado',
                'cancelado',
            ])->default('pendiente_pago');
            $table->enum('origen_modulo', [
                'manual',
                'gasto_general',
                'gasto_extra',
                'liquidacion_operador',
                'comprobacion_operador',
                'migracion',
            ])->default('manual');
            $table->string('origen_legacy', 100)->nullable();
            $table->unsignedBigInteger('origen_legacy_id')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['id_empresa', 'fecha_gasto'], 'gastos_empresa_fecha_index');
            $table->index('tipo_gasto');
            $table->index('metodo_imputacion');
            $table->index('estatus');
            $table->unique(['origen_legacy', 'origen_legacy_id'], 'gastos_legacy_unique');
        });

        Schema::create('gasto_partidas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gasto_id')->constrained('gastos')->cascadeOnDelete();
            $table->foreignId('categoria_gasto_id')
                ->nullable()
                ->constrained('categorias_gastos')
                ->nullOnDelete();
            $table->foreignId('gasto_concepto_id')
                ->nullable()
                ->constrained('gasto_conceptos')
                ->nullOnDelete();
            $table->string('concepto', 255);
            $table->text('descripcion')->nullable();
            $table->decimal('monto', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('gasto_imputaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gasto_id')->constrained('gastos')->cascadeOnDelete();
            $table->foreignId('gasto_partida_id')
                ->nullable()
                ->constrained('gasto_partidas')
                ->nullOnDelete();
            $table->unsignedBigInteger('periodo_id')->nullable();
            $table->date('fecha_imputacion');
            $table->enum('tipo_imputacion', [
                'periodo',
                'unidad',
                'viaje',
                'cotizacion',
                'contenedor',
                'operador',
                'empresa',
            ]);
            $table->string('imputable_type', 150)->nullable();
            $table->unsignedBigInteger('imputable_id')->nullable();
            $table->decimal('monto_imputado', 15, 2)->default(0);
            $table->enum('origen', [
                'directo',
                'manual',
                'prorrateo',
                'diferido',
                'comprobacion',
                'liquidacion',
            ])->default('directo');
            $table->timestamps();

            $table->index('periodo_id');
            $table->index('fecha_imputacion');
            $table->index('tipo_imputacion');
            $table->index(['imputable_type', 'imputable_id'], 'gasto_imputaciones_polymorphic_index');
        });

        Schema::create('gasto_programaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gasto_id')->constrained('gastos')->cascadeOnDelete();
            $table->unsignedBigInteger('periodo_id')->nullable();
            $table->unsignedInteger('numero_periodo')->default(1);
            $table->date('fecha_programada');
            $table->date('fecha_vencimiento')->nullable();
            $table->decimal('monto_programado', 15, 2)->default(0);
            $table->decimal('monto_pagado', 15, 2)->default(0);
            $table->foreignId('gasto_imputacion_id')
                ->nullable()
                ->constrained('gasto_imputaciones')
                ->nullOnDelete();
            $table->enum('estatus', [
                'pendiente',
                'parcial',
                'pagado',
                'vencido',
                'cancelado',
            ])->default('pendiente');
            $table->timestamps();

            $table->index('periodo_id');
            $table->index('fecha_programada');
            $table->index('estatus');
        });

        Schema::create('gasto_pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gasto_id')->constrained('gastos')->cascadeOnDelete();
            $table->foreignId('gasto_programacion_id')
                ->nullable()
                ->constrained('gasto_programaciones')
                ->nullOnDelete();
            $table->foreignId('cuenta_bancaria_id')
                ->nullable()
                ->constrained('bancos')
                ->nullOnDelete();
            $table->foreignId('movimiento_bancario_id')
                ->nullable()
                ->constrained('cat_bancos_cuentas_movimientos')
                ->nullOnDelete();
            $table->date('fecha_pago');
            $table->decimal('monto', 15, 2)->default(0);
            $table->string('metodo_pago', 100)->nullable();
            $table->string('referencia', 100)->nullable();
            $table->string('comprobante', 255)->nullable();
            $table->enum('estatus', ['pendiente', 'aplicado', 'cancelado'])->default('aplicado');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index('fecha_pago');
            $table->index('estatus');
        });

        Schema::create('gasto_recuperaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gasto_id')->constrained('gastos')->cascadeOnDelete();
            $table->foreignId('cotizacion_id')
                ->nullable()
                ->constrained('cotizaciones')
                ->nullOnDelete();
            $table->foreignId('docum_cotizacion_id')
                ->nullable()
                ->constrained('docum_cotizacion')
                ->nullOnDelete();
            $table->string('concepto', 255);
            $table->decimal('monto_costo', 15, 2)->default(0);
            $table->decimal('monto_cobrar', 15, 2)->default(0);
            $table->enum('estatus_cobro', [
                'pendiente',
                'facturado',
                'cobrado',
                'cancelado',
            ])->default('pendiente');
            $table->timestamps();

            $table->index('estatus_cobro');
        });

        Schema::create('gasto_vinculos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gasto_id')->constrained('gastos')->cascadeOnDelete();
            $table->foreignId('gasto_partida_id')
                ->nullable()
                ->constrained('gasto_partidas')
                ->cascadeOnDelete();
            $table->enum('tipo_vinculo', [
                'periodo',
                'unidad',
                'viaje',
                'cotizacion',
                'contenedor',
                'operador',
                'asignacion',
            ]);
            $table->string('vinculable_type', 150)->nullable();
            $table->unsignedBigInteger('vinculable_id')->nullable();
            $table->unsignedBigInteger('periodo_id')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index('tipo_vinculo');
            $table->index('periodo_id');
            $table->index(['vinculable_type', 'vinculable_id'], 'gasto_vinculos_polymorphic_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gasto_vinculos');
        Schema::dropIfExists('gasto_recuperaciones');
        Schema::dropIfExists('gasto_pagos');
        Schema::dropIfExists('gasto_programaciones');
        Schema::dropIfExists('gasto_imputaciones');
        Schema::dropIfExists('gasto_partidas');
        Schema::dropIfExists('gastos');
        Schema::dropIfExists('gasto_conceptos');
    }
};
