<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('docum_cotizacion', function (Blueprint $table) {


            $table->date('cita_at')->nullable()->after('num_contenedor');


            $table->date('eta')->nullable()->after('cita_at');


            $table->unsignedBigInteger('naviera_id')->nullable();

            $table->date('pedimento_recibido_at')->nullable()->after('naviera_id');

        });
    }

    public function down(): void
    {
        Schema::table('docum_cotizacion', function (Blueprint $table) {

            $table->dropColumn([
                'cita_at',
                'eta',
                'naviera_id',
                'pedimento_recibido_at',

            ]);
        });
    }
};
