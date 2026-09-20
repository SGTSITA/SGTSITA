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
        Schema::table('docum_cotizacion', function (Blueprint $table) {
            $table->string('comprobante_pago_pdf')->nullable()->after('evidencia_descarga');
            $table->string('comprobante_pago_xml')->nullable()->after('comprobante_pago_pdf');
            $table->timestamp('comprobante_pago_pdf_at')->nullable()->after('comprobante_pago_xml');
            $table->timestamp('comprobante_pago_xml_at')->nullable()->after('comprobante_pago_pdf_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('docum_cotizacion', function (Blueprint $table) {
            $table->dropColumn([
                'comprobante_pago_pdf',
                'comprobante_pago_xml',
                'comprobante_pago_pdf_at',
                'comprobante_pago_xml_at'
            ]);
        });
    }
};
