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
}
