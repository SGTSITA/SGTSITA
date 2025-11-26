<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
    {
        Schema::table('proveedores', function (Blueprint $table) {
            $table->enum('tipo_viaje', ['foraneo', 'local', 'local_foraneo'])
                ->default('foraneo')
                ->after('tipo');
        });
    }

    public function down(): void
    {
        Schema::table('proveedores', function (Blueprint $table) {
            $table->dropColumn('tipo_viaje');
        });
    }
};
