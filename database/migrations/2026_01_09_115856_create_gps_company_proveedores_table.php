<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('gps_company_proveedores', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('id_empresa');
            $table->unsignedBigInteger('id_proveedor');
            $table->unsignedBigInteger('id_gps_company');

            // credenciales del proveedor
            $table->longText('account_info');

            // estado de la configuración
            $table->boolean('estado')->default(1);

            $table->timestamps();

            // evita duplicados
            $table->unique(
                ['id_empresa', 'id_proveedor', 'id_gps_company'],
                'empresa_proveedor_gps_unique'
            );

            // foreign keys (SIN cascade)
            $table->foreign('id_empresa')
                ->references('id')
                ->on('empresas')
                ->restrictOnDelete();

            $table->foreign('id_proveedor')
                ->references('id')
                ->on('proveedores')
                ->restrictOnDelete();

            $table->foreign('id_gps_company')
                ->references('id')
                ->on('gps_company')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gps_company_proveedores');
    }
};
