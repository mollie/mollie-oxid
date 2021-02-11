<?php

namespace Mollie\Payment\extend\Application\Controller\Admin;

use Mollie\Payment\Application\Helper\Payment;
use OxidEsales\Eshop\Core\Registry;
use Mollie\Payment\Application\Model\PaymentConfig;

class OrderMain extends OrderMain_parent
{
    /**
     * Method is used for overriding.
     */
    protected function onOrderSend()
    {
        parent::onOrderSend();

        $oOrder = oxNew(\OxidEsales\Eshop\Application\Model\Order::class);
        if ($oOrder->load($this->getEditObjectId()) && $oOrder->mollieIsMolliePaymentUsed()) {
            $oOrder->mollieMarkOrderAsShipped();
        }
    }

    /**
     * Saves main orders configuration parameters.
     */
    public function save()
    {
        $aParams = \OxidEsales\Eshop\Core\Registry::getRequest()->getRequestParameter('editval');

        $blUpdateTrackingCode = false;
        $oOrder = oxNew(\OxidEsales\Eshop\Application\Model\Order::class);
        if (!empty($aParams['oxorder__oxtrackcode']) &&
            $oOrder->load($this->getEditObjectId()) &&
            $oOrder->mollieIsMolliePaymentUsed() &&
            $aParams['oxorder__oxtrackcode'] != $oOrder->oxorder__oxtrackcode->value &&
            $oOrder->oxorder__oxsenddate->value != '-' &&
            $oOrder->oxorder__oxsenddate->value != '0000-00-00 00:00:00'
        ) {
            $blUpdateTrackingCode = true;
        }

        parent::save();

        if ($blUpdateTrackingCode === true) {
            $oOrder->mollieUpdateShippingTrackingCode($aParams['oxorder__oxtrackcode']);
        }
    }
}
