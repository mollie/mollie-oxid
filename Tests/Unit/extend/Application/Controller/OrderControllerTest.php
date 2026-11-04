<?php


namespace Mollie\Payment\Tests\Unit\extend\Application\Controller;


use Mollie\Payment\Application\Helper\PaymentFinishLink;
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

    /**
     * Prepares mocks for a return from Mollie after the payment was restarted via the payment finish link
     *
     * @param  string $sReinitOrderIdInSession
     * @param  User   $oOrderUser
     * @param  bool   $blCheckUsrRemoval OXID itself removes "usr" from the session when no user is found, so this check is optional
     * @return \OxidEsales\Eshop\Core\Session
     */
    protected function prepareReinitializedPaymentReturn($sReinitOrderIdInSession, $oOrderUser, $blCheckUsrRemoval = true)
    {
        UtilsObject::setClassInstance(\OxidEsales\Eshop\Application\Model\Payment::class, $this->getPaymentMock());

        $oRequest = $this->getMockBuilder(\OxidEsales\Eshop\Core\Request::class)->disableOriginalConstructor()->getMock();
        $oRequest->method('getRequestEscapedParameter')->willReturnCallback(function ($sName) {
            return $sName == \Mollie\Payment\extend\Application\Model\Order::MOLLIE_PAYMENT_REINIT_PARAM ? '1' : null;
        });
        Registry::set(\OxidEsales\Eshop\Core\Request::class, $oRequest);

        $aSessionVars = [
            'sess_challenge' => 'reinitOrder',
            PaymentFinishLink::SESSION_VAR_REINIT_ORDER_ID => $sReinitOrderIdInSession,
        ];
        $oSession = $this->getMockBuilder(\OxidEsales\Eshop\Core\Session::class)->disableOriginalConstructor()->getMock();
        $oSession->method('getVariable')->willReturnCallback(function ($sName) use ($aSessionVars) {
            return isset($aSessionVars[$sName]) ? $aSessionVars[$sName] : null;
        });
        $oSession->method('checkSessionChallenge')->willReturn(true);
        $oSession->method('getBasket')->willReturn($this->getBasketMock());
        $oSession->method('setVariable')->willReturnCallback(function ($sName) {
            if ($sName == 'usr') {
                $this->fail('Unexpected session variable usr');
            }
        });
        $oSession->method('deleteVariable')->willReturnCallback(function ($sName) use ($blCheckUsrRemoval) {
            if ($sName == 'usr' && $blCheckUsrRemoval === true) {
                $this->fail('Unexpected removal of session variable usr');
            }
        });
        Registry::set(\OxidEsales\Eshop\Core\Session::class, $oSession);

        $oConfig = $this->getMockBuilder(\OxidEsales\Eshop\Core\Config::class)->disableOriginalConstructor()->getMock();
        $oConfig->method('getShopId')->willReturn(1);
        $oConfig->method('getConfigParam')->willReturnMap([
            ['blConfirmAGB', null, false],
            ['blEnableIntangibleProdAgreement', null, false],
        ]);
        Registry::set(\OxidEsales\Eshop\Core\Config::class, $oConfig);

        $oTransactionHandler = $this->getMockBuilder(\Mollie\Payment\Application\Model\TransactionHandler\Payment::class)->disableOriginalConstructor()->getMock();
        $oTransactionHandler->method('processTransaction')->willReturn(['success' => true]);

        $oCreditcard = $this->getMockBuilder(Creditcard::class)->disableOriginalConstructor()->getMock();
        $oCreditcard->method('getTransactionHandler')->willReturn($oTransactionHandler);

        $order = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $order->method('isLoaded')->willReturn(true);
        $order->method('getId')->willReturn('reinitOrder');
        $order->method('getOrderUser')->willReturn($oOrderUser);
        $order->method('__get')->willReturn(new \OxidEsales\Eshop\Core\Field("12345"));
        $order->method('mollieGetPaymentModel')->willReturn($oCreditcard);
        $order->method('finalizeOrder')->willReturn(1);

        UtilsObject::setClassInstance(Order::class, $order);

        return $oSession;
    }

    public function testHandleMollieReturnReinitializedPaymentUsesOrderUser()
    {
        $oOrderUser = $this->getMockBuilder(User::class)->disableOriginalConstructor()->getMock();
        $oSession = $this->prepareReinitializedPaymentReturn('reinitOrder', $oOrderUser);

        $oOrderController = new \Mollie\Payment\extend\Application\Controller\OrderController();
        $oOrderController->setUser(null); // no active user
        $oOrderController->setSession($oSession);

        $result = $oOrderController->handleMollieReturn();

        $this->assertEquals("thankyou", $result);
        $this->assertSame($oOrderUser, $oOrderController->getUser());

        $oOrderController->setUser(null);
    }

    public function testHandleMollieReturnReinitializedPaymentOtherOrderInSession()
    {
        $oOrderUser = $this->getMockBuilder(User::class)->disableOriginalConstructor()->getMock();
        $oSession = $this->prepareReinitializedPaymentReturn('otherOrder', $oOrderUser, false);

        $oOrderController = new \Mollie\Payment\extend\Application\Controller\OrderController();
        $oOrderController->setUser(null); // no active user
        $oOrderController->setSession($oSession);

        $result = $oOrderController->handleMollieReturn();

        // order user is not used, so OXID requests the user step
        $this->assertEquals("user", $result);
        $this->assertFalse($oOrderController->getUser());

        $oOrderController->setUser(null);
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

    public function testHandleMollieReturnNotCompleted()
    {
        UtilsObject::setClassInstance(\OxidEsales\Eshop\Application\Model\Payment::class, $this->getPaymentMock());

        $aSessionVars = [];
        $oSession = $this->getMockBuilder(\OxidEsales\Eshop\Core\Session::class)->disableOriginalConstructor()->getMock();
        $oSession->method('getVariable')->willReturn('12345');
        $oSession->method('getBasket')->willReturn($this->getBasketMock());
        $oSession->method('setVariable')->willReturnCallback(function ($sName, $sValue) use (&$aSessionVars) {
            $aSessionVars[$sName] = $sValue;
        });

        Registry::set(\OxidEsales\Eshop\Core\Session::class, $oSession);

        $oUtils = $this->getMock(\OxidEsales\Eshop\Core\Utils::class, array('redirect'));
        $oUtils->method('redirect')->willReturn(null);
        Registry::set(\OxidEsales\Eshop\Core\Utils::class, $oUtils);

        $aResult = ['success' => false, 'status' => 'open'];

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
        $this->assertEquals(-50, $aSessionVars['payerror']);
        $this->assertEquals(Registry::getLang()->translateString('MOLLIE_ERROR_ORDER_NOT_COMPLETED'), $aSessionVars['payerrortext']);
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