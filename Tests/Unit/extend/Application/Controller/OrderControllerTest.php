<?php


namespace Mollie\Payment\Tests\Unit\extend\Application\Controller;


use Mollie\Payment\Application\Model\Payment\Creditcard;
use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Session;
use OxidEsales\Eshop\Core\UtilsObject;
use OxidEsales\TestingLibrary\UnitTestCase;

class OrderControllerTest extends UnitTestCase
{
    protected function getBasketMock()
    {
        $oBasket = $this->getMockBuilder(Basket::class)->disableOriginalConstructor()->getMock();
        $oBasket->method('getProductsCount')->willReturn(2);
        $oBasket->method('getPaymentId')->willReturn('molliecreditcard');
        $oBasket->method('getPriceForPayment')->willReturn(5);
        return $oBasket;
    }

    protected function getPaymentMock()
    {
        $oPayment = $this->getMockBuilder(\OxidEsales\Eshop\Application\Model\Payment::class)->disableOriginalConstructor()->getMock();
        $oPayment->method('isMolliePaymentMethod')->willReturn(true);
        $oPayment->method('load')->willReturn(true);
        $oPayment->method('isValidPayment')->willReturn(true);
        return $oPayment;
    }

    public function testHandleMollieReturn()
    {
        UtilsObject::setClassInstance(\OxidEsales\Eshop\Application\Model\Payment::class, $this->getPaymentMock());

        $oSession = $this->getMockBuilder(\OxidEsales\Eshop\Core\Session::class)->disableOriginalConstructor()->getMock();
        $oSession->method('getVariable')->willReturn('12345');
        $oSession->method('checkSessionChallenge')->willReturn(true);
        $oSession->method('getBasket')->willReturn($this->getBasketMock());

        Registry::set(\OxidEsales\Eshop\Core\Session::class, $oSession);

        $oConfig = $this->getMockBuilder(\OxidEsales\Eshop\Core\Config::class)->disableOriginalConstructor()->getMock();
        $oConfig->method('getShopId')->willReturn(1);
        $oConfig->method('getConfigParam')->willReturnMap([
            ['blConfirmAGB', null, false],
            ['blEnableIntangibleProdAgreement', null, false],
        ]);

        Registry::set(\OxidEsales\Eshop\Core\Config::class, $oConfig);

        $aResult = ['success' => true];

        $oTransactionHandler = $this->getMockBuilder(\Mollie\Payment\Application\Model\TransactionHandler\Payment::class)->disableOriginalConstructor()->getMock();
        $oTransactionHandler->method('processTransaction')->willReturn($aResult);

        $oCreditcard = $this->getMockBuilder(Creditcard::class)->disableOriginalConstructor()->getMock();
        $oCreditcard->method('getTransactionHandler')->willReturn($oTransactionHandler);

        $order = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $order->method('isLoaded')->willReturn(true);
        $order->method('__get')->with('oxorder__oxtransid')->willReturn(new \OxidEsales\Eshop\Core\Field("12345"));
        $order->method('mollieGetPaymentModel')->willReturn($oCreditcard);
        $order->method('finalizeOrder')->willReturn(1);

        UtilsObject::setClassInstance(Order::class, $order);

        $oUser = $this->getMockBuilder(User::class)->disableOriginalConstructor()->getMock();

        $oOrderController = new \Mollie\Payment\extend\Application\Controller\OrderController();
        $oOrderController->setUser($oUser);
        $oOrderController->setSession($oSession);

        $result = $oOrderController->handleMollieReturn();
        $this->assertEquals("thankyou", $result);
    }

    public function testHandleMollieReturnNoOrder()
    {
        UtilsObject::setClassInstance(\OxidEsales\Eshop\Application\Model\Payment::class, $this->getPaymentMock());

        $oSession = $this->getMockBuilder(\OxidEsales\Eshop\Core\Session::class)->disableOriginalConstructor()->getMock();
        $oSession->method('getVariable')->willReturn(null);
        $oSession->method('getBasket')->willReturn($this->getBasketMock());

        Registry::set(\OxidEsales\Eshop\Core\Session::class, $oSession);

        $oUtils = $this->getMock(\OxidEsales\Eshop\Core\Utils::class, array('redirect'));
        $oUtils->method('redirect')->willReturn(null);
        Registry::set(\OxidEsales\Eshop\Core\Utils::class, $oUtils);

        $oOrderController = new \Mollie\Payment\extend\Application\Controller\OrderController();
        $oOrderController->setSession($oSession);

        $result = $oOrderController->handleMollieReturn();
        $this->assertFalse($result);
    }

