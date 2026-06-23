<?php

namespace Mollie\Payment\Application\Model\Payment;

use OxidEsales\Eshop\Application\Model\Order;

class Riverty extends Base
{
    /**
     * Payment id in the oxid shop
     *
     * @var string
     */
    protected $sOxidPaymentId = 'mollieriverty';

    /**
     * Method code used for API request
     *
     * @var string
     */
    protected $sMolliePaymentCode = 'riverty';

    /**
     * @var bool
     */
    protected $blNeedsExtendedAddress = true;

    /**
     * Riverty only supports manual capture mode with Payments API
     *
     * @var string|false
     */
    protected $sCaptureMethod = 'manual';

    /**
     * If filled, the payment method will only be shown if one of the allowed currencies is active in checkout
     *
     * @var array
     */
    protected $aAllowedCurrencies = [
        'EUR'
    ];

    /**
     * Array with country-codes the payment method is restricted to
     * If property is set to false it is available to all countries
     *
     * @var array|false
     */
    protected $aBillingCountryRestrictedTo = [
        'NL',
        'BE',
        'DE',
        'AT',
    ];

    /**
     * @var bool
     */
    protected $blIsShippedCaptureSupported = true;

    /**
     * Returns if payment has to be captured manually
     *
     * @param Order $oOrder
     * @return bool
     */
    public function isManualCaptureNeeded(Order $oOrder)
    {
        if ($oOrder->mollieIsManualCaptureMethod() === true) {
            return true;
        }
        return parent::isManualCaptureNeeded($oOrder);
    }

    /**
     * shipped_capture leads to automatic capture when order is marked as shipped
     *
     * @return string|false
     */
    public function getConfiguredCaptureMode()
    {
        return "shipped_capture";
    }
}
