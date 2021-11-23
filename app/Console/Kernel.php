<?php

namespace App\Console;

use App\Console\Commands\GetCryptoUpdate;
use App\Console\Commands\GetUsStockUpdate;
use App\Console\Commands\IdleSessionRemover;
use App\Console\Commands\ScrapCommodities;
use App\Console\Commands\ScrapIdx;
use App\Console\Commands\UpdateCloseDayPrice;
use Carbon\Carbon;
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
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command(ScrapCommodities::class)->weekdays()
            ->everyFiveMinutes()->timezone('America/New_York')->between('8:00', '17:00');
        $schedule->command(ScrapIdx::class)->weekdays()
            ->everyFiveMinutes()->timezone('Asia/Jakarta')->between('8:00', '17:00');
        $schedule->command(GetUsStockUpdate::class)->weekdays()
            ->everyTenMinutes()->timezone('America/New_York')->between('8:00', '17:00');
        $schedule->command(GetCryptoUpdate::class)->everyFourHours();
        $schedule->command(UpdateCloseDayPrice::class)->when(function () {
            return Carbon::now()->timezone('Asia/Jakarta')->endOfDay();
        });
        $schedule->command(IdleSessionRemover::class)->everyMinute();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
