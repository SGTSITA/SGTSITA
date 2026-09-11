<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('liquidaciones', function (Blueprint $table) {
            $table->decimal('pago_adelantos', 15, 2)->nullable()->after('pago_prestamos');
            $table->foreignId('user_id')->nullable()->after('pago_adelantos')->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
