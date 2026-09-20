<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('equipos', function (Blueprint $table) {
            $table->boolean('carga_diesel_al_final')
                ->default(0)
                ->after('usar_config_global')
                ->comment('1 = Carga diésel al final del viaje, 0 = Carga diésel al inicio');
        });
    }

    public function down(): void
    {
        Schema::table('equipos', function (Blueprint $table) {
            $table->dropColumn('carga_diesel_al_final');
        });
    }
};
