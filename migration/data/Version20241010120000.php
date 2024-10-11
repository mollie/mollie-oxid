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

class Version20241010120000 extends BaseMigration
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

        $this->addColumnBySqlIfNotExists($schema, 'molliecronjob', 'OXSHOPID', "ALTER TABLE `molliecronjob` ADD `OXSHOPID` INT(11) NOT NULL DEFAULT '1' AFTER `OXID`;");
    }
}
