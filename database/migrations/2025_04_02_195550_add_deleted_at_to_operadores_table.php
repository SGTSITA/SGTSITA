<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('operadores', function (Blueprint $table) {
            $table->softDeletes(); // agrega la columna deleted_at
        });
    }

    public function down()
    {
        Schema::table('operadores', function (Blueprint $table) {
            $table->dropSoftDeletes(); // elimina la columna deleted_at
        });
    }
};