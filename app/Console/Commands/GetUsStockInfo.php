<?php

namespace App\Console\Commands;

use App\Models\WatchlistStockUs;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

use function PHPUnit\Framework\throwException;

class GetUsStockInfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'us-stock:info';

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
            $endpoint = config('app.finnhub.url') . '/search';
            $token = config('app.finnhub.api_key');

            $symbol_to_listen = ['TSLA', 'AMZN', 'GOOGL', 'AAPL'];

            foreach ($symbol_to_listen as $symbol) {

                $this->info("Requesting data for $symbol...");

                $response = $client->request('GET', $endpoint, ['query' => array(
                    'q'     => $symbol,
                    'token' => $token
                )]);

                if ($response->getStatusCode() == 200) {
                    $content = json_decode($response->getBody(), true);

                    if ($content['count'] > 0) {
                        foreach ($content['result'] as $result_data) {
                            if ($result_data['symbol'] == $symbol) {
                                $this->info(json_encode($result_data));

                                WatchlistStockUs::updateOrCreate([
                                    'symbol' => $result_data['symbol'],
                                    'name' => $result_data['description']
                                ], [
                                    'symbol' => $result_data['symbol'],
                                    'name' => $result_data['description']
                                ]);
                            }
                        }
                    } else {
                        $this->info('Empty data !');
                    }
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
