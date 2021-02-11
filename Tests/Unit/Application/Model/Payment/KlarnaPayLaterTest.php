<?php


namespace Mollie\Payment\Tests\Unit\Application\Model\Payment;


use Mollie\Payment\Application\Model\PaymentConfig;
use OxidEsales\Eshop\Core\UtilsObject;
use OxidEsales\TestingLibrary\UnitTestCase;

class KlarnaPayLaterTest extends UnitTestCase
{
    public function testGetApiMethod()
    {
        $expected = 'order';

        $oPaymentConfig = $this->getMockBuilder(PaymentConfig::class)->disableOriginalConstructor()->getMock();
        #$oPaymentConfig->method('getPaymentConfig')->willReturn(['api' => 'payment']);

        UtilsObject::setClassInstance(PaymentConfig::class, $oPaymentConfig);

        $oPayment = new \Mollie\Payment\Application\Model\Payment\KlarnaPayLater();
        $result = $oPayment->getApiMethod();

        $this->assertEquals($expected, $result);
    }
}
