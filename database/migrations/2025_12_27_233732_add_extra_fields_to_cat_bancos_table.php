<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('cat_bancos', function (Blueprint $table) {
            $table->string('color_secundario', 20)
                       ->nullable()
                       ->after('color');

            $table->unsignedInteger('orden')
                ->nullable()
                ->after('id');

            $table->string('moneda', 5)
                ->default('MXN')
                ->after('color_secundario');

            $table->string('pais', 5)
                ->default('MX')
                ->after('moneda');
        });
    }

    public function down(): void
    {
        Schema::table('cat_bancos', function (Blueprint $table) {
            $table->dropColumn(['orden', 'moneda', 'pais']);
        });
    }
};
