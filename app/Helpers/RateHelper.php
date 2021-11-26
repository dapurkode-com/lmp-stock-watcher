<?php

namespace App\Helpers;

use Exception;
use Goutte\Client;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\DomCrawler\Crawler;
use function PHPUnit\Framework\throwException;

class RateHelper
{
    /**
     * @return string
     */
    static function getUrl(): string
    {
        return "https://www.bi.go.id/id/statistik/informasi-kurs/transaksi-bi/default.aspx";
    }

    static function getCrawler(): Crawler
    {
        $client = new Client();
        return $client->request('GET', self::getUrl());
    }

    /**
     * @param string $currency
     * @return array|null
     * @throws Exception
     */
    static function getRate(string $currency = ''): ?array
    {
        $crawler = self::getCrawler();
        $rateCrawlers = $crawler->filterXPath("//table[2]/tbody/tr");

        foreach ($rateCrawlers as $content){
            $row = new Crawler($content);
            if($row->filter('td')->eq(0)->text() == $currency){
                return [
                    'currency'  => $row->filter('td')->eq(0)->text(),
                    'value'     => NumberUtilHelper::floatValue($row->filter('td')->eq(1)->text()),
                    'sell'      => NumberUtilHelper::floatValue($row->filter('td')->eq(2)->text()),
                    'buy'       => NumberUtilHelper::floatValue($row->filter('td')->eq(3)->text())
                ];
            }
        }

        throw new Exception('Rate not found');
    }

    /**
     * @param array $rate
     * @param $price
     * @return float
     */
    static function calcWithRate(array $rate, $price): float
    {
        if (gettype($price) == "string") $price = NumberUtilHelper::floatValue($price);

        return NumberUtilHelper::rounding($price / $rate['value'] * $rate['buy']);
    }
}
