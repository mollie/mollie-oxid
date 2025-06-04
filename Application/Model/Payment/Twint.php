<?php

namespace Mollie\Payment\Application\Model\Payment;

use OxidEsales\Eshop\Application\Model\Order;

class Twint extends Base
{
    /**
     * Payment id in the oxid shop
     *
     * @var string
     */
    protected $sOxidPaymentId = 'mollietwint';

    /**
     * Method code used for API request
     *
     * @var string
     */
    protected $sMolliePaymentCode = 'twint';

    /**
     * If filled, the payment method will only be shown if one of the allowed currencies is active in checkout
     *
     * @var array
     */
    protected $aAllowedCurrencies = [
        'CHF'
    ];

    /**
     * Is used to show in backend if payment method can be used in general
     * This method has the purpose to be overloaded by child-classes with specific parameters
     *
     * @return bool
     */
    public function isMolliePaymentActiveInGeneral()
    {
        return $this->isMolliePaymentActive(false, 5, 'CHF'); // only available for payment with CHF
    }

    /**
     * Return parameters specific to the given payment type, if existing
     *
     * @param Order $oOrder
     * @return array
     */
    public function getPaymentSpecificParameters(Order $oOrder)
    {
        $aParams = parent::getPaymentSpecificParameters($oOrder);

        if ($this->getApiMethod($oOrder) == 'payment') {
            $aParams['locale'] = 'de_CH';
        }
        return $aParams;
    }
}
