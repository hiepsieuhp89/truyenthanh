<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

use App\Jobs\UpdateDevicesStatistics;
use App\Jobs\UpdateDevicesStatus;
use App\Jobs\UpdateAreaAccount;
use App\Jobs\UpdateDeviceInfo;
use App\Jobs\RemoveUnnecessaryFiles;

use App\Jobs\ExportDevices;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //$schedule->job(new UpdateDevicesStatistics())->everyMinute();
        
        $schedule->job(new UpdateDevicesStatus())->everyMinute();
        $schedule->job(new UpdateAreaAccount())->everyMinute();
        $schedule->job(new UpdateDevicesStatistics())->everyMinute();

        //$schedule->job(new ExportDevices())->everyMinute();

        //$schedule->job(new RemoveUnnecessaryFiles())->daily();

        $schedule->job(new UpdateDeviceInfo())->daily();
        
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
