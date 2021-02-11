<?php


namespace Mollie\Payment\Tests\Unit\extend\Application\Controller\Admin;


use OxidEsales\Eshop\Core\Registry;
use OxidEsales\TestingLibrary\UnitTestCase;

class PaymentMainTest extends UnitTestCase
{
    protected $_oConfig;

    public function setUp()
    {
        $oShop = $this->getMockBuilder(\OxidEsales\Eshop\Application\Model\Shop::class)->disableOriginalConstructor()->getMock();
        $oShop->method('getId')->willReturn('shopId');
        $oShop->method('__get')->with('oxshops__oxname')->willReturn(new \OxidEsales\Eshop\Core\Field("shopname"));

        $this->_oConfig = $this->getMockBuilder(\OxidEsales\Eshop\Core\Config::class)->disableOriginalConstructor()->getMock();
        $this->_oConfig->method('getActiveShop')->willReturn($oShop);
        $this->_oConfig->method('getShopId')->willReturn("shopId");
    }

    public function testMollieGetOrderFolders()
    {
        $expected = "test";

        $this->_oConfig->method('getConfigParam')->willReturnMap([
            ['aOrderfolder', null, $expected],
            ['iMaxShopId', null, null],
        ]);

        Registry::set(\OxidEsales\Eshop\Core\Config::class, $this->_oConfig);

        $oPaymentMainController = new \Mollie\Payment\extend\Application\Controller\Admin\PaymentMain();
        $result = $oPaymentMainController->mollieGetOrderFolders();

        $this->assertEquals($expected, $result);
    }

    public function testMollieIsTokenConfigured()
    {
        $this->_oConfig->method('getConfigParam')->willReturn(null);
        $this->_oConfig->method('getShopConfVar')->willReturnMap([
            ['sMollieMode', null, '', 'live'],
            ['sMollieLiveToken', null, '', '12345'],
        ]);

        Registry::set(\OxidEsales\Eshop\Core\Config::class, $this->_oConfig);

        $oPaymentMainController = new \Mollie\Payment\extend\Application\Controller\Admin\PaymentMain();
        $result = $oPaymentMainController->mollieIsTokenConfigured();

        $this->assertTrue($result);
    }

    public function testMollieIsTokenConfiguredTest()
    {
        $this->_oConfig->method('getConfigParam')->willReturn(null);
        $this->_oConfig->method('getShopConfVar')->willReturnMap([
            ['sMollieMode', null, '', 'test'],
            ['sMollieLiveToken', null, '', null],
            ['sMollieTestToken', null, '', '12345'],
        ]);

        Registry::set(\OxidEsales\Eshop\Core\Config::class, $this->_oConfig);

        $oPaymentMainController = new \Mollie\Payment\extend\Application\Controller\Admin\PaymentMain();
        $result = $oPaymentMainController->mollieIsTokenConfigured();

        $this->assertTrue($result);
    }

    public function testMollieIsTokenConfiguredFalse()
    {
        $this->_oConfig->method('getConfigParam')->willReturn(null);
        $this->_oConfig->method('getShopConfVar')->willReturn(null);

        Registry::set(\OxidEsales\Eshop\Core\Config::class, $this->_oConfig);

        $oPaymentMainController = new \Mollie\Payment\extend\Application\Controller\Admin\PaymentMain();
        $result = $oPaymentMainController->mollieIsTokenConfigured();

        $this->assertFalse($result);
    }

    public function testMollieGetExpiryDayOptions()
    {
        $oPaymentMainController = new \Mollie\Payment\extend\Application\Controller\Admin\PaymentMain();
        $result = $oPaymentMainController->mollieGetExpiryDayOptions();

        $this->assertCount(31, $result);
    }

    public function testSave()
    {
        $oPaymentMainController = new \Mollie\Payment\extend\Application\Controller\Admin\PaymentMain();
        $result = $oPaymentMainController->save();

        $this->assertNull($result);
    }
}