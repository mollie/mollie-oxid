<?php

namespace Mollie\Payment\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Mollie\Payment\Application\Model\BaseMigration;

class Version20250527120000 extends BaseMigration
{
    protected $aOrderAPIMethods = [
        'mollieklarna',
        'molliein3',
        'molliebillie',
        'mollieriverty',
    ];

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

        $this->setOrderAPIField();
        $this->renameCaptureMethodConfig();
    }

    protected function setOrderAPIField()
    {
        // In this version the Order API only methods have been enabled for Payments API too.
        // A config entry has to be created for them for Order API if not already existing
        // otherwise they would change from Order API to Payments API on module update.
        foreach ($this->aOrderAPIMethods as $sPaymentId) {
            if ($this->hasMolliePaymentConfig($sPaymentId) === true) {
                $sQuery = "UPDATE molliepaymentconfig SET API = 'order' WHERE OXID = '".$sPaymentId."'";
            } else {
                $sQuery = "INSERT INTO molliepaymentconfig (OXID, API, CONFIG) VALUES ('".$sPaymentId."', 'order', '[]')";
            }
            $this->addSql($sQuery);
        }
    }

    // Migrations can also be executed without shop context (vendor/bin/oe-eshop-db_migrate), so the OXID framework
    // (oxNew, DatabaseProvider, ...) can't be used here, only the Doctrine connection of the migration

    protected function hasMolliePaymentConfig($sPaymentId)
    {
        $sQuery = "SELECT 1 FROM molliepaymentconfig WHERE oxid = ?";
        $dbValue = $this->connection->fetchOne($sQuery, [$sPaymentId]);
        if ($dbValue == '1') {
            return true;
        }
        return false;
    }

    protected function renameCaptureMethodConfig()
    {
        $sQuery = "SELECT CONFIG FROM molliepaymentconfig WHERE OXID = 'molliecreditcard'";
        $sConfig = $this->connection->fetchOne($sQuery);
        if ($sConfig === false) {
            return;
        }

        $aCreditcardConfig = json_decode($sConfig, true);
        if (!empty($aCreditcardConfig['creditcard_capture_method'])) {
            $aCreditcardConfig['capture_method'] = $aCreditcardConfig['creditcard_capture_method'];
            unset($aCreditcardConfig['creditcard_capture_method']);

            $aCreditcardConfig['capture_method'] = str_replace("creditcard_", "", $aCreditcardConfig['capture_method']);

            $this->addSql("UPDATE molliepaymentconfig SET CONFIG = ? WHERE OXID = 'molliecreditcard'", [json_encode($aCreditcardConfig)]);
        }
    }
}
