<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
{
    Schema::table('cotizaciones', function (Blueprint $table) {

     
        $table->string('puerto', 100)->nullable()->after('id_proveedor');
        $table->dateTime('fecha_ingreso_puerto')->nullable();
        $table->dateTime('fecha_salida_puerto')->nullable();

        $table->integer('dias_estadia')->default(0);
        $table->integer('dias_pernocta')->default(0);

        $table->decimal('tarifa_estadia', 10, 2)->default(0);
        $table->decimal('tarifa_pernocta', 10, 2)->default(0);

        $table->decimal('total_estadia', 10, 2)->default(0);
        $table->decimal('total_pernocta', 10, 2)->default(0);
        $table->decimal('total_general', 10, 2)->default(0);

        $table->text('motivo_demora')->nullable();
        $table->boolean('liberado')->default(0);
        $table->dateTime('fecha_liberacion')->nullable();
        $table->string('responsable', 150)->nullable();
        $table->string('observaciones', 255)->nullable();

        // FK si manejas transportistas
        // $table->foreign('id_transportista')->references('id')->on('transportistas')->nullOnDelete();
    });
}

public function down(): void
{
    Schema::table('cotizaciones', function (Blueprint $table) {
        $table->dropColumn([
            'puerto',
            'fecha_ingreso_puerto',
            'fecha_salida_puerto',
            'dias_estadia',
            'dias_pernocta',
            'tarifa_estadia',
            'tarifa_pernocta',
            'total_estadia',
            'total_pernocta',
            'total_general',
            'motivo_demora',
            'liberado',
            'fecha_liberacion',
            'responsable',
            'observaciones'
        ]);
    });
}
};
