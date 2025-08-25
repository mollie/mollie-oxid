<?php

namespace Mollie\Payment\Application\Model\Cronjob;

use Mollie\Payment\Application\Helper\Api;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Field;
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
    protected function getUncapturedOrders()
    {
        $aOrders = [];

        $sTriggerDate = date('Y-m-d H:i:s', time() - (60 * 60 * 24));
        $sQuery = " SELECT
                        OXID
                    FROM
                        oxorder
                    WHERE
                        oxstorno = 0 AND
                        oxpaymenttype LIKE '%mollie%' AND
                        oxorderdate < ? AND
                        oxtransstatus = 'OK' AND
                        OXSENDDATE != '0000-00-00 00:00:00' AND
                        MOLLIEWASCAPTURED = '0' AND
                        MOLLIECAPTUREMETHOD = 'manual'";
        $aParams = [$sTriggerDate];
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
     * @param Order $oOrder
     * @return bool
     */
    protected function isOrderAuthorized($oOrder)
    {
        $oTransaction = $oOrder->mollieGetTransaction();
        if ($oTransaction && $oTransaction->isAuthorized() === true) {
            return true;
        }
        return false;
    }

    /**
     * Collects exired order ids and captures the authorized amount
     *
     * @return bool
     */
    protected function handleCronjob(): bool
    {
        $aUncapturedOrders = $this->getUncapturedOrders();
        foreach ($aUncapturedOrders as $sOrderId) {
            $oOrder = oxNew(Order::class);

            self::outputExtendedInfo("Check if payment can be captured", $sOrderId);
            if ($oOrder->load($sOrderId) && $this->isOrderAuthorized($oOrder) === true) {
                $dAmount = (double)$oOrder->oxorder__oxtotalordersum->value;
                $aParams['amount'] = Api::getInstance()->getAmountArray($dAmount, $oOrder->oxorder__oxcurrency->value);

                self::outputExtendedInfo("Send capture request: ".print_r($aParams, true), $oOrder->getId());

                $result = $oOrder->mollieCaptureOrder($aParams);
                if ($result) {
                    self::outputStandardInfo("Successfully captured order", $oOrder->getId());
                    $oOrder->oxorder__molliewascaptured = new Field(1);
                    $oOrder->save();
                } else {
                    self::outputExtendedInfo("Received no response for capture -> Capture failed.", $oOrder->getId());
                }
            } else {
                self::outputExtendedInfo("Payment is not in a captureable state", $sOrderId);
            }
        }
        return true;
    }
}
