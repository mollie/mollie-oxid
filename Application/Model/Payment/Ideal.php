<?php

namespace Mollie\Payment\Application\Model\Payment;

use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Registry;

class Ideal extends Base
{
    /**
     * Payment id in the oxid shop
     *
     * @var string
     */
    protected $sOxidPaymentId = 'mollieideal';

    /**
     * Method code used for API request
     *
     * @var string
     */
    protected $sMolliePaymentCode = 'ideal';

    /**
     * If filled, the payment method will only be shown if one of the allowed currencies is active in checkout
     *
     * @var array
     */
    protected $aAllowedCurrencies = [
        'EUR',
    ];

    /**
     * Array with country-codes the payment method is restricted to
     * If property is set to false it is available to all countries
     *
     * @var array|false
     */
    protected $aBillingCountryRestrictedTo = [
        'NL'
    ];
}
