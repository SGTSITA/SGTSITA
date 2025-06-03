<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGpsCompanyTable extends Migration
{
    public function up()
    {
        Schema::create('gps_company', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('url');
            $table->string('telefono')->nullable();
            $table->string('correo')->nullable();
            $table->string('contacto')->nullable();
            $table->timestamps();
            $table->softDeletes(); // borrado lógico
        });
    }

    public function down()
    {
        Schema::dropIfExists('gps_company');
    }
}
