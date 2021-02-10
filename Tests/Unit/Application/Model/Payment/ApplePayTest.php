<?php

namespace Mollie\Payment\Tests\Unit\Application\Model\Payment;

use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use OxidEsales\TestingLibrary\UnitTestCase;

class ApplePayTest extends UnitTestCase
{
    public function testGetPaymentSpecificParameters()
    {
        $expected = 'bar';

        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $oOrder->method('mollieIsApplePayButtonMode')->willReturn(true);

        $token = ['foo' => $expected];

        $oRequest = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock();
        $oRequest->method('getRequestEscapedParameter')->willReturn($token);

        Registry::set(Request::class, $oRequest);

        $oPayment = new \Mollie\Payment\Application\Model\Payment\ApplePay();
        $result = $oPayment->getPaymentSpecificParameters($oOrder);

        $this->assertArrayHasKey('applePayPaymentToken', $result);

        $decoded = json_decode($result['applePayPaymentToken'], true);
        $this->assertEquals($expected, $decoded['foo']);
    }

    public function testGetPaymentSpecificParametersParent()
    {
        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $oOrder->method('mollieIsApplePayButtonMode')->willReturn(false);

        $oRequest = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock();
        $oRequest->method('getRequestEscapedParameter')->willReturn(null);

        Registry::set(Request::class, $oRequest);

        $oPayment = new \Mollie\Payment\Application\Model\Payment\ApplePay();
        $result = $oPayment->getPaymentSpecificParameters($oOrder);

        $this->assertCount(0, $result);
    }

    public function testIsRedirectUrlNeeded()
    {
        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $oOrder->method('mollieIsApplePayButtonMode')->willReturn(true);

        $oPayment = new \Mollie\Payment\Application\Model\Payment\ApplePay();
        $result = $oPayment->isRedirectUrlNeeded($oOrder);

        $this->assertFalse($result);
    }

    public function testIsRedirectUrlNeededParent()
    {
        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $oOrder->method('mollieIsApplePayButtonMode')->willReturn(false);

        $oPayment = new \Mollie\Payment\Application\Model\Payment\ApplePay();
        $result = $oPayment->isRedirectUrlNeeded($oOrder);

        $this->assertTrue($result);
    }
}
