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
    Schema::table('costos_mep_pendientes', function (Blueprint $table) {
        $table->decimal('total', 12, 4)->nullable()->after('precio_sobrepeso');
    });
}


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('costos_mep_pendientes', function (Blueprint $table) {
            //
        });
    }
};
