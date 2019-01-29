<?php

namespace Mollie\Payment\extend\Application\Controller;

use Mollie\Payment\Application\Model\TransactionHandler;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Registry;

class OrderController extends OrderController_parent
{
    /**
     * Load previously created order
     *
     * @return Order|false
     */
    protected function mollieGetOrder()
    {
        $sSessChallenge = Registry::getSession()->getVariable('sess_challenge');
        if (!empty($sSessChallenge)) {
            $oOrder = oxNew(Order::class);
            $oOrder->load($sSessChallenge);
            if ($oOrder->isLoaded() === true) {
                return $oOrder;
            }
        }
        return false;
    }

    /**
     * Writes error-status to session and redirects to payment page
     *
     * @param string $sErrorLangIdent
     * @return void
     */
    protected function redirectWithError($sErrorLangIdent)
    {
        Registry::getSession()->setVariable('payerror', -20);
        Registry::getSession()->setVariable('payerrortext', Registry::getLang()->translateString($sErrorLangIdent));
        Registry::getUtils()->redirect(Registry::getConfig()->getCurrentShopUrl().'index.php?cl=payment');
    }

    /**
     *
     * @return string
     */
    public function handleMollieReturn()
    {
        if ($this->getPayment()->isMolliePaymentMethod()) {
            $oOrder = $this->mollieGetOrder();
            if (!$oOrder) {
                $this->redirectWithError('MOLLIE_ERROR_ORDER_NOT_FOUND');
            }

            $sTransactionId = $oOrder->oxorder__oxtransid->value;
            if (empty($sTransactionId)) {
                $this->redirectWithError('MOLLIE_ERROR_TRANSACTIONID_NOT_FOUND');
            }

            $oTransactionHandler = oxNew(TransactionHandler::class);
            $aResult = $oTransactionHandler->processTransaction($oOrder, 'success');

            if ($aResult['success'] === false) {
                Registry::getSession()->deleteVariable('sess_challenge');

                $sErrorIdent = 'MOLLIE_ERROR_SOMETHING_WENT_WRONG';
                if ($aResult['status'] == 'canceled') {
                    $sErrorIdent = 'MOLLIE_ERROR_ORDER_CANCELED';
                }
                $this->redirectWithError($sErrorIdent);
            }

            // else - continue to parent::execute since success must be true
        }

        return parent::execute();
    }
}
