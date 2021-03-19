<?php


namespace Mollie\Payment\Tests\Unit\Application\Controller;


use Mollie\Payment\Application\Helper\DeliverySet;
use Mollie\Payment\Application\Helper\Payment;
use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Application\Model\BasketItem;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Exception\OutOfStockException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Session;
use OxidEsales\Eshop\Core\UtilsObject;
use OxidEsales\TestingLibrary\UnitTestCase;

class MollieApplePayTest extends UnitTestCase
{
    protected function setUp()
    {
        UtilsObject::resetClassInstances();
        \Mollie\Payment\Application\Helper\User::destroyInstance();
    }

    public function testGetMerchantSession()
    {
        $oRequest = $this->getMockBuilder(\OxidEsales\Eshop\Core\Request::class)->disableOriginalConstructor()->getMock();
        $oRequest->method('getRequestEscapedParameter')->willReturn(true);

        Registry::set(\OxidEsales\Eshop\Core\Request::class, $oRequest);

        $oWallet = $this->getMockBuilder(\Mollie\Api\Endpoints\WalletEndpoint::class)->disableOriginalConstructor()->getMock();
        $oWallet->method('requestApplePayPaymentSession')->willReturn("test123");

        $oMollieApi = $this->getMockBuilder(\Mollie\Api\MollieApiClient::class)->disableOriginalConstructor()->getMock();
        $oMollieApi->wallets = $oWallet;

        UtilsObject::setClassInstance(\Mollie\Api\MollieApiClient::class, $oMollieApi);

        $oUtils = $this->getMockBuilder(\OxidEsales\Eshop\Core\Utils::class)->disableOriginalConstructor()->getMock();
        $oUtils->method('showMessageAndExit')->willReturn(null);

        Registry::set(\OxidEsales\Eshop\Core\Utils::class, $oUtils);

        $oController = new \Mollie\Payment\Application\Controller\MollieApplePay();
        $result = $oController->getMerchantSession();

        $this->assertNull($result);
    }

    public function testGetMerchantSessionException()
    {
        $oRequest = $this->getMockBuilder(\OxidEsales\Eshop\Core\Request::class)->disableOriginalConstructor()->getMock();
        $oRequest->method('getRequestEscapedParameter')->willReturn(true);

        Registry::set(\OxidEsales\Eshop\Core\Request::class, $oRequest);

        $oWallet = $this->getMockBuilder(\Mollie\Api\Endpoints\WalletEndpoint::class)->disableOriginalConstructor()->getMock();
        $oWallet->method('requestApplePayPaymentSession')->willThrowException(new \Exception("Test-Error"));

        $oMollieApi = $this->getMockBuilder(\Mollie\Api\MollieApiClient::class)->disableOriginalConstructor()->getMock();
        $oMollieApi->wallets = $oWallet;

        UtilsObject::setClassInstance(\Mollie\Api\MollieApiClient::class, $oMollieApi);

        $oUtils = $this->getMockBuilder(\OxidEsales\Eshop\Core\Utils::class)->disableOriginalConstructor()->getMock();
        $oUtils->method('showMessageAndExit')->willReturn(null);

        Registry::set(\OxidEsales\Eshop\Core\Utils::class, $oUtils);

        $oController = new \Mollie\Payment\Application\Controller\MollieApplePay();
        $result = $oController->getMerchantSession();

        $this->assertNull($result);
    }

    public function testGetDomainName()
    {
        $expected = "Host";

        $_SERVER['HTTP_HOST'] = $expected;

        $oController = oxNew($this->getProxyClassName(\Mollie\Payment\Application\Controller\MollieApplePay::class));
        $result = $oController->getDomainName();

        $this->assertEquals($expected, $result);
    }

    public function testGetDomainNameServerName()
    {
        $expected = "ServerName";

        $_SERVER['SERVER_NAME'] = $expected;

        $oController = oxNew($this->getProxyClassName(\Mollie\Payment\Application\Controller\MollieApplePay::class));
        $result = $oController->getDomainName();

        $this->assertEquals($expected, $result);
    }

