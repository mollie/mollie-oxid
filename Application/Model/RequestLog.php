<?php

namespace Mollie\Payment\Application\Model;

use OxidEsales\Eshop\Core\DatabaseProvider;

class RequestLog
{
    public static $sTableName = "mollierequestlog";

    /**
     * Return create query for module installation
     *
     * @return string
     */
    public static function getTableCreateQuery()
    {
        return "CREATE TABLE `".self::$sTableName."` (
            `OXID` INT(32) NOT NULL AUTO_INCREMENT COLLATE 'latin1_general_ci',
            `TIMESTAMP` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `ORDERID` VARCHAR(32) NOT NULL,
            `STOREID` VARCHAR(32) NOT NULL,
            `REQUESTTYPE` VARCHAR(32) NOT NULL DEFAULT '',
            `RESPONSESTATUS` VARCHAR(32) NOT NULL DEFAULT '',
            `REQUEST` TEXT NOT NULL,
            `RESPONSE` TEXT NOT NULL,
            PRIMARY KEY (OXID)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT COLLATE='utf8_general_ci';";
    }

    /**
     * Encode data object to a saveable string
     *
     * @param $oData
     * @return string
     */
    protected function encodeData($oData)
    {
        return json_encode($oData);
    }

    /**
     * Decode data array from a encoded string
     *
     * @param string $sData
     * @return array
     */
    protected function decodeData($sData)
    {
        return json_decode($sData, true);
    }

    /**
     * Logs an error response from a request, coming in form of an exception
     *
     * @param array $aRequest
     * @param string $sCode
     * @param string $sMessage
     * @param string $sMethod
     */
    public function logExceptionResponse($aRequest, $sCode, $sMessage, $sMethod)
    {
        $aResponse = [
            'resource' => $sMethod,
            'status' => 'ERROR',
            'code' => $sCode,
            'customMessage' => $sMessage
        ];

        $this->logRequest($aRequest, (object)$aResponse);
    }

    /**
     * Remove unnecessary information from the response
     *
     * @param object $oResponse
     * @return array
     */
    protected function formatResponse($oResponse)
    {
        $aResponse = get_object_vars($oResponse);
        if (isset($aResponse['_links'])) {
            unset($aResponse['_links']);
        }
        return $aResponse;
    }

    /**
     * Parse data and write the request and response in one DB entry
     *
     * @param array $aRequest
     * @param $oResponse
     */
    public function logRequest($aRequest, $oResponse)
    {
        $oDb = DatabaseProvider::getDb();

        $sOrderId = $oDb->quote(isset($aRequest['metadata']['order_id']) ? $aRequest['metadata']['order_id'] : '');
        $sStoreId = $oDb->quote(isset($aRequest['metadata']['store_id']) ? $aRequest['metadata']['store_id'] : '');
        $sRequestType = $oDb->quote(!is_null($oResponse->resource) ? $oResponse->resource : '');
        $sResponseStatus = $oDb->quote(!is_null($oResponse->status) ? $oResponse->status : '');

        $sSavedRequest = $oDb->quote($this->encodeData($aRequest));
        $sSavedResponse = $oDb->quote($this->encodeData($this->formatResponse($oResponse)));

        $sQuery = " INSERT INTO `".self::$sTableName."` (
                        ORDERID, STOREID, REQUESTTYPE, RESPONSESTATUS, REQUEST, RESPONSE
                    ) VALUES (
                        $sOrderId,
                        $sStoreId,
                        $sRequestType,
                        $sResponseStatus,
                        $sSavedRequest,
                        $sSavedResponse
                    )";
        $oDb->Execute($sQuery);
    }
}
