<?php

namespace Mollie\Payment\Core;

use Mollie\Payment\Application\Helper\Database;
use Mollie\Payment\Application\Model\RequestLog;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\DatabaseProvider;
use Mollie\Payment\Application\Helper\Payment;
use Mollie\Payment\Application\Model\PaymentConfig;
use Mollie\Payment\Application\Model\Cronjob;

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
     * List of all removed payment methods
     *
     * @var array
     */
    public static $aRemovedPaymentMethods = array(
        'molliebitcoin',
        'mollieinghomepay',
    );

    /**
     * Execute action on activate event.
     *
     * @return void
     */
    public static function onActivate()
    {
        self::addDatabaseStructure();
        self::addData();
        self::deleteRemovedPaymentMethods();
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
        if(Registry::getConfig()->isAdmin()) { // onDeactivate is triggered in the apply-configuration console command which should not deavtivate the payment methods
            self::deactivePaymentMethods();
            self::clearTmp();
        }
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
     * Add database data needed for the Mollie module
     *
     * @return void
     */
    protected static function addData()
    {
        self::addPaymentMethods();

        self::insertRowIfNotExists('oxcontents', array('OXID' => 'molliesecondchanceemail'), 'INSERT INTO `oxcontents` (`OXID`, `OXLOADID`, `OXSHOPID`, `OXSNIPPET`, `OXTYPE`, `OXACTIVE`, `OXACTIVE_1`, `OXPOSITION`, `OXTITLE`, `OXCONTENT`, `OXTITLE_1`, `OXCONTENT_1`, `OXACTIVE_2`, `OXTITLE_2`, `OXCONTENT_2`, `OXACTIVE_3`, `OXTITLE_3`, `OXCONTENT_3`, `OXCATID`, `OXFOLDER`, `OXTERMVERSION`) VALUES ("molliesecondchanceemail", "molliesecondchanceemail", 1, 1, 0, 1, 1, "", "Mollie Second Chance Email", "Hallo [{ $order->oxorder__oxbillsal->value|oxmultilangsal }] [{ $order->oxorder__oxbillfname->value }] [{ $order->oxorder__oxbilllname->value }],<br>\r\n<br>\r\nVielen Dank für Ihren Einkauf bei [{ $shop->oxshops__oxname->value }]!<br>\r\n<br>\r\nSie k&ouml;nnen Ihren Bestellvorgang abschlie&szlig;en indem Sie auf <a href=\'[{$sFinishPaymentUrl}]\'>diesen Link</a> klicken.", "Mollie Second Chance Email", "Hello [{ $order->oxorder__oxbillsal->value|oxmultilangsal }] [{ $order->oxorder__oxbillfname->value }] [{ $order->oxorder__oxbilllname->value }],<br>\r\n<br>\r\nThank you for shopping with [{ $shop->oxshops__oxname->value }]!<br>\r\n<br>\r\nYou can now finish your order by clicking <a href=\'[{$sFinishPaymentUrl}]\'>here</a>", 1, "", "", 1, "", "", "30e44ab83fdee7564.23264141", "CMSFOLDER_EMAILS", "");');
    }

    /**
     * Adding Mollie payments.
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
                DatabaseProvider::getDb()->Execute("INSERT INTO oxobject2group(OXID,OXSHOPID,OXOBJECTID,OXGROUPSID) values (REPLACE(UUID(),'-',''), :shopid, :paymentid, :groupid);", [
                    ':shopid' => Registry::getConfig()->getShopId(),
                    ':paymentid' => $sPaymentId,
                    ':groupid' => $sGroupId,
                ]);
            }

            self::insertRowIfNotExists('oxobject2payment', array('OXPAYMENTID' => $sPaymentId, 'OXTYPE' => 'oxdelset'), "INSERT INTO oxobject2payment(OXID,OXPAYMENTID,OXOBJECTID,OXTYPE) values (REPLACE(UUID(),'-',''), :paymentid, 'oxidstandard', 'oxdelset');", [':paymentid' => $sPaymentId]);
        }
    }

    /**
     * Deletes removed payment methods
     *
     * @return void
     */
    protected static function deleteRemovedPaymentMethods()
    {
        foreach (self::$aRemovedPaymentMethods as $sPaymentId) {
            self::deletePaymentMethod($sPaymentId);
        }
    }

    /**
     * Deletes payment method from the database
     *
     * @param  string $sPaymentId
     * @return void
     */
    protected static function deletePaymentMethod($sPaymentId)
    {
        DatabaseProvider::getDb()->Execute("DELETE FROM oxpayments WHERE oxid = ?", array($sPaymentId));
        DatabaseProvider::getDb()->Execute("DELETE FROM ".PaymentConfig::$sTableName." WHERE oxid = ?", array($sPaymentId));
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
        self::addTableIfNotExists(RequestLog::$sTableName, RequestLog::getTableCreateQuery());
        self::addTableIfNotExists(Cronjob::$sTableName, Cronjob::getTableCreateQuery());

        //ADD NEW COLUMNS
        self::addColumnIfNotExists('oxorder', 'MOLLIEDELCOSTREFUNDED', "ALTER TABLE `oxorder` ADD COLUMN `MOLLIEDELCOSTREFUNDED` DOUBLE NOT NULL DEFAULT '0';");
        self::addColumnIfNotExists('oxorder', 'MOLLIEPAYCOSTREFUNDED', "ALTER TABLE `oxorder` ADD COLUMN `MOLLIEPAYCOSTREFUNDED` DOUBLE NOT NULL DEFAULT '0';");
        self::addColumnIfNotExists('oxorder', 'MOLLIEWRAPCOSTREFUNDED', "ALTER TABLE `oxorder` ADD COLUMN `MOLLIEWRAPCOSTREFUNDED` DOUBLE NOT NULL DEFAULT '0';");
        self::addColumnIfNotExists('oxorder', 'MOLLIEGIFTCARDREFUNDED', "ALTER TABLE `oxorder` ADD COLUMN `MOLLIEGIFTCARDREFUNDED` DOUBLE NOT NULL DEFAULT '0';");
        self::addColumnIfNotExists('oxorder', 'MOLLIEVOUCHERDISCOUNTREFUNDED', "ALTER TABLE `oxorder` ADD COLUMN `MOLLIEVOUCHERDISCOUNTREFUNDED` DOUBLE NOT NULL DEFAULT '0';");
        self::addColumnIfNotExists('oxorder', 'MOLLIEDISCOUNTREFUNDED', "ALTER TABLE `oxorder` ADD COLUMN `MOLLIEDISCOUNTREFUNDED` DOUBLE NOT NULL DEFAULT '0';");
        self::addColumnIfNotExists('oxorder', 'MOLLIEMODE', "ALTER TABLE `oxorder` ADD COLUMN `MOLLIEMODE` VARCHAR(32) CHARSET utf8 COLLATE utf8_general_ci DEFAULT '' NOT NULL;");
        self::addColumnIfNotExists('oxorder', 'MOLLIESECONDCHANCEMAILSENT', "ALTER TABLE `oxorder` ADD COLUMN `MOLLIESECONDCHANCEMAILSENT` datetime NOT NULL default '0000-00-00 00:00:00';");
        self::addColumnIfNotExists('oxorderarticles', 'MOLLIEQUANTITYREFUNDED', "ALTER TABLE `oxorderarticles` ADD COLUMN `MOLLIEQUANTITYREFUNDED` INT(11) NOT NULL DEFAULT '0';");
        self::addColumnIfNotExists('oxorderarticles', 'MOLLIEAMOUNTREFUNDED', "ALTER TABLE `oxorderarticles` ADD COLUMN `MOLLIEAMOUNTREFUNDED` DOUBLE NOT NULL DEFAULT '0';");

        $aNewColumnDataQueriesMollieApi = [
            "UPDATE `oxorder` SET mollieapi = 'payment' WHERE oxpaymenttype LIKE 'mollie%' AND oxtransid LIKE 'tr_%'",
            "UPDATE `oxorder` SET mollieapi = 'order' WHERE oxpaymenttype LIKE 'mollie%' AND oxtransid LIKE 'ord_%'",
        ];
        self::addColumnIfNotExists('oxorder', 'MOLLIEAPI', "ALTER TABLE `oxorder` ADD COLUMN `MOLLIEAPI` VARCHAR(32) CHARSET utf8 COLLATE utf8_general_ci DEFAULT '' NOT NULL;", $aNewColumnDataQueriesMollieApi);
        $aShipmentSentQuery = ["UPDATE `oxorder` SET MOLLIESHIPMENTHASBEENMARKED = 1 WHERE oxpaymenttype LIKE 'mollie%' AND oxsenddate > '1970-01-01 00:00:01';"];
        self::addColumnIfNotExists('oxorder', 'MOLLIESHIPMENTHASBEENMARKED', "ALTER TABLE `oxorder` ADD COLUMN `MOLLIESHIPMENTHASBEENMARKED` tinyint(1) UNSIGNED NOT NULL DEFAULT  '0';", $aShipmentSentQuery);

        self::addColumnIfNotExists('oxuser', 'MOLLIECUSTOMERID', "ALTER TABLE `oxuser` ADD COLUMN `MOLLIECUSTOMERID` VARCHAR(32) CHARSET utf8 COLLATE utf8_general_ci DEFAULT '' NOT NULL;");
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
        $aTables = DatabaseProvider::getDb()->getAll("SHOW TABLES LIKE ?", array($sTableName));
        if (!$aTables || count($aTables) == 0) {
            DatabaseProvider::getDb()->Execute($sQuery);
            return true;
        }
        return false;
    }

    /**
     * Add a column to a database table.
     *
     * @param string $sTableName            table name
     * @param string $sColumnName           column name
     * @param string $sQuery                sql-query to add column to table
     * @param array  $aNewColumnDataQueries  array of queries to execute when column was added
     *
     * @return boolean true or false
     */
    public static function addColumnIfNotExists($sTableName, $sColumnName, $sQuery, $aNewColumnDataQueries = array())
    {
        $aColumns = DatabaseProvider::getDb()->getAll("SHOW COLUMNS FROM {$sTableName} LIKE ?", array($sColumnName));
        if (empty($aColumns)) {
            try {
                DatabaseProvider::getDb()->Execute($sQuery);
                foreach ($aNewColumnDataQueries as $sQuery) {
                    DatabaseProvider::getDb()->Execute($sQuery);
                }
                return true;
            } catch (\Exception $e) {
                // do nothing as of yet
            }
        }
        return false;
    }

    /**
     * Insert a database row to an existing table.
     *
     * @param string $sTableName database table name
     * @param array  $aKeyValue  keys of rows to add for existance check
     * @param string $sQuery     sql-query to insert data
     * @param array  $aParams    sql-query insert parameters
     *
     * @return boolean true or false
     */
    protected static function insertRowIfNotExists($sTableName, $aKeyValue, $sQuery, $aParams = [])
    {
        $sCheckQuery = "SELECT * FROM {$sTableName} WHERE 1";
        foreach ($aKeyValue as $key => $value) {
            $sCheckQuery .= " AND $key = '$value'";
        }

        if (!DatabaseProvider::getDb()->getOne($sCheckQuery)) { // row not existing yet?
            DatabaseProvider::getDb()->Execute($sQuery, $aParams);
            return true;
        }
        return false;
    }

    /**
     * Deactivates Mollie paymethods on module deactivation.
     *
     * @return void
     */
    protected static function deactivePaymentMethods()
    {
        $oRequest = Registry::getRequest();
        if ($oRequest->getRequestParameter('cl') == 'module_config' && $oRequest->getRequestParameter('fnc') == 'save') {
            return; // Dont deactivate payment methods when changing config in admin ( this triggers module deactivation )
        }

        $aInValues = array_keys(self::getMolliePaymentMethods());
        DatabaseProvider::getDb()->Execute("UPDATE oxpayments SET oxactive = 0 WHERE oxid IN ".Database::getInstance()->getPreparedInStatement($aInValues), $aInValues);
    }
}
