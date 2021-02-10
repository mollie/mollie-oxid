<?php


namespace Mollie\Payment\Tests\Unit\Application\Model\Payment;


use Mollie\Api\Endpoints\MethodEndpoint;
use Mollie\Payment\Application\Helper\Payment;
use Mollie\Payment\Application\Model\PaymentConfig;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use OxidEsales\Eshop\Core\UtilsObject;
use OxidEsales\TestingLibrary\UnitTestCase;

class BanktransferTest extends UnitTestCase
{
    public function testGetPaymentSpecificParameters()
    {
        $expected = 'TestMail';

        $oPaymentConfig = $this->getMockBuilder(PaymentConfig::class)->disableOriginalConstructor()->getMock();
        $oPaymentConfig->method('getPaymentConfig')->willReturn(['api' => 'payment', 'due_days' => 5]);

        UtilsObject::setClassInstance(PaymentConfig::class, $oPaymentConfig);

        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $oOrder->method('__get')->willReturn(new Field($expected));

        $oPayment = new \Mollie\Payment\Application\Model\Payment\Banktransfer();
        $result = $oPayment->getPaymentSpecificParameters($oOrder);

        $this->assertEquals($expected, $result['billingEmail']);
    }

    public function testGetPaymentSpecificParametersOrderApi()
    {
        $expected = 'TestMail';

        $oPaymentConfig = $this->getMockBuilder(PaymentConfig::class)->disableOriginalConstructor()->getMock();
        $oPaymentConfig->method('getPaymentConfig')->willReturn(['api' => 'order', 'due_days' => null]);

        UtilsObject::setClassInstance(PaymentConfig::class, $oPaymentConfig);

        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $oOrder->method('__get')->willReturn(new Field($expected));

        $oPayment = new \Mollie\Payment\Application\Model\Payment\Banktransfer();
        $result = $oPayment->getPaymentSpecificParameters($oOrder);

        $this->assertCount(0, $result);
    }

    public function testgetApiEndpoint()
    {
        $oApiOrderEndpoint = $this->getMockBuilder(\Mollie\Api\Endpoints\OrderEndpoint::class)->disableOriginalConstructor()->getMock();

        $oMollieApi = $this->getMockBuilder(\Mollie\Api\MollieApiClient::class)->disableOriginalConstructor()->getMock();
        $oMollieApi->orders = $oApiOrderEndpoint;

        UtilsObject::setClassInstance(\Mollie\Api\MollieApiClient::class, $oMollieApi);

        $oPaymentConfig = $this->getMockBuilder(PaymentConfig::class)->disableOriginalConstructor()->getMock();
        $oPaymentConfig->method('getPaymentConfig')->willReturn(['api' => 'order']);

        UtilsObject::setClassInstance(PaymentConfig::class, $oPaymentConfig);

        $oPayment = new \Mollie\Payment\Application\Model\Payment\Banktransfer();
        $result = $oPayment->getApiEndpoint();

        $this->assertInstanceOf(\Mollie\Api\Endpoints\OrderEndpoint::class, $result);

        Payment::destroyInstance();
    }

    public function testgetApiEndpointPayment()
    {
        $oApiPaymentEndpoint = $this->getMockBuilder(\Mollie\Api\Endpoints\PaymentEndpoint::class)->disableOriginalConstructor()->getMock();

        $oMollieApi = $this->getMockBuilder(\Mollie\Api\MollieApiClient::class)->disableOriginalConstructor()->getMock();
        $oMollieApi->payments = $oApiPaymentEndpoint;

        UtilsObject::setClassInstance(\Mollie\Api\MollieApiClient::class, $oMollieApi);

        $oPaymentConfig = $this->getMockBuilder(PaymentConfig::class)->disableOriginalConstructor()->getMock();
        $oPaymentConfig->method('getPaymentConfig')->willReturn(['api' => 'payment']);

        UtilsObject::setClassInstance(PaymentConfig::class, $oPaymentConfig);

        $oPayment = new \Mollie\Payment\Application\Model\Payment\Banktransfer();
        $result = $oPayment->getApiEndpoint();

        $this->assertInstanceOf(\Mollie\Api\Endpoints\PaymentEndpoint::class, $result);

        Payment::destroyInstance();
    }

    public function testGetApiRequestModel()
    {
        $oPaymentConfig = $this->getMockBuilder(PaymentConfig::class)->disableOriginalConstructor()->getMock();
        $oPaymentConfig->method('getPaymentConfig')->willReturn(['api' => 'order']);

        UtilsObject::setClassInstance(PaymentConfig::class, $oPaymentConfig);

        $oPayment = new \Mollie\Payment\Application\Model\Payment\Banktransfer();

        $result = $oPayment->getApiRequestModel();
        $this->assertInstanceOf(\Mollie\Payment\Application\Model\Request\Order::class, $result);

        $result = $oPayment->getTransactionHandler();
        $this->assertInstanceOf(\Mollie\Payment\Application\Model\TransactionHandler\Order::class, $result);
    }

    public function testGetApiRequestModelPayment()
    {
        $oPaymentConfig = $this->getMockBuilder(PaymentConfig::class)->disableOriginalConstructor()->getMock();
        $oPaymentConfig->method('getPaymentConfig')->willReturn(['api' => 'payment']);

        UtilsObject::setClassInstance(PaymentConfig::class, $oPaymentConfig);

        $oPayment = new \Mollie\Payment\Application\Model\Payment\Banktransfer();

        $result = $oPayment->getApiRequestModel();
        $this->assertInstanceOf(\Mollie\Payment\Application\Model\Request\Payment::class, $result);

        $result = $oPayment->getTransactionHandler();
        $this->assertInstanceOf(\Mollie\Payment\Application\Model\TransactionHandler\Payment::class, $result);
    }

