<?php

namespace App\Console\Commands;

use GuzzleHttp\Client;
use Illuminate\Console\Command;
use App\Models\WatchlistStockCrypto;

class GetCryptoPrice2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crypto:price-2';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retrieving Crypto Prince on Indodax';

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
            $endpoint = 'https://indodax.com/api/tickers';

            $symbols = WatchlistStockCrypto::where('is_active', true)->get();

            $symbol_to_listen = $symbols->pluck('symbol');

            $response = $client->request('GET', $endpoint);
            if ($response->getStatusCode() == 200) {
                $content = json_decode($response->getBody(), true);

                foreach ($symbol_to_listen as $symbol) {
                    $key = strtolower($symbol) . '_idr';
                    $data = array_key_exists($key, $content['tickers']) ? $content['tickers'][$key] : null;

                    if ($data != null) {
                        $this->info(json_encode($data));
                        WatchlistStockCrypto::updateOrCreate([
                            'symbol' => $symbol,
                        ], [
                            'last' => $data['last'],
                            'buy' => $data['buy'],
                            'sell' => $data['sell'],
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
