<?php

namespace App\Console\Commands;

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

        // $response = $client->getResponse();
        // var_dump($response->getContent());

        $crawler->filterXPath("//table[2]/tbody/tr")->each(function ($row) {
            $this->info(json_encode(array(
                'name' => $row->filter('td')->eq(0)->text(),
                'price' => $row->filter('td')->eq(1)->text(),
            )));
        });

        return 1;
    }
}