    public function testIsMolliePaymentActiveFalse()
    {
        $oMethods = $this->getMockBuilder(MethodEndpoint::class)->disableOriginalConstructor()->getMock();
        $oMethods->method('all')->willReturn([]);

        $oMollieApi = $this->getMockBuilder(\Mollie\Api\MollieApiClient::class)->disableOriginalConstructor()->getMock();
        $oMollieApi->methods = $oMethods;

        UtilsObject::setClassInstance(\Mollie\Api\MollieApiClient::class, $oMollieApi);

        $oPayment = new \Mollie\Payment\Application\Model\Payment\Banktransfer();
        $result = $oPayment->isMolliePaymentActive();

        $this->assertFalse($result);

        Payment::destroyInstance();
    }

    public function testIsMolliePaymentActive()
    {
        $expected  = "img.png";

        $oImage = new \stdClass();
        $oImage->size2x = $expected;

        $oItem = $this->getMockBuilder(\Mollie\Api\Resources\Method::class)->disableOriginalConstructor()->getMock();
        $oItem->id = "banktransfer";
        $oItem->description = "banktransfer description";
        $oItem->image = $oImage;

        $oMethods = $this->getMockBuilder(MethodEndpoint::class)->disableOriginalConstructor()->getMock();
        $oMethods->method('all')->willReturn([$oItem]);

        $oMollieApi = $this->getMockBuilder(\Mollie\Api\MollieApiClient::class)->disableOriginalConstructor()->getMock();
        $oMollieApi->methods = $oMethods;

        UtilsObject::setClassInstance(\Mollie\Api\MollieApiClient::class, $oMollieApi);

        $oPayment = new \Mollie\Payment\Application\Model\Payment\Banktransfer();

        $result = $oPayment->isMolliePaymentActive();
        $this->assertTrue($result);

        $oConfig = $this->getMockBuilder(Config::class)->disableOriginalConstructor()->getMock();
        $oConfig->method('getShopConfVar')->willReturn(null);

        Registry::set(Config::class, $oConfig);

        $result = $oPayment->getMolliePaymentMethodPic();
        $this->assertStringEndsWith($expected, $result);

        Payment::destroyInstance();
    }

    public function testGetExpiryDays()
    {
        $expected = 11;

        $oPaymentConfig = $this->getMockBuilder(PaymentConfig::class)->disableOriginalConstructor()->getMock();
        $oPaymentConfig->method('getPaymentConfig')->willReturn(['expiryDays' => $expected]);

        UtilsObject::setClassInstance(PaymentConfig::class, $oPaymentConfig);

        $oPayment = new \Mollie\Payment\Application\Model\Payment\Banktransfer();
        $result = $oPayment->getExpiryDays();

        $this->assertEquals($expected, $result);
    }

    public function testGetExpiryDaysDefault()
    {
        $oPayment = new \Mollie\Payment\Application\Model\Payment\Banktransfer();
        $result = $oPayment->getExpiryDays();

        $this->assertEquals(30, $result);
    }

    public function testGetAlternativeLogoUrl()
    {
        $expected = "test";

        $oViewConf = $this->getMockBuilder(\OxidEsales\Eshop\Core\ViewConfig::class)->disableOriginalConstructor()->getMock();
        $oViewConf->method('getModuleUrl')->willReturn("http://someurl.com/".$expected);

        $oView = $this->getMockBuilder(\OxidEsales\Eshop\Application\Controller\FrontendController::class)->disableOriginalConstructor()->getMock();
        $oView->method('getViewConfig')->willReturn($oViewConf);

        $oConfig = $this->getMockBuilder(Config::class)->disableOriginalConstructor()->getMock();
        $oConfig->method('getShopConfVar')->willReturn('Logo');
        $oConfig->method('getActiveView')->willReturn($oView);

        Registry::set(Config::class, $oConfig);

        $oPayment = new \Mollie\Payment\Application\Model\Payment\Banktransfer();

        $result = $oPayment->getAlternativeLogoUrl();
        $this->assertStringEndsWith($expected, $result);

        $result = $oPayment->getMolliePaymentMethodPic();
        $this->assertStringEndsWith($expected, $result);
    }

    public function testGetAlternativeLogoUrlFalse()
    {
        $oConfig = $this->getMockBuilder(Config::class)->disableOriginalConstructor()->getMock();
        $oConfig->method('getShopConfVar')->willReturn(null);

        Registry::set(Config::class, $oConfig);

        $oPayment = new \Mollie\Payment\Application\Model\Payment\Banktransfer();
        $result = $oPayment->getAlternativeLogoUrl();

        $this->assertFalse($result);
    }

    public function testGetMolliePaymentMethodPic()
    {
        $oMethods = $this->getMockBuilder(MethodEndpoint::class)->disableOriginalConstructor()->getMock();
        $oMethods->method('all')->willReturn([]);

        $oMollieApi = $this->getMockBuilder(\Mollie\Api\MollieApiClient::class)->disableOriginalConstructor()->getMock();
        $oMollieApi->methods = $oMethods;

        UtilsObject::setClassInstance(\Mollie\Api\MollieApiClient::class, $oMollieApi);

        $oConfig = $this->getMockBuilder(Config::class)->disableOriginalConstructor()->getMock();
        $oConfig->method('getShopConfVar')->willReturn(null);

        Registry::set(Config::class, $oConfig);

        $oPayment = new \Mollie\Payment\Application\Model\Payment\Banktransfer();
        $result = $oPayment->getMolliePaymentMethodPic();

        $this->assertFalse($result);
    }
}
