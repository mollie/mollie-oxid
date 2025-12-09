<?php

namespace Mollie\Payment\Application\Model\Payment;

// FCRM_REMOVE_ORDERS_API
class KlarnaPayNow extends Base
{
    /**
     * Payment id in the oxid shop
     *
     * @var string
     */
    protected $sOxidPaymentId = 'mollieklarnapaynow';

    /**
     * Method code used for API request
     *
     * @var string
     */
    protected $sMolliePaymentCode = 'klarnapaynow';

    /**
     * Determines if payment method is deprecated.
     * Deprecated methods are disabled, can't be used anymore and will be removed in a future release.
     * They stay in the module to allow finishing old orders where these methods have been used
     *
     * @var bool
     */
    protected $blMethodIsDeprecated = true;

    /**
     * @var bool
     */
    protected $blNeedsExtendedAddress = true;
}
