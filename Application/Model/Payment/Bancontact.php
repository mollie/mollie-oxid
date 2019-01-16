<?php

namespace Mollie\Payment\Application\Model\Payment;

class Bancontact extends Base
{
    /**
     * Payment id in the oxid shop
     *
     * @var string
     */
    protected $sOxidPaymentId = 'molliebancontact';

    /**
     * Method code used for API request
     *
     * @var string
     */
    protected $sMolliePaymentCode = 'bancontact';
}
