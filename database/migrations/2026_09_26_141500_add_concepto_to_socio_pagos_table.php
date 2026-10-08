<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('socio_pagos', 'concepto')) {
            Schema::table('socio_pagos', function (Blueprint $table) {
                $table->string('concepto', 255)->nullable()->after('fecha_aplicacion');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('socio_pagos', 'concepto')) {
            Schema::table('socio_pagos', function (Blueprint $table) {
                $table->dropColumn('concepto');
            });
        }
    }
};
