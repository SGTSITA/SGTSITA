<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CatalogosSat extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(SatCfdiUsageSeeder::class);
        $this->call(SatPaymentsFormsSeeder::class);
        $this->call(SatPaymentsMethodsSeeder::class);
        $this->call(SatTaxRegimensSeeder::class);
    }
}
