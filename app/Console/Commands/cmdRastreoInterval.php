<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
//use App\Models\RastreoIntervals; 
 //use App\Services\UbiService;
//use App\Models\coordenadashistorial;

class RevisaRegistrosRastreo extends Command
{
    protected $signature = 'rastreo:intervalConfig';

    protected $description = 'Revisa la base de datos las cotizaciones planeadas y si encuentra ejecuta el rastro';

   

        public function __construct(UbiService $ubiService)
        {
            parent::__construct();
           
        }

    public function handle()
    {
            

    }
}
