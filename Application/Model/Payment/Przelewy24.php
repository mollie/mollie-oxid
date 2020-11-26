<?php

namespace Mollie\Payment\Application\Model\Payment;

class Przelewy24 extends Base
{
    /**
     * Payment id in the oxid shop
     *
     * @var string
     */
    protected $sOxidPaymentId = 'mollieprzelewy24';

    /**
     * Method code used for API request
     *
     * @var string
     */
    protected $sMolliePaymentCode = 'przelewy24';
}
