<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('gastos_extras', function (Blueprint $table) {

            $table->string('estatus')
                ->default('pendiente')
                ->after('monto');

            $table->dateTime('fecha_aplicacion')
                ->nullable()
                ->after('estatus');

            $table->unsignedBigInteger('cuenta_bancaria_id')
                ->nullable()
                ->after('fecha_aplicacion');

            // opcional: si tienes tabla cuentas_bancarias
            $table->foreign('cuenta_bancaria_id')
             ->references('id')
                ->on('bancos')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('gastos_extras', function (Blueprint $table) {


            $table->dropColumn([
                'estatus',
                'fecha_aplicacion',
                'cuenta_bancaria_id'
            ]);
        });
    }
};
