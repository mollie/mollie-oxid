<?php

namespace Mollie\Payment\Application\Model\Payment;

class Blik extends Base
{
    /**
     * Payment id in the oxid shop
     *
     * @var string
     */
    protected $sOxidPaymentId = 'mollieblik';

    /**
     * Method code used for API request
     *
     * @var string
     */
    protected $sMolliePaymentCode = 'blik';

    /**
     * If filled, the payment method will only be shown if one of the allowed currencies is active in checkout
     *
     * @var array
     */
    protected $aAllowedCurrencies = [
        'PLN'
    ];

    /**
     * Is used to show in backend if payment method can be used in general
     * This method has the purpose to be overloaded by child-classes with specific parameters
     *
     * @return bool
     */
    public function isMolliePaymentActiveInGeneral()
    {
        return $this->isMolliePaymentActive(false, 5, 'PLN'); // only available for payment with polish zloty
    }
}
