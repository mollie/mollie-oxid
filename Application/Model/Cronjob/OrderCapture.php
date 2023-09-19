<?php

namespace Mollie\Payment\Application\Model\Cronjob;

use Mollie\Payment\Application\Helper\Payment;
use OxidEsales\Eshop\Application\Model\Order;
use Mollie\Payment\Application\Model\Payment\Base;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Registry;

class OrderCapture extends \Mollie\Payment\Application\Model\Cronjob\Base
{
    /**
     * Id of current cronjob
     *
     * @var string
     */
    protected $sCronjobId = 'mollie_capture_orders';

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
    protected function getSendedOrders()
    {
        $aOrders = [];

        $sProcessingFolder = Registry::getConfig()->getShopConfVar('sMollieStatusProcessing');
        $sTriggerDate = date('Y-m-d H:i:s', time() - (60 * 60 * 24));
        $sMinPaidDate = date('Y-m-d H:i:s', time() - (60 * 2)); // This will prevent finishing legit orders before the customer does
        $sQuery = " SELECT
                        OXID
                    FROM
                        oxorder
                    WHERE
                        oxstorno = 0 AND
                        oxpaymenttype LIKE '%mollie%' AND
                        oxorderdate < ? AND
                        oxtransstatus = 'OK' AND
                        oxfolder = ? AND
                        OXSENDDATE != '0000-00-00 00:00:00' AND
                        mollieshipmenthasbeenmarked != '0' AND
                        oxpaid < ?";
        $aParams = [$sTriggerDate, $sProcessingFolder, $sMinPaidDate];
        if ($this->getShopId() !== false) {
            $sQuery .= " AND oxshopid = ? ";
            $aParams[] = $this->getShopId();
        }
        $aResult = DatabaseProvider::getDb()->getAll($sQuery, $aParams);
        foreach ($aResult as $aRow) {
            $aOrders[] = $aRow[0];
        }

        return $aOrders;
    }

    /**
     * Collects exired order ids and captures the authorized amount
     *
     * @return bool
     */
    protected function handleCronjob(): bool
    {
        $aUnfinishedOrders = $this->getSendedOrders();
        foreach ($aUnfinishedOrders as $sUnfinishedOrderId) {
            $oOrder = oxNew(Order::class);
            if ($oOrder->load($sUnfinishedOrderId) ) {
                $oOrder->captureOrder();
                $this->outputInfo("Finished Order with ID ".$oOrder->getId());
            }
        }
        return true;
    }

}
