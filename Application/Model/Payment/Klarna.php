<?php

namespace Mollie\Payment\Application\Model\Payment;

class Klarna extends Base
{
    /**
     * Payment id in the oxid shop
     *
     * @var string
     */
    protected $sOxidPaymentId = 'mollieklarna';

    /**
     * Method code used for API request
     *
     * @var string
     */
    protected $sMolliePaymentCode = 'klarna';

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
        'PLN',
        'CZK',
        'RON',
        'HUF',
    ];

    /**
     * Array with country-codes the payment method is restricted to
     * If property is set to false it is available to all countries
     *
     * @var array|false
     */
    protected $aBillingCountryRestrictedTo = [
        'AT',' BE',' DK',' FI',' FR',' DE',' IT',' IE',' NL',' NO',' PT',' ES',' SE',' CH',' GB',' GR',' PL',' SK',' CZ',' RO',' HU'
    ];

    /**
     * @var bool
     */
    protected $blNeedsExtendedAddress = true;

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
