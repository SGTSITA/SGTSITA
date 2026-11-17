<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    public function up(): void
    {
        DB::statement("
            ALTER TABLE activity_log
            MODIFY old_values LONGTEXT NULL
        ");

        DB::statement("
            ALTER TABLE activity_log
            MODIFY new_values LONGTEXT NULL
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE activity_log
            MODIFY old_values LONGTEXT
            CHARACTER SET utf8mb4
            COLLATE utf8mb4_bin
            CHECK (json_valid(old_values))
        ");

        DB::statement("
            ALTER TABLE activity_log
            MODIFY new_values LONGTEXT
            CHARACTER SET utf8mb4
            COLLATE utf8mb4_bin
            CHECK (json_valid(new_values))
        ");
    }
};
