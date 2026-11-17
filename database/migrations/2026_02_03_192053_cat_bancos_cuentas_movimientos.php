<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('cat_bancos_cuentas_movimientos', function (Blueprint $table) {

            $table->id();


            $table->foreignId('cuenta_bancaria_id')
                ->constrained('bancos')
                ->cascadeOnDelete();


            $table->date('fecha_movimiento');
            $table->string('concepto', 355);
            $table->string('referencia', 100)->nullable();


            $table->enum('tipo', ['cargo', 'abono']);
            $table->decimal('monto', 15, 2);


            $table->enum('origen', [
                'manual',
                'banco',
                'sistema',
                'ajuste',
                'importacion'
            ])->default('manual');


            $table->string('referenciaable_type')->nullable();
            $table->unsignedBigInteger('referenciaable_id')->nullable();

            $table->index(
                ['referenciaable_type', 'referenciaable_id'],
                'mov_ref_index'
            );

            $table->boolean('cancelado')->default(false);
            $table->timestamp('fecha_cancelacion')->nullable();

            // 👤 Usuario
            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();


            $table->text('observaciones')->nullable();

            $table->timestamps();


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
