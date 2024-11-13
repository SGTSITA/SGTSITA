<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\BancosController;
class SaldosBancariosCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:saldosBancarios';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'El comando se encarga de guardar el saldo inicial y final de los bancos con la finalidad de tener un registro diario';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        BancosController::saldos_diarios();
        return Command::SUCCESS;
    }
}
