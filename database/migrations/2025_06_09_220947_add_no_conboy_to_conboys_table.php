<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('conboys', function (Blueprint $table) {
        $table->string('no_conboy')->unique()->after('nombre');
    });
}

public function down()
{
    Schema::table('conboys', function (Blueprint $table) {
        $table->dropColumn('no_conboy');
    });
}
    
};
