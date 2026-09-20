<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddKmRecorridosLitrosDieselToCotizacionesTable extends Migration
{
    public function up()
    {
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->decimal('km_recorridos', 10, 2)
                ->nullable()
                ->after('precio_tonelada');

            $table->decimal('litros_diesel', 10, 2)
                ->nullable()
                ->after('km_recorridos');
        });
    }

    public function down()
    {
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->dropColumn([
                'km_recorridos',
                'litros_diesel',
            ]);
        });
    }
}
