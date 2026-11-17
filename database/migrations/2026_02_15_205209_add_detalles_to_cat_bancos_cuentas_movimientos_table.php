<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('cat_bancos_cuentas_movimientos', function (Blueprint $table) {
            $table->json('detalles')
                  ->nullable()
                  ->after('observaciones');
        });
    }

    public function down(): void
    {
        Schema::table('cat_bancos_cuentas_movimientos', function (Blueprint $table) {
            $table->dropColumn('detalles');
        });
    }
};
