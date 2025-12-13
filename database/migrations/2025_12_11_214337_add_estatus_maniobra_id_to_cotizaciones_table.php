<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->foreignId('estatus_maniobra_id')
                ->nullable()
                ->constrained('estatus_maniobras')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->dropForeign(['estatus_maniobra_id']);
            $table->dropColumn('estatus_maniobra_id');
        });
    }
};
