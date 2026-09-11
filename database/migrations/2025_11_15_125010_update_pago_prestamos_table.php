<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pagos_prestamos', function (Blueprint $table) {

            // Tipo de origen del pago
            $table->enum('tipo_origen', ['liquidacion', 'directo'])
                ->default('liquidacion')
                ->after('monto_pago');

            // Información para pagos directos a banco
            $table->unsignedBigInteger('id_banco')
                ->nullable()
                ->after('tipo_origen');

            $table->string('referencia', 150)
                ->nullable()
                ->after('id_banco');

            $table->date('fecha_pago')
                ->nullable()
                ->after('referencia');
        });
    }

    public function down(): void
    {
        Schema::table('pagos_prestamos', function (Blueprint $table) {
            $table->dropColumn(['tipo_origen', 'id_banco', 'referencia', 'fecha_pago']);
        });
    }
};
