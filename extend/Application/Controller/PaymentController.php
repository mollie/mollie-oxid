<?php

namespace Mollie\Payment\extend\Application\Controller;

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
     * Removes Mollie payment methods which are not activated in the Mollie account from the payment list
     *
     * @return void
     */
    protected function mollieRemoveDeactivatedPaymentTypes()
    {
        foreach ($this->_oPaymentList as $oPayment) {
            if (method_exists($oPayment, 'isMolliePaymentMethod') && $oPayment->isMolliePaymentMethod() === true) {
                $oBasket = Registry::getSession()->getBasket();
                $oMolliePayment = $oPayment->getMolliePaymentModel($oBasket->getPrice()->getBruttoPrice(), $oBasket->getBasketCurrency()->name);
                if ($oMolliePayment->isMolliePaymentActive() === false || $oMolliePayment->mollieIsBasketSumInLimits($oBasket->getPrice()->getBruttoPrice()) === false) {
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
        if ((bool)Registry::getConfig()->getShopConfVar('blMollieRemoveDeactivatedMethods') === true) {
            $this->mollieRemoveDeactivatedPaymentTypes();
        }
        return $this->_oPaymentList;
    }
}
