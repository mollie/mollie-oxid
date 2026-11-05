<?php


namespace Mollie\Payment\Tests\Unit\Application\Model\Payment;


use Mollie\Payment\Application\Model\PaymentConfig;
use OxidEsales\Eshop\Core\UtilsObject;
use OxidEsales\TestingLibrary\UnitTestCase;

class KlarnaPayLaterTest extends UnitTestCase
{
    public function testGetApiMethod()
    {
        $oPaymentConfig = $this->getMockBuilder(PaymentConfig::class)->disableOriginalConstructor()->getMock();

        UtilsObject::setClassInstance(PaymentConfig::class, $oPaymentConfig);

        $oPayment = new \Mollie\Payment\Application\Model\Payment\KlarnaPayLater();

        // New orders always use the Payments API
        $this->assertEquals('payment', $oPayment->getApiMethod());

        // Old orders keep the API they were created with
        $oOldOrder = $this->getMockBuilder(\OxidEsales\Eshop\Application\Model\Order::class)->disableOriginalConstructor()->getMock();
        $oOldOrder->method('__isset')->willReturn(true);
        $oOldOrder->method('__get')->willReturnMap([
            ['oxorder__mollieapi', new \OxidEsales\Eshop\Core\Field('order')],
        ]);
        $this->assertEquals('order', $oPayment->getApiMethod($oOldOrder));
    }
}
