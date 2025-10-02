<?php

namespace Mollie\Payment\extend\Application\Controller;

use Mollie\Payment\Application\Helper\User;
use Mollie\Payment\Application\Helper\PayPalExpress;
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

        if ($this->mollieHasPayPalExpressAddressBeenChanged() === true) {
            $this->mollieCancelPayPalExpress();
        }

        Registry::getSession()->deleteVariable('mollieIsRedirected');
        Registry::getSession()->deleteVariable('mollieRedirectUrl');
        parent::init();
    }

    /**
     * Removes Mollie payment methods which are not available for the current basket situation
     *
     * @return void
     */
    protected function mollieRemoveUnavailablePaymentMethods()
    {
        $oBasket = Registry::getSession()->getBasket();
        foreach ($this->_oPaymentList as $oPayment) {
            if (method_exists($oPayment, 'isMolliePaymentMethod') && $oPayment->isMolliePaymentMethod() === true) {
                $oMolliePayment = $oPayment->getMolliePaymentModel($oBasket->getPrice()->getBruttoPrice(), $oBasket->getBasketCurrency()->name);
                if ($oMolliePayment->isMethodAvailable($oBasket) === false) {
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

    /**
     * Returns url to cancel PayPal Express checkout session and show payment list
     *
     * @return string
     */
    public function getMolliePayPaylExpressCheckoutCancelUrl()
    {
        return Registry::getConfig()->getSslShopUrl()."?cl=payment&fnc=mollieCancelPayPalExpress";
    }

    /**
     * Cancel PPE action
     *
     * @return void
     */
    public function mollieCancelPayPalExpress()
    {
        PayPalExpress::getInstance()->mollieCancelPayPalExpress();
    }

    /**
     * Returns oxid payment id of PPE
     *
     * @return string
     */
    public function mollieGetPayPalExpressPaymentId()
    {
        return \Mollie\Payment\Application\Model\Payment\PayPalExpress::OXID;
    }

    /**
     * Checks if the PayPal Express address has been changed by comparing the current address with the stored address hash in the session.
     *
     * @return bool True if the address has been changed, false otherwise.
     */
    protected function mollieHasPayPalExpressAddressBeenChanged()
    {
        if ($this->getUser()->mollieGetEncodedDeliveryAddress() != Registry::getSession()->getVariable('mollie_ppe_addresshash')) {
            return true;
        }

        if (Registry::getSession()->getVariable('blshowshipaddress') === false || empty(Registry::getSession()->getVariable('deladrid'))) { // PPE payments will always have differing delivery-address
            return true;
        }

        return false;
    }
}
