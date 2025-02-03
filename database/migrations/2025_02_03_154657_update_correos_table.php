<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCorreosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('correos', function (Blueprint $table) {
            if (Schema::hasColumn('correos', 'notificacion_nueva')) {
                $table->dropColumn('notificacion_nueva'); 
            }

            if (!Schema::hasColumn('correos', 'cotizacion_nueva')) {
                $table->boolean('cotizacion_nueva')->default(false)->after('referencia'); 
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('correos', function (Blueprint $table) {
            if (Schema::hasColumn('correos', 'cotizacion_nueva')) {
                $table->dropColumn('cotizacion_nueva'); // Revertir cambio
            }

            if (!Schema::hasColumn('correos', 'notificacion_nueva')) {
                $table->boolean('notificacion_nueva')->default(false)->after('referencia'); // Restaurar la antigua si no existe
            }
        });
    }
}
