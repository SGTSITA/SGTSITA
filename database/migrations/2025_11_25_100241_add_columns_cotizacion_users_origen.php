<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
    {
        Schema::table('cotizaciones', function (Blueprint $table) {

            $table->string('origen_captura')->nullable()->default('SGT')->after('fecha_en_patio');
            $table->unsignedBigInteger('user_id')->nullable()->after('origen_captura');


        });
    }

    public function down(): void
    {
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->dropColumn([
                'origen_captura',
                'user_id'
            ]);
        });
    }
};
