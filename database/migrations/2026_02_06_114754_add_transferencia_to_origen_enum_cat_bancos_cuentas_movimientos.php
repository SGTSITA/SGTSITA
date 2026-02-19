<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    public function up(): void
    {
        DB::statement("
            ALTER TABLE cat_bancos_cuentas_movimientos
            MODIFY origen ENUM(
                'manual',
                'banco',
                'sistema',
                'ajuste',
                'importacion',
                'transferencia'
            ) NOT NULL DEFAULT 'manual'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE cat_bancos_cuentas_movimientos
            MODIFY origen ENUM(
                'manual',
                'banco',
                'sistema',
                'ajuste',
                'importacion'
            ) NOT NULL DEFAULT 'manual'
        ");
    }
};
