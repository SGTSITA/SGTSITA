<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

//use App\Models\SchedulerInterval;
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
        Commands\cmdRastreoInterval::class,
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

        try {
            $intervalSetting = RastreoIntervals::where('task_name', 'rastreo_gps_interval')->first();
            if ($intervalSetting && !empty($intervalSetting->interval)) {
                $method = $intervalSetting->interval; // e.g. 'everyMinute', 'everyFiveMinutes', 'hourly', 'daily', etc.
                if (method_exists($schedule->command('rastreo:intervalConfig'), $method)) {
                    $schedule->command('rastreo:intervalConfig')->$method();
                } else {
                    $schedule->command('rastreo:intervalConfig')->hourly();
                }
            } else {
                $schedule->command('rastreo:intervalConfig')->hourly();
            }
        } catch (\Exception $e) {
            // Safe fallback if database isn't initialized or accessible
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
