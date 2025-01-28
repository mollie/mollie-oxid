<?php

namespace Mollie\Payment\Application\Helper;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Application\Model\Order as CoreOrder;

class Order
{
    /**
     * @var Order
     */
    protected static $oInstance = null;

    /**
     * Create singleton instance of order helper
     *
     * @return Order
     */
    public static function getInstance()
    {
        if (self::$oInstance === null) {
            self::$oInstance = oxNew(self::class);
        }
        return self::$oInstance;
    }

    /**
     * Cancel current order because it failed i.e. because customer canceled payment
     *
     * @return void
     */
    public function cancelCurrentOrder()
    {
        $sSessChallenge = Registry::getSession()->getVariable('sess_challenge');

        $oOrder = oxNew(CoreOrder::class);
        if ($oOrder->load($sSessChallenge) === true) {
            if ($oOrder->oxorder__oxtransstatus->value != 'OK') {
                $oOrder->cancelOrder();
            }
        }
        Registry::getSession()->deleteVariable('sess_challenge');
    }


    /**
     * Fix usual string representation to a workable float value
     *
     * @param string $sPrice
     * @return string
     */
    public function fixPrice($sPrice)
    {
        $iPosComma = strpos($sPrice, ",");
        $iPosPoint = strpos($sPrice, ".");
        if ($iPosPoint !== false && $iPosComma !== false && $iPosPoint < $iPosComma) {
            $sPrice = str_replace('.', '', $sPrice); // Assuming  a price like "1.499,95" - fix it to "1499,95"
        }
        return str_replace(',', '.', $sPrice); // Fix price from "1499,95" to "1499.95"
    }
}
