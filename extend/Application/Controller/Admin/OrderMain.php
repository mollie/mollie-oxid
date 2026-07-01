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
        $aParams = Registry::getRequest()->getRequestParameter('editval');

        $blUpdateTrackingCode = false;
        $oOrder = oxNew(\OxidEsales\Eshop\Application\Model\Order::class);
        $oOrder->load($this->getEditObjectId());
        if (!empty($aParams['oxorder__oxtrackcode']) &&
            $oOrder->isLoaded() &&
            $oOrder->mollieIsMolliePaymentUsed() &&
            $aParams['oxorder__oxtrackcode'] != $oOrder->oxorder__oxtrackcode->value &&
            $oOrder->oxorder__oxsenddate->value != '-' &&
            $oOrder->oxorder__oxsenddate->value != '0000-00-00 00:00:00'
        ) {
            $blUpdateTrackingCode = true;
        }

        if (Registry::getConfig()->getRequestParameter("setPayment") === 'oxempty' && $oOrder->mollieIsMolliePaymentUsed()) {
            $_POST['setPayment'] = $oOrder->oxorder__oxpaymenttype->value; // Prevent payment type being set to oxempty
        }

        parent::save();

        if ($blUpdateTrackingCode === true) {
            $oOrder->mollieUpdateShippingTrackingCode($aParams['oxorder__oxtrackcode']);
        }
    }
}
