<?php

namespace App\Console\Commands;

use App\Helpers\CoinMarketCapHelper;
use App\Helpers\RateHelper;
use App\Models\WatchlistStockCrypto;
use Carbon\Carbon;
use DB;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;

class GetCryptoUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'watch:crypto';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retrieving Crypto Update on Coin Market Cap';

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
        $start = microtime(true);
        try {
            $rate = RateHelper::getRate('USD');
            $data_get = 1;

            do {
                $content = CoinMarketCapHelper::request('GET', 'cryptocurrency/listings/latest', [
                    'limit' => 4000,
                    'aux' => "tags",
                    'start' => $data_get
                ]);

                $all_data = $content['status']['total_count'];
                $data_get += sizeof($content['data']);

                $this->info("all_data : $all_data");
                $this->info("data_get : $data_get");

                foreach ($content['data'] as $data) {
                    WatchlistStockCrypto::updateOrCreate(
                        ['symbol' => $data['symbol'], 'name' => $data['name']],
                        [
                            'current_price' => RateHelper::calcWithRate($rate, $data['quote']['USD']['price']),
                            'percent_change_1h' => $data['quote']['USD']['percent_change_1h'],
                            'percent_change_24h' => $data['quote']['USD']['percent_change_24h'],
                            'last_updated' => new Carbon($data['quote']['USD']['last_updated']),
                        ]
                    );
                }
            } while ($data_get < $all_data);

            return 1;

        } catch (GuzzleException | Exception $e) {
            $this->error($e->getMessage());
            return 0;
        } finally {
            $time = microtime(true) - $start;
            $this->info("Execute time : $time");
        }
    }


}
