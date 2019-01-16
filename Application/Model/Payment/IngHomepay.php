<?php

namespace Mollie\Payment\Application\Model\Payment;

class IngHomepay extends Base
{
    /**
     * Payment id in the oxid shop
     *
     * @var string
     */
    protected $sOxidPaymentId = 'mollieinghomepay';

    /**
     * Method code used for API request
     *
     * @var string
     */
    protected $sMolliePaymentCode = 'inghomepay';
}
