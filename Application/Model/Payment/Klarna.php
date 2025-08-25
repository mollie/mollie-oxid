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
     * @var bool
     */
    protected $blNeedsExtendedAddress = true;

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
        $sCaptureMethod = $this->getConfigParam('capture_method');
        if ($sCaptureMethod == 'shipped_capture') {
            return 'manual';
        }
        return parent::getCaptureMethod();
    }
}
