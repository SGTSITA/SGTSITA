<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('viajes_costos', function (Blueprint $table) {
            $table->id();


            $table->foreignId('viaje_id')
                ->constrained('viajes')
                ->cascadeOnDelete();


            $table->string('concepto');



            $table->decimal('monto', 10, 2)->default(0);


            $table->enum('tipo_operacion', ['cargo', 'descuento'])
                ->default('cargo');



            $table->json('meta')->nullable();


            $table->decimal('monto_cobrado', 10, 2)->default(0);
            $table->boolean('cobrado')->default(false);
            $table->timestamp('fecha_cobro')->nullable();

            $table->timestamps();


            $table->unique(['viaje_id', 'concepto']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('viajes_costos');
    }
};
