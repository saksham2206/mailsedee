<?php

namespace Acelle\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Acelle\Model\Automation2;
use Acelle\Model\Notification;
use Acelle\Cashier\Cashier;
use Acelle\Model\Subscription;
use Acelle\Model\Setting;
use Laravel\Tinker\Console\TinkerCommand;
use Exception;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        // Commands\Inspire::class,
        /* no longer needed as of Laravel 5.5
        Commands\TestCampaign::class,
        Commands\UpgradeTranslation::class,
        Commands\RunHandler::class,
        Commands\ImportList::class,
        Commands\VerifySender::class,
        Commands\SystemCleanup::class,
        Commands\GeoIpCheck::class,
        TinkerCommand::class,
        */
    ];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     */
    protected function schedule(Schedule $schedule)
    {
        //file_put_contents(storage_path('app/log.txt'),'Start',FILE_APPEND);
        if (!isInitiated()) {
            return;
        }

        $schedule->call(function () {
            event(new \Acelle\Events\CronJobExecuted());
        })->name('cronjob_event:log')->everyMinute();

        // Automation2
        $schedule->call(function () {
            file_put_contents(storage_path('app/log.txt'),'Automation',FILE_APPEND);
            Automation2::run();
        })->name('automation:run')->everyMinute();

        // Bounce/feedback handler
        $schedule->command('handler:run')->everyMinute();

        // Queued import/export/campaign
        // Allow overlapping: max 10 proccess as a given time (if cronjob interval is every minute)
        $schedule->command('queue:work --tries=1 --max-time=600 --stop-when-empty')->everyMinute();

        // Make it more likely to have a running queue at any given time
        // Make sure it is stopped before another queue listener is created
        $schedule->command('queue:work --tries=1 --max-time=290')->everyFiveMinutes();

        // Sender verifying
        $schedule->command('sender:verify')->everyFiveMinutes();

        // System clean up
        $schedule->command('system:cleanup')->daily();

        // GeoIp database check
        $schedule->command('geoip:check')->everyMinute()->withoutOverlapping(60);

        // Subscription
        $schedule->call(function () {
            Subscription::checkAll();
        })->name('subscription:run')->everyMinute();
        
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
