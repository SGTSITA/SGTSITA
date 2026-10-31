<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('user_proveedores', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->restrictOnDelete();

            $table->foreignId('proveedor_id')
                  ->constrained('proveedores')
                  ->restrictOnDelete();

            $table->timestamps();

            $table->unique(['user_id', 'proveedor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_proveedores');
    }
};
