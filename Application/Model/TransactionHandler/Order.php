<?php

namespace Mollie\Payment\Application\Model\TransactionHandler;

use OxidEsales\Eshop\Application\Model\Order as CoreOrder;
use Mollie\Api\Resources\Order as ApiOrder;
use OxidEsales\Eshop\Core\Registry;

class Order extends Base
{
    /**
     * Handle order according to the given transaction status
     *
     * @param ApiOrder $oTransaction
     * @param CoreOrder $oOrder
     * @param string $sType
     * @return array
     */
    protected function handleTransactionStatus(ApiOrder $oTransaction, CoreOrder $oOrder, $sType)
    {
        $blSuccess = false;

        /**
         * Check if last payment was canceled, failed or expired and redirect customer to cart for retry.
         */
        $oLastPayment = isset($oTransaction->_embedded->payments) ? end($oTransaction->_embedded->payments) : null;
        $sLastPaymentStatus = isset($oLastPayment) ? $oLastPayment->status : null;
        if ($sLastPaymentStatus == 'canceled' || $sLastPaymentStatus == 'failed' || $sLastPaymentStatus == 'expired') {
            $oOrder->cancelOrder();
            return ['success' => false, 'status' => $sLastPaymentStatus];
        }

        if ($oTransaction->isPaid() || $oTransaction->isAuthorized()) {
            if ($oTransaction->amount->currency != $oOrder->oxorder__oxcurrency->value) {
                return ['success' => false, 'status' => 'paid', 'error' => 'Currency does not match.'];
            }

            if ($oOrder->mollieIsPaid() === false && $sType == 'webhook') {
                if ($oOrder->oxorder__oxstorno->value == 1) {
                    $oOrder->mollieUncancelOrder();
                }

                if (abs($oTransaction->amount->value - $oOrder->oxorder__oxtotalordersum->value) < 0.01) {
                    if ($oTransaction->isPaid()) {
                        $oOrder->mollieMarkAsPaid();
                    }
                    $oOrder->mollieSetFolder(Registry::getConfig()->getShopConfVar('sMollieStatusProcessing'));
                }
            }
            $blSuccess = true;
        }

        if ($oTransaction->isRefunded()) {
            $blSuccess = true;
        }

        if ($oTransaction->isCreated()) {
            if ($oTransaction->method == 'banktransfer') {
                $sBankTransferPending = $oOrder->mollieGetPaymentModel()->getConfigParam('pending_status');
                if (!empty($sBankTransferPending)) {
                    $oOrder->mollieSetFolder($sBankTransferPending);
                }
            }
            $blSuccess = true;
        }

        if (($oTransaction->isCanceled() || $oTransaction->isExpired())) {
            $oOrder->cancelOrder();
        }

        if ($oTransaction->isCompleted()) {
            $blSuccess = true;
        }
        return ['success' => $blSuccess, 'status' => $oTransaction->status];
    }
}
