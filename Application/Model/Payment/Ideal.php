<?php

namespace Mollie\Payment\Application\Model\Payment;

class Ideal extends Base
{
    /**
     * Payment id in the oxid shop
     *
     * @var string
     */
    protected $sOxidPaymentId = 'mollieideal';

    /**
     * Method code used for API request
     *
     * @var string
     */
    protected $sMolliePaymentCode = 'ideal';

    /**
     * Determines custom config template if existing, otherwise false
     *
     * @var string|bool
     */
    protected $sCustomConfigTemplate = 'mollieideal.tpl';
}
