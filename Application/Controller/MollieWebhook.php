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
     * The render function
     */
    public function render()
    {
        if (Registry::getRequest()->getRequestParameter('testByMollie')) {
            return $this->_sThisTemplate;
        }

        $sTransactionId = Registry::getRequest()->getRequestParameter('id');
        if (!empty($sTransactionId)) {
            $oOrder = oxNew(Order::class);
            if ($oOrder->mollieLoadOrderByTransactionId($sTransactionId) === true) {
                $oOrder->mollieGetPaymentModel()->getTransactionHandler()->processTransaction($oOrder);
            } 
        }

        return $this->_sThisTemplate;
    }
}
