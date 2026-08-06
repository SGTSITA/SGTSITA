<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('docum_cotizacion_accesos', function (Blueprint $table) {
            $table->json('shared_files')->nullable()->after('password_hash');
        });
    }

    public function down(): void
    {
        Schema::table('docum_cotizacion_accesos', function (Blueprint $table) {
            $table->dropColumn('shared_files');
        });
    }
};
