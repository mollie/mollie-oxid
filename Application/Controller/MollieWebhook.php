<?php

namespace Mollie\Payment\Application\Controller;

use Mollie\Payment\Application\Model\TransactionHandler;
use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Registry;

class MollieWebhook extends FrontendController
{
    /**
     * @var string
     */
    protected $_sThisTemplate = 'molliewebhook.tpl';

    /**
     * Gather order id for the given transaction id from the db and load the order object if possible
     *
     * @param string $sTransactionId
     * @return Order|false
     */
    protected function getOrderByTransactionId($sTransactionId)
    {
        $sQuery = "SELECT oxid FROM oxorder WHERE oxtransid = ?";

        $sOrderId = DatabaseProvider::getDb()->getOne($sQuery, array($sTransactionId));
        if (!empty($sOrderId)) {
            $oOrder = oxNew(Order::class);
            $oOrder->load($sOrderId);
            if ($oOrder->isLoaded()) {
                return $oOrder;
            }
        }
        return false;
    }

    /**
     * The render function
     */
    public function render()
    {
        if (Registry::getRequest()->getRequestParameter('testByMollie')) {
            return $this->_sThisTemplate;
        }

        $sTransactionId = Registry::getRequest()->getRequestParameter('id');
        if (!empty($sTransactionId)) {
            $oOrder = $this->getOrderByTransactionId($sTransactionId);
            if ($oOrder) {
                $oOrder->mollieGetPaymentModel()->getTransactionHandler()->processTransaction($oOrder);
            } else {
                // Throw HTTP error when order not found, this will trigger Mollie to retry sending the status
                // For some payment methods the webhook is called before the order exists
                Registry::getUtils()->setHeader("HTTP/1.1 409 Conflict");
                Registry::getUtils()->showMessageAndExit("");
            }
        }

        return $this->_sThisTemplate;
    }
}
