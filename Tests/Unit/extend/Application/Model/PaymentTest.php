<?php


namespace Mollie\Payment\Tests\Unit\extend\Application\Model;


use Mollie\Payment\Application\Model\Payment\Creditcard;
use OxidEsales\TestingLibrary\UnitTestCase;

class PaymentTest extends UnitTestCase
{
    protected function setUp()
    {
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute('TRUNCATE TABLE `oxobject2group`');
    }

    protected function tearDown()
    {
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute('TRUNCATE TABLE `oxobject2group`');
    }

    public function testIsMolliePaymentMethod()
    {
        $oPayment = new \Mollie\Payment\extend\Application\Model\Payment();
        $oPayment->setId("molliecreditcard");

        $result = $oPayment->isMolliePaymentMethod();

        $this->assertTrue($result);

        $oPayment->setId("oxidpaypal");

        $result = $oPayment->isMolliePaymentMethod();

        $this->assertFalse($result);
    }

    public function testGetMolliePaymentModel()
    {
        $oPayment = new \Mollie\Payment\extend\Application\Model\Payment();
        $oPayment->setId("molliecreditcard");

        $oModel = $oPayment->getMolliePaymentModel();

        $this->assertInstanceOf(Creditcard::class, $oModel);

        $oPayment->setId("oxidpaypal");

        $oModel = $oPayment->getMolliePaymentModel();

        $this->assertNull($oModel);
    }
}