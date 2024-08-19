<?php

namespace Mollie\Payment\Application\Model\Payment;

class PayPal extends Base
{
    /**
     * Payment id in the oxid shop
     *
     * @var string
     */
    protected $sOxidPaymentId = 'molliepaypal';

    /**
     * Method code used for API request
     *
     * @var string
     */
    protected $sMolliePaymentCode = 'paypal';

    /**
     * Determines if a shipping address has to be sent every time
     *
     * @var bool
     */
    protected $blShippingAddressIsMandatory = true;
}
