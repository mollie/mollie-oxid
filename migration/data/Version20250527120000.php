<?php

namespace Mollie\Payment\Migrations;

use Doctrine\DBAL\Schema\Schema;
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

    protected function hasMolliePaymentConfig($sPaymentId)
    {
        $sQuery = "SELECT COUNT(*) FROM molliepaymentconfig WHERE oxid = ?";
        $result = $this->connection->fetchOne($sQuery, [$sPaymentId]);
        return (int)$result > 0;
    }

    protected function renameCaptureMethodConfig()
    {
        $sQuery = "SELECT CONFIG FROM molliepaymentconfig WHERE OXID = 'molliecreditcard'";
        $configJson = $this->connection->fetchOne($sQuery);
        if ($configJson === false) {
            return;
        }

        $config = json_decode($configJson, true);
        if (!is_array($config) || empty($config['creditcard_capture_method'])) {
            return;
        }

        $config['capture_method'] = str_replace("creditcard_", "", $config['creditcard_capture_method']);
        unset($config['creditcard_capture_method']);

        $this->addSql(
            "UPDATE molliepaymentconfig SET CONFIG = ? WHERE OXID = 'molliecreditcard'",
            [json_encode($config)]
        );
    }
}
