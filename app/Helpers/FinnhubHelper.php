<?php

namespace App\Helpers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class FinnhubHelper
{
    /**
     * @throws GuzzleException
     */
    static function request(string $method, string $endpoint = '', array $payload = [])
    {
        $client = new Client;
        $response = $client->request($method, self::getUrl($endpoint),
            [
                'query' => array_merge($payload, ['token' => self::getToken()]),
                'headers' => [
                    'Accept' => 'application/json',
                ]
            ]
        );

        return json_decode($response->getBody(), true);
    }

    static function getUrl(string $endpoint = ''): string
    {
        $baseUrl = config('app.finnhub.url');
        return "$baseUrl/$endpoint";
    }

    static function getToken(): string
    {
        return config('app.finnhub.api_key');
    }
}
