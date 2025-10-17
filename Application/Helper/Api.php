<?php

namespace Mollie\Payment\Application\Helper;

use Mollie\Payment\Application\Model\RequestLog;
use OxidEsales\Eshop\Core\Registry;

class Api
{
    /**
     * @var Api
     */
    protected static $oInstance = null;

    /**
     * Create singleton instance of api helper
     *
     * @return Api
     */
    public static function getInstance()
    {
        if (self::$oInstance === null) {
            self::$oInstance = oxNew(self::class);
        }
        return self::$oInstance;
    }

    /**
     * Format prices to always have 2 decimal places
     *
     * @param double $dPrice
     * @return string
     */
    public function formatPrice($dPrice)
    {
        return number_format($dPrice, 2, '.', '');
    }

    /**
     * Returns amount array used for different prices
     *
     * @param double $dPrice
     * @param string $sCurrency
     * @return array
     */
    public function getAmountArray($dPrice, $sCurrency)
    {
        return [
            'value' => $this->formatPrice($dPrice),
            'currency' => $sCurrency
        ];
    }

    /**
     * @param array $aRequest
     * @param object $oResponse
     * @param string|null $sOrderId
     * @param string|null $sStoreId
     * @return void
     */
    public function logApiRequest($aRequest, $oResponse, $sOrderId = null, $sStoreId = null)
    {
        $oRequestLog = oxNew(RequestLog::class);
        $oRequestLog->logRequest($aRequest, $oResponse, $sOrderId, $sStoreId);
    }

    /**
     * Reformats errormessage
     *
     * @param  string $sErrorMessage
     * @return string
     */
    public function formatErrorMessage($sErrorMessage)
    {
        $iBodyPos = stripos($sErrorMessage, 'Request body:');
        if ($iBodyPos !== false) {
            $sErrorMessage = substr($sErrorMessage, 0, $iBodyPos);
        }

        $iDocumentationPos = stripos($sErrorMessage, 'Documentation:');
        if ($iDocumentationPos !== false) {
            $sErrorMessage = substr($sErrorMessage, 0, $iDocumentationPos);
        }

        return trim($sErrorMessage);
    }
}