    public function testGetDeliveryMethods()
    {
        $expected = ['DeliveryList'];

        $oRequest = $this->getMockBuilder(\OxidEsales\Eshop\Core\Request::class)->disableOriginalConstructor()->getMock();
        $oRequest->method('getRequestEscapedParameter')->willReturn(true);

        Registry::set(\OxidEsales\Eshop\Core\Request::class, $oRequest);

        $oUtils = $this->getMockBuilder(\OxidEsales\Eshop\Core\Utils::class)->disableOriginalConstructor()->getMock();
        $oUtils->method('showMessageAndExit')->willReturn(null);

        Registry::set(\OxidEsales\Eshop\Core\Utils::class, $oUtils);

        $oBasketItem = $this->getMockBuilder(BasketItem::class)->disableOriginalConstructor()->getMock();
        $oBasketItem->method('getBasketItemkey')->willReturn('itemKey');

        $oBasket = $this->getMockBuilder(Basket::class)->disableOriginalConstructor()->getMock();
        $oBasket->method('addToBasket')->willReturn($oBasketItem);

        $oSession = $this->getMockBuilder(\OxidEsales\Eshop\Core\Session::class)->disableOriginalConstructor()->getMock();
        $oSession->method('getBasket')->willReturn($oBasket);

        Registry::set(\OxidEsales\Eshop\Core\Session::class, $oSession);

        $oDeliverySetHelper = $this->getMockBuilder(\Mollie\Payment\Application\Helper\DeliverySet::class)->disableOriginalConstructor()->getMock();
        $oDeliverySetHelper->method('getDeliveryMethods')->willReturn($expected);

        UtilsObject::setClassInstance(\Mollie\Payment\Application\Helper\DeliverySet::class, $oDeliverySetHelper);

        $oController = new \Mollie\Payment\Application\Controller\MollieApplePay();
        $result = $oController->getDeliveryMethods();

        $this->assertNull($result);

        UtilsObject::resetClassInstances();
        DeliverySet::destroyInstance();
    }

    public function testUpdateShippingSet()
    {
        $oRequest = $this->getMockBuilder(\OxidEsales\Eshop\Core\Request::class)->disableOriginalConstructor()->getMock();
        $oRequest->method('getRequestEscapedParameter')->willReturn('test');

        Registry::set(\OxidEsales\Eshop\Core\Request::class, $oRequest);

        $oUtils = $this->getMockBuilder(\OxidEsales\Eshop\Core\Utils::class)->disableOriginalConstructor()->getMock();
        $oUtils->method('showMessageAndExit')->willReturn(null);

        Registry::set(\OxidEsales\Eshop\Core\Utils::class, $oUtils);

        $oController = new \Mollie\Payment\Application\Controller\MollieApplePay();
        $result = $oController->updateShippingSet();

        $this->assertNull($result);
    }

    public function testFinalizeMollieOrder()
    {
        $oRequest = $this->getMockBuilder(\OxidEsales\Eshop\Core\Request::class)->disableOriginalConstructor()->getMock();
        $oRequest->method('getRequestEscapedParameter')->willReturn('test');

        Registry::set(\OxidEsales\Eshop\Core\Request::class, $oRequest);

        $oBasketItem = $this->getMockBuilder(BasketItem::class)->disableOriginalConstructor()->getMock();
        $oBasketItem->method('getBasketItemKey')->willReturn('test');

        $oBasket = $this->getMockBuilder(Basket::class)->disableOriginalConstructor()->getMock();
        $oBasket->method('addToBasket')->willReturn($oBasketItem);

        $oSession = $this->getMockBuilder(Session::class)->disableOriginalConstructor()->getMock();
        $oSession->method('getBasket')->willReturn($oBasket);

        Registry::set(Session::class, $oSession);

        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $oOrder->method('finalizeOrder')->willReturn(Order::ORDER_STATE_OK);
        $oOrder->method('getId')->willReturn('testId');

        UtilsObject::setClassInstance(Order::class, $oOrder);

        $oUser = $this->getMockBuilder(User::class)->disableOriginalConstructor()->getMock();

        $oUserHelper = $this->getMockBuilder(\Mollie\Payment\Application\Helper\User::class)->disableOriginalConstructor()->getMock();
        $oUserHelper->method('getApplePayUser')->willReturn($oUser);

        UtilsObject::setClassInstance(\Mollie\Payment\Application\Helper\User::class, $oUserHelper);

        $oUtils = $this->getMockBuilder(\OxidEsales\Eshop\Core\Utils::class)->disableOriginalConstructor()->getMock();
        $oUtils->method('showMessageAndExit')->willReturn(null);

        Registry::set(\OxidEsales\Eshop\Core\Utils::class, $oUtils);

        $oController = new \Mollie\Payment\Application\Controller\MollieApplePay();
        $result = $oController->finalizeMollieOrder();

        $this->assertNull($result);

        UtilsObject::resetClassInstances();
        \Mollie\Payment\Application\Helper\User::destroyInstance();
    }

