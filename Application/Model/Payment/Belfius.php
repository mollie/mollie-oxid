<?php

namespace Mollie\Payment\Application\Model\Payment;

class Belfius extends Base
{
    /**
     * Payment id in the oxid shop
     *
     * @var string
     */
    protected $sOxidPaymentId = 'molliebelfius';

    /**
     * Method code used for API request
     *
     * @var string
     */
    protected $sMolliePaymentCode = 'belfius';
}
