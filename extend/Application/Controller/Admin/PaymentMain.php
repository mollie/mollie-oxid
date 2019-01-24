<?php

namespace Mollie\Payment\extend\Application\Controller\Admin;

use Mollie\Payment\Application\Helper\Payment;
use OxidEsales\Eshop\Core\Registry;
use Mollie\Payment\Application\Model\PaymentConfig;

class PaymentMain extends PaymentMain_parent
{
    /**
     * Saves payment parameters changes.
     *
     * @return void
     */
    public function save()
    {
        parent::save();

        $aMollieParams = Registry::getRequest()->getRequestParameter("mollie");

        $oPaymentConfig = oxNew(PaymentConfig::class);
        $oPaymentConfig->savePaymentConfig($this->getEditObjectId(), $aMollieParams);
    }

    /**
     * Return order status array
     *
     * @return array
     */
    public function mollieGetOrderFolders()
    {
        return Registry::getConfig()->getConfigParam('aOrderfolder');
    }
}
