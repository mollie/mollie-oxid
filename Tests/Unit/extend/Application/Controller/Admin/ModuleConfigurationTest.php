<?php


namespace Mollie\Payment\Tests\Unit\extend\Application\Controller\Admin;


use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use OxidEsales\TestingLibrary\UnitTestCase;

class ModuleConfigurationTest extends UnitTestCase
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

        $_FILES = [
            "testFile" => [
                "name" => "filename.png",
                "error" => 0,
            ],
        ];
    }

    public function testMollieGetOrderFolders()
    {
        $expected = "test";

        $this->_oConfig->method('getConfigParam')->willReturnMap([
            ['aOrderfolder', null, $expected],
            ['iMaxShopId', null, null],
        ]);

        Registry::set(\OxidEsales\Eshop\Core\Config::class, $this->_oConfig);

        $oModuleConfigController = new \Mollie\Payment\extend\Application\Controller\Admin\ModuleConfiguration();
        $result = $oModuleConfigController->mollieGetOrderFolders();

        $this->assertEquals($expected, $result);
    }

    public function testMollieSecondChanceDayDiffs()
    {
        $oModuleConfigController = new \Mollie\Payment\extend\Application\Controller\Admin\ModuleConfiguration();
        $result = $oModuleConfigController->mollieSecondChanceDayDiffs();

        $this->assertCount(14, $result);
    }

    public function testMollieHasUploadError()
    {
        $oModuleConfigController = new \Mollie\Payment\extend\Application\Controller\Admin\ModuleConfiguration();
        $result = $oModuleConfigController->mollieHasUploadError();

        $this->assertFalse($result);
    }

    public function testMollieHasApiKeysFalse()
    {
        $this->_oConfig->method('getConfigParam')->willReturn(null);
        $this->_oConfig->method('getShopConfVar')->willReturn(null);

        Registry::set(\OxidEsales\Eshop\Core\Config::class, $this->_oConfig);

        $oModuleConfigController = new \Mollie\Payment\extend\Application\Controller\Admin\ModuleConfiguration();
        $result = $oModuleConfigController->mollieHasApiKeys();

        $this->assertFalse($result);
    }

    public function testMollieHasApiKeysLive()
    {
        $this->_oConfig->method('getConfigParam')->willReturn(null);
        $this->_oConfig->method('getShopConfVar')->willReturn('123');

        Registry::set(\OxidEsales\Eshop\Core\Config::class, $this->_oConfig);

        $oModuleConfigController = new \Mollie\Payment\extend\Application\Controller\Admin\ModuleConfiguration();
        $result = $oModuleConfigController->mollieHasApiKeys();

        $this->assertTrue($result);
    }

    public function testMollieHasApiKeysTest()
    {
        $this->_oConfig->method('getConfigParam')->willReturn(null);
        $this->_oConfig->method('getShopConfVar')->willReturnMap([
            ['sMollieLiveToken', null, '', null],
            ['sMollieTestToken', null, '', '123'],
        ]);

        Registry::set(\OxidEsales\Eshop\Core\Config::class, $this->_oConfig);

        $oModuleConfigController = new \Mollie\Payment\extend\Application\Controller\Admin\ModuleConfiguration();
        $result = $oModuleConfigController->mollieHasApiKeys();

        $this->assertTrue($result);
    }

    public function testDeleteMollieAltLogo()
    {
        $oModuleConfigController = new \Mollie\Payment\extend\Application\Controller\Admin\ModuleConfiguration();
        $result = $oModuleConfigController->deleteMollieAltLogo();

        $this->assertNull($result);
    }

    public function testMolliePaymentMethods()
    {
        $oModuleConfigController = new \Mollie\Payment\extend\Application\Controller\Admin\ModuleConfiguration();
        $result = $oModuleConfigController->molliePaymentMethods();

        $this->assertCount(17, $result);
    }

    public function testMollieIsApiKeyUsable()
    {
        $this->_oConfig->method('getConfigParam')->willReturn(null);
        $this->_oConfig->method('getShopConfVar')->willReturnMap([
            ['sMollieLiveToken', null, '', null],
            ['sMollieTestToken', null, '', null],
        ]);

        Registry::set(\OxidEsales\Eshop\Core\Config::class, $this->_oConfig);

        $oModuleConfigController = new \Mollie\Payment\extend\Application\Controller\Admin\ModuleConfiguration();
        $result = $oModuleConfigController->mollieIsApiKeyUsable("sMollieTestToken");

        $this->assertFalse($result);
    }

    public function testMollieGetConfiguredAltLogoValue()
    {
        $expected = "test";

        $this->_oConfig->method('getConfigParam')->willReturn(null);
        $this->_oConfig->method('getShopConfVar')->willReturn($expected);

        Registry::set(\OxidEsales\Eshop\Core\Config::class, $this->_oConfig);

        $oModuleConfigController = new \Mollie\Payment\extend\Application\Controller\Admin\ModuleConfiguration();
        $result = $oModuleConfigController->mollieGetConfiguredAltLogoValue("testVar");

        $this->assertEquals($expected, $result);
    }

    public function testSaveConfVars()
    {
        $oUtilsFile = $this->getMockBuilder(\OxidEsales\Eshop\Core\UtilsFile::class)->disableOriginalConstructor()->getMock();
        $oUtilsFile->method('processFile')->willReturn(false);

        Registry::set(\OxidEsales\Eshop\Core\UtilsFile::class, $oUtilsFile);

        $oModuleConfigController = oxNew($this->getProxyClassName(\Mollie\Payment\extend\Application\Controller\Admin\ModuleConfiguration::class));
        $oModuleConfigController->setNonPublicVar('_sModuleId', "molliepayment");
        $oModuleConfigController->setEditObjectId("molliepayment");

        $result = $oModuleConfigController->saveConfVars();

        $this->assertNull($result);
    }

    public function testSaveConfVarsTrue()
    {
        $oUtilsFile = $this->getMockBuilder(\OxidEsales\Eshop\Core\UtilsFile::class)->disableOriginalConstructor()->getMock();
        $oUtilsFile->method('processFile')->willReturn(true);

        Registry::set(\OxidEsales\Eshop\Core\UtilsFile::class, $oUtilsFile);

        $oModuleConfigController = oxNew($this->getProxyClassName(\Mollie\Payment\extend\Application\Controller\Admin\ModuleConfiguration::class));
        $oModuleConfigController->setNonPublicVar('_sModuleId', "molliepayment");
        $oModuleConfigController->setEditObjectId("molliepayment");

        $result = $oModuleConfigController->saveConfVars();

        $this->assertNull($result);
    }

    public function testSaveConfVarsException()
    {
        $oUtilsFile = $this->getMockBuilder(\OxidEsales\Eshop\Core\UtilsFile::class)->disableOriginalConstructor()->getMock();
        $oUtilsFile->method('processFile')->willThrowException(new \Exception("Test-Error"));

        Registry::set(\OxidEsales\Eshop\Core\UtilsFile::class, $oUtilsFile);

        $oRequest = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock();
        $oRequest->method('getRequestParameter')->willReturnMap([
            ['cl', null, 'module_config'],
            ['fnc', null, 'save'],
        ]);

        Registry::set(Request::class, $oRequest);
        
        $oModuleConfigController = oxNew($this->getProxyClassName(\Mollie\Payment\extend\Application\Controller\Admin\ModuleConfiguration::class));
        $oModuleConfigController->setNonPublicVar('_sModuleId', "molliepayment");
        $oModuleConfigController->setEditObjectId("molliepayment");

        $result = $oModuleConfigController->saveConfVars();

        $this->assertNull($result);

        $result = $oModuleConfigController->mollieGetUploadError();

        $this->assertNotEquals(Registry::getLang()->translateString("MOLLIE_ALTLOGO_ERROR"),  $result);
    }
}