<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('coordenadas_historial', function (Blueprint $table) {


            $table->boolean('status_api')
                ->default(1)
                ->after('tipo');


            $table->unsignedBigInteger('id_compania_gps')
                ->nullable()
                ->after('status_api');

            $table->foreign('id_compania_gps')
                ->references('id')
                ->on('gps_company')
                ->nullOnDelete();


            $table->decimal('tiempo_respuesta_ms', 10, 2)
                ->nullable()
                ->after('id_compania_gps');


            $table->string('valorSolicitado', 100)
                ->nullable()
                ->after('tiempo_respuesta_ms');


            $table->longText('response_json')
                ->nullable()
                ->after('valorSolicitado');


            $table->text('error_message')
                ->nullable()
                ->after('response_json');


            $table->index('valorSolicitado');

        });
    }

    public function down(): void
    {
        Schema::table('coordenadas_historial', function (Blueprint $table) {

            $table->dropForeign(['id_compania_gps']);

            $table->dropColumn([
                'status_api',
                'id_compania_gps',
                'tiempo_respuesta_ms',
                'imei',
                'tipo_referencia',
                'referencia_id',
                'response_json',
                'error_message'
            ]);
        });
    }
};
