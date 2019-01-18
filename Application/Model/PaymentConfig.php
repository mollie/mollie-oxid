<?php

namespace Mollie\Payment\Application\Model;

use OxidEsales\Eshop\Core\DatabaseProvider;

class PaymentConfig
{
    public static $sTableName = "molliepaymentconfig";

    /**
     * Return create query for module installation
     *
     * @return string
     */
    public static function getTableCreateQuery()
    {
        return "CREATE TABLE `".self::$sTableName."` (
            `OXID` CHAR(32) NOT NULL COLLATE 'latin1_general_ci',
            `API` VARCHAR(32) NOT NULL DEFAULT '',
            `CONFIG` TEXT NOT NULL,
            PRIMARY KEY (`OXID`)
        ) COLLATE='utf8_general_ci' ENGINE=InnoDB;";
    }

    /**
     * Save Mollie payment configuration for given payment type
     *
     * @param string $sPaymentId
     * @param array $aConfig
     * @return bool
     */
    public function savePaymentConfig($sPaymentId, $aConfig)
    {
        if (!isset($aConfig['api'])) {
            return false; // Faulty values - cancel execution
        }

        $sMollieApi = $aConfig['api'];
        unset($aConfig['api']);

        return $this->handleData($sPaymentId, $sMollieApi, $aConfig);
    }

    /**
     * Encode custom config array to a saveable string
     *
     * @param array $aCustomConfig
     * @return string
     */
    protected function encodeCustomConfig($aCustomConfig)
    {
        return json_encode($aCustomConfig);
    }

    /**
     * Decode custom config array to a saveable string
     *
     * @param string $sCustomConfig
     * @return array
     */
    protected function decodeCustomConfig($sCustomConfig)
    {
        return json_decode($sCustomConfig, true);
    }

    /**
     * Insert new entity
     *
     * @param string $sPaymentId
     * @param string $sMollieApi
     * @param array $aCustomConfig
     * @return bool
     */
    protected function handleData($sPaymentId, $sMollieApi, $aCustomConfig)
    {
        $oDb = DatabaseProvider::getDb();

        $sPaymentId = $oDb->quote($sPaymentId);
        $sMollieApi = $oDb->quote($sMollieApi);
        $sConfig = $oDb->quote($this->encodeCustomConfig($aCustomConfig));

        $sQuery = "INSERT INTO ".self::$sTableName." (OXID, API, CONFIG) VALUES({$sPaymentId}, {$sMollieApi}, {$sConfig}) ON DUPLICATE KEY UPDATE API = {$sMollieApi}, CONFIG = {$sConfig}";

        DatabaseProvider::getDb()->Execute($sQuery);

        return true;
    }

    /**
     * Return config array for given payment method
     *
     * @param string $sPaymentId
     * @return array
     */
    public function getPaymentConfig($sPaymentId)
    {
        $oDb = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC);

        $sQuery = "SELECT * FROM ".self::$sTableName." WHERE OXID = ".$oDb->quote($sPaymentId)." LIMIT 1";
        $aResult = $oDb->getRow($sQuery);

        $aReturn = [];
        if (!empty($aResult)) {
            $aReturn = array_merge(array('api' => $aResult['API']), $this->decodeCustomConfig($aResult['CONFIG']));
        }
        return $aReturn;
    }
}
