<?php

namespace App\Console\Commands;

use App\Models\WatchlistStockIdx;
use Goutte\Client;
use Illuminate\Console\Command;

class ScrapIdx extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrap:idx';

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
    public function handle()
    {
        $client = new Client();

        $crawler = $client->request('GET', 'https://www.idxchannel.com/market-stock');

        $symbol_to_listen = ['ANTM', 'ASII', 'BBRI', 'BUKA', 'KLBF', 'PTBA', 'TLKM', 'WIKA'];

        $crawler->filterXPath("//table[@id='table_id']/tbody/tr")->each(function ($row) use ($symbol_to_listen) {

            if (in_array($row->filter('td')->eq(1)->text(), $symbol_to_listen)) {

                $this->info(json_encode(array(
                    'code' => $row->filter('td')->eq(1)->text(),
                    'name' => $row->filter('td')->eq(2)->text(),
                    'price' => $row->filter('td')->eq(4)->text(),
                )));

                WatchlistStockIdx::updateOrCreate([
                    'symbol' => $row->filter('td')->eq(1)->text()
                ], [
                    'symbol' => $row->filter('td')->eq(1)->text(),
                    'name' => $row->filter('td')->eq(2)->text(),
                    'prev_price' => $row->filter('td')->eq(3)->text(),
                    'current_price' => $row->filter('td')->eq(4)->text(),
                    'change' => $row->filter('td')->eq(5)->text(),
                    'percent_change' => rtrim($row->filter('td')->eq(6)->text(), '%'),
                ]);
            }
        });

        return 1;
    }
}
