<?php

namespace Mollie\Payment\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\DatabaseProvider;
use Mollie\Payment\Application\Helper\Payment;
use Mollie\Payment\Application\Model\BaseMigration;
use Mollie\Payment\Application\Model\PaymentConfig;
use Mollie\Payment\Application\Model\RequestLog;
use Mollie\Payment\Application\Model\Cronjob;

class Version20241009120000 extends BaseMigration
{
    /**
     * @param Schema $schema
     */
    public function down(Schema $schema): void
    {
    }

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->connection->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');

        $this->createTables($schema);
        $this->addNewColumns($schema);
    }

    /**
     * Adds new mollie tables to the database
     *
     * @param Schema $schema
     * @return void
     */
    protected function createTables(Schema $schema): void
    {
        $this->createTableIfNotExists($schema, PaymentConfig::$sTableName, PaymentConfig::getTableCreateQuery());
        $this->createTableIfNotExists($schema, RequestLog::$sTableName, RequestLog::getTableCreateQuery());
        $this->createTableIfNotExists($schema, Cronjob::$sTableName, Cronjob::getTableCreateQuery());
    }

    /**
     * Adds new columns to existing tables
     *
     * @param Schema $schema
     * @return void
     */
    protected function addNewColumns(Schema $schema): void
    {
        $this->addColumnIfNotExists($schema, 'oxorder', 'MOLLIEDELCOSTREFUNDED', Types::FLOAT, ['columnDefinition' => 'NOT NULL', 'default' => 1]);
        $this->addColumnIfNotExists($schema, 'oxorder', 'MOLLIEPAYCOSTREFUNDED', Types::FLOAT, ['columnDefinition' => 'NOT NULL', 'default' => 1]);
        $this->addColumnIfNotExists($schema, 'oxorder', 'MOLLIEWRAPCOSTREFUNDED', Types::FLOAT, ['columnDefinition' => 'NOT NULL', 'default' => 1]);
        $this->addColumnIfNotExists($schema, 'oxorder', 'MOLLIEGIFTCARDREFUNDED', Types::FLOAT, ['columnDefinition' => 'NOT NULL', 'default' => 1]);
        $this->addColumnIfNotExists($schema, 'oxorder', 'MOLLIEWASCAPTURED', Types::FLOAT, ['columnDefinition' => 'NOT NULL', 'default' => 1]);
        $this->addColumnIfNotExists($schema, 'oxorder', 'MOLLIEVOUCHERDISCOUNTREFUNDED', Types::FLOAT, ['columnDefinition' => 'NOT NULL', 'default' => 1]);
        $this->addColumnIfNotExists($schema, 'oxorder', 'MOLLIEDISCOUNTREFUNDED', Types::FLOAT, ['columnDefinition' => 'NOT NULL', 'default' => 1]);
        $this->addColumnIfNotExists($schema, 'oxorder', 'MOLLIEMODE', Types::STRING, ['columnDefinition' => 'VARCHAR(32) CHARSET utf8 COLLATE utf8_general_ci NOT NULL', 'default' => '0']);
        $this->addColumnIfNotExists($schema, 'oxorder', 'MOLLIESECONDCHANCEMAILSENT', Types::DATETIME_MUTABLE, ['columnDefinition' => 'NOT NULL', 'default' => '0000-00-00 00:00:00']);
        $this->addColumnIfNotExists($schema, 'oxorder', 'MOLLIEEXTERNALTRANSID', Types::STRING, ['columnDefinition' => 'VARCHAR(64) CHARSET utf8 COLLATE utf8_general_ci NOT NULL', 'default' => '']);
        $this->addColumnIfNotExists($schema, 'oxorder', 'MOLLIECAPTUREMETHOD', Types::STRING, ['columnDefinition' => 'VARCHAR(64) CHARSET utf8 COLLATE utf8_general_ci NOT NULL', 'default' => '']);
        $this->addColumnIfNotExists($schema, 'oxorderarticles', 'MOLLIEQUANTITYREFUNDED', Types::INTEGER, ['columnDefinition' => 'INT(11) NOT NULL', 'default' => 0]);
        $this->addColumnIfNotExists($schema, 'oxorderarticles', 'MOLLIEAMOUNTREFUNDED', Types::FLOAT, ['columnDefinition' => 'NOT NULL', 'default' => 0]);

        $aNewColumnDataQueriesMollieApi = [
            "UPDATE `oxorder` SET mollieapi = 'payment' WHERE oxpaymenttype LIKE 'mollie%' AND oxtransid LIKE 'tr_%'",
            "UPDATE `oxorder` SET mollieapi = 'order' WHERE oxpaymenttype LIKE 'mollie%' AND oxtransid LIKE 'ord_%'",
        ];
        $this->addColumnIfNotExists($schema, 'oxorder', 'MOLLIEAPI', Types::STRING, ['columnDefinition' => 'VARCHAR(32) CHARSET utf8 COLLATE utf8_general_ci NOT NULL', 'default' => ''], $aNewColumnDataQueriesMollieApi);

        $aShipmentSentQuery = ["UPDATE `oxorder` SET MOLLIESHIPMENTHASBEENMARKED = 1 WHERE oxpaymenttype LIKE 'mollie%' AND oxsenddate > '1970-01-01 00:00:01';"];
        $this->addColumnIfNotExists($schema, 'oxorder', 'MOLLIESHIPMENTHASBEENMARKED', Types::SMALLINT, ['columnDefinition' => 'tinyint(1) UNSIGNED NOT NULL', 'default' => 0], $aShipmentSentQuery);

        $this->addColumnIfNotExists($schema, 'oxuser', 'MOLLIECUSTOMERID', Types::STRING, ['columnDefinition' => 'VARCHAR(32) CHARSET utf8 COLLATE utf8_general_ci NOT NULL', 'default' => '']);

        $aCronjobShopIdFollowup = ["ALTER TABLE `molliecronjob` DROP PRIMARY KEY, ADD PRIMARY KEY (`OXID`, `OXSHOPID`) USING BTREE;"];
        $this->addColumnBySqlIfNotExists($schema, 'molliecronjob', 'OXSHOPID', "ALTER TABLE `molliecronjob` ADD `OXSHOPID` INT(11) NOT NULL DEFAULT '1' AFTER `OXID`;", $aCronjobShopIdFollowup);
    }
}
