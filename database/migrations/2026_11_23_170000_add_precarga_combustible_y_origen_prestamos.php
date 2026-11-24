<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('gastos', function (Blueprint $table) {
            if (!Schema::hasColumn('gastos', 'es_precarga_combustible')) {
                $table->boolean('es_precarga_combustible')->default(false)->after('origen_legacy_id');
            }
            if (!Schema::hasColumn('gastos', 'id_equipo_precarga')) {
                $table->unsignedBigInteger('id_equipo_precarga')->nullable()->after('es_precarga_combustible');
            }
            if (!Schema::hasColumn('gastos', 'origen_contenedor_num')) {
                $table->string('origen_contenedor_num', 100)->nullable()->after('id_equipo_precarga');
            }
            if (!Schema::hasColumn('gastos', 'origen_cotizacion_id')) {
                $table->unsignedBigInteger('origen_cotizacion_id')->nullable()->after('origen_contenedor_num');
            }
            if (!Schema::hasColumn('gastos', 'historial_reasignaciones')) {
                $table->json('historial_reasignaciones')->nullable()->after('origen_cotizacion_id');
            }
        });

        Schema::table('prestamos', function (Blueprint $table) {
            if (!Schema::hasColumn('prestamos', 'origen_asignacion_id')) {
                $table->unsignedBigInteger('origen_asignacion_id')->nullable()->after('saldo_actual');
            }
            if (!Schema::hasColumn('prestamos', 'origen_tipo')) {
                $table->string('origen_tipo', 80)->nullable()->after('origen_asignacion_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('gastos', function (Blueprint $table) {
            $columnas = [
                'es_precarga_combustible',
                'id_equipo_precarga',
                'origen_contenedor_num',
                'origen_cotizacion_id',
                'historial_reasignaciones',
            ];
            foreach ($columnas as $col) {
                if (Schema::hasColumn('gastos', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('prestamos', function (Blueprint $table) {
            if (Schema::hasColumn('prestamos', 'origen_asignacion_id')) {
                $table->dropColumn('origen_asignacion_id');
            }
            if (Schema::hasColumn('prestamos', 'origen_tipo')) {
                $table->dropColumn('origen_tipo');
            }
        });
    }
};
