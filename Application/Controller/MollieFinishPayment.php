<?php

namespace Mollie\Payment\Application\Controller;

use Mollie\Payment\Application\Model\TransactionHandler;
use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Registry;

class MollieFinishPayment extends FrontendController
{
    /**
     * @var string
     */
    protected $_sThisTemplate = 'molliewebhook.tpl';

    /**
     * Returns order or false if no id given or order not eligible
     *
     * @return bool|object
     */
    protected function getOrder()
    {
        $sOrderId = Registry::getRequest()->getRequestParameter('id');
        if ($sOrderId) {
            $oOrder = oxNew(Order::class);
            if ($oOrder->load($sOrderId) && $oOrder->mollieIsEligibleForPaymentFinish()) {
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
