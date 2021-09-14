<?php

namespace App\Console\Commands;

use Goutte\Client;
use Illuminate\Console\Command;

class ScrapCrypto extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrap:crypto';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Web Scrapping for Crypto Currency on goldprice.org';

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

        $crawler = $client->request('GET', "https://goldprice.org/cryptocurrency-price");

        $crawler->filterXPath("//div[@class='view-content']/table/tbody/tr")->each(function ($row) {
            $this->info(json_encode(array(
                'name' => $this->clean($row->filter('td')->eq(1)->text()),
                'price' => $row->filter('td')->eq(3)->text(),
                'url' => $row->filter('td')->eq(1)->filterXPath('//a[1]')->attr('href')
            )));
        });

        return 1;
    }

    function clean($conv)
    {
        $conv =
            preg_replace('/[\s]+/mu', '', $conv);
        return $conv;
    }
}
