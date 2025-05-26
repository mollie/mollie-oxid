<?php

namespace Mollie\Payment\Application\Model\Cronjob;

use Mollie\Payment\Application\Helper\Payment;
use OxidEsales\Eshop\Application\Model\Order;
use Mollie\Payment\Application\Model\Payment\Base;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Registry;

class FinishOrders extends \Mollie\Payment\Application\Model\Cronjob\Base
{
    /**
     * Id of current cronjob
     *
     * @var string
     */
    protected $sCronjobId = 'mollie_finish_orders';

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
    protected function getPaidUnfinishedOrders()
    {
        $aOrders = [];

        $sProcessingFolder = Registry::getConfig()->getShopConfVar('sMollieStatusProcessing');
        $iMollieCronFinishOrdersDays = (int)Registry::getConfig()->getShopConfVar('iMollieCronFinishOrdersDays');
        if (empty($iMollieCronFinishOrdersDays)) {
            $iMollieCronFinishOrdersDays = 14;
        }
        $sTriggerDate = date('Y-m-d H:i:s', time() - (60 * 60 * 24 * $iMollieCronFinishOrdersDays));
        $sMinPaidDate = date('Y-m-d H:i:s', time() - (60 * 2)); // This will prevent finishing legit orders before the customer does
        $sQuery = " SELECT 
                        OXID 
                    FROM 
                        oxorder 
                    WHERE 
                        oxstorno = 0 AND 
                        oxpaymenttype LIKE '%mollie%' AND 
                        oxorderdate > ? AND 
                        oxtransstatus = 'NOT_FINISHED' AND 
                        oxfolder = ? AND 
                        oxpaid != '0000-00-00 00:00:00' AND
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
     * Collects exired order ids and cancels these orders
     *
     * @return bool
     */
    protected function handleCronjob()
    {
        $aUnfinishedOrders = $this->getPaidUnfinishedOrders();
        foreach ($aUnfinishedOrders as $sUnfinishedOrderId) {
            $oOrder = oxNew(Order::class);

            self::outputExtendedInfo("Check if order can be finished", $sUnfinishedOrderId);
            if ($oOrder->load($sUnfinishedOrderId) && $oOrder->mollieIsOrderInUnfinishedState()) {
                $oOrder->mollieFinishOrder();
                self::outputStandardInfo("Successfully finished order", $oOrder->getId());
            } else {
                self::outputExtendedInfo("Order is not in a finishable state", $sUnfinishedOrderId);
            }
        }
        return true;
    }
}
