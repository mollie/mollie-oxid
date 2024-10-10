<?php

namespace Mollie\Payment\Application\Model;

use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\EshopCommunity\Core\Database\Adapter\DatabaseInterface;

class Cronjob
{
    /**
     * @var Cronjob
     */
    protected static $oInstance = null;

    /**
     * Table name
     *
     * @var string
     */
    public static $sTableName = "molliecronjob";

    /**
     * Create singleton instance of cronjob resource model
     *
     * @return Cronjob
     */
    public static function getInstance()
    {
        if (self::$oInstance === null) {
            self::$oInstance = oxNew(self::class);
        }
        return self::$oInstance;
    }

    /**
     * Return create query for module installation
     *
     * @return string
     */
    public static function getTableCreateQuery()
    {
        return "CREATE TABLE `".self::$sTableName."` (
            `OXID` CHAR(32) NOT NULL COLLATE 'latin1_general_ci',
            `OXSHOPID` int(11) NOT NULL DEFAULT 1,
            `MINUTE_INTERVAL` INT(11) NOT NULL,
            `LAST_RUN` DATETIME NULL DEFAULT NULL,
            PRIMARY KEY (`OXID`,`OXSHOPID`) USING BTREE
        ) COLLATE='utf8_general_ci' ENGINE=InnoDB";
    }

    /**
     * Adds new cronjob to the table
     *
     * @param  string $sCronjobId
     * @param  int    $iDefaultMinuteInterval
     * @param  int    $iShopId
     * @return void
     */
    public function addNewCronjob($sCronjobId, $iDefaultMinuteInterval, $iShopId)
    {
        $sQuery = "INSERT INTO `".self::$sTableName."` (OXID, OXSHOPID, MINUTE_INTERVAL, LAST_RUN) VALUES(:oxid, :shopid, :minuteinterval, '0000-00-00 00:00:00');";

        DatabaseProvider::getDb()->Execute($sQuery, [
            ':oxid' => $sCronjobId,
            ':shopid' => $iShopId,
            ':minuteinterval' => $iDefaultMinuteInterval,
        ]);
    }

    /**
     * Check if cronjob already exists
     *
     * @param  string $sCronjobId
     * @param  int    $iShopId
     * @return bool
     */
    public function isCronjobAlreadyExisting($sCronjobId, $iShopId)
    {
        $sQuery = "SELECT OXID FROM `".self::$sTableName."` WHERE OXID = ? AND OXSHOPID = ?;";
        if (!DatabaseProvider::getDb()->getOne($sQuery, array($sCronjobId, $iShopId))) {
            return false;
        }
        return true;
    }

    /**
     * Marks given cronjob id as finished
     *
     * @param  string $sCronjobId
     * @return void
     */
    public function markCronjobAsFinished($sCronjobId, $iShopId)
    {
        DatabaseProvider::getDb()->execute("UPDATE `".self::$sTableName."` SET LAST_RUN = NOW() WHERE OXID = ? AND OXSHOPID = ?;", array($sCronjobId, $iShopId));
    }

    /**
     * Return cronjob data for given cronjobId
     *
     * @param  string $sCronjobId
     * @param  int    $iShopId
     * @return array
     */
    public function getCronjobData($sCronjobId, $iShopId)
    {
        $oDb = DatabaseProvider::getDb(true);
        $oDb->setFetchMode(DatabaseInterface::FETCH_MODE_ASSOC);
        $sQuery = "SELECT * FROM `".self::$sTableName."` WHERE OXID = ? AND OXSHOPID = ?;";
        return $oDb->getRow($sQuery, array($sCronjobId, $iShopId));
    }
}