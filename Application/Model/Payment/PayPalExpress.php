<?php

namespace Mollie\Payment\Application\Model\Payment;

use Mollie\Api\Exceptions\ApiException;
use Mollie\Payment\Application\Helper\PayPalExpress as PayPalExpressHelper;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Application\Model\Order;

class PayPalExpress extends Base
{
    /**
     * Oxid Mollie PPE
     */
    const OXID = 'molliepaypalexpress';

    /**
     * Payment id in the oxid shop
     *
     * @var string
     */
    protected $sOxidPaymentId = self::OXID;

    /**
     * Method code used for API request
     *
     * @var string
     */
    protected $sMolliePaymentCode = 'paypal';

    /**
     * Determines if the payment method can be display in the payment list in checkout
     *
     * @var bool
     */
    protected $blShowInPaymentList = false;

    /**
     * Method to perform certain actions when the API call failed
     *
     * @param  ApiException $exc
     * @return void
     */
    public function handlePaymentError(ApiException $exc)
    {
        PayPalExpressHelper::getInstance()->mollieCancelPayPalExpress(false);
    }
}