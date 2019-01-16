<?php

namespace Mollie\Payment\Application\Model\Payment;

class Sofort extends Base
{
    /**
     * Payment id in the oxid shop
     *
     * @var string
     */
    protected $sOxidPaymentId = 'molliesofort';

    /**
     * Method code used for API request
     *
     * @var string
     */
    protected $sMolliePaymentCode = 'sofort';
}