    public function testFinalizeMollieOrderError()
    {
        $oRequest = $this->getMockBuilder(\OxidEsales\Eshop\Core\Request::class)->disableOriginalConstructor()->getMock();
        $oRequest->method('getRequestEscapedParameter')->willReturn('test');

        Registry::set(\OxidEsales\Eshop\Core\Request::class, $oRequest);

        $oBasketItem = $this->getMockBuilder(BasketItem::class)->disableOriginalConstructor()->getMock();
        $oBasketItem->method('getBasketItemKey')->willReturn('test');

        $oBasket = $this->getMockBuilder(Basket::class)->disableOriginalConstructor()->getMock();
        $oBasket->method('addToBasket')->willReturn($oBasketItem);

        $oSession = $this->getMockBuilder(Session::class)->disableOriginalConstructor()->getMock();
        $oSession->method('getBasket')->willReturn($oBasket);

        Registry::set(Session::class, $oSession);

        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $oOrder->method('finalizeOrder')->willReturn(Order::ORDER_STATE_INVALIDPAYMENT);
        $oOrder->method('getId')->willReturn('testId');

        UtilsObject::setClassInstance(Order::class, $oOrder);

        $oUser = $this->getMockBuilder(User::class)->disableOriginalConstructor()->getMock();

        $oUserHelper = $this->getMockBuilder(\Mollie\Payment\Application\Helper\User::class)->disableOriginalConstructor()->getMock();
        $oUserHelper->method('getApplePayUser')->willReturn($oUser);

        UtilsObject::setClassInstance(\Mollie\Payment\Application\Helper\User::class, $oUserHelper);

        $oDeliverySetHelper = $this->getMockBuilder(\Mollie\Payment\Application\Helper\DeliverySet::class)->disableOriginalConstructor()->getMock();
        $oDeliverySetHelper->method('isDeliverySetAvailableWithPaymentType')->willReturn(false);

        UtilsObject::setClassInstance(\Mollie\Payment\Application\Helper\DeliverySet::class, $oDeliverySetHelper);

        $oUtils = $this->getMockBuilder(\OxidEsales\Eshop\Core\Utils::class)->disableOriginalConstructor()->getMock();
        $oUtils->method('showMessageAndExit')->willReturn(null);

        Registry::set(\OxidEsales\Eshop\Core\Utils::class, $oUtils);

        $oController = new \Mollie\Payment\Application\Controller\MollieApplePay();
        $result = $oController->finalizeMollieOrder();

        $this->assertNull($result);

        UtilsObject::resetClassInstances();
        DeliverySet::destroyInstance();
        \Mollie\Payment\Application\Helper\User::destroyInstance();
    }

    public function testFinalizeMollieOrderException()
    {
        $oRequest = $this->getMockBuilder(\OxidEsales\Eshop\Core\Request::class)->disableOriginalConstructor()->getMock();
        $oRequest->method('getRequestEscapedParameter')->willReturn('test');

        Registry::set(\OxidEsales\Eshop\Core\Request::class, $oRequest);

        $oBasketItem = $this->getMockBuilder(BasketItem::class)->disableOriginalConstructor()->getMock();
        $oBasketItem->method('getBasketItemKey')->willReturn('test');

        $oBasket = $this->getMockBuilder(Basket::class)->disableOriginalConstructor()->getMock();
        $oBasket->method('addToBasket')->willReturn($oBasketItem);

        $oSession = $this->getMockBuilder(Session::class)->disableOriginalConstructor()->getMock();
        $oSession->method('getBasket')->willReturn($oBasket);

        Registry::set(Session::class, $oSession);

        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $oOrder->method('finalizeOrder')->willThrowException(new \Exception('Test-Error'));

        UtilsObject::setClassInstance(Order::class, $oOrder);

        $oUser = $this->getMockBuilder(User::class)->disableOriginalConstructor()->getMock();

        $oUserHelper = $this->getMockBuilder(\Mollie\Payment\Application\Helper\User::class)->disableOriginalConstructor()->getMock();
        $oUserHelper->method('getApplePayUser')->willReturn($oUser);

        UtilsObject::setClassInstance(\Mollie\Payment\Application\Helper\User::class, $oUserHelper);

        $oUtils = $this->getMockBuilder(\OxidEsales\Eshop\Core\Utils::class)->disableOriginalConstructor()->getMock();
        $oUtils->method('showMessageAndExit')->willReturn(null);

        Registry::set(\OxidEsales\Eshop\Core\Utils::class, $oUtils);

        $oController = new \Mollie\Payment\Application\Controller\MollieApplePay();
        $result = $oController->finalizeMollieOrder();

        $this->assertNull($result);

        UtilsObject::resetClassInstances();
        \Mollie\Payment\Application\Helper\User::destroyInstance();
    }

