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
  
   public function up(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->string('modulo')->nullable()->after('guard_name');
            $table->string('descripcion')->nullable()->after('modulo');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
     

    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropColumn(['modulo', 'descripcion']);
        });
    }
};
