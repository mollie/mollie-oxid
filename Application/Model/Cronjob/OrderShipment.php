<?php

namespace Mollie\Payment\Application\Model\Cronjob;

use Mollie\Payment\Application\Helper\Payment;
use OxidEsales\Eshop\Application\Model\Order;
use Mollie\Payment\Application\Model\Payment\Base;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Registry;

class OrderShipment extends \Mollie\Payment\Application\Model\Cronjob\Base
{
    /**
     * Id of current cronjob
     *
     * @var string
     */
    protected $sCronjobId = 'mollie_order_shipment';

    /**
     * Default cronjob interval in minutes
     *
     * @var int
     */
    protected $iDefaultMinuteInterval = 10;

    /**
     * Collects all orders with a send date which was not marked yet
     *
     * @return array
     */
    protected function getUnmarkedShippedOrders()
    {
        $aOrders = [];

        $sMinSendDate = date('Y-m-d H:i:s', time() - (60 * 60 * 24)); // only look at orders in the last 24 hours

        $sQuery = " SELECT 
                        oxid 
                    FROM 
                        oxorder 
                    WHERE 
                        oxpaymenttype LIKE '%mollie%' AND
                        oxtransid LIKE '%ord_%' AND
                        oxsenddate >= ? AND
                        mollieshipmenthasbeenmarked = 0";
        $aParams = [$sMinSendDate];
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
     * Collects unmarked order ids and marks them as shipped
     *
     * @return bool
     */
    protected function handleCronjob()
    {
        $aUnmarkedOrders = $this->getUnmarkedShippedOrders();
        foreach ($aUnmarkedOrders as $sUnmarkedOrderId) {
            $oOrder = oxNew(Order::class);
            if ($oOrder->load($sUnmarkedOrderId) && $oOrder->mollieIsMolliePaymentUsed()) {
                $oOrder->mollieMarkOrderAsShipped();
                $this->outputInfo("Marked order-id ".$oOrder->getId()." as shipped.");
            }
        }
        return true;
    }
}
