<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notificacion_reglas', function (Blueprint $table) {
            $table->boolean('incluir_url_documento')
                ->default(false)
                ->after('notificar_proveedor')
                ->comment('Indica si la notificación debe incluir la URL del documento');
        });
    }

    public function down(): void
    {
        Schema::table('notificacion_reglas', function (Blueprint $table) {
            $table->dropColumn('incluir_url_documento');
        });
    }
};
