<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        if (!Schema::hasTable('contenedor_visibilidad_24h')) {
            Schema::create('contenedor_visibilidad_24h', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_contenedor')->index();
                $table->unsignedBigInteger('id_cotizacion')->nullable()->index();
                $table->unsignedBigInteger('id_empresa')->nullable()->index();
                $table->dateTime('fecha_inicio_visibilidad')->index();
                $table->dateTime('fecha_fin_visibilidad')->index();
                $table->boolean('visible')->default(true)->index();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('contenedor_visibilidad_24h');
    }
};
