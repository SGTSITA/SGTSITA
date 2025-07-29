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
    public function up()
    {
         Schema::create('rastreo_intervals', function (Blueprint $table) {
        $table->id();
        $table->string('task_name')->unique();
        $table->string('interval'); // por ejemplo 'everyFiveMinutes', 'hourly', o expresión cron
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rastreo_intervals');
    }
};
