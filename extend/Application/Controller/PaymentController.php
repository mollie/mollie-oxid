<?php

namespace Mollie\Payment\extend\Application\Controller;

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
     * Returns billing country code of current basket
     *
     * @param  Basket $oBasket
     * @return string
     */
    protected function mollieGetBillingCountry($oBasket)
    {
        $oUser = $oBasket->getBasketUser();

        $oCountry = oxNew(Country::class);
        $oCountry->load($oUser->oxuser__oxcountryid->value);

        return $oCountry->oxcountry__oxisoalpha2->value;
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
     * 1. Config option "blMollieRemoveDeactivatedMethods" activated AND payment method not activated in the Mollie dashboard
     * 2. Config option "blMollieRemoveByBillingCountry" activated AND payment method is not available for given billing country
     * 3. BasketSum is outside of the min-/max-limits of the payment method
     * 4. Payment method has a billing country restriction and customer is not from that country
     *
     * @return void
     */
    protected function mollieRemoveUnavailablePaymentMethods()
    {
        $blRemoveDeactivated = (bool)Registry::getConfig()->getShopConfVar('blMollieRemoveDeactivatedMethods');
        $blRemoveByBillingCountry = (bool)Registry::getConfig()->getShopConfVar('blMollieRemoveByBillingCountry');
        $oBasket = Registry::getSession()->getBasket();
        $sBillingCountryCode = $this->mollieGetBillingCountry($oBasket);
        foreach ($this->_oPaymentList as $oPayment) {
            if (method_exists($oPayment, 'isMolliePaymentMethod') && $oPayment->isMolliePaymentMethod() === true) {
                $oMolliePayment = $oPayment->getMolliePaymentModel($oBasket->getPrice()->getBruttoPrice(), $oBasket->getBasketCurrency()->name);
                if (($blRemoveDeactivated === true && $oMolliePayment->isMolliePaymentActive() === false) ||
                    ($blRemoveByBillingCountry === true && $oMolliePayment->isMolliePaymentActive($sBillingCountryCode) === false) ||
                    $oMolliePayment->mollieIsBasketSumInLimits($oBasket->getPrice()->getBruttoPrice()) === false ||
                    $oMolliePayment->mollieIsMethodAvailableForCountry($sBillingCountryCode) === false ||
                    ($oMolliePayment->isOnlyB2BSupported() === true && $this->mollieIsB2BOrder($oBasket) === false)
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
        parent::getPaymentList();
        $this->mollieRemoveUnavailablePaymentMethods();
        return $this->_oPaymentList;
    }
}
