<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDisolucionFieldsToConvoysTable extends Migration
{
    public function up(): void
    {
        Schema::table('conboys', function (Blueprint $table) {
            $table->enum('tipo_disolucion', ['geocerca', 'tiempo'])->nullable()->after('fecha_fin');
            $table->enum('estatus', ['activo', 'disuelto'])->default('activo')->after('tipo_disolucion');
            $table->timestamp('fecha_disolucion')->nullable()->after('estatus');
            $table->decimal('geocerca_lat', 10, 6)->nullable()->after('fecha_disolucion');
            $table->decimal('geocerca_lng', 10, 6)->nullable()->after('geocerca_lat');
            $table->integer('geocerca_radio')->nullable()->after('geocerca_lng');
        });
    }

    public function down(): void
    {
        Schema::table('conboys', function (Blueprint $table) {
            $table->dropColumn([
                'tipo_disolucion',
                'estatus',
                'fecha_disolucion',
                'geocerca_lat',
                'geocerca_lng',
                'geocerca_radio',
            ]);
        });
    }
}
