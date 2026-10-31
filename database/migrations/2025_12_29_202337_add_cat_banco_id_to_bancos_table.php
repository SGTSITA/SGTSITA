<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('bancos', function (Blueprint $table) {
            $table->unsignedBigInteger('cat_banco_id')
                  ->nullable()
                  ->after('id');

            $table->foreign('cat_banco_id')
                  ->references('id')
                  ->on('cat_bancos')
                  ->nullOnDelete(); // NO romper cuentas si borran banco
        });
    }

    public function down(): void
    {
        Schema::table('bancos', function (Blueprint $table) {
            $table->dropForeign(['cat_banco_id']);
            $table->dropColumn('cat_banco_id');
        });
    }
};
