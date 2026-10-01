<?php

namespace Mollie\Payment\Application\Model\Payment;

use Mollie\Payment\Application\Helper\Mandate;
use Mollie\Payment\Application\Helper\Payment;
use Mollie\Payment\Application\Helper\User;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Registry;
class PayPal extends Base
{
    /**
     * Payment id in the oxid shop
     *
     * @var string
     */
    protected $sOxidPaymentId = 'molliepaypal';

    /**
     * Method code used for API request
     *
     * @var string
     */
    protected $sMolliePaymentCode = 'paypal';

    /**
     * Determines if a shipping address has to be sent every time
     *
     * @var bool
     */
    protected $blShippingAddressIsMandatory = true;

    /**
     * Determines custom frontend template if existing, otherwise false
     *
     * @var string|bool
     */
    protected $sCustomFrontendTemplate = 'molliepaypal.tpl';

    /**
     * Return parameters specific to the given payment type, if existing
     *
     * @param Order $oOrder
     * @return array
     */
    public function getPaymentSpecificParameters(Order $oOrder)
    {
        $aParams = parent::getPaymentSpecificParameters($oOrder);

        // Feature is only activated for live mode, because Mollie throws an error when you send a request to live API with a test customerId which was created during testing before switching to live mode
        if ($this->canShowPayPalVaulting() && (bool)$this->getDynValueParameter('paypal_vault_accepted') === true) {
            $oUser = $oOrder->getUser();
            if (empty((string)$oUser->oxuser__molliecustomerid->value)) {
                User::getInstance()->createMollieUser($oUser);
            }
            $aParams['customerId'] = (string)$oUser->oxuser__molliecustomerid->value;

            $sMandateId = Mandate::getInstance()->getActivePayPalMandateId($oUser);
            if (!empty($sMandateId)) {
                $aParams['sequenceType'] = 'recurring';
                $aParams['mandateId'] = $sMandateId;
            } else {
                $aParams['sequenceType'] = 'first';
            }
        }

        return $aParams;
    }

    /**
     * @return bool
     */
    public function hasActivePayPalVault()
    {
        $oUser = Registry::getConfig()->getActiveView()->getUser();
        if (Mandate::getInstance()->hasActivePayPalMandate($oUser) === true) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function canShowPayPalVaulting()
    {
        $oUser = Registry::getConfig()->getActiveView()->getUser();
        if ($oUser && $oUser->hasAccount() && Payment::getInstance()->getMollieMode() == 'live') {
            return true;
        }
        return false;
    }
}
