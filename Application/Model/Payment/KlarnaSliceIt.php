<?php

namespace Mollie\Payment\Application\Model\Payment;

class KlarnaSliceIt extends Base
{
    /**
     * Payment id in the oxid shop
     *
     * @var string
     */
    protected $sOxidPaymentId = 'mollieklarnasliceit';

    /**
     * Method code used for API request
     *
     * @var string
     */
    protected $sMolliePaymentCode = 'klarnasliceit';

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
