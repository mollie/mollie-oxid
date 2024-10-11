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

class Version20241010130000 extends BaseMigration
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

        $this->addSql("UPDATE `oxorder` SET mollieapi = 'payment' WHERE oxpaymenttype LIKE 'mollie%' AND oxtransid LIKE 'tr_%';");
        $this->addSql("UPDATE `oxorder` SET mollieapi = 'order' WHERE oxpaymenttype LIKE 'mollie%' AND oxtransid LIKE 'ord_%';");

        $this->addSql("UPDATE `oxorder` SET MOLLIESHIPMENTHASBEENMARKED = 1 WHERE oxpaymenttype LIKE 'mollie%' AND oxsenddate > '1970-01-01 00:00:01';");

        $this->addSql("ALTER TABLE `molliecronjob` DROP PRIMARY KEY, ADD PRIMARY KEY (`OXID`, `OXSHOPID`) USING BTREE;");
    }
}
