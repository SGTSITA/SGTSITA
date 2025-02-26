<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('cuentas_bancarias', function (Blueprint $table) {
            $table->boolean('activo')->default(true)->after('cuenta_clabe'); // Nuevo campo
            $table->softDeletes(); // Habilita SoftDeletes
        });
    }

    public function down()
    {
        Schema::table('cuentas_bancarias', function (Blueprint $table) {
            $table->dropColumn('activo');
            $table->dropSoftDeletes();
        });
    }
};