<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(\Illuminate\Console\Scheduling\Schedule $schedule): void
    {
        // OpenAQ / WAQI station sync — refresh the global station list every 30 minutes.
        $schedule->command('stations:sync')->everyThirtyMinutes()->withoutOverlapping();

        // IQAir auto-cache. Cambodia is the priority location, so refresh it every
        // 2 hours; its ~24 cities feed iqair_readings + aqi_history so the map,
        // city list and line charts always have fresh data.
        $schedule->command('iqair:sync-country Cambodia')
            ->everyTwoHours()
            ->withoutOverlapping();

        // Neighbouring countries — heavier, so only twice a day on staggered times.
        $schedule->command('iqair:sync-country Vietnam,Thailand,Laos')
            ->twiceDaily(3, 15)
            ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
