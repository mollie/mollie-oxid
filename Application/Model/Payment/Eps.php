<?php

namespace Mollie\Payment\Application\Model\Payment;

class Eps extends Base
{
    /**
     * Payment id in the oxid shop
     *
     * @var string
     */
    protected $sOxidPaymentId = 'mollieeps';

    /**
     * Method code used for API request
     *
     * @var string
     */
    protected $sMolliePaymentCode = 'eps';
}
