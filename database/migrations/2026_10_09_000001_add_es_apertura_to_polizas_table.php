<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEsAperturaToPolizasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('polizas')) {
            Schema::table('polizas', function (Blueprint $table) {
                if (!Schema::hasColumn('polizas', 'es_apertura')) {
                    $table->boolean('es_apertura')->default(false)->after('tipo')->index();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('polizas')) {
            Schema::table('polizas', function (Blueprint $table) {
                if (Schema::hasColumn('polizas', 'es_apertura')) {
                    $table->dropIndex(['es_apertura']);
                    $table->dropColumn('es_apertura');
                }
            });
        }
    }
}
