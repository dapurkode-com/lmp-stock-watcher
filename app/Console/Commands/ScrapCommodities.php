<?php

namespace App\Console\Commands;

use App\Helpers\NumberUtilHelper;
use App\Helpers\RateHelper;
use App\Models\WatchlistStockCommodity;
use Carbon\Carbon;
use DB;
use Exception;
use Goutte\Client;
use Illuminate\Console\Command;

/**
 * ScrapCommodities is a command class that used to
 * gathering commodities price data from scraping Business Insider Site
 *
 * @package Commands
 * @author Satya Wibawa <i.g.b.n.satyawibawa@gmail.com>
 *
 */
class ScrapCommodities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'watch:commodity';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scraping data commodities on https://markets.businessinsider.com/commodities';

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
     * @throws Exception
     */
    public function handle(): int
    {
        $start = microtime(true);
        $now = Carbon::now();

        $client = new Client();
        $crawler = $client->request('GET', "https://markets.businessinsider.com/commodities");

        $rate = RateHelper::getRate('USD');

        $crawler->filterXPath("//table[2]/tbody/tr")->each(function ($row) use ($now, $rate) {

            $current_price = NumberUtilHelper::rounding(RateHelper::calcWithRate($rate, $row->filter('td')->eq(1)->text()) / 31.1) ; // convert troy per ounce to gram
            $change = NumberUtilHelper::rounding(RateHelper::calcWithRate($rate, $row->filter('td')->eq(3)->text()) / 31.1); // convert troy per ounce to gram
            WatchlistStockCommodity::updateOrCreate([
                'name' => $row->filter('td')->eq(0)->text()
            ], [
                'prev_day_close_price' => $current_price - $change, // get prev price from current minus change
                'current_price' => $current_price,
                'change' => $change,
                'percent_change' => NumberUtilHelper::floatValue(rtrim($row->filter('td')->eq(2)->text(), '%')),
                'last_updated' => $now
            ]);
        });

        $time = microtime(true) - $start;
        $this->info("Execute time : $time");
        return 1;
    }


}
