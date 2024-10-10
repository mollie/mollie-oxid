<?php

namespace Mollie\Payment\Core;

use Mollie\Payment\Application\Helper\Database;
use OxidEsales\DoctrineMigrationWrapper\MigrationsBuilder;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\DatabaseProvider;
use Mollie\Payment\Application\Helper\Payment;
use Mollie\Payment\Application\Model\PaymentConfig;
use Symfony\Component\Console\Output\BufferedOutput;

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
    public static $aGroupsToAdd = [
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
    ];

    /**
     * List of all removed payment methods
     *
     * @var array
     */
    public static $aRemovedPaymentMethods = [
        'molliebitcoin',
        'mollieinghomepay',
    ];

    /**
     * Execute action on activate event.
     *
     * @return void
     */
    public static function onActivate()
    {
        self::executeModuleMigrations();
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
     * Executes module migrations
     *
     * @return void
     */
    protected static function executeModuleMigrations()
    {
        $oMigrations = (new MigrationsBuilder())->build();

        $oOutput = new BufferedOutput();
        $oMigrations->setOutput($oOutput);
        $blNeedsUpdate = $oMigrations->execute('migrations:up-to-date', 'molliepayment');

        if ($blNeedsUpdate) {
            $oMigrations->execute('migrations:migrate', 'molliepayment');
        }
    }

    /**
     * Add database data needed for the Mollie module
     *
     * @return void
     */
    protected static function addData()
    {
        self::addPaymentMethods();

        self::insertRowIfNotExists('oxcontents', ['OXID' => 'molliesecondchanceemail'], 'INSERT INTO `oxcontents` (`OXID`, `OXLOADID`, `OXSHOPID`, `OXSNIPPET`, `OXTYPE`, `OXACTIVE`, `OXACTIVE_1`, `OXPOSITION`, `OXTITLE`, `OXCONTENT`, `OXTITLE_1`, `OXCONTENT_1`, `OXACTIVE_2`, `OXTITLE_2`, `OXCONTENT_2`, `OXACTIVE_3`, `OXTITLE_3`, `OXCONTENT_3`, `OXCATID`, `OXFOLDER`, `OXTERMVERSION`) VALUES ("molliesecondchanceemail", "molliesecondchanceemail", 1, 1, 0, 1, 1, "", "Mollie Second Chance Email", "Hallo [{ $order->oxorder__oxbillsal->value|oxmultilangsal }] [{ $order->oxorder__oxbillfname->value }] [{ $order->oxorder__oxbilllname->value }],<br>\r\n<br>\r\nVielen Dank für Ihren Einkauf bei [{ $shop->oxshops__oxname->value }]!<br>\r\n<br>\r\nSie k&ouml;nnen Ihren Bestellvorgang abschlie&szlig;en indem Sie auf <a href=\'[{$sFinishPaymentUrl}]\'>diesen Link</a> klicken.", "Mollie Second Chance Email", "Hello [{ $order->oxorder__oxbillsal->value|oxmultilangsal }] [{ $order->oxorder__oxbillfname->value }] [{ $order->oxorder__oxbilllname->value }],<br>\r\n<br>\r\nThank you for shopping with [{ $shop->oxshops__oxname->value }]!<br>\r\n<br>\r\nYou can now finish your order by clicking <a href=\'[{$sFinishPaymentUrl}]\'>here</a>", 1, "", "", 1, "", "", "30e44ab83fdee7564.23264141", "CMSFOLDER_EMAILS", "");');
    }

    /**
     * Adding Mollie payments.
     *
     * @return void
     */
    protected static function addPaymentMethods()
    {
        foreach (Payment::getInstance()->getMolliePaymentMethods() as $sPaymentId => $sPaymentTitle) {
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
        $blNewlyAdded = self::insertRowIfNotExists('oxpayments', ['OXID' => $sPaymentId], "INSERT INTO oxpayments(OXID,OXACTIVE,OXDESC,OXADDSUM,OXADDSUMTYPE,OXFROMBONI,OXFROMAMOUNT,OXTOAMOUNT,OXVALDESC,OXCHECKED,OXDESC_1,OXVALDESC_1,OXDESC_2,OXVALDESC_2,OXDESC_3,OXVALDESC_3,OXLONGDESC,OXLONGDESC_1,OXLONGDESC_2,OXLONGDESC_3,OXSORT) VALUES ('{$sPaymentId}', 0, '{$sPaymentTitle}', 0, 'abs', 0, 0, 1000000, '', 0, '{$sPaymentTitle}', '', '', '', '', '', '', '', '', '', 0);");

        if ($blNewlyAdded === true) {
            //Insert basic payment method configuration
            foreach (self::$aGroupsToAdd as $sGroupId) {
                DatabaseProvider::getDb()->Execute("INSERT INTO oxobject2group(OXID,OXSHOPID,OXOBJECTID,OXGROUPSID) values (REPLACE(UUID(),'-',''), :shopid, :paymentid, :groupid);", [
                    ':shopid' => Registry::getConfig()->getShopId(),
                    ':paymentid' => $sPaymentId,
                    ':groupid' => $sGroupId,
                ]);
            }

            self::insertRowIfNotExists('oxobject2payment', ['OXPAYMENTID' => $sPaymentId, 'OXTYPE' => 'oxdelset'], "INSERT INTO oxobject2payment(OXID,OXPAYMENTID,OXOBJECTID,OXTYPE) values (REPLACE(UUID(),'-',''), :paymentid, 'oxidstandard', 'oxdelset');", [':paymentid' => $sPaymentId]);
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
        DatabaseProvider::getDb()->Execute("DELETE FROM oxpayments WHERE oxid = ?", [$sPaymentId]);
        DatabaseProvider::getDb()->Execute("DELETE FROM ".PaymentConfig::$sTableName." WHERE oxid = ?", [$sPaymentId]);
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

        $aInValues = array_keys(Payment::getInstance()->getMolliePaymentMethods());
        DatabaseProvider::getDb()->Execute("UPDATE oxpayments SET oxactive = 0 WHERE oxid IN ".Database::getInstance()->getPreparedInStatement($aInValues), $aInValues);
    }
}
