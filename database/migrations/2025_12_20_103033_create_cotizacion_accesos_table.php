<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('docum_cotizacion_accesos', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('documento_id')->nullable();
            $table->unsignedBigInteger('proveedor_id')->nullable();

            $table->string('token', 64)->unique();
            $table->string('password_hash', 255)->nullable();

            $table->boolean('activo')->default(true);

            $table->timestamp('expires_at')->nullable();

            $table->timestamp('last_access_at')->nullable();
            $table->string('last_ip', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->unsignedBigInteger('user_id')->nullable();

            $table->timestamps();

            $table->foreign('documento_id')
                ->references('id')
                ->on('docum_cotizacion')
                ->nullOnDelete();

            $table->foreign('proveedor_id')
                ->references('id')
                ->on('proveedores')
                ->nullOnDelete();

            $table->foreign('user_id')
            ->references('id')
            ->on('users')
            ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('docum_cotizacion_accesos');
    }
};
