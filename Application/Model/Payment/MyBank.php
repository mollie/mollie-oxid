<?php

namespace Mollie\Payment\Application\Model\Payment;

class MyBank extends Base
{
    /**
     * Payment id in the oxid shop
     *
     * @var string
     */
    protected $sOxidPaymentId = 'molliemybank';

    /**
     * Method code used for API request
     *
     * @var string
     */
    protected $sMolliePaymentCode = 'mybank';

    /**
     * @var bool
     */
    protected $blNeedsExtendedAddress = true;

    /**
     * Returns if payment method is available for the current basket situation. The limiting factors are:
     *
     * 1. All factors from parent call
     * 2. OrderAPI doesn't support requests without first- or lastname for MyBank, so don't show it
     *
     * @return bool
     */
    public function isMethodAvailable($oBasket)
    {
        $blReturn = parent::isMethodAvailable($oBasket);
        $oUser = $oBasket->getBasketUser();
        if ($this->getApiMethod() === 'order' && (empty($oUser->oxuser__oxfname->value) || empty($oUser->oxuser__oxlname->value))) {
            $blReturn = false;
        }
        return $blReturn;
    }
}
