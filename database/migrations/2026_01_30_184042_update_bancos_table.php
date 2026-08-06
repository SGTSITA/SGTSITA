<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('bancos', function (Blueprint $table) {

            // Agregar moneda si no existe
            if (!Schema::hasColumn('bancos', 'moneda')) {
                $table->string('moneda', 10)
                      ->default('MXN')
                      ->after('saldo');
            }

            // Cambiar tipos a float
            // $table->float('saldo', 15, 2)
            //       ->default(0)
            //       ->change();

            // $table->float('saldo_inicial', 15, 2)
            //       ->default(0)
            //       ->change();

            // // Ya no se usará, lo dejamos nullable por seguridad
            // $table->text('nombre_banco')
            //       ->nullable()
            //       ->change();
        });
    }

    public function down(): void
    {
        Schema::table('bancos', function (Blueprint $table) {
            $table->dropColumn('moneda');

            // Opcional: revertir cambios
            $table->text('saldo')->change();
            $table->text('saldo_inicial')->change();
            $table->text('nombre_banco')->nullable(false)->change();
        });
    }
};
