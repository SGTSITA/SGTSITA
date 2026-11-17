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
    public function up()
    {
        Schema::table('rastreo_intervals', function (Blueprint $table) {
            $table->boolean('alerta_distancia')->default(false)->after('interval');
            $table->integer('metros_alerta')->default(50)->after('alerta_distancia');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rastreo_intervals', function (Blueprint $table) {
            $table->dropColumn(['alerta_distancia', 'metros_alerta']);
        });
    }
};
