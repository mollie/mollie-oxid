<?php

namespace Mollie\Payment\Application\Model;

use Mollie\Payment\Application\Model\Payment\Base;
use OxidEsales\Eshop\Application\Model\Order;
use Mollie\Api\Resources\Order as ApiOrder;
use Mollie\Api\Resources\Payment;
use OxidEsales\Eshop\Core\Registry;

class TransactionHandler
{
    /**
     * Logfile name
     *
     * @var string
     */
    protected $sLogFileName = 'MollieTransactions.log';

    /**
     * Handle order according to the given transaction status
     *
     * @param ApiOrder $oTransaction
     * @param Order $oOrder
     * @param string $sType
     * @return array
     */
    protected function handleOrderTransactionStatus(ApiOrder $oTransaction, Order $oOrder, $sType)
    {
        $blSuccess = false;

        /**
         * Check if last payment was canceled, failed or expired and redirect customer to cart for retry.
         */
        $oLastPayment = isset($oTransaction->_embedded->payments) ? end($oTransaction->_embedded->payments) : null;
        $sLastPaymentStatus = isset($oLastPayment) ? $oLastPayment->status : null;
        if ($sLastPaymentStatus == 'canceled' || $sLastPaymentStatus == 'failed' || $sLastPaymentStatus == 'expired') {
            $oOrder->cancelOrder();
            $msg = ['success' => false, 'status' => $sLastPaymentStatus];
            return $msg;
        }

        if ($oTransaction->isPaid() || $oTransaction->isAuthorized()) {
            if ($oTransaction->amount->currency != $oOrder->oxorder__oxcurrency->value) {
                return ['success' => false, 'status' => 'paid', 'error' => 'Currency does not match.'];
            }

            if ($oOrder->mollieIsPaid() === false && $sType == 'webhook') {
                if ($oOrder->oxorder__oxstorno->value == 1) {
                    // uncancel?
                }

                if (abs($oTransaction->amount->value - $oOrder->oxorder__oxtotalordersum->value) < 0.01) {
                    if ($oTransaction->isPaid()) {
                        $oOrder->mollieMarkAsPaid();
                    }

                    if ($oTransaction->isAuthorized()) {
                        // remove paid flag?
                        // script cant land here
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

        if (($oTransaction->isCanceled() || $oTransaction->isExpired()) && $sType == 'webhook') {
            $oOrder->cancelOrder();
        }

        if ($oTransaction->isCompleted()) {
            $blSuccess = true;
        }
        return ['success' => $blSuccess, 'status' => $oTransaction->status];
    }

    /**
     * Handle order according to the given transaction status
     *
     * @param Payment $oTransaction
     * @param Order $oOrder
     * @param string $sType
     * @return array
     */
    protected function handlePaymentTransactionStatus(Payment $oTransaction, Order $oOrder, $sType)
    {
        $blSuccess = false;
        $sStatus = $oTransaction->status;

        if ($oTransaction->isPaid() && $oTransaction->hasRefunds() === false) {
            if ($oTransaction->amount->currency != $oOrder->oxorder__oxcurrency->value) {
                return ['success' => false, 'status' => 'paid', 'error' => 'Currency does not match.'];
            }

            if ($oOrder->mollieIsPaid() === false && $sType == 'webhook') {
                if ($oOrder->oxorder__oxstorno->value == 1) {
                    // uncancel?
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

        if (($oTransaction->isCanceled() || $oTransaction->isExpired() || $oTransaction->isExpired()) && $sType == 'webhook') {
            $oOrder->cancelOrder();
        }

        if ($oTransaction->isPending()) {
            $blSuccess = true;
            $sStatus = 'pending';
        }
        return ['success' => $blSuccess, 'status' => $sStatus];
    }

    /**
     * Log transaction status to log file if enabled
     *
     * @param $aResult
     * @return void
     */
    protected function logResult($aResult)
    {
        if ((bool)Registry::getConfig()->getShopConfVar('blMollieLogTransactionInfo') === true) {
            $sMessage = date("Y-m-d h:i:s")." Transaction handled: ".print_r($aResult, true)." \n";

            $sLogFilePath = getShopBasePath().'/log/'.$this->sLogFileName;
            $oLogFile = fopen($sLogFilePath, "a");
            if ($oLogFile) {
                fwrite($oLogFile, $sMessage);
                fclose($oLogFile);
            }
        }
    }

    /**
     * Process transaction status after payment and in the webhook
     *
     * @param Order $oOrder
     * @param string $sType
     * @return array
     */
    public function processTransaction(Order $oOrder, $sType = 'webhook')
    {
        /** @var Base $oPaymentModel */
        $oPaymentModel = $oOrder->mollieGetPaymentModel();

        try {
            $oTransaction = $oPaymentModel->getApiEndpoint()->get($oOrder->oxorder__oxtransid->value, ["embed" => "payments"]);

            if ($oPaymentModel->getApiMethod() == 'order') {
                $aResult = $this->handleOrderTransactionStatus($oTransaction, $oOrder, $sType);
            } else {
                $aResult = $this->handlePaymentTransactionStatus($oTransaction, $oOrder, $sType);
            }
        } catch(\Exception $exc) {
            $aResult = ['success' => false, 'status' => 'exception', 'error' => $exc->getMessage()];
        }

        $aResult['transactionId'] = $oOrder->oxorder__oxtransid->value;
        $aResult['orderId'] = $oOrder->getId();
        $aResult['type'] = $sType;

        $this->logResult($aResult);

        return $aResult;
    }
}
