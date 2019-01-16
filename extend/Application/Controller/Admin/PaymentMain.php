<?php

namespace Mollie\Payment\extend\Application\Controller\Admin;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
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

        $aMollieParams = Registry::get(Request::class)->getRequestParameter("mollie");

        $oPaymentConfig = oxNew(PaymentConfig::class);
        $oPaymentConfig->savePaymentConfig($this->getEditObjectId(), $aMollieParams);
    }
}
