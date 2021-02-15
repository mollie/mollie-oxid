<?php


namespace Mollie\Payment\Tests\Unit\Application\Model;


use OxidEsales\TestingLibrary\UnitTestCase;
use Mollie\Payment\Application\Model\PaymentConfig;

class PaymentConfigTest extends UnitTestCase
{
    public function tearDown()
    {
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute('TRUNCATE TABLE `'.PaymentConfig::$sTableName.'`');

        parent::tearDown();
    }

    public function testSavePaymentConfig()
    {
        $sPaymentId = "mollietestpayment";

        $oPaymentConfig = new PaymentConfig();
        $result = $oPaymentConfig->savePaymentConfig($sPaymentId, ['api' => 'test']);

        $this->assertTrue($result);

        $result = \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->getOne("SELECT oxid FROM ".PaymentConfig::$sTableName." WHERE oxid = ?", array($sPaymentId));

        $this->assertEquals($sPaymentId, $result);
    }

    public function testGetPaymentConfig()
    {
        $sPaymentId = "mollietestpaymentdata";
        $expected = "test";

        $oPaymentConfig = new PaymentConfig();
        $oPaymentConfig->savePaymentConfig($sPaymentId, ['api' => 'test', 'testData' => $expected]);

        $result = $oPaymentConfig->getPaymentConfig($sPaymentId);

        $this->assertArrayHasKey('testData', $result);
        $this->assertEquals($expected, $result['testData']);
    }
}