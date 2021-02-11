<?php

namespace Mollie\Payment\Application\Model\TransactionHandler;

use OxidEsales\Eshop\Application\Model\Order;
use Mollie\Api\Resources\Payment as ApiPayment;
use OxidEsales\Eshop\Core\Registry;

class Payment extends Base
{
    /**
     * Handle order according to the given transaction status
     *
     * @param ApiPayment $oTransaction
     * @param Order $oOrder
     * @param string $sType
     * @return array
     */
    protected function handleTransactionStatus(ApiPayment $oTransaction, Order $oOrder, $sType)
    {
        $blSuccess = false;
        $sStatus = $oTransaction->status;

        if ($oTransaction->isPaid() && $oTransaction->hasRefunds() === false) {
            if ($oTransaction->amount->currency != $oOrder->oxorder__oxcurrency->value) {
                return ['success' => false, 'status' => 'paid', 'error' => 'Currency does not match.'];
            }

            if ($oOrder->mollieIsPaid() === false && $sType == 'webhook') {
                if ($oOrder->oxorder__oxstorno->value == 1) {
                    $oOrder->mollieUncancelOrder();
                }

                if (abs($oTransaction->amount->value - $oOrder->oxorder__oxtotalordersum->value) < 0.01) {
                    $oOrder->mollieMarkAsPaid();
                    $oOrder->mollieSetFolder(Registry::getConfig()->getShopConfVar('sMollieStatusProcessing'));
                }
            }
            $blSuccess = true;
        }

        if ($oTransaction->hasRefunds()) {
            $blSuccess = true;
        }

        if ($oTransaction->isOpen()) {
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

        if ($oTransaction->isPending()) {
            $blSuccess = true;
            $sStatus = 'pending';
        }
        return ['success' => $blSuccess, 'status' => $sStatus];
    }
}
