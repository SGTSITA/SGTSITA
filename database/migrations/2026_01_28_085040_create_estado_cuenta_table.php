<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('estado_cuenta', function (Blueprint $table) {
            $table->id();

            $table->string('numero', 30);
            $table->unsignedBigInteger('id_empresa');

            $table->unique(['id_empresa', 'numero'], 'estado_cuenta_empresa_numero_unique');


            $table->foreign('id_empresa')
          ->references('id')
          ->on('empresas');

            $table->foreignId('created_by')
                ->constrained('users');

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estado_cuenta');
    }
};
