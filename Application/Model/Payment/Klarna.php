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
     * @var bool
     */
    protected $blNeedsExtendedAddress = true;
}
