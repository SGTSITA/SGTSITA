<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('cat_bancos_cuentas_movimientos', function (Blueprint $table) {

            $table->id();

            // 🔗 Cuenta bancaria
            $table->foreignId('cuenta_bancaria_id')
                ->constrained('bancos')
                ->cascadeOnDelete();

            // 📅 Datos del movimiento
            $table->date('fecha_movimiento');
            $table->string('concepto', 355);
            $table->string('referencia', 100)->nullable();

            // 💸 Tipo y monto
            $table->enum('tipo', ['cargo', 'abono']);
            $table->decimal('monto', 15, 2);

            // 🔍 Origen del movimiento
            $table->enum('origen', [
                'manual',
                'banco',
                'sistema',
                'ajuste',
                'importacion'
            ])->default('manual');

            // 🔗 Referencia polimórfica (cotización, planeación, liquidación, etc.)
            $table->nullableMorphs('referenciaable');
            // crea referenciaable_id y referenciaable_type

            // 🔁 Cancelación (no se elimina)
            $table->boolean('cancelado')->default(false);
            $table->timestamp('fecha_cancelacion')->nullable();

            // 👤 Usuario
            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // 📝 Extras
            $table->text('observaciones')->nullable();

            $table->timestamps();

            // ⚡ Índices útiles
            $table->index(['fecha_movimiento', 'tipo']);
            $table->index('origen');
            $table->index('cancelado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cat_bancos_cuentas_movimientos');
    }
};
