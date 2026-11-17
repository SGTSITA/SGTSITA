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
        Schema::table('dinero_contenedor', function (Blueprint $table) {
            $table->boolean('es_principal')->default(0)->after('monto')->index();
            $table->unsignedBigInteger('id_movimiento_bancario')->nullable()->after('es_principal');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dinero_contenedor', function (Blueprint $table) {
            $table->dropIndex(['es_principal']);
            $table->dropColumn(['es_principal', 'id_movimiento_bancario']);
        });
    }
};
