<?php

namespace Mollie\Payment\extend\Application\Controller;

use Mollie\Payment\Application\Helper\User;
use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Application\Model\Country;
use OxidEsales\Eshop\Core\Registry;
use Mollie\Payment\Application\Helper\Order as OrderHelper;

class PaymentController extends PaymentController_parent
{
    /**
     * Delete sess_challenge from session to trigger the creation of a new order when needed
     */
    public function init()
    {
        $sSessChallenge = Registry::getSession()->getVariable('sess_challenge');
        $blMollieIsRedirected = Registry::getSession()->getVariable('mollieIsRedirected');
        if (!empty($sSessChallenge) && $blMollieIsRedirected === true) {
            OrderHelper::getInstance()->cancelCurrentOrder();
        }
        Registry::getSession()->deleteVariable('mollieIsRedirected');
        parent::init();
    }

    /**
     * Returns if current order is being considered as a B2B order
     *
     * @param  Basket $oBasket
     * @return bool
     */
    protected function mollieIsB2BOrder($oBasket)
    {
        $oUser = $oBasket->getBasketUser();
        if (!empty($oUser->oxuser__oxcompany->value)) {
            return true;
        }
        return false;
    }

    /**
     * Removes Mollie payment methods which are not available for the current basket situation. The limiting factors can be:
     * 1. Payment method not activated in the Mollie dashboard or for the current billing country, basket amount, currency situation
     * 2. BasketSum is outside of the min-/max-limits of the payment method
     * 3. Payment method has a billing country restriction and customer is not from that country
     * 4. Payment method is only available for B2B orders and current order is not a B2B order
     * 5. Currently selected currency is not supported by payment method
     *
     * @return void
     */
    protected function mollieRemoveUnavailablePaymentMethods()
    {
        $oBasket = Registry::getSession()->getBasket();
        $sBillingCountryCode = User::getInstance()->getBillingCountry($oBasket);
        foreach ($this->_oPaymentList as $oPayment) {
            if (method_exists($oPayment, 'isMolliePaymentMethod') && $oPayment->isMolliePaymentMethod() === true) {
                $sCurrency = $oBasket->getBasketCurrency()->name;
                $oMolliePayment = $oPayment->getMolliePaymentModel($oBasket->getPrice()->getBruttoPrice(), $sCurrency);
                if ($oMolliePayment->isMolliePaymentActive($sBillingCountryCode, $oBasket->getPrice()->getBruttoPrice(), $sCurrency) === false ||
                    $oMolliePayment->mollieIsBasketSumInLimits($oBasket->getPrice()->getBruttoPrice(), $sBillingCountryCode, $sCurrency) === false ||
                    $oMolliePayment->mollieIsMethodAvailableForCountry($sBillingCountryCode) === false ||
                    ($oMolliePayment->isOnlyB2BSupported() === true && $this->mollieIsB2BOrder($oBasket) === false) ||
                    $oMolliePayment->isCurrencySupported($sCurrency) === false
                ) {
                    unset($this->_oPaymentList[$oPayment->getId()]);
                }
            }
        }
    }

    /**
     * Template variable getter. Returns paymentlist
     *
     * @return object
     */
    public function getPaymentList()
    {
        $blFilterList = false;
        if ($this->_oPaymentList === null) {
            $blFilterList = true;
        }

        parent::getPaymentList();
        if ($blFilterList === true) { // filtering only needed once this filtered list remains in _oPaymentList but method is called multiple times
            $this->mollieRemoveUnavailablePaymentMethods();
        }
        return $this->_oPaymentList;
    }
}
