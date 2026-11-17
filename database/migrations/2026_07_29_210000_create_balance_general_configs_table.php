<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateBalanceGeneralConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('balance_general_configs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_empresa')->nullable();
            $table->enum('grupo', ['activo', 'pasivo', 'capital']);
            $table->string('concepto');
            $table->string('tipo_calculo'); // e.g. 'bancos', 'cxc', 'cxp', 'gxp', 'utilidad_ejercicio', 'utilidades_acumuladas', 'manual'
            $table->decimal('valor_manual', 15, 2)->default(0.00);
            $table->text('detalles_calculo')->nullable(); // JSON / string parameters
            $table->integer('orden')->default(0);
            $table->timestamps();

            // Foreign key to empresas if table exists
            if (Schema::hasTable('empresas')) {
                $table->foreign('id_empresa')->references('id')->on('empresas')->onDelete('cascade');
            }
        });

        // Insert default layout configuration
        $defaults = [
            // Activos
            ['grupo' => 'activo', 'concepto' => 'BANCOS', 'tipo_calculo' => 'bancos', 'orden' => 10],
            ['grupo' => 'activo', 'concepto' => 'CUENTAS POR COBRAR', 'tipo_calculo' => 'cxc', 'orden' => 20],

            // Pasivos
            ['grupo' => 'pasivo', 'concepto' => 'CUENTAS POR PAGAR', 'tipo_calculo' => 'cxp', 'orden' => 10],
            ['grupo' => 'pasivo', 'concepto' => 'GASTOS POR PAGAR', 'tipo_calculo' => 'gxp', 'orden' => 20],

            // Capital
            ['grupo' => 'capital', 'concepto' => 'UTILIDAD DEL EJERCICIO', 'tipo_calculo' => 'utilidad_ejercicio', 'orden' => 10],
            ['grupo' => 'capital', 'concepto' => 'UTILIDADES ACUMULADAS', 'tipo_calculo' => 'utilidades_acumuladas', 'orden' => 20],
        ];

        foreach ($defaults as $row) {
            DB::table('balance_general_configs')->insert(array_merge($row, [
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('balance_general_configs');
    }
}
