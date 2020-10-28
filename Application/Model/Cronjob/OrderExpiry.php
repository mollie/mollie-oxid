<?php

namespace Mollie\Payment\Application\Model\Cronjob;

use Mollie\Payment\Application\Helper\Payment;
use OxidEsales\Eshop\Application\Model\Order;
use Mollie\Payment\Application\Model\Payment\Base;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Registry;

class OrderExpiry extends \Mollie\Payment\Application\Model\Cronjob\Base
{
    /**
     * Id of current cronjob
     *
     * @var string
     */
    protected $sCronjobId = 'mollie_order_expiry';

    /**
     * Default cronjob interval in minutes
     *
     * @var int
     */
    protected $iDefaultMinuteInterval = 10;

    /**
     * Collects all expired order ids
     *
     * @return array
     */
    protected function getExpiredOrders()
    {
        $aOrders = [];

        $oPaymentHelper = Payment::getInstance();
        foreach ($oPaymentHelper->getMolliePaymentMethods() as $sPaymentId => $sPaymentTitle) {
            $oPaymentModel = $oPaymentHelper->getMolliePaymentModel($sPaymentId);
            $aOrders = array_merge($aOrders, $this->getExpiredOrdersForPaymentMethod($oPaymentModel));
        }
        return $aOrders;
    }

    /**
     * Collects all expired order ids for given payment type
     *
     * @param  Base $oPaymentModel
     * @return array
     */
    protected function getExpiredOrdersForPaymentMethod(Base $oPaymentModel)
    {
        $aOrders = [];
        $iExpiryDays = $oPaymentModel->getExpiryDays();
        if ($oPaymentModel->isOrderExpirySupported() === true && $iExpiryDays !== 'deactivated' && is_numeric($iExpiryDays)) {
            $sPendingFolder = Registry::getConfig()->getShopConfVar('sMollieStatusPending');
            $sQuery = "SELECT OXID FROM oxorder WHERE oxstorno = 0 AND oxpaymenttype = '{$oPaymentModel->getOxidPaymentId()}' AND oxfolder = '{$sPendingFolder}' AND oxorderdate < DATE_ADD(NOW(), INTERVAL -{$iExpiryDays} DAY)";
            $aResult = DatabaseProvider::getDb()->getAll($sQuery);
            foreach ($aResult as $aRow) {
                $aOrders[] = $aRow[0];
            }
        }
        return $aOrders;
    }

    /**
     * Collects exired order ids and cancels these orders
     *
     * @return bool
     */
    protected function handleCronjob()
    {
        $aExpiredOrders = $this->getExpiredOrders();
        foreach ($aExpiredOrders as $sExpiredOrderId) {
            $oOrder = oxNew(Order::class);
            if ($oOrder->load($sExpiredOrderId)) {
                echo 'Cancel '.$oOrder->getId()."\n";
                $oOrder->cancelOrder();
            }
        }
        return true;
    }
}
