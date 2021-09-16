<?php

namespace App\Console\Commands;

use GuzzleHttp\Client;
use App\Events\UsStockEvent;
use Illuminate\Console\Command;
use App\Models\WatchlistStockUs;

class GetUsStockPrice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'us-stock:price';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retrieving US Stock Info on Finnhub';

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
            $endpoint = config('app.finnhub.url') . '/quote';
            $token = config('app.finnhub.api_key');

            $stocks = WatchlistStockUs::where('is_active', true)->get();

            foreach ($stocks as $stock) {

                $this->info("Requesting data for $stock->symbol...");

                $response = $client->request('GET', $endpoint, ['query' => array(
                    'symbol'     => $stock->symbol,
                    'token' => $token
                )]);

                if ($response->getStatusCode() == 200) {
                    $content = json_decode($response->getBody(), true);

                    $stock->prev_price = $content['pc'] ?? 0;
                    $stock->current_price = $content['c'] ?? 0;
                    $stock->change = $content['d'] ?? 0;
                    $stock->percent_change = $content['dp'] ?? 0;
                    $stock->save();
                } else {
                    $this->error("Failed to get data !");
                }
                $this->info('');
            }

            return 1;
        } catch (Exception $e) {
            $this->error($e->getMessage());
            return 0;
        }
    }
}
