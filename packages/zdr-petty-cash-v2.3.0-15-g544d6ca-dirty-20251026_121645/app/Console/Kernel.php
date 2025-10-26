<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\SetupStorage::class,
        Commands\CreatePlaceholderImages::class,
        Commands\PettyCashArchive::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Schedule the demo database refresh command every 15 minutes in demo mode.
        $schedule->command('demo:refresh-database')->everyFifteenMinutes();

        // Schedule petty cash archiving
        $schedule->command('petty-cash:archive --period=daily')->dailyAt('23:59');
        $schedule->command('petty-cash:archive --period=3days')->cron('59 23 */3 * *'); // Every 3 days at 23:59
        $schedule->command('petty-cash:archive --period=weekly')->weeklyOn(6, '23:59'); // Every Saturday at 23:59
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
