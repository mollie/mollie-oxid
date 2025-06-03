<?php

namespace Mollie\Payment\Application\Model\Payment;

class In3 extends Base
{
    /**
     * Payment id in the oxid shop
     *
     * @var string
     */
    protected $sOxidPaymentId = 'molliein3';

    /**
     * Method code used for API request
     *
     * @var string
     */
    protected $sMolliePaymentCode = 'in3';

    /**
     * @var bool
     */
    protected $blNeedsExtendedAddress = true;

    /**
     * Array with country-codes the payment method is restricted to
     * If property is set to false it is available to all countries
     *
     * @var array|false
     */
    protected $aBillingCountryRestrictedTo = [
        'NL',
    ];
}
