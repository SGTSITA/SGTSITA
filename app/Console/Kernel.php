<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Models\SchedulerInterval;
use App\Models\RastreoIntervals;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\SaldosBancariosCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('command:saldosBancarios')->daily();
        

        $intervalRecord = RastreoIntervals::where('task_name', 'rastreo_gps_interval')->first();
       
         $interval = $intervalRecord ? $intervalRecord->interval : 'hourly';

        $command = $schedule->command('rastreo:intervalConfig');

        switch ($interval) {
            case 'everyMinute':
                $command->everyMinute();
                break;
            case 'everyFiveMinutes':
                $command->everyFiveMinutes();
                break;
            case 'hourly':
                $command->hourly();
                break;
            case 'daily':
                $command->daily();
                break;
            case 'weekly':
                $command->weekly();
                break;
            default:
                $command->everyFiveMinutes();
                break;
        }
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
