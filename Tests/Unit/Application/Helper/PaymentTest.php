<?php


namespace Mollie\Payment\Tests\Unit\Application\Helper;


use Mollie\Api\Endpoints\MethodEndpoint;
use Mollie\Payment\Application\Helper\Payment;
use OxidEsales\Eshop\Application\Model\Shop;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\UtilsObject;
use OxidEsales\EshopCommunity\Core\Module\Module;
use OxidEsales\TestingLibrary\UnitTestCase;

class PaymentTest extends UnitTestCase
{
    public function testGetMolliePaymentModel()
    {
        $oPayment = Payment::getInstance();
        $result = $oPayment->getMolliePaymentModel("molliecreditcard");

        $this->assertInstanceOf(\Mollie\Payment\Application\Model\Payment\Base::class, $result);

        $this->expectException(\Exception::class);
        $result = $oPayment->getMolliePaymentModel("oxidcreditcard");
    }

    public function testGetMolliePaymentInfo()
    {
        $oConfig = $this->getMockBuilder(\OxidEsales\Eshop\Core\Config::class)->disableOriginalConstructor()->getMock();
        $oConfig->method('getShopConfVar')->willReturn("token");

        Registry::set(\OxidEsales\Eshop\Core\Config::class, $oConfig);

        $oImage = new \stdClass();
        $oImage->size2x = "img.png";

        $oItem = $this->getMockBuilder(\Mollie\Api\Resources\Method::class)->disableOriginalConstructor()->getMock();
        $oItem->id = "creditcard";
        $oItem->description = "creditcard description";
        $oItem->image = $oImage;

        $oMethods = $this->getMockBuilder(MethodEndpoint::class)->disableOriginalConstructor()->getMock();
        $oMethods->method('all')->willReturn([$oItem]);

        $oModule = $this->getMockBuilder(\OxidEsales\EshopCommunity\Core\Module\Module::class)->disableOriginalConstructor()->getMock();
        $oModule->method('getInfo')->willReturn('1.2.3');

        UtilsObject::setClassInstance(\OxidEsales\EshopCommunity\Core\Module\Module::class, $oModule);

        $oMollieApi = $this->getMockBuilder(\Mollie\Api\MollieApiClient::class)->disableOriginalConstructor()->getMock();
        $oMollieApi->methods = $oMethods;

        UtilsObject::setClassInstance(\Mollie\Api\MollieApiClient::class, $oMollieApi);

        $oPayment = Payment::getInstance();

        $blConnectionSuccessful = $oPayment->isConnectionWithTokenSuccessful('live');
        $this->assertTrue($blConnectionSuccessful);

        $result = $oPayment->getMolliePaymentInfo(10, 'test');

        $this->assertCount(1, $result);
    }

    public function testGetMolliePaymentInfoException()
    {
        $oConfig = $this->getMockBuilder(\OxidEsales\Eshop\Core\Config::class)->disableOriginalConstructor()->getMock();
        $oConfig->method('getShopConfVar')->willReturn(null);

        Registry::set(\OxidEsales\Eshop\Core\Config::class, $oConfig);

        $oPayment = Payment::getInstance();
        $result = $oPayment->getMolliePaymentInfo(10, 'test');

        $this->assertCount(0, $result);
    }

    public function testIsConnectionWithTokenSuccessfulFalse()
    {
        $oMethods = $this->getMockBuilder(MethodEndpoint::class)->disableOriginalConstructor()->getMock();
        $oMethods->method('all')->willReturn([]);

        $oMollieApi = $this->getMockBuilder(\Mollie\Api\MollieApiClient::class)->disableOriginalConstructor()->getMock();
        $oMollieApi->methods = $oMethods;

        UtilsObject::setClassInstance(\Mollie\Api\MollieApiClient::class, $oMollieApi);

        $oPayment = Payment::getInstance();

        $result = $oPayment->isConnectionWithTokenSuccessful('live');

        $this->assertFalse($result);
    }

    public function testGetModuleVersion()
    {
        $expected = "X.X";

        $oModule = $this->getMockBuilder(Module::class)->disableOriginalConstructor()->getMock();
        $oModule->method('getInfo')->willReturn($expected);

        UtilsObject::setClassInstance(Module::class, $oModule);

        $oPayment = oxNew($this->getProxyClassName(Payment::class));
        $result = $oPayment->getModuleVersion();

        $this->assertEquals($expected, $result);
    }

    public function testGetShopVersion()
    {
        $expected = "X.X";

        $oShop = $this->getMockBuilder(Shop::class)->disableOriginalConstructor()->getMock();
        $oShop->method('__get')->willReturn(new \OxidEsales\Eshop\Core\Field($expected));

        $oConfig = $this->getMockBuilder(\OxidEsales\Eshop\Core\Config::class)->disableOriginalConstructor()->getMock();
        $oConfig->method('getActiveShop')->willReturn($oShop);

        Registry::set(\OxidEsales\Eshop\Core\Config::class, $oConfig);

        $oPayment = oxNew($this->getProxyClassName(Payment::class));
        $result = $oPayment->getShopVersion();

        $this->assertEquals($expected, $result);
    }

    public function testGetProfileId()
    {
        $expected = "ProfileId";

        $oProfile = $this->getMockBuilder(\Mollie\Api\Resources\CurrentProfile::class)->disableOriginalConstructor()->getMock();
        $oProfile->id = $expected;

        $oProfiles = $this->getMockBuilder(\Mollie\Api\Endpoints\ProfileEndpoint::class)->disableOriginalConstructor()->getMock();
        $oProfiles->method('getCurrent')->willReturn($oProfile);

        $oMollieApi = $this->getMockBuilder(\Mollie\Api\MollieApiClient::class)->disableOriginalConstructor()->getMock();
        $oMollieApi->profiles = $oProfiles;

        UtilsObject::setClassInstance(\Mollie\Api\MollieApiClient::class, $oMollieApi);

        $oPayment = Payment::getInstance();

        $result = $oPayment->getProfileId();
        $this->assertEquals($expected, $result);
    }

    public function testGetLocale()
    {
        $expected = Registry::getLang()->translateString('MOLLIE_LOCALE');

        $oPayment = Payment::getInstance();
        $result = $oPayment->getLocale();

        $this->assertEquals($expected, $result);
    }

    public function testGetLocaleNotTranslated()
    {
        $expected = "en_US";

        $oLang = $this->getMockBuilder(\OxidEsales\Eshop\Core\Language::class)->disableOriginalConstructor()->getMock();
        $oLang->method('isTranslated')->willReturn(false);

        Registry::set(\OxidEsales\Eshop\Core\Language::class, $oLang);

        $oPayment = Payment::getInstance();
        $result = $oPayment->getLocale();

        $this->assertEquals($expected, $result);
    }
}