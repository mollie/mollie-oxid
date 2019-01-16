<?php

namespace Mollie\Payment\Application\Model\Payment;

class Banktransfer extends Base
{
    /**
     * Payment id in the oxid shop
     *
     * @var string
     */
    protected $sOxidPaymentId = 'molliebanktransfer';

    /**
     * Method code used for API request
     *
     * @var string
     */
    protected $sMolliePaymentCode = 'banktransfer';

    /**
     * Determines custom config template if existing, otherwise false
     *
     * @var string|bool
     */
    protected $sCustomConfigTemplate = 'molliebanktransfer.tpl';
}
