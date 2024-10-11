<?php

namespace Mollie\Payment\Application\Model;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use OxidEsales\Eshop\Core\DatabaseProvider;

abstract class BaseMigration extends AbstractMigration
{
    protected function createTableIfNotExists(Schema $oSchema, $sTableName, $sCreateSql): bool
    {
        if (!$oSchema->hasTable($sTableName)) {
            $this->addSql($sCreateSql);
            return true;
        }
        return false;
    }

    protected function addColumnIfNotExists(Schema $oSchema, $sTableName, $sColumnName, $sTypeName, $aOptions, $aFollowupQueries = []): bool
    {
        $table = $oSchema->getTable($sTableName);
        if (!$table->hasColumn($sColumnName)) {
            $table->addColumn($sColumnName, $sTypeName, $aOptions);

            $this->handleFollowupQueries($aFollowupQueries);
            return true;
        }
        return false;
    }

    protected function addColumnBySqlIfNotExists(Schema $oSchema, $sTableName, $sColumnName, $sAlterSql, $aFollowupQueries = []): bool
    {
        $table = $oSchema->getTable($sTableName);
        if (!$table->hasColumn($sColumnName)) {
            $this->addSql($sAlterSql);

            $this->handleFollowupQueries($aFollowupQueries);
            return true;
        }
        return false;
    }

    protected function handleFollowupQueries($aFollowupQueries)
    {
        foreach ($aFollowupQueries as $sQuery) {
            $this->addSql($sQuery);
        }
    }
}
