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
    Schema::table('cuentas_bancarias', function (Blueprint $table) {
        $table->boolean('cuenta_1')->default(false)->after('activo');
        $table->boolean('cuenta_2')->default(false)->after('cuenta_1');
    });
}

public function down()
{
    Schema::table('cuentas_bancarias', function (Blueprint $table) {
        $table->dropColumn(['cuenta_1', 'cuenta_2']);
    });
}

};
