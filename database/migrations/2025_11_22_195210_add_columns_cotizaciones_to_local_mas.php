<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->string('bloque_local')->nullable()->after('sub_cliente_local');
            $table->time('bloque_hora_i_local')->nullable()->after('bloque_local');
            $table->time('bloque_hora_f_local')->nullable()->after('bloque_hora_i_local');


        });
    }

    public function down(): void
    {
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->dropColumn([
                'bloque_local',
                'bloque_hora_i_local',
                'bloque_hora_f_local',
            ]);
        });
    }
};
