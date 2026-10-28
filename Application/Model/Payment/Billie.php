<?php

namespace Mollie\Payment\Application\Model\Payment;

class Billie extends Base
{
    /**
     * Payment id in the oxid shop
     *
     * @var string
     */
    protected $sOxidPaymentId = 'molliebillie';

    /**
     * Method code used for API request
     *
     * @var string
     */
    protected $sMolliePaymentCode = 'billie';

    /**
     * If filled, the payment method will only be shown if one of the allowed currencies is active in checkout
     *
     * @var array
     */
    protected $aAllowedCurrencies = [
        'EUR',
        'DKK',
        'SEK',
        'NOK',
        'CHF',
        'GBP',
    ];

    /**
     * Array with country-codes the payment method is restricted to
     * If property is set to false it is available to all countries
     *
     * @var array|false
     */
    protected $aBillingCountryRestrictedTo = [
        'DE', 'AT', 'SE', 'NL', 'FR', 'NO', 'CH', 'UK', 'DK', 'FI', 'ES', 'IT'
    ];

    /**
     * @var bool
     */
    protected $blNeedsExtendedAddress = true;

    /**
     * Determines if the payment method is only available for B2B orders
     * B2B mode is assumed when the company field in the billing address is filled
     *
     * @var bool
     */
    protected $blIsOnlyB2BSupported = true;

    /**
     * @var bool
     */
    protected $blIsShippedCaptureSupported = true;

    /**
     * @var array|null
     */
    protected $aAvailableCaptureMethods = [
        'shipped_capture',
        'direct_capture',
    ];

    /**
     * Returns the capture method
     *
     * @return string|false
     */
    public function getCaptureMethod()
    {
        $sCaptureMethod = $this->getConfiguredCaptureMode();
        if ($sCaptureMethod == 'shipped_capture') {
            return 'manual';
        }
        return parent::getCaptureMethod();
    }
}
