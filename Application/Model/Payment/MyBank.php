<?php

namespace Mollie\Payment\Application\Model\Payment;

class MyBank extends Base
{
    /**
     * Payment id in the oxid shop
     *
     * @var string
     */
    protected $sOxidPaymentId = 'molliemybank';

    /**
     * Method code used for API request
     *
     * @var string
     */
    protected $sMolliePaymentCode = 'mybank';
}
