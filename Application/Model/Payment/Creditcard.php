<?php

namespace Mollie\Payment\Application\Model\Payment;

use Mollie\Payment\Application\Helper\Payment;
use Mollie\Payment\Application\Helper\User;
use OxidEsales\Eshop\Application\Model\Order;

class Creditcard extends Base
{
    /**
     * Payment id in the oxid shop
     *
     * @var string
     */
    protected $sOxidPaymentId = 'molliecreditcard';

    /**
     * Method code used for API request
     *
     * @var string
     */
    protected $sMolliePaymentCode = 'creditcard';

    /**
     * Determines custom config template if existing, otherwise false
     *
     * @var string|bool
     */
    protected $sCustomConfigTemplate = 'mollie_config_creditcard.tpl';

    /**
     * Determines custom frontend template if existing, otherwise false
     *
     * @var string|bool
     */
    protected $sCustomFrontendTemplate = 'molliecreditcard.tpl';

    /**
     * @var array|null
     */
    protected $aAvailableCaptureMethods = [
        'authorize_capture',
        'direct_capture',
        'automatic_capture',
    ];

    /**
     * Returns current Mollie profileId
     *
     * @return string
     * @throws \Mollie\Api\Exceptions\ApiException
     */
    public function getProfileId()
    {
        return Payment::getInstance()->getProfileId();
    }
    
    /**
     * Returns configured mollie mode
     *
     * @return string
     */
    public function getMollieMode()
    {
        return Payment::getInstance()->getMollieMode();
    }

    /**
     * Returns language locale
     *
     * @return string
     */
    public function getLocale()
    {
        return Payment::getInstance()->getLocale();
    }

    /**
     * Return parameters specific to the given payment type, if existing
     *
     * @param Order $oOrder
     * @return array
     */
    public function getPaymentSpecificParameters(Order $oOrder)
    {
        $aParams = parent::getPaymentSpecificParameters($oOrder);

        $oUser = $oOrder->getUser();
        // Feature is only activared for live mode, because Mollie throws an error when you send a request to live API with a test customerId which was created during testing before switching to live mode
        if ((bool)$this->getConfigParam('single_click_enabled') === true && $oUser && $oUser->hasAccount() && (bool)$this->getDynValueParameter('single_click_accepted') === true && $this->getMollieMode() == 'live') {
            if (empty((string)$oUser->oxuser__molliecustomerid->value)) {
                User::getInstance()->createMollieUser($oUser);
            }
            $aParams['customerId'] = (string)$oUser->oxuser__molliecustomerid->value;
        }

        $sCCToken = $this->getDynValueParameter('mollieCCToken');
        if (!empty($sCCToken)) {
            $aParams['cardToken'] = $sCCToken;
        }

        return $aParams;
    }

    /**
     * Returns the capture method
     *
     * @return string|false
     */
    public function getCaptureMethod()
    {
        $sCaptureMethod = $this->getConfiguredCaptureMode();
        if ($sCaptureMethod == 'authorize_capture') {
            return 'manual';
        } elseif ($sCaptureMethod == 'automatic_capture') {
            return 'automatic';
        }
        return parent::getCaptureMethod();
    }

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
}
