<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('docum_cotizacion', function (Blueprint $table) {


            $table->string('evidencia_descarga')
                ->nullable()
                ->after('doda');


            $table->dateTime('fecha_evidencia_descarga')
                ->nullable()
                ->after('evidencia_descarga');



        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('docum_cotizacion', function (Blueprint $table) {

            $table->dropColumn([
                'evidencia_descarga',
                'fecha_evidencia_descarga'
            ]);
        });
    }
};
