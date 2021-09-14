<?php

namespace App\Console\Commands;

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

        $crawler->filterXPath("//table[@id='table_id']/tbody/tr")->each(function ($row) {
            $this->info(json_encode(array(
                'code' => $row->filter('td')->eq(1)->text(),
                'name' => $row->filter('td')->eq(2)->text(),
                'price' => $row->filter('td')->eq(3)->text(),
                'change' => $row->filter('td')->eq(4)->text(),
                'change_prc' => $row->filter('td')->eq(5)->text(),
                'url' => $row->filter('td')->eq(1)->filterXPath('//a[1]')->attr('href')
            )));
        });

        return 1;
    }
}
