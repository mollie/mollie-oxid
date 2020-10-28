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

    /**
     * Check if the token was correctly configured
     *
     * @return bool
     */
    public function mollieIsTokenConfigured()
    {
        if (!Payment::getInstance()->getMollieToken()) {
            return false;
        }
        return true;
    }

    /**
     * Returns option array for expiry day configuration
     *
     * @return array
     */
    public function mollieGetExpiryDayOptions()
    {
        $aOptions = ['deactivated' => Registry::getLang()->translateString('MOLLIE_DEACTIVATED')];
        for($i = 1; $i <= 30; $i++) {
            $aOptions[$i] = $i.' '.Registry::getLang()->translateString('MOLLIE_ORDER_EXPIRY_DAYS');
        }
        return $aOptions;
    }
}
