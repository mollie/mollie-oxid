<?php

namespace Mollie\Payment\Application\Model\Payment;

use OxidEsales\Eshop\Application\Model\Order;

class Vipps extends Base
{
    /**
     * Payment id in the oxid shop
     *
     * @var string
     */
    protected $sOxidPaymentId = 'mollievipps';

    /**
     * Method code used for API request
     *
     * @var string
     */
    protected $sMolliePaymentCode = 'vipps';

    /**
     * If filled, the payment method will only be shown if one of the allowed currencies is active in checkout
     *
     * @var array
     */
    protected $aAllowedCurrencies = [
        'NOK'
    ];

    /**
     * Array with country-codes the payment method is restricted to
     * If property is set to false it is available to all countries
     *
     * @var array|false
     */
    protected $aBillingCountryRestrictedTo = [
        'NO',
    ];

    /**
     * Is used to show in backend if payment method can be used in general
     * This method has the purpose to be overloaded by child-classes with specific parameters
     *
     * @return bool
     */
    public function isMolliePaymentActiveInGeneral()
    {
        return $this->isMolliePaymentActive(false, 5, 'NOK'); // only available for payment with NOK
    }
}
