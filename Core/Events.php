<?php

namespace Mollie\Payment\Core;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\DatabaseProvider;
use Mollie\Payment\Application\Helper\Payment;
use Mollie\Payment\Application\Model\PaymentConfig;

/**
 * Activation and deactivation handler
 */
class Events
{
    /**
     * Lists of all custom-groups to add the payment-methods to
     *
     * @var array
     */
    public static $aGroupsToAdd = array(
        'oxidadmin',
        'oxidcustomer',
        'oxiddealer',
        'oxidforeigncustomer',
        'oxidgoodcust',
        'oxidmiddlecust',
        'oxidnewcustomer',
        'oxidnewsletter',
        'oxidnotyetordered',
        'oxidpowershopper',
        'oxidpricea',
        'oxidpriceb',
        'oxidpricec',
        'oxidsmallcust',
    );

    /**
     * Execute action on activate event.
     *
     * @return void
     */
    public static function onActivate()
    {
        self::addDatabaseStructure();
        self::addPaymentMethods();
        self::regenerateViews();
        self::clearTmp();
    }

    /**
     * Execute action on deactivate event.
     *
     * @return void
     */
    public static function onDeactivate()
    {
        #self::deactivePaymentMethods();
        self::clearTmp();
    }

    /**
     * Regenerates database view-tables.
     *
     * @return void
     */
    protected static function regenerateViews()
    {
        $oShop = oxNew('oxShop');
        $oShop->generateViews();
    }

    /**
     * Clear tmp dir and smarty cache.
     *
     * @return void
     */
    protected static function clearTmp()
    {
        $sTmpDir = getShopBasePath() . "/tmp/";
        $sSmartyDir = $sTmpDir . "smarty/";

        foreach (glob($sTmpDir . "*.txt") as $sFileName) {
            @unlink($sFileName);
        }
        foreach (glob($sSmartyDir . "*.php") as $sFileName) {
            @unlink($sFileName);
        }
    }

    /**
     * Get all available mollie payment methods from payment helper
     *
     * @return array
     */
    protected static function getMolliePaymentMethods()
    {
        return Payment::getInstance()->getMolliePaymentMethods();
    }

    /**
     * Adding payone payments.
     *
     * @return void
     */
    protected static function addPaymentMethods()
    {
        foreach (self::getMolliePaymentMethods() as $sPaymentId => $sPaymentTitle) {
            self::addPaymentMethod($sPaymentId, $sPaymentTitle);
        }
    }

    /**
     * Add payment-methods and a basic configuration to the database
     *
     * @param string $sPaymentId
     * @param string $sPaymentTitle
     * @return void
     */
    protected static function addPaymentMethod($sPaymentId, $sPaymentTitle)
    {
        $blNewlyAdded = self::insertRowIfNotExists('oxpayments', array('OXID' => $sPaymentId), "INSERT INTO oxpayments(OXID,OXACTIVE,OXDESC,OXADDSUM,OXADDSUMTYPE,OXFROMBONI,OXFROMAMOUNT,OXTOAMOUNT,OXVALDESC,OXCHECKED,OXDESC_1,OXVALDESC_1,OXDESC_2,OXVALDESC_2,OXDESC_3,OXVALDESC_3,OXLONGDESC,OXLONGDESC_1,OXLONGDESC_2,OXLONGDESC_3,OXSORT) VALUES ('{$sPaymentId}', 0, '{$sPaymentTitle}', 0, 'abs', 0, 0, 1000000, '', 0, '{$sPaymentTitle}', '', '', '', '', '', '', '', '', '', 0);");

        if ($blNewlyAdded === true) {
            //Insert basic payment method configuration
            foreach (self::$aGroupsToAdd as $sGroupId) {
                DatabaseProvider::getDb()->Execute("INSERT INTO oxobject2group(OXID,OXSHOPID,OXOBJECTID,OXGROUPSID) values (REPLACE(UUID(),'-',''), '".Registry::getConfig()->getShopId()."', '{$sPaymentId}', '{$sGroupId}');");
            }

            self::insertRowIfNotExists('oxobject2payment', array('OXPAYMENTID' => $sPaymentId, 'OXTYPE' => 'oxdelset'), "INSERT INTO oxobject2payment(OXID,OXPAYMENTID,OXOBJECTID,OXTYPE) values (REPLACE(UUID(),'-',''), '{$sPaymentId}', 'oxidstandard', 'oxdelset');");
        }
    }

    /**
     * Add new tables and add columns to existing tables
     *
     * @return void
     */
    protected static function addDatabaseStructure()
    {
        //CREATE NEW TABLES
        self::addTableIfNotExists(PaymentConfig::$sTableName, PaymentConfig::getTableCreateQuery());
    }

    /**
     * Add a database table.
     *
     * @param string $sTableName table to add
     * @param string $sQuery     sql-query to add table
     *
     * @return boolean true or false
     */
    protected static function addTableIfNotExists($sTableName, $sQuery)
    {
        $aTables = DatabaseProvider::getDb()->getAll("SHOW TABLES LIKE '{$sTableName}'");
        if (!$aTables || count($aTables) == 0) {
            DatabaseProvider::getDb()->Execute($sQuery);
            return true;
        }
        return false;
    }

    /**
     * Add a column to a database table.
     *
     * @param string $sTableName  table name
     * @param string $sColumnName column name
     * @param string $sQuery      sql-query to add column to table
     *
     * @return boolean true or false
     */
    protected static function addColumnIfNotExists($sTableName, $sColumnName, $sQuery)
    {
        $aColumns = DatabaseProvider::getDb()->getAll("SHOW COLUMNS FROM {$sTableName} LIKE '{$sColumnName}'");

        if (empty($aColumns)) {
            try {
                DatabaseProvider::getDb()->Execute($sQuery);
            } catch (\Exception $e) {
                // do nothing as of yet
            }
            return true;
        }
        return false;
    }

    /**
     * Insert a database row to an existing table.
     *
     * @param string $sTableName database table name
     * @param array  $aKeyValue  keys of rows to add for existance check
     * @param string $sQuery     sql-query to insert data
     *
     * @return boolean true or false
     */
    protected static function insertRowIfNotExists($sTableName, $aKeyValue, $sQuery)
    {
        $sCheckQuery = "SELECT * FROM {$sTableName} WHERE 1";
        foreach ($aKeyValue as $key => $value) {
            $sCheckQuery .= " AND $key = '$value'";
        }

        if (!DatabaseProvider::getDb()->getOne($sCheckQuery)) { // row not existing yet?
            DatabaseProvider::getDb()->Execute($sQuery);
            return true;
        }
        return false;
    }

    /**
     * Deactivates payone paymethods on module deactivation.
     *
     * @return void
     */
    protected static function deactivePaymentMethods()
    {
        DatabaseProvider::getDb()->Execute("UPDATE oxpayments SET oxactive = 0 WHERE oxid IN ('".implode("','", array_keys(self::getMolliePaymentMethods()))."')");
    }
}