    public function testGetProductBasketPrice()
    {
        $oBasketItem = $this->getMockBuilder(BasketItem::class)->disableOriginalConstructor()->getMock();
        $oBasketItem->method('getBasketItemKey')->willReturn('test');

        $oBasket = $this->getMockBuilder(Basket::class)->disableOriginalConstructor()->getMock();
        $oBasket->method('addToBasket')->willReturn($oBasketItem);

        $oSession = $this->getMockBuilder(Session::class)->disableOriginalConstructor()->getMock();
        $oSession->method('getBasket')->willReturn($oBasket);

        Registry::set(Session::class, $oSession);

        $oRequest = $this->getMockBuilder(\OxidEsales\Eshop\Core\Request::class)->disableOriginalConstructor()->getMock();
        $oRequest->method('getRequestEscapedParameter')->willReturnMap([
            ['detailsProductId', null, 'productId'],
        ]);

        Registry::set(\OxidEsales\Eshop\Core\Request::class, $oRequest);

        $oUtils = $this->getMockBuilder(\OxidEsales\Eshop\Core\Utils::class)->disableOriginalConstructor()->getMock();
        $oUtils->method('showMessageAndExit')->willReturn(null);

        Registry::set(\OxidEsales\Eshop\Core\Utils::class, $oUtils);

        $oController = new \Mollie\Payment\Application\Controller\MollieApplePay();
        $result = $oController->getProductBasketPrice();

        $this->assertNull($result);
    }

    public function testGetProductBasketPriceOutOfStock()
    {
        $oBasket = $this->getMockBuilder(Basket::class)->disableOriginalConstructor()->getMock();
        $oBasket->method('addToBasket')->willThrowException(new OutOfStockException());

        $oSession = $this->getMockBuilder(Session::class)->disableOriginalConstructor()->getMock();
        $oSession->method('getBasket')->willReturn($oBasket);

        Registry::set(Session::class, $oSession);

        $oRequest = $this->getMockBuilder(\OxidEsales\Eshop\Core\Request::class)->disableOriginalConstructor()->getMock();
        $oRequest->method('getRequestEscapedParameter')->willReturnMap([
            ['detailsProductId', null, 'productId'],
            ['detailsProductAmount', null, 8],
        ]);

        Registry::set(\OxidEsales\Eshop\Core\Request::class, $oRequest);

        $oUtils = $this->getMockBuilder(\OxidEsales\Eshop\Core\Utils::class)->disableOriginalConstructor()->getMock();
        $oUtils->method('showMessageAndExit')->willReturn(null);

        Registry::set(\OxidEsales\Eshop\Core\Utils::class, $oUtils);

        $oController = new \Mollie\Payment\Application\Controller\MollieApplePay();
        $result = $oController->getProductBasketPrice();

        $this->assertNull($result);
    }

    public function testGetProductBasketPriceException()
    {
        $oBasket = $this->getMockBuilder(Basket::class)->disableOriginalConstructor()->getMock();
        $oBasket->method('addToBasket')->willThrowException(new \Exception());

        $oSession = $this->getMockBuilder(Session::class)->disableOriginalConstructor()->getMock();
        $oSession->method('getBasket')->willReturn($oBasket);

        Registry::set(Session::class, $oSession);

        $oRequest = $this->getMockBuilder(\OxidEsales\Eshop\Core\Request::class)->disableOriginalConstructor()->getMock();
        $oRequest->method('getRequestEscapedParameter')->willReturnMap([
            ['detailsProductId', null, 'productId'],
            ['detailsProductAmount', null, 8],
        ]);

        Registry::set(\OxidEsales\Eshop\Core\Request::class, $oRequest);

        $oUtils = $this->getMockBuilder(\OxidEsales\Eshop\Core\Utils::class)->disableOriginalConstructor()->getMock();
        $oUtils->method('showMessageAndExit')->willReturn(null);

        Registry::set(\OxidEsales\Eshop\Core\Utils::class, $oUtils);

        $oController = new \Mollie\Payment\Application\Controller\MollieApplePay();
        $result = $oController->getProductBasketPrice();

        $this->assertNull($result);
    }
}