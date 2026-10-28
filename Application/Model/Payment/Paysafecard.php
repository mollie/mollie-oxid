<?php

namespace Mollie\Payment\Application\Model\Payment;

class Paysafecard extends Base
{
    /**
     * Payment id in the oxid shop
     *
     * @var string
     */
    protected $sOxidPaymentId = 'molliepaysafecard';

    /**
     * Method code used for API request
     *
     * @var string
     */
    protected $sMolliePaymentCode = 'paysafecard';

    /**
     * If filled, the payment method will only be shown if one of the allowed currencies is active in checkout
     *
     * @var array
     */
    protected $aAllowedCurrencies = [
        'EUR',
        'CHF',
        'EUR',
        'GBP',
        'NOK',
        'PLN',
        'RON',
        'SEK',
    ];

    /**
     * Array with country-codes the payment method is restricted to
     * If property is set to false it is available to all countries
     *
     * @var array|false
     */
    protected $aBillingCountryRestrictedTo = [
        'AT','AU','BE','BG','CA','CH','CY','CZ','DE','DK','ES','FI','FR','GB','GE','GI','GR','HR','HU','IE','IT','LI','LT','LU','MT','MX','NL','NZ','NO','PE','PL','PT','RO','SE','SI','SK','UY'
    ];
}
