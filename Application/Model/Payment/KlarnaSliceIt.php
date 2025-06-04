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
     * @var bool
     */
    protected $blNeedsExtendedAddress = true;
}
