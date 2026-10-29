<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('activity_log', function (Blueprint $table) {


            $table->json('campos_modificados')
                ->nullable()
                ->after('new_values');


            $table->unsignedBigInteger('empresa_id')
                ->nullable()
                ->after('campos_modificados');


            $table->string('referencia', 150)
                ->nullable()
                ->after('empresa_id')
                ->index();


            $table->index('empresa_id');


            $table->foreign('empresa_id')
                ->references('id')
                ->on('empresas')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('activity_log', function (Blueprint $table) {

            $table->dropForeign(['empresa_id']);

            $table->dropColumn([
                'campos_modificados',
                'empresa_id',
                'referencia'
            ]);
        });
    }
};
