<?php

namespace Mollie\Payment\Tests\Unit\Application\Controller;

use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Core\Price;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Session;
use OxidEsales\Eshop\Core\UtilsObject;
use OxidEsales\TestingLibrary\UnitTestCase;

class MolliePayPalExpressTest extends UnitTestCase
{
/*
 * public handlePayPalReturn
 * - getSessionFromMollie
 * - getPayPalExpressBasket
 * - handlePayPalExpressError
 */
    public function testInitSession()
    {
        $expected = "success";

        $oModule = $this->getMockBuilder(\OxidEsales\EshopCommunity\Core\Module\Module::class)->disableOriginalConstructor()->getMock();
        $oModule->method('getInfo')->willReturn('1.2.3');

        UtilsObject::setClassInstance(\OxidEsales\EshopCommunity\Core\Module\Module::class, $oModule);

        $oMollieSession = $this->getMockBuilder(\Mollie\Api\Resources\Session::class)->disableOriginalConstructor()->getMock();
        $oMollieSession->id = $expected;

        $oEndpoint = $this->getMockBuilder(\Mollie\Api\Endpoints\SessionEndpoint::class)->disableOriginalConstructor()->getMock();
        $oEndpoint->method('create')->willReturn($oMollieSession);

        $oMollieApi = $this->getMockBuilder(\Mollie\Api\MollieApiClient::class)->disableOriginalConstructor()->getMock();
        $oMollieApi->sessions = $oEndpoint;

        UtilsObject::setClassInstance(\Mollie\Api\MollieApiClient::class, $oMollieApi);

        $oShop = $this->getMockBuilder(Shop::class)->disableOriginalConstructor()->setMethods(['getFieldData'])->getMock();
        $oShop->method('getFieldData')->willReturn("name");

        $oConfig = $this->getMockBuilder(\OxidEsales\Eshop\Core\Config::class)->disableOriginalConstructor()->getMock();
        $oConfig->method('getShopConfVar')->willReturn("mollieToken");
        $oConfig->method('getActiveShop')->willReturn($oShop);
        Registry::set(\OxidEsales\Eshop\Core\Config::class, $oConfig);

        $oCurrency = new \stdClass();
        $oCurrency->name = "Test";

        $oBasket = $this->getMockBuilder(Basket::class)->disableOriginalConstructor()->getMock();
        $oBasket->method('getBruttoSum')->willReturn(100);
        $oBasket->method('getBasketCurrency')->willReturn($oCurrency);

        Registry::set(\OxidEsales\Eshop\Core\Session::class, null);
        Registry::getSession()->setBasket($oBasket);

        $oUtils = $this->getMock(\OxidEsales\Eshop\Core\Utils::class, array('showMessageAndExit'));
        $oUtils->method('showMessageAndExit')->willReturn(null);
        Registry::set(\OxidEsales\Eshop\Core\Utils::class, $oUtils);

        $oController = new \Mollie\Payment\Application\Controller\MolliePayPalExpress();
        $oController->initSession();

        $this->assertEquals($expected, Registry::getSession()->getVariable('mollie_ppe_sessionId'));
    }

    public function testHandlePayPalCancel()
    {
        $oUtils = $this->getMock(\OxidEsales\Eshop\Core\Utils::class, array('redirect'));
        $oUtils->method('redirect')->willReturn(null);
        Registry::set(\OxidEsales\Eshop\Core\Utils::class, $oUtils);

        $oController = new \Mollie\Payment\Application\Controller\MolliePayPalExpress();
        $result = $oController->handlePayPalCancel();

        $this->assertNull($result);
    }

    public function testHandlePayPalReturn()
    {
        $expected = "sessionAuthId";
        $userId = "4711";

        $oUser = $this->getMockBuilder(\OxidEsales\Eshop\Application\Model\User::class)->disableOriginalConstructor()->getMock();
        $oUser->method('getId')->willReturn($userId);

        UtilsObject::setClassInstance(\OxidEsales\Eshop\Application\Model\User::class, $oUser);

        $oView = $this->getMockBuilder(\OxidEsales\Eshop\Application\Controller\OrderController::class)->disableOriginalConstructor()->getMock();
        $oView->method('getUser')->willReturn(false);

        $oConfig = $this->getMockBuilder(\OxidEsales\Eshop\Core\Config::class)->disableOriginalConstructor()->getMock();
        $oConfig->method('getShopConfVar')->willReturn("mollieToken");
        $oConfig->method('getActiveView')->willReturn($oView);
        Registry::set(\OxidEsales\Eshop\Core\Config::class, $oConfig);

        $oModule = $this->getMockBuilder(\OxidEsales\EshopCommunity\Core\Module\Module::class)->disableOriginalConstructor()->getMock();
        $oModule->method('getInfo')->willReturn('1.2.3');

        UtilsObject::setClassInstance(\OxidEsales\EshopCommunity\Core\Module\Module::class, $oModule);

        $oAddress = new \stdClass();
        $oAddress->email = "test";
        $oAddress->givenName = "Paul";
        $oAddress->familyName = "Payer";
        $oAddress->country = "DE";
        $oAddress->city = "Berlin";
        $oAddress->postalCode = "12345";
        $oAddress->streetAndNumber = "Teststr. 9";

        $oMollieSession = $this->getMockBuilder(\Mollie\Api\Resources\Session::class)->disableOriginalConstructor()->getMock();
        $oMollieSession->authenticationId = $expected;
        $oMollieSession->shippingAddress = $oAddress;

        $oEndpoint = $this->getMockBuilder(\Mollie\Api\Endpoints\SessionEndpoint::class)->disableOriginalConstructor()->getMock();
        $oEndpoint->method('get')->willReturn($oMollieSession);

        $oMollieApi = $this->getMockBuilder(\Mollie\Api\MollieApiClient::class)->disableOriginalConstructor()->getMock();
        $oMollieApi->sessions = $oEndpoint;

        UtilsObject::setClassInstance(\Mollie\Api\MollieApiClient::class, $oMollieApi);

        Registry::getSession()->setVariable('mollie_ppe_sessionId', "test");

        $oUtils = $this->getMock(\OxidEsales\Eshop\Core\Utils::class, array('redirect'));
        $oUtils->method('redirect')->willReturn(null);
        Registry::set(\OxidEsales\Eshop\Core\Utils::class, $oUtils);

        Registry::getSession()->setVariable('sShipSet', "default");

        $oBasket = $this->getMockBuilder(Basket::class)->disableOriginalConstructor()->getMock();
        Registry::getSession()->setBasket($oBasket);

        $oController = new \Mollie\Payment\Application\Controller\MolliePayPalExpress();
        $oController->handlePayPalReturn();

        $this->assertEquals($expected, Registry::getSession()->getVariable('mollie_ppe_authenticationId'));
        $this->assertEquals($userId, Registry::getSession()->getVariable('usr'));
    }
}