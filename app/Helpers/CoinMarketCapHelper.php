<?php

namespace App\Helpers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class CoinMarketCapHelper
{
    /**
     * @throws GuzzleException
     */
    static function request(string $method, string $endpoint = '', array $payload = [])
    {
        $client = new Client;
        $response = $client->request($method, self::getUrl($endpoint),
            [
                'query' => $payload,
                'headers' => [
                    'Accept' => 'application/json',
                    'X-CMC_PRO_API_KEY' => self::getToken()
                ]
            ]
        );

        return json_decode($response->getBody(), true);
    }

    static function getUrl(string $endpoint = ''): string
    {
        $baseUrl = config('app.coinmarketcap.url');
        return "$baseUrl/$endpoint";
    }

    static function getToken(): string
    {
        return config('app.coinmarketcap.api_key');
    }
}
