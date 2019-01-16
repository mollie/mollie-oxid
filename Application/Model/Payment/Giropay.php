<?php

namespace Mollie\Payment\Application\Model\Payment;

class Giropay extends Base
{
    /**
     * Payment id in the oxid shop
     *
     * @var string
     */
    protected $sOxidPaymentId = 'molliegiropay';

    /**
     * Method code used for API request
     *
     * @var string
     */
    protected $sMolliePaymentCode = 'giropay';
}
