<?php

namespace App\Console\Commands;

use Goutte\Client;
use Illuminate\Console\Command;

class ScrapUsStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrap:us';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Web Scrapping for US Stock Exchange on www.tradingview.com';

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

        // $response = $client->getResponse();
        // var_dump($response->getContent());

        $crawler = $client->request('GET', "https://www.tradingview.com/markets/stocks-usa/market-movers-large-cap/");

        $symbol_to_listen = ['TSLA', 'AMZN', 'GOOGL', 'AAPL'];

        $crawler->filterXPath("//tbody[@class='tv-data-table__tbody']/tr")->each(function ($row) use ($symbol_to_listen) {
            $row_data = array(
                'code' => $row->filter('td')->eq(0)->filterXPath("//a[@class='tv-screener__symbol']")->text(),
                'name' => $row->filter('td')->eq(0)->filterXPath("//span[@class='tv-screener__description']")->text(),
                'price' => $row->filter('td')->eq(1)->text(),
                'change' => $row->filter('td')->eq(3)->text(),
                'change_prc' => $row->filter('td')->eq(2)->text(),
            );
            if (in_array($row_data['code'], $symbol_to_listen)) {
                $this->info(json_encode($row_data));
            }
        });

        return 1;
    }
}