    public function testHandleMollieReturnNoTransaction()
    {
        UtilsObject::setClassInstance(\OxidEsales\Eshop\Application\Model\Payment::class, $this->getPaymentMock());

        $oSession = $this->getMockBuilder(\OxidEsales\Eshop\Core\Session::class)->disableOriginalConstructor()->getMock();
        $oSession->method('getVariable')->willReturn('12345');
        $oSession->method('getBasket')->willReturn($this->getBasketMock());

        Registry::set(\OxidEsales\Eshop\Core\Session::class, $oSession);

        $oUtils = $this->getMock(\OxidEsales\Eshop\Core\Utils::class, array('redirect'));
        $oUtils->method('redirect')->willReturn(null);
        Registry::set(\OxidEsales\Eshop\Core\Utils::class, $oUtils);

        $order = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $order->method('isLoaded')->willReturn(true);
        $order->method('__get')->with('oxorder__oxtransid')->willReturn(new \OxidEsales\Eshop\Core\Field(""));

        UtilsObject::setClassInstance(Order::class, $order);

        $oOrderController = new \Mollie\Payment\extend\Application\Controller\OrderController();
        $oOrderController->setSession($oSession);

        $result = $oOrderController->handleMollieReturn();
        $this->assertFalse($result);
    }

    public function testHandleMollieReturnCanceled()
    {
        UtilsObject::setClassInstance(\OxidEsales\Eshop\Application\Model\Payment::class, $this->getPaymentMock());

        $oSession = $this->getMockBuilder(\OxidEsales\Eshop\Core\Session::class)->disableOriginalConstructor()->getMock();
        $oSession->method('getVariable')->willReturn('12345');
        $oSession->method('getBasket')->willReturn($this->getBasketMock());

        Registry::set(\OxidEsales\Eshop\Core\Session::class, $oSession);

        $oUtils = $this->getMock(\OxidEsales\Eshop\Core\Utils::class, array('redirect'));
        $oUtils->method('redirect')->willReturn(null);
        Registry::set(\OxidEsales\Eshop\Core\Utils::class, $oUtils);

        $aResult = ['success' => false, 'status' => 'canceled'];

        $oTransactionHandler = $this->getMockBuilder(\Mollie\Payment\Application\Model\TransactionHandler\Payment::class)->disableOriginalConstructor()->getMock();
        $oTransactionHandler->method('processTransaction')->willReturn($aResult);

        $oCreditcard = $this->getMockBuilder(Creditcard::class)->disableOriginalConstructor()->getMock();
        $oCreditcard->method('getTransactionHandler')->willReturn($oTransactionHandler);

        $order = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $order->method('isLoaded')->willReturn(true);
        $order->method('__get')->with('oxorder__oxtransid')->willReturn(new \OxidEsales\Eshop\Core\Field("12345"));
        $order->method('mollieGetPaymentModel')->willReturn($oCreditcard);

        UtilsObject::setClassInstance(Order::class, $order);

        $oOrderController = new \Mollie\Payment\extend\Application\Controller\OrderController();
        $oOrderController->setSession($oSession);

        $result = $oOrderController->handleMollieReturn();
        $this->assertFalse($result);
    }

    public function testHandleMollieReturnFailed()
    {
        UtilsObject::setClassInstance(\OxidEsales\Eshop\Application\Model\Payment::class, $this->getPaymentMock());

        $oSession = $this->getMockBuilder(\OxidEsales\Eshop\Core\Session::class)->disableOriginalConstructor()->getMock();
        $oSession->method('getVariable')->willReturn('12345');
        $oSession->method('getBasket')->willReturn($this->getBasketMock());

        Registry::set(\OxidEsales\Eshop\Core\Session::class, $oSession);

        $oUtils = $this->getMock(\OxidEsales\Eshop\Core\Utils::class, array('redirect'));
        $oUtils->method('redirect')->willReturn(null);
        Registry::set(\OxidEsales\Eshop\Core\Utils::class, $oUtils);

        $aResult = ['success' => false, 'status' => 'failed'];

        $oTransactionHandler = $this->getMockBuilder(\Mollie\Payment\Application\Model\TransactionHandler\Payment::class)->disableOriginalConstructor()->getMock();
        $oTransactionHandler->method('processTransaction')->willReturn($aResult);

        $oCreditcard = $this->getMockBuilder(Creditcard::class)->disableOriginalConstructor()->getMock();
        $oCreditcard->method('getTransactionHandler')->willReturn($oTransactionHandler);

        $order = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $order->method('isLoaded')->willReturn(true);
        $order->method('__get')->with('oxorder__oxtransid')->willReturn(new \OxidEsales\Eshop\Core\Field("12345"));
        $order->method('mollieGetPaymentModel')->willReturn($oCreditcard);

        UtilsObject::setClassInstance(Order::class, $order);

        $oOrderController = new \Mollie\Payment\extend\Application\Controller\OrderController();
        $oOrderController->setSession($oSession);

        $result = $oOrderController->handleMollieReturn();
        $this->assertFalse($result);
    }

    public function testRender()
    {
        $oSession = $this->getMockBuilder(\OxidEsales\Eshop\Core\Session::class)->disableOriginalConstructor()->getMock();
        $oSession->method('getVariable')->willReturn(true);

        Registry::set(\OxidEsales\Eshop\Core\Session::class, $oSession);

        $oOrderController = new \Mollie\Payment\extend\Application\Controller\OrderController();
        $oOrderController->setSession($oSession);
        $oOrderController->setIsOrderStep(false);

        $result = $oOrderController->render();
        $this->assertEquals("page/checkout/order.tpl", $result);
    }
}