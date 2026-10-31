<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('cat_bancos', function (Blueprint $table) {
            $table->id();


            $table->string('nombre', 100);
            $table->string('codigo', 20)->unique();
            $table->string('razon_social')->nullable();

            $table->string('logo')->nullable();
            $table->string('color', 20)->nullable();

            $table->boolean('activo')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cat_bancos');
    }
};
