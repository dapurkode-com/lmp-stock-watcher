<?php

namespace App\Console\Commands;

use App\Models\WatchlistStockIdx;
use Carbon\Carbon;
use Goutte\Client;
use Illuminate\Console\Command;

class ScrapIdx extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'watch:idx';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Web Scrapping for Indonesia Stock Exchange on www.idxchannel.com';

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

        $now = Carbon::now();

        $client = new Client();
        $crawler = $client->request('GET', 'https://www.idxchannel.com/market-stock');

        $crawler->filterXPath("//table[@id='table_id']/tbody/tr")->each(function ($row) use ($now) {

            WatchlistStockIdx::updateOrCreate([
                'symbol' => $row->filter('td')->eq(1)->text()
            ], [
                'symbol' => $row->filter('td')->eq(1)->text(),
                'name' => $row->filter('td')->eq(2)->text(),
                'prev_day_close_price' => $row->filter('td')->eq(3)->text(),
                'current_price' => $row->filter('td')->eq(4)->text(),
                'change' => $row->filter('td')->eq(5)->text(),
                'percent_change' => rtrim($row->filter('td')->eq(6)->text(), '%'),
                'last_updated' => $now
            ]);

        });

        $time = microtime(true) - $start;
        $this->info("Execute time : $time");
        return 1;
    }
}
