<?php


namespace Mollie\Payment\Tests\Unit\Application\Model\Payment;


use Mollie\Api\Endpoints\MethodEndpoint;
use Mollie\Payment\Application\Helper\Payment;
use Mollie\Payment\Application\Model\PaymentConfig;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use OxidEsales\Eshop\Core\UtilsObject;
use OxidEsales\TestingLibrary\UnitTestCase;

class CreditcardTest extends UnitTestCase
{
    public function testGetProfileId()
    {
        $expected = "test";

        $current = new \stdClass();
        $current->id = $expected;

        $oProfiles = $this->getMockBuilder(\Mollie\Api\Endpoints\ProfileEndpoint::class)->disableOriginalConstructor()->getMock();
        $oProfiles->method('getCurrent')->willReturn($current);

        $oMollieApi = $this->getMockBuilder(\Mollie\Api\MollieApiClient::class)->disableOriginalConstructor()->getMock();
        $oMollieApi->profiles = $oProfiles;

        UtilsObject::setClassInstance(\Mollie\Api\MollieApiClient::class, $oMollieApi);

        $oPayment = new \Mollie\Payment\Application\Model\Payment\Creditcard();
        $result = $oPayment->getProfileId();

        $this->assertEquals($expected, $result);
    }

    public function testGetMollieMode()
    {
        $expected = "test";

        $oConfig = $this->getMockBuilder(Config::class)->disableOriginalConstructor()->getMock();
        $oConfig->method('getShopConfVar')->willReturn($expected);

        Registry::set(Config::class, $oConfig);

        $oPayment = new \Mollie\Payment\Application\Model\Payment\Creditcard();
        $result = $oPayment->getMollieMode();

        $this->assertEquals($expected, $result);
    }

    public function testGetLocale()
    {
        $expected = 'en_US';

        $oLang = $this->getMockBuilder(\OxidEsales\Eshop\Core\Language::class)->disableOriginalConstructor()->getMock();
        $oLang->method('isTranslated')->willReturn(false);

        Registry::set(\OxidEsales\Eshop\Core\Language::class, $oLang);

        $oPayment = new \Mollie\Payment\Application\Model\Payment\Creditcard();
        $result = $oPayment->getLocale();

        $this->assertEquals($expected, $result);
    }

    public function testGetPaymentSpecificParameters()
    {
        $expected = "testCCToken";

        $oUser = $this->getMockBuilder(User::class)->disableOriginalConstructor()->getMock();
        $oUser->method('hasAccount')->willReturn(true);
        $oUser->method('__get')->willReturn(new Field(null));

        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $oOrder->method('getUser')->willReturn($oUser);

        $oPaymentConfig = $this->getMockBuilder(PaymentConfig::class)->disableOriginalConstructor()->getMock();
        $oPaymentConfig->method('getPaymentConfig')->willReturn(['single_click_enabled' => true]);

        UtilsObject::setClassInstance(PaymentConfig::class, $oPaymentConfig);

        $aDynValue = [
            'mollieCCToken' => $expected,
            'single_click_accepted' => true,
        ];

        $oRequest = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock();
        $oRequest->method('getRequestParameter')->willReturn($aDynValue);

        Registry::set(Request::class, $oRequest);

        $oConfig = $this->getMockBuilder(Config::class)->disableOriginalConstructor()->getMock();
        $oConfig->method('getShopConfVar')->willReturn('live');

        Registry::set(Config::class, $oConfig);

        \Mollie\Payment\Application\Helper\User::destroyInstance();

        $oUserHelper = $this->getMockBuilder(\Mollie\Payment\Application\Helper\User::class)->disableOriginalConstructor()->getMock();
        #$oUserHelper->method('createMollieUser')->willReturn(null);

        UtilsObject::setClassInstance(\Mollie\Payment\Application\Helper\User::class, $oUserHelper);

        $oPayment = new \Mollie\Payment\Application\Model\Payment\Creditcard();
        $result = $oPayment->getPaymentSpecificParameters($oOrder);

        $this->assertArrayHasKey("cardToken", $result);
        $this->assertEquals($expected, $result['cardToken']);

        UtilsObject::resetClassInstances();
        \Mollie\Payment\Application\Helper\User::destroyInstance();
    }
}
