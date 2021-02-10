<?php


namespace Mollie\Payment\Tests\Unit\Application\Model\Payment;


use Mollie\Api\Endpoints\MethodEndpoint;
use Mollie\Payment\Application\Helper\Payment;
use Mollie\Payment\Application\Model\PaymentConfig;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use OxidEsales\Eshop\Core\UtilsObject;
use OxidEsales\TestingLibrary\UnitTestCase;

class IdealTest extends UnitTestCase
{
    public function testGetPaymentSpecificParameters()
    {
        $expected = 'issuer';

        $aDynValue = ['mollie_ideal_issuer' => $expected];

        $oRequest = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock();
        $oRequest->method('getRequestParameter')->willReturn($aDynValue);

        Registry::set(Request::class, $oRequest);

        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();

        $oPayment = new \Mollie\Payment\Application\Model\Payment\Ideal();
        $result = $oPayment->getPaymentSpecificParameters($oOrder);

        $this->assertEquals($expected, $result['issuer']);
    }

    public function testGetPaymentSpecificParametersParent()
    {
        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();

        $oPayment = new \Mollie\Payment\Application\Model\Payment\Ideal();
        $result = $oPayment->getPaymentSpecificParameters($oOrder);

        $this->assertCount(0, $result);
    }

    public function testGetIssuers()
    {
        $oIssuer = new \stdClass();
        $oIssuer->name = 'test';
        $oIssuer->image = new \stdClass();
        $oIssuer->image->size2x = 'testpic.png';

        $oMethods = $this->getMockBuilder(MethodEndpoint::class)->disableOriginalConstructor()->getMock();
        $oMethods->method('get')->willReturn([$oIssuer]);

        $oMollieApi = $this->getMockBuilder(\Mollie\Api\MollieApiClient::class)->disableOriginalConstructor()->getMock();
        $oMollieApi->methods = $oMethods;

        UtilsObject::setClassInstance(\Mollie\Api\MollieApiClient::class, $oMollieApi);

        $oPaymentConfig = $this->getMockBuilder(PaymentConfig::class)->disableOriginalConstructor()->getMock();
        $oPaymentConfig->method('getPaymentConfig')->willReturn(['add_qr' => true, 'issuer_list_type' => 'dropdown']);

        UtilsObject::setClassInstance(PaymentConfig::class, $oPaymentConfig);

        $oPayment = new \Mollie\Payment\Application\Model\Payment\Ideal();
        $result = $oPayment->getIssuers([], '');

        $this->assertArrayHasKey('qr', $result);
    }

    public function testGetMolliePaymentCode()
    {
        $oPayment = new \Mollie\Payment\Application\Model\Payment\Ideal();
        $result = $oPayment->getMolliePaymentCode();

        $this->assertEquals("ideal", $result);
    }

    public function testIsOnlyOrderApiSupported()
    {
        $oPayment = new \Mollie\Payment\Application\Model\Payment\Ideal();
        $result = $oPayment->isOnlyOrderApiSupported();

        $this->assertFalse($result);
    }

    public function testIsOrderExpirySupported()
    {
        $oPayment = new \Mollie\Payment\Application\Model\Payment\Ideal();
        $result = $oPayment->isOrderExpirySupported();

        $this->assertTrue($result);
    }

    public function testIsMollieMethodHiddenInitially()
    {
        $oPayment = new \Mollie\Payment\Application\Model\Payment\Ideal();
        $result = $oPayment->isMollieMethodHiddenInitially();

        $this->assertFalse($result);
    }

    public function testGetCustomConfigTemplate()
    {
        $oPayment = new \Mollie\Payment\Application\Model\Payment\Ideal();
        $result = $oPayment->getCustomConfigTemplate();

        $this->assertEquals("mollie_config_ideal.tpl", $result);
    }

    public function testGetCustomFrontendTemplate()
    {
        $oPayment = new \Mollie\Payment\Application\Model\Payment\Ideal();
        $result = $oPayment->getCustomFrontendTemplate();

        $this->assertEquals("mollieideal.tpl", $result);
    }
}
