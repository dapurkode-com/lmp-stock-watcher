<?php

namespace App\Console\Commands;

use App\Helpers\FinnhubHelper;
use App\Helpers\NumberUtilHelper;
use App\Helpers\RateHelper;
use App\Models\WatchlistStockUs;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;

class GetUsStockUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'watch:us-stock';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retrieving US Stock Info on Finnhub';

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
        $start = microtime(true);
        try {

            $now = Carbon::now();
            $rate = RateHelper::getRate('USD');

            WatchlistStockUs::chunkById(30, function ($stocks) use ($now, $rate) {
                $batchStart = microtime(true);
                foreach ($stocks as $stock){
                    $content = FinnhubHelper::request('GET', 'quote', [
                        'symbol' => $stock->symbol
                    ]);

                    WatchlistStockUs::updateOrCreate(
                        ['symbol' => $stock->symbol],
                        [
                            'prev_day_close_price' => RateHelper::calcWithRate($rate, $content['pc'] ?? 0),
                            'current_price' => RateHelper::calcWithRate($rate, $content['c'] ?? 0),
                            'change' => NumberUtilHelper::rounding($content['d']) ?? 0,
                            'percent_change' =>  RateHelper::calcWithRate($rate,$content['dp']?? 0),
                            'last_updated' => $now
                        ]
                    );
                }
                $sleepTime = (60 - (microtime(true) - $batchStart));
                sleep($sleepTime > 0 ? $sleepTime : 1);
            });
            return 1;
        } catch (Exception $e) {
            $this->error($e->getMessage());
            return 0;
        } finally {
            $time = microtime(true) - $start;
            $this->info("Execute time : $time");
        }
    }
}
