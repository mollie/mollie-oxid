<?php

namespace Mollie\Payment\Application\Controller;

use Mollie\Payment\Application\Helper\PaymentFinishLink;
use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Registry;

class MollieFinishPayment extends FrontendController
{
    /**
     * @var string
     */
    protected $_sThisTemplate = 'molliewebhook.tpl';

    /**
     * Returns order or false if no id given, token is invalid or order not eligible
     *
     * @return bool|object
     */
    protected function getOrder()
    {
        $oRequest = Registry::getRequest();

        $sOrderId = $oRequest->getRequestParameter('id');
        if ($sOrderId) {
            $oOrder = oxNew(Order::class);
            if (!$oOrder->load($sOrderId) || $oOrder->mollieIsPaymentFinishTokenValid($oRequest->getRequestParameter(PaymentFinishLink::PARAM_TOKEN)) === false) {
                Registry::getUtilsView()->addErrorToDisplay('MOLLIE_ERROR_PAYMENT_LINK_INVALID');
                return false;
            }

            if ($oOrder->mollieIsEligibleForPaymentFinish()) {
                return $oOrder;
            }
        }
        return false;
    }

    /**
     * The render function
     */
    public function render()
    {
        $sRedirectUrl = Registry::getConfig()->getSslShopUrl()."?cl=basket";

        $oOrder = $this->getOrder();
        if ($oOrder !== false) {
            // this should redirect to Mollie - so this method should not be finished
            $oOrder->mollieReinitializePayment();
        }

        Registry::getUtils()->redirect($sRedirectUrl);
    }
}
