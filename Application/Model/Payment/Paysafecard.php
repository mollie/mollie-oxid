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
}
