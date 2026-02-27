<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('grid_columnas_user_estado', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('grid_key', 100);
            $table->json('state_json');

            $table->timestamps();


            $table->unique(['user_id', 'grid_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grid_columnas_user_estado');
    }
};
