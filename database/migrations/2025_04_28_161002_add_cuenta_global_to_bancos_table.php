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
    Schema::table('bancos', function (Blueprint $table) {
        $table->boolean('cuenta_global')->default(false)->after('estado');
    });
}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
{
    Schema::table('bancos', function (Blueprint $table) {
        $table->dropColumn('cuenta_global');
    });
}
};
