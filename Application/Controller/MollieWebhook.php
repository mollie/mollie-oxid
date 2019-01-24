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
                $oTransactionHandler = oxNew(TransactionHandler::class);
                $oTransactionHandler->processTransaction($oOrder);
            }
        }

        return $this->_sThisTemplate;
    }
}
