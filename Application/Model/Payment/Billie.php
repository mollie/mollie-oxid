<?php

namespace Mollie\Payment\Application\Model\Payment;

class Billie extends Base
{
    /**
     * Payment id in the oxid shop
     *
     * @var string
     */
    protected $sOxidPaymentId = 'molliebillie';

    /**
     * Method code used for API request
     *
     * @var string
     */
    protected $sMolliePaymentCode = 'billie';

    /**
     * @var bool
     */
    protected $blNeedsExtendedAddress = true;

    /**
     * Determines if the payment method is only available for B2B orders
     * B2B mode is assumed when the company field in the billing address is filled
     *
     * @var bool
     */
    protected $blIsOnlyB2BSupported = true;
}
