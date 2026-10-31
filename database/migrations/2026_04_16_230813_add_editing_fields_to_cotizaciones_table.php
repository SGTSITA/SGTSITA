<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->unsignedBigInteger('editing_by')->nullable()->after('updated_at');
            $table->timestamp('editing_at')->nullable()->after('editing_by');


        });
    }

    public function down(): void
    {
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->dropForeign(['editing_by']);
            $table->dropColumn(['editing_by', 'editing_at']);
        });
    }
};
