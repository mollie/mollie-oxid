<?php

namespace Mollie\Payment\Application\Model\Payment;

use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Registry;

class ApplePay extends Base
{
    /**
     * Payment id in the oxid shop
     *
     * @var string
     */
    protected $sOxidPaymentId = 'mollieapplepay';

    /**
     * Method code used for API request
     *
     * @var string
     */
    protected $sMolliePaymentCode = 'applepay';

    /**
     * Determines custom config template if existing, otherwise false
     *
     * @var string|bool
     */
    protected $sCustomConfigTemplate = 'mollie_config_applepay.tpl';

    /**
     * Determines custom frontend template if existing, otherwise false
     *
     * @var string|bool
     */
    protected $sCustomFrontendTemplate = 'mollieapplepay.tpl';

    /**
     * Determines if the payment method is hidden at first when payment list is displayed
     *
     * @var bool
     */
    protected $blIsMethodHiddenInitially = true;

    /**
     * Return parameters specific to the given payment type, if existing
     *
     * @param Order $oOrder
     * @return array
     */
    public function getPaymentSpecificParameters(Order $oOrder)
    {
        $aApplePayToken = Registry::getRequest()->getRequestEscapedParameter('token');
        if (!empty($aApplePayToken) && $oOrder->mollieIsApplePayButtonMode() === true) {
            return ['applePayPaymentToken' => json_encode($aApplePayToken)];
        }
        return parent::getPaymentSpecificParameters($oOrder);
    }

    /**
     * Returns if the payment methods needs to add the redirect url
     *
     * @param  Order $oOrder
     * @return bool
     */
    public function isRedirectUrlNeeded(Order $oOrder)
    {
        if ($oOrder->mollieIsApplePayButtonMode() === true) {
            return false;
        }
        return parent::isRedirectUrlNeeded($oOrder);
    }
}
