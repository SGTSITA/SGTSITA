<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('estado_cuenta_cotizaciones', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cotizacion_id')
                ->constrained('cotizaciones')
                ->cascadeOnDelete();

            $table->foreignId('estado_cuenta_id')
                ->constrained('estado_cuenta');

            $table->foreignId('assigned_by')
                ->constrained('users');

            $table->timestamps();


            $table->unique('cotizacion_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estado_cuenta_cotizaciones');
    }
};
