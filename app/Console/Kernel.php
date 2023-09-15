<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{

    protected $commands = [
        Commands\AttendanceDaily::class,
        Commands\AlertCron::class,
        Commands\GenerateSalaries::class,
        Commands\PercentageCron::class,
        Commands\CheckLocation::class
    ];

    protected function schedule(Schedule $schedule)
    {
        $schedule->command('attendance:cron')->dailyAt('23:59');
        $schedule->command('alert:cron')->dailyAt('23:59');
        $schedule->command('generate:salaries')->monthlyOn(now()->startOfMonth()->format('j'), '00:00');
        $schedule->command('percentage:cron')->monthlyOn(now()->endOfMonth()->format('j'), '23:59');
        $schedule->command('check:location')->everyTenMinutes();
    }

    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}