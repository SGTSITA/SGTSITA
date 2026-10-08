<?php
 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
 
return new class extends Migration {
    public function up(): void
    {
        Schema::create('socio_pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_empresa')->constrained('empresas')->restrictOnDelete();
            $table->foreignId('socio_id')->constrained('socios')->cascadeOnDelete();
            $table->decimal('monto', 15, 2);
            $table->foreignId('banco_id')->constrained('bancos')->restrictOnDelete();
            $table->date('fecha_aplicacion');
            $table->foreignId('calculo_periodo_id')->nullable()->constrained('socios_calculos_periodos')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
 
            $table->index(['id_empresa', 'socio_id']);
        });
    }
 
    public function down(): void
    {
        Schema::dropIfExists('socio_pagos');
    }
};
