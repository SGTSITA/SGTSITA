<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('socio_pagos', 'calculo_periodo_id')) {
            Schema::table('socio_pagos', function (Blueprint $table) {
                $table->foreignId('calculo_periodo_id')->nullable()->after('fecha_aplicacion')->constrained('socios_calculos_periodos')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('socio_pagos', 'calculo_periodo_id')) {
            Schema::table('socio_pagos', function (Blueprint $table) {
                $table->dropForeign(['calculo_periodo_id']);
                $table->dropColumn('calculo_periodo_id');
            });
        }
    }
};
