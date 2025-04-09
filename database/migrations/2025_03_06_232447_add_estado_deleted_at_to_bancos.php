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
            if (!Schema::hasColumn('bancos', 'estado')) {
                $table->boolean('estado')->default(true); // true = activo, false = inactivo
            }
            if (!Schema::hasColumn('bancos', 'deleted_at')) {
                $table->softDeletes(); // Campo para borrado lógico
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
        Schema::table('bancos', function (Blueprint $table) {
            if (Schema::hasColumn('bancos', 'estado')) {
                $table->dropColumn('estado');
            }
            if (Schema::hasColumn('bancos', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
