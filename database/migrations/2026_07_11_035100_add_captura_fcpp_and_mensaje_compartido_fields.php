<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->boolean('captura_fcpp')->default(false)->after('id_empresa');
        });

        Schema::table('asignaciones', function (Blueprint $table) {
            $table->text('mensaje_compartido')->nullable()->after('password_temporal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('captura_fcpp');
        });

        Schema::table('asignaciones', function (Blueprint $table) {
            $table->dropColumn('mensaje_compartido');
        });
    }
};
