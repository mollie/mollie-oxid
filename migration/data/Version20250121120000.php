<?php

namespace Mollie\Payment\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\DatabaseProvider;
use Mollie\Payment\Application\Model\BaseMigration;

class Version20250121120000 extends BaseMigration
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

        $this->addNewColumns($schema);
    }

    /**
     * Adds new columns to existing tables
     *
     * @param Schema $schema
     * @return void
     */
    protected function addNewColumns(Schema $schema): void
    {
        $this->addColumnIfNotExists($schema, 'oxorder', 'MOLLIEORDERISLOCKED', Types::SMALLINT, ['columnDefinition' => 'tinyint(1) UNSIGNED NOT NULL DEFAULT 0', 'default' => 0]);
    }
}
