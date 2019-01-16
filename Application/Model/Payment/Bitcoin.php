<?php

namespace Mollie\Payment\Application\Model\Payment;

class Bitcoin extends Base
{
    /**
     * Payment id in the oxid shop
     *
     * @var string
     */
    protected $sOxidPaymentId = 'molliebitcoin';

    /**
     * Method code used for API request
     *
     * @var string
     */
    protected $sMolliePaymentCode = 'bitcoin';
}
