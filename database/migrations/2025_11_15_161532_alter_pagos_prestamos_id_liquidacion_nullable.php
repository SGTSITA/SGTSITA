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
        // 1. Eliminar FK si existe
        DB::statement('ALTER TABLE pagos_prestamos DROP FOREIGN KEY pagos_prestamos_id_liquidacion_foreign');

        // 2. Hacer nullable
        DB::statement('ALTER TABLE pagos_prestamos MODIFY id_liquidacion BIGINT UNSIGNED NULL');
    }

    public function down()
    {
        // Revertir a NOT NULL
        DB::statement('ALTER TABLE pagos_prestamos MODIFY id_liquidacion BIGINT UNSIGNED NOT NULL');

        // Restaurar FK
        DB::statement('ALTER TABLE pagos_prestamos 
            ADD CONSTRAINT pagos_prestamos_id_liquidacion_foreign 
            FOREIGN KEY (id_liquidacion) REFERENCES liquidaciones(id)');
    }
};
