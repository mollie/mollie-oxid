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

class GiftcardTest extends UnitTestCase
{
    public function testGetPaymentSpecificParameters()
    {
        $expected = 'TestIssuer';

        $aDynValue = ['mollie_giftcard_issuer' => $expected];

        $oRequest = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock();
        $oRequest->method('getRequestParameter')->willReturn($aDynValue);

        Registry::set(Request::class, $oRequest);

        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();

        $oPayment = new \Mollie\Payment\Application\Model\Payment\Giftcard();
        $result = $oPayment->getPaymentSpecificParameters($oOrder);

        $this->assertEquals($expected, $result['issuer']);
    }

    public function testGetPaymentSpecificParametersParent()
    {
        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();

        $oPayment = new \Mollie\Payment\Application\Model\Payment\Giftcard();
        $result = $oPayment->getPaymentSpecificParameters($oOrder);

        $this->assertCount(0, $result);
    }

    public function testGetIssuers()
    {
        $expected = "test";

        $oIssuer = new \stdClass();
        $oIssuer->id = $expected;
        $oIssuer->name = 'test';
        $oIssuer->image = new \stdClass();
        $oIssuer->image->size2x = 'testpic.png';

        $oIssuers = new \stdClass();
        $oIssuers->issuers = [$oIssuer];

        $oMethods = $this->getMockBuilder(MethodEndpoint::class)->disableOriginalConstructor()->getMock();
        $oMethods->method('get')->willReturn($oIssuers);

        $oMollieApi = $this->getMockBuilder(\Mollie\Api\MollieApiClient::class)->disableOriginalConstructor()->getMock();
        $oMollieApi->methods = $oMethods;

        UtilsObject::setClassInstance(\Mollie\Api\MollieApiClient::class, $oMollieApi);

        $oPaymentConfig = $this->getMockBuilder(PaymentConfig::class)->disableOriginalConstructor()->getMock();
        $oPaymentConfig->method('getPaymentConfig')->willReturn(['add_qr' => true, 'issuer_list_type' => 'radiobutton']);

        UtilsObject::setClassInstance(PaymentConfig::class, $oPaymentConfig);

        $oPayment = new \Mollie\Payment\Application\Model\Payment\Giftcard();
        $result = $oPayment->getIssuers([], '');

        $this->assertArrayHasKey($expected, $result);
    }

    public function testGetIssuersException()
    {
        $oMethods = $this->getMockBuilder(MethodEndpoint::class)->disableOriginalConstructor()->getMock();
        $oMethods->method('get')->willThrowException(new \Exception("Test-exception"));

        $oMollieApi = $this->getMockBuilder(\Mollie\Api\MollieApiClient::class)->disableOriginalConstructor()->getMock();
        $oMollieApi->methods = $oMethods;

        UtilsObject::setClassInstance(\Mollie\Api\MollieApiClient::class, $oMollieApi);

        $oPaymentConfig = $this->getMockBuilder(PaymentConfig::class)->disableOriginalConstructor()->getMock();
        $oPaymentConfig->method('getPaymentConfig')->willReturn(['add_qr' => true, 'issuer_list_type' => 'radiobutton']);

        UtilsObject::setClassInstance(PaymentConfig::class, $oPaymentConfig);

        $oPayment = new \Mollie\Payment\Application\Model\Payment\Giftcard();
        $result = $oPayment->getIssuers([], '');

        $this->assertCount(0, $result);
    }
}
