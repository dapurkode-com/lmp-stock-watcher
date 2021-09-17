<?php

namespace App\Console\Commands;

use App\Models\WatchlistStockCommodity;
use Goutte\Client;
use Illuminate\Console\Command;

class ScrapCommodities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrap:commodities';

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
     */
    public function handle()
    {
        $client = new Client();

        $crawler = $client->request('GET', "https://markets.businessinsider.com/commodities");

        $name_to_listen = ['Gold', 'Silver'];

        $crawler->filterXPath("//table[2]/tbody/tr")->each(function ($row) use ($name_to_listen) {
            if (in_array($row->filter('td')->eq(0)->text(), $name_to_listen)) {

                $this->info(json_encode(array(
                    'name' => $row->filter('td')->eq(0)->text(),
                    'price' => $row->filter('td')->eq(1)->text(),
                )));


                WatchlistStockCommodity::updateOrCreate([
                    'name' => $row->filter('td')->eq(0)->text()
                ], [
                    'name' => $row->filter('td')->eq(0)->text(),
                    'current_price' => $this->floatvalue($row->filter('td')->eq(1)->text()),
                    'change' => $this->floatvalue($row->filter('td')->eq(3)->text()),
                    'percent_change' => $this->floatvalue(rtrim($row->filter('td')->eq(2)->text(), '%')),
                ]);
            }
        });

        return 1;
    }

    function floatvalue($val)
    {
        $val = str_replace(",", ".", $val);
        $val = preg_replace('/\.(?=.*\.)/', '', $val);
        return floatval($val);
    }
}
