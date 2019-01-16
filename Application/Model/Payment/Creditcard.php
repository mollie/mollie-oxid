<?php

namespace Mollie\Payment\Application\Model\Payment;

class Creditcard extends Base
{
    /**
     * Payment id in the oxid shop
     *
     * @var string
     */
    protected $sOxidPaymentId = 'molliecreditcard';

    /**
     * Method code used for API request
     *
     * @var string
     */
    protected $sMolliePaymentCode = 'creditcard';
}
