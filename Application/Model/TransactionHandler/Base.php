<?php

namespace Mollie\Payment\Application\Model\TransactionHandler;

use Mollie\Payment\Application\Model\Payment\Base as PaymentBase;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Registry;

abstract class Base
{
    /**
     * Logfile name
     *
     * @var string
     */
    protected $sLogFileName = 'MollieTransactions.log';

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
     * Check for given external trans id
     *
     * @param object $oTransaction
     * @param Order $oOrder
     * @return array
     */
    protected function handleExternalTransId($oTransaction, Order $oOrder)
    {
        $sExternalTransactionId = false;
        if (isset($oTransaction, $oTransaction->details, $oTransaction->details->paypalReference)) {
            $sExternalTransactionId = $oTransaction->details->paypalReference;
        }

        if (isset($oTransaction, $oTransaction->details, $oTransaction->details->transferReference)) {
            $sExternalTransactionId = $oTransaction->details->transferReference;
        }

        if ($sExternalTransactionId !== false) {
            $oOrder->mollieSetExternalTransactionId($sExternalTransactionId);
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
        try {
            $oTransaction = $oOrder->mollieGetTransaction();
            if (empty($oTransaction)) {
                throw new \Exception('Could not get transaction');
            }

            $aResult = $this->handleTransactionStatus($oTransaction, $oOrder, $sType);
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
