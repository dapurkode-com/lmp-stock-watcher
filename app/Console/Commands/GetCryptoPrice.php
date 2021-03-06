<?php

namespace App\Console\Commands;

use App\Models\WatchlistStockCrypto;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;

/**
 * GetCryptoPrice is a command class that used to
 * gathering cryptocurrency price from scraping Indodax site
 *
 * @package Commands
 * @author Satya Wibawa <i.g.b.n.satyawibawa@gmail.com>
 * @deprecated due updating cryptoprice with REST API coin market cap
 */
class GetCryptoPrice extends Command
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
    public function handle(): int
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
        } catch (Exception|GuzzleException $e) {
            $this->error($e->getMessage());
            return 0;
        }
    }
}
