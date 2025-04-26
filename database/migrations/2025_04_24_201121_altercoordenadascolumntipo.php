<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class altercoordenadascolumntipo extends Migration
{
    public function up()
    {
        Schema::table('coordenadas', function (Blueprint $table) {
            if (!Schema::hasColumn('coordenadas', 'tipo_c_estado')) {
                $table->tinyInteger('tipo_c_estado')->default(0)->after('cargado_patio_datetime')->comment('0: no compartido, 1: compartido, 2: finalizado');
            }
            if (!Schema::hasColumn('coordenadas', 'tipo_b_estado')) {
                $table->tinyInteger('tipo_b_estado')->default(0)->after('tipo_c_estado')->comment('0: no compartido, 1: compartido, 2: finalizado');
            }
            if (!Schema::hasColumn('coordenadas', 'tipo_f_estado')) {
                $table->tinyInteger('tipo_f_estado')->default(0)->after('tipo_b_estado')->comment('0: no compartido, 1: compartido, 2: finalizado');
            }
        });
    }

    public function down()
    {
        Schema::table('coordenadas', function (Blueprint $table) {
            $table->dropColumn(['tipo_c_estado', 'tipo_b_estado', 'tipo_f_estado']);
        });
    }
}
