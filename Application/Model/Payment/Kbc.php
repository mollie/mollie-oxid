<?php

namespace Mollie\Payment\Application\Model\Payment;

class Kbc extends Base
{
    /**
     * Payment id in the oxid shop
     *
     * @var string
     */
    protected $sOxidPaymentId = 'molliekbc';

    /**
     * Method code used for API request
     *
     * @var string
     */
    protected $sMolliePaymentCode = 'kbc';
}
