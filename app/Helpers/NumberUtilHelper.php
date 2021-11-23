<?php

namespace App\Helpers;

class NumberUtilHelper
{
    /**
     * @param string $strValue
     * @return float
     */
    static function floatValue(string $strValue): float
    {
        $val = str_replace(",", ".", $strValue);
        $val = preg_replace('/\.(?=.*\.)/', '', $val);
        return floatval($val);
    }


}
