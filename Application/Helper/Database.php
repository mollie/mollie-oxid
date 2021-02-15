<?php

namespace Mollie\Payment\Application\Helper;

use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Registry;

class Database
{
    /**
     * @var Database
     */
    protected static $oInstance = null;

    /**
     * Create singleton instance of database helper
     *
     * @return Database
     */
    public static function getInstance()
    {
        if (self::$oInstance === null) {
            self::$oInstance = oxNew(self::class);
        }
        return self::$oInstance;
    }

    /**
     * Deletes oxconfig entry for given alt logo conf var
     *
     * @param string $sDeleteConfVar
     * @return void
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseErrorException
     */
    public function deleteMollieAltLogo($sDeleteConfVar)
    {
        $sQuery = "DELETE FROM oxconfig WHERE oxvarname = ? AND oxshopid = ? AND oxvartype = 'str'";
        DatabaseProvider::getDb()->Execute($sQuery, array($sDeleteConfVar, Registry::getConfig()->getShopId()));
    }

    /**
     * Returns parameter-string for prepared mysql statement
     *
     * @param array $aValues
     * @return string
     */
    public function getPreparedInStatement($aValues)
    {
        $sReturn = '';
        foreach ($aValues as $sValue) {
            $sReturn .= '?,';
        }
        return '('.rtrim($sReturn, ',').')';
    }
}
