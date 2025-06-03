<?php

namespace Mollie\Payment\Application\Model\Payment;

class KlarnaPayLater extends Base
{
    /**
     * Payment id in the oxid shop
     *
     * @var string
     */
    protected $sOxidPaymentId = 'mollieklarnapaylater';

    /**
     * Method code used for API request
     *
     * @var string
     */
    protected $sMolliePaymentCode = 'klarnapaylater';

    /**
     * @var bool
     */
    protected $blNeedsExtendedAddress = true;
}
