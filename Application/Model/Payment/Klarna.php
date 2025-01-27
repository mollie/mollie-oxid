<?php

namespace Mollie\Payment\Application\Model\Payment;

class Klarna extends Base
{
    /**
     * Payment id in the oxid shop
     *
     * @var string
     */
    protected $sOxidPaymentId = 'mollieklarna';

    /**
     * Method code used for API request
     *
     * @var string
     */
    protected $sMolliePaymentCode = 'klarna';

    /**
     * Determines if the payment methods only supports the order API
     *
     * @var bool
     */
    protected $blIsOnlyOrderApiSupported = true;
}
