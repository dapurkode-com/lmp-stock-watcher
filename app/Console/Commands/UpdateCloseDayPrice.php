<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * UpdateCloseDayPrice is a command class that used to
 * update prev_day_close_price on table watchlist_stock_cryptos when days changed
 *
 * @package Commands
 * @author Satya Wibawa <i.g.b.n.satyawibawa@gmail.com>
 *
 */
class UpdateCloseDayPrice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'watch:close-the-day';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update close day price';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        try {
            DB::update("UPDATE watchlist_stock_cryptos SET prev_day_close_price = current_price");
            return 1;
        } catch (Exception $e) {
            return 0;
        }

    }
}
