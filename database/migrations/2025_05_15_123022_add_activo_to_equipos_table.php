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
    Schema::table('equipos', function (Blueprint $table) {
        $table->boolean('activo')->default(true)->after('tipo');
    });
}


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
{
    Schema::table('equipos', function (Blueprint $table) {
        $table->dropColumn('activo');
    });
}
};
