<?php

namespace Mollie\Payment\Application\Helper;

use OxidEsales\Eshop\Core\Registry;

class Api
{
    /**
     * @var Api
     */
    protected static $oInstance = null;

    /**
     * Create singleton instance of api helper
     *
     * @return Api
     */
    public static function getInstance()
    {
        if (self::$oInstance === null) {
            self::$oInstance = oxNew(self::class);
        }
        return self::$oInstance;
    }

    /**
     * Format prices to always have 2 decimal places
     *
     * @param double $dPrice
     * @return string
     */
    public function formatPrice($dPrice)
    {
        return number_format($dPrice, 2, '.', '');
    }

    /**
     * Returns amount array used for different prices
     *
     * @param double $dPrice
     * @param string $sCurrency
     * @return array
     */
    public function getAmountArray($dPrice, $sCurrency)
    {
        return [
            'value' => $this->formatPrice($dPrice),
            'currency' => $sCurrency
        ];
    }
}
