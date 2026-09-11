<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('equipos', function (Blueprint $table) {


            $table->boolean('usar_config_global')
                ->default(1)
                ->after('gps_company_id')
                ->comment('1 = usa config global, 0 = usa credenciales propias');


            $table->longText('credenciales_gps')
                ->nullable()
                ->after('usar_config_global')
                ->comment('JSON cifrado con credenciales del equipo');

        });
    }

    public function down(): void
    {
        Schema::table('equipos', function (Blueprint $table) {
            $table->dropColumn([
                'usar_config_global',
                'credenciales_gps'
            ]);
        });
    }
};
