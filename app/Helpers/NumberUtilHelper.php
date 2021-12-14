<?php

namespace App\Helpers;

/**
 * NumberUtilHelper is a helper class for number operation
 *
 * @package App\Helpers
 * @author Satya Wibawa <i.g.b.n.satyawibawa@gmail.com>
 */
class NumberUtilHelper
{
    /**
     * Get float value from parsing string
     *
     * @param string $strValue
     * @return float
     */
    static function floatValue(string $strValue): float
    {
        $val = str_replace(",", ".", $strValue);
        $val = preg_replace('/\.(?=.*\.)/', '', $val);
        return floatval($val);
    }

    /**
     * Default rounding
     *
     * @param $value
     * @return float
     */
    static function rounding($value): float
    {
        return round($value, 0);
    }
}
