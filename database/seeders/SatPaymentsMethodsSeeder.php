<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SatPaymentsMethodsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $paymentMethods = [
            ['sat_code' => 'PUE', 'metodo_pago' => 'PAGO EN UNA SOLA EXHIBICIÓN', 'is_active' => true],
            ['sat_code' => 'PPD', 'metodo_pago' => 'PAGO EN PARCIALIDADES O DIFERIDO', 'is_active' => true]

        ];

        DB::table('sat_metodos_pago')->insert($paymentMethods);
    }
}
