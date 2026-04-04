<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('contactos', function (Blueprint $table) {
            $table->string('tipo', 20)
                ->default('contacto')
                ->after('nombre')
                ->comment('contacto | grupo');

            $table->string('wa_id', 50)
                ->after('tipo')
                ->comment('WhatsApp ID: 521XXXXXXXXXX@c.us o XXXXX@g.us');
        });
    }

    public function down(): void
    {
        Schema::table('contactos', function (Blueprint $table) {
            $table->dropColumn(['tipo', 'wa_id']);
        });
    }
};
