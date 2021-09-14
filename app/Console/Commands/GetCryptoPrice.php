<?php

namespace App\Console\Commands;

use GuzzleHttp\Client;
use Illuminate\Console\Command;
use App\Models\WatchlistStockCrypto;

class GetCryptoPrice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crypto:price';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retrieving Crypto Prince on Coin Market Cap';

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
            $endpoint = config('app.coinmarketcap.url') . '/cryptocurrency/price-performance-stats/latest';
            $token = config('app.coinmarketcap.api_key');


            $symbols = WatchlistStockCrypto::where('is_active', true)->get();

            $symbol_to_listen = $symbols->pluck('symbol')->implode(',');

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

                foreach ($symbols as $symbol) {

                    $data = $content['data'][$symbol->marketcap_id];

                    if ($data != null) {
                        $this->info(json_encode($data));
                        WatchlistStockCrypto::updateOrCreate([
                            'symbol' => $data['symbol'],
                            'name' => $data['name'],
                            'marketcap_id'   => $data['id']
                        ], [
                            'prev_price' => $data['periods']['all_time']['quote']['USD']['open'],
                            'current_price' => $data['periods']['all_time']['quote']['USD']['close'],
                            'change' => $data['periods']['all_time']['quote']['USD']['price_change'],
                            'prev_price' => $data['periods']['all_time']['quote']['USD']['open'],
                        ]);
                    }
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
