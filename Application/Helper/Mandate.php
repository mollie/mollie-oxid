<?php

namespace Mollie\Payment\Application\Helper;

use OxidEsales\Eshop\Core\Registry;
use Mollie\Payment\Application\Helper\Payment;
use Mollie\Api\Types\MandateMethod;

class Mandate
{
    /**
     * @var Mandate
     */
    protected static $oInstance = null;

    /**
     * Create singleton instance of mandate helper
     *
     * @return Mandate
     */
    public static function getInstance()
    {
        if (self::$oInstance === null) {
            self::$oInstance = oxNew(self::class);
        }
        return self::$oInstance;
    }

    /**
     * Check if customer has an active mandate
     *
     * @param $oUser
     * @return bool
     */
    public function hasActivePayPalMandate($oUser)
    {
        if (!empty($this->getActivePayPalMandateId($oUser))) {
            return true;
        }
        return false;
    }

    /**
     * Get paypal mandate id
     *
     * @param $oUser
     * @return string|false
     */
    public function getActivePayPalMandateId($oUser)
    {
        if ($oUser && $oUser->hasAccount() && !empty((string)$oUser->oxuser__molliecustomerid->value) && Payment::getInstance()->getMollieMode() == 'live') {
            $oMandateList = Payment::getInstance()->loadMollieApi()->mandates->listForId((string)$oUser->oxuser__molliecustomerid->value);
            foreach ($oMandateList as $oMandate) {
                if ($oMandate->isValid() === true && strtolower($oMandate->method) == 'paypal') {
                    return $oMandate->id;
                }
            }
        }
        return false;
    }
}
