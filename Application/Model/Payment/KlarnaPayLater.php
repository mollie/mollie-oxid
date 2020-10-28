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
     * Determines if the payment methods only supports the order API
     *
     * @var bool
     */
    protected $blIsOnlyOrderApiSupported = true;

    /**
     * Determines if the payment methods supports the order expiry mechanism
     *
     * @var bool
     */
    protected $blIsOrderExpirySupported = false;
}
