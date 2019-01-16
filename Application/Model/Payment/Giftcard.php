<?php

namespace Mollie\Payment\Application\Model\Payment;

class Giftcard extends Base
{
    /**
     * Payment id in the oxid shop
     *
     * @var string
     */
    protected $sOxidPaymentId = 'molliegiftcard';

    /**
     * Method code used for API request
     *
     * @var string
     */
    protected $sMolliePaymentCode = 'giftcard';

    /**
     * Determines custom config template if existing, otherwise false
     *
     * @var string|bool
     */
    protected $sCustomConfigTemplate = 'molliegiftcard.tpl';
}
