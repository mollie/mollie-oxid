<?php

namespace Mollie\Payment\Tests\Unit\migration\data;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\Configuration\Configuration;
use Doctrine\Migrations\OutputWriter;
use Doctrine\Migrations\Version\Version;
use Mollie\Payment\Migrations\Version20250527120000;
use OxidEsales\TestingLibrary\UnitTestCase;

require_once __DIR__ . '/../../../../migration/data/Version20250527120000.php'; // migrations are loaded by path, not by the autoloader

class Version20250527120000Test extends UnitTestCase
{
    public function testUpUsesOnlyMigrationConnection()
    {
        // separate in-memory DB, so the migration can't use the shop DB through the OXID framework
        $oConnection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);
        $oConnection->executeStatement('CREATE TABLE molliepaymentconfig (OXID VARCHAR(32) PRIMARY KEY, API VARCHAR(32), CONFIG TEXT NOT NULL)');
        $oConnection->executeStatement(
            "INSERT INTO molliepaymentconfig VALUES ('molliecreditcard', 'payment', ?)",
            [json_encode(['creditcard_capture_method' => 'creditcard_manual_capture', 'creditcard_data_input' => 'hosted_checkout'])]
        );
        $oConnection->executeStatement("INSERT INTO molliepaymentconfig VALUES ('mollieklarna', 'payment', '[]')");

        $oConfiguration = $this->getMockBuilder(Configuration::class)->disableOriginalConstructor()->getMock();
        $oConfiguration->method('getConnection')->willReturn($oConnection);
        $oConfiguration->method('getOutputWriter')->willReturn($this->getMockBuilder(OutputWriter::class)->disableOriginalConstructor()->getMock());

        $aStatements = [];
        $aParameters = [];
        $oVersion = $this->getMockBuilder(Version::class)->disableOriginalConstructor()->getMock();
        $oVersion->method('getConfiguration')->willReturn($oConfiguration);
        $oVersion->method('addSql')->willReturnCallback(function ($sSql, $aParams = []) use (&$aStatements, &$aParameters) {
            $aStatements[] = $sSql;
            $aParameters[] = $aParams;
        });

        $oMigration = new Version20250527120000($oVersion);
        $oMigration->up(new Schema());

        $this->assertSame([
            "UPDATE molliepaymentconfig SET API = 'order' WHERE OXID = 'mollieklarna'",
            "INSERT INTO molliepaymentconfig (OXID, API, CONFIG) VALUES ('molliein3', 'order', '[]')",
            "INSERT INTO molliepaymentconfig (OXID, API, CONFIG) VALUES ('molliebillie', 'order', '[]')",
            "INSERT INTO molliepaymentconfig (OXID, API, CONFIG) VALUES ('mollieriverty', 'order', '[]')",
            "UPDATE molliepaymentconfig SET CONFIG = ? WHERE OXID = 'molliecreditcard'",
        ], $aStatements);

        $this->assertSame(['creditcard_data_input' => 'hosted_checkout', 'capture_method' => 'manual_capture'], json_decode($aParameters[4][0], true));
    }
}
