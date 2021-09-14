<?php

namespace App\Console\Commands;

use App\Models\WatchlistStockCrypto;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class GetCryptoInfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crypto:info';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retrieving Crypto Info on Coin Market Cap';

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
        try {
            $client = new Client;
            $endpoint = config('app.coinmarketcap.url') . '/cryptocurrency/map';
            $token = config('app.coinmarketcap.api_key');

            $symbol_to_listen = 'BTC,ETH,ADA,BNB,USDT,XRP,DOT,SOL,XLM';


            $this->info("Requesting data for $symbol_to_listen...");

            $response = $client->request('GET', $endpoint, [
                'headers' => [
                    'Accept'            => 'application/json',
                    'X-CMC_PRO_API_KEY' => $token
                ], 'query' => [
                    'symbol'     => $symbol_to_listen
                ]
            ]);
            if ($response->getStatusCode() == 200) {
                $content = json_decode($response->getBody(), true);

                foreach ($content['data'] as $result_data) {
                    $this->info(json_encode($result_data));

                    WatchlistStockCrypto::updateOrCreate([
                        'symbol' => $result_data['symbol'],
                        'name' => $result_data['name']
                    ], [
                        'symbol' => $result_data['symbol'],
                        'name' => $result_data['name'],
                    ]);
                }
            } else {
                $this->error("Failed to get data !");
            }
            $this->info('');

            return 1;
        } catch (Exception $e) {
            $this->error($e->getMessage());
            return 0;
        }
    }
}
