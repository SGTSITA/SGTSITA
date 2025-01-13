<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;

class SatPaymentsFormsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $paymentForms = [
            ['sat_code' => '01', 'forma_pago' => 'Efectivo', 'is_active' => true],
            ['sat_code' => '02', 'forma_pago' => 'Cheque nominativo', 'is_active' => true],
            ['sat_code' => '03', 'forma_pago' => 'Transferencia electrónica de fondos', 'is_active' => true],
            ['sat_code' => '04', 'forma_pago' => 'Tarjeta de crédito', 'is_active' => true],
            ['sat_code' => '05', 'forma_pago' => 'Monedero electrónico', 'is_active' => true],
            ['sat_code' => '06', 'forma_pago' => 'Dinero electrónico', 'is_active' => true],
            ['sat_code' => '08', 'forma_pago' => 'Vales de despensa', 'is_active' => true],
            ['sat_code' => '12', 'forma_pago' => 'Dación en pago', 'is_active' => true],
            ['sat_code' => '13', 'forma_pago' => 'Pago por subrogación', 'is_active' => true],
            ['sat_code' => '14', 'forma_pago' => 'Pago por consignación', 'is_active' => true],
            ['sat_code' => '15', 'forma_pago' => 'Condonación', 'is_active' => true],
            ['sat_code' => '17', 'forma_pago' => 'Compensación', 'is_active' => true],
            ['sat_code' => '23', 'forma_pago' => 'Novación', 'is_active' => true],
            ['sat_code' => '24', 'forma_pago' => 'Confusión', 'is_active' => true],
            ['sat_code' => '25', 'forma_pago' => 'Remisión de deuda', 'is_active' => true],
            ['sat_code' => '26', 'forma_pago' => 'Prescripción o caducidad', 'is_active' => true],
            ['sat_code' => '27', 'forma_pago' => 'A satisfacción del acreedor', 'is_active' => true],
            ['sat_code' => '28', 'forma_pago' => 'Tarjeta de débito', 'is_active' => true],
            ['sat_code' => '29', 'forma_pago' => 'Tarjeta de servicios', 'is_active' => true],
            ['sat_code' => '30', 'forma_pago' => 'Aplicación de anticipos', 'is_active' => true],
            ['sat_code' => '31', 'forma_pago' => 'Intermediario pagos', 'is_active' => true],
            ['sat_code' => '99', 'forma_pago' => 'Por definir', 'is_active' => true],
        ];

        DB::table('sat_formas_pago')->insert($paymentForms);
    }
}
