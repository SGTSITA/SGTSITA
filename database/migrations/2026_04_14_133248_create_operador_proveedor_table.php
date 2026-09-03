<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('proveedor_operador', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('operador_id');
            $table->unsignedBigInteger('proveedor_id');


            $table->unsignedBigInteger('empresa_id')->nullable();

            $table->boolean('estado')->default(1);

            $table->timestamps();


            $table->foreign('operador_id')
                ->references('id')
                ->on('operadores')
                ->onDelete('cascade');

            $table->foreign('proveedor_id')
                ->references('id')
                ->on('proveedores')
                ->onDelete('cascade');


            $table->unique(['operador_id', 'proveedor_id', 'empresa_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proveedor_operador');
    }
};
