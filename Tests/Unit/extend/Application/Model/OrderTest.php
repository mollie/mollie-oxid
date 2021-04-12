<?php

namespace Mollie\Payment\Tests\Unit\extend\Application\Model;

use Mollie\Payment\Application\Helper\Payment;
use Mollie\Payment\Application\Model\Payment\Creditcard;
use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Application\Model\OrderArticle;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Application\Model\UserPayment;
use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\Email;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use OxidEsales\Eshop\Core\Session;
use OxidEsales\Eshop\Core\UtilsObject;
use OxidEsales\TestingLibrary\UnitTestCase;

class OrderTest extends UnitTestCase
{
    public function testMollieSetOrderNumber()
    {
        $oOrder = new \Mollie\Payment\extend\Application\Model\Order();
        $oOrder->setId();
        $oOrder->save();
        $oOrder->mollieSetOrderNumber();

        $this->assertTrue(is_numeric($oOrder->oxorder__oxordernr->value));
    }

    public function testMollieSetApplePayButtonMode()
    {
        $oOrder = new \Mollie\Payment\extend\Application\Model\Order();

        $this->assertFalse($oOrder->mollieIsApplePayButtonMode());

        $oOrder->mollieSetApplePayButtonMode(true);

        $this->assertTrue($oOrder->mollieIsApplePayButtonMode());
    }

    public function testMollieGetPaymentModel()
    {
        $oOrder = new \Mollie\Payment\extend\Application\Model\Order();
        $oOrder->oxorder__oxpaymenttype = new Field('molliecreditcard');
        $result = $oOrder->mollieGetPaymentModel();

        $this->assertInstanceOf(Creditcard::class, $result);
    }

    public function testMollieMarkOrderAsShipped()
    {
        Payment::destroyInstance();

        $oResponse = $this->getMockBuilder(\Mollie\Api\Resources\Shipment::class)->disableOriginalConstructor()->getMock();

        $oApiOrder = $this->getMockBuilder(\Mollie\Api\Resources\Order::class)->disableOriginalConstructor()->getMock();
        $oApiOrder->method('shipAll')->willReturn($oResponse);

        $oApiEndpoint = $this->getMockBuilder(\Mollie\Api\Endpoints\OrderEndpoint::class)->disableOriginalConstructor()->getMock();
        $oApiEndpoint->method('get')->willReturn($oApiOrder);

        $oPaymentModel = $this->getMockBuilder(Creditcard::class)->disableOriginalConstructor()->getMock();
        $oPaymentModel->method('getApiEndpoint')->willReturn($oApiEndpoint);

        $oPaymentHelper = $this->getMockBuilder(Payment::class)->disableOriginalConstructor()->getMock();
        $oPaymentHelper->method('getMolliePaymentModel')->willReturn($oPaymentModel);
        $oPaymentHelper->method('isMolliePaymentMethod')->willReturn(true);

        UtilsObject::setClassInstance(Payment::class, $oPaymentHelper);

        $oOrder = new \Mollie\Payment\extend\Application\Model\Order();
        $oOrder->oxorder__oxtrackcode = new Field("test123");
        $oOrder->oxorder__oxpaymenttype = new Field("molliecreditcard");
        $result = $oOrder->mollieMarkOrderAsShipped();

        $this->assertNull($result);

        UtilsObject::resetClassInstances();
        Payment::destroyInstance();
    }

    public function testMollieMarkOrderAsShippedNonMolliePayment()
    {
        $oOrder = new \Mollie\Payment\extend\Application\Model\Order();
        $oOrder->oxorder__oxpaymenttype = new Field("oxcreditcard");
        $result = $oOrder->mollieMarkOrderAsShipped();

        $this->assertNull($result);
    }

    public function testMollieMarkOrderAsShippedException()
    {
        Payment::destroyInstance();

        $oApiEndpoint = $this->getMockBuilder(\Mollie\Api\Endpoints\OrderEndpoint::class)->disableOriginalConstructor()->getMock();
        $oApiEndpoint->method('get')->willThrowException(new \Exception('Test-Exception'));

        $oPaymentModel = $this->getMockBuilder(Creditcard::class)->disableOriginalConstructor()->getMock();
        $oPaymentModel->method('getApiEndpoint')->willReturn($oApiEndpoint);

        $oPaymentHelper = $this->getMockBuilder(Payment::class)->disableOriginalConstructor()->getMock();
        $oPaymentHelper->method('getMolliePaymentModel')->willReturn($oPaymentModel);
        $oPaymentHelper->method('isMolliePaymentMethod')->willReturn(true);

        UtilsObject::setClassInstance(Payment::class, $oPaymentHelper);

        $oOrder = new \Mollie\Payment\extend\Application\Model\Order();
        $oOrder->oxorder__oxtrackcode = new Field("test123");
        $oOrder->oxorder__oxpaymenttype = new Field("molliecreditcard");
        $result = $oOrder->mollieMarkOrderAsShipped();

        $this->assertNull($result);

        UtilsObject::resetClassInstances();
        Payment::destroyInstance();
    }

    public function testMollieUpdateShippingTrackingCode()
    {
        Payment::destroyInstance();

        $oShipment = $this->getMockBuilder(\Mollie\Api\Resources\Shipment::class)->disableOriginalConstructor()->getMock();

        $oApiOrder = $this->getMockBuilder(\Mollie\Api\Resources\Order::class)->disableOriginalConstructor()->getMock();
        $oApiOrder->method('shipments')->willReturn([$oShipment]);

        $oApiEndpoint = $this->getMockBuilder(\Mollie\Api\Endpoints\OrderEndpoint::class)->disableOriginalConstructor()->getMock();
        $oApiEndpoint->method('get')->willReturn($oApiOrder);

        $oPaymentModel = $this->getMockBuilder(Creditcard::class)->disableOriginalConstructor()->getMock();
        $oPaymentModel->method('getApiEndpoint')->willReturn($oApiEndpoint);

        $oPaymentHelper = $this->getMockBuilder(Payment::class)->disableOriginalConstructor()->getMock();
        $oPaymentHelper->method('getMolliePaymentModel')->willReturn($oPaymentModel);

        UtilsObject::setClassInstance(Payment::class, $oPaymentHelper);

        $oOrder = new \Mollie\Payment\extend\Application\Model\Order();
        $result = $oOrder->mollieUpdateShippingTrackingCode("trackingCode");

        $this->assertNull($result);

        UtilsObject::resetClassInstances();
        Payment::destroyInstance();
    }

    public function testMollieUpdateShippingTrackingCodeException()
    {
        Payment::destroyInstance();

        $oApiEndpoint = $this->getMockBuilder(\Mollie\Api\Endpoints\OrderEndpoint::class)->disableOriginalConstructor()->getMock();
        $oApiEndpoint->method('get')->willThrowException(new \Exception('Test-Exception'));

        $oPaymentModel = $this->getMockBuilder(Creditcard::class)->disableOriginalConstructor()->getMock();
        $oPaymentModel->method('getApiEndpoint')->willReturn($oApiEndpoint);

        $oPaymentHelper = $this->getMockBuilder(Payment::class)->disableOriginalConstructor()->getMock();
        $oPaymentHelper->method('getMolliePaymentModel')->willReturn($oPaymentModel);

        UtilsObject::setClassInstance(Payment::class, $oPaymentHelper);

        $oOrder = new \Mollie\Payment\extend\Application\Model\Order();
        $result = $oOrder->mollieUpdateShippingTrackingCode("trackingCode");

        $this->assertNull($result);

        UtilsObject::resetClassInstances();
        Payment::destroyInstance();
    }

    public function testMollieIsMolliePaymentUsed()
    {
        $oOrder = new \Mollie\Payment\extend\Application\Model\Order();

        $this->assertFalse($oOrder->mollieIsMolliePaymentUsed());

        $oOrder->oxorder__oxpaymenttype = new Field('molliecreditcard');

        $this->assertTrue($oOrder->mollieIsMolliePaymentUsed());
    }

    public function testMollieUncancelOrder()
    {
        $oOrderarticle = $this->getMockBuilder(OrderArticle::class)->disableOriginalConstructor()->getMock();
        
        $oOrder = new \Mollie\Payment\extend\Application\Model\Order();
        $oOrder->oxorder__oxstorno = new Field(1);
        $oOrder->setOrderArticleList([$oOrderarticle]);
        $oOrder->mollieUncancelOrder();

        $this->assertEquals(0, $oOrder->oxorder__oxstorno->value);
    }

    public function testMollieIsPaid()
    {
        $oOrder = new \Mollie\Payment\extend\Application\Model\Order();

        $this->assertFalse($oOrder->mollieIsPaid());

        $oOrder->save();
        $oOrder->mollieMarkAsPaid();

        $this->assertTrue($oOrder->mollieIsPaid());
    }

    public function testMollieMarkAsSecondChanceMailSent()
    {
        $oOrder = new \Mollie\Payment\extend\Application\Model\Order();
        $oOrder->mollieMarkAsSecondChanceMailSent();

        $this->assertNotEmpty($oOrder->oxorder__molliesecondchancemailsent->value);
    }

    public function testMollieSetFolder()
    {
        $expected = "TestFolder";

        $oOrder = new \Mollie\Payment\extend\Application\Model\Order();
        $oOrder->mollieSetFolder($expected);

        $this->assertEquals($expected, $oOrder->oxorder__oxfolder->value);
    }

    public function testMollieIsReturnAfterPayment()
    {
        $oOrder = new \Mollie\Payment\extend\Application\Model\Order();
        $result = $oOrder->mollieIsReturnAfterPayment();

        $this->assertFalse($result);

        $oRequest = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock();
        $oRequest->method('getRequestEscapedParameter')->willReturn('handleMollieReturn');

        Registry::set(Request::class, $oRequest);

        $result = $oOrder->mollieIsReturnAfterPayment();

        $this->assertTrue($result);
    }

    public function testCheckOrderExist()
    {
        $oOrder = oxNew($this->getProxyClassName(\Mollie\Payment\extend\Application\Model\Order::class));
        $result = $oOrder->_checkOrderExist($oOrder->getId());

        $this->assertFalse($result);

        $oOrder->save();
        $result = $oOrder->_checkOrderExist($oOrder->getId());
        $this->assertTrue($result);

        $oOrder->setNonPublicVar('blMollieFinalizeReturnMode', true);
        $oOrder->setNonPublicVar('blMollieReinitializePaymentMode', true);

        $result = $oOrder->_checkOrderExist($oOrder->getId());

        $this->assertFalse($result);
    }

    public function testLoadFromBasket()
    {
        $expected = "test";

        $oBasket = Registry::getSession()->getBasket();

        $oSession = $this->getMockBuilder(Session::class)->disableOriginalConstructor()->getMock();
        $oSession->method('getVariable')->willReturn($expected);

        Registry::set(Session::class, $oSession);
        
        $oOrder = oxNew($this->getProxyClassName(\Mollie\Payment\extend\Application\Model\Order::class));
        $oOrder->_loadFromBasket($oBasket);

        $this->assertEquals($expected, $oOrder->oxorder__oxremark->value);

        $oOrder->setNonPublicVar('blMollieFinalizeReturnMode', true);

        $result = $oOrder->_loadFromBasket($oBasket);

        $this->assertNull($result);
    }

    public function testSetPayment()
    {
        $oPayment = $this->getMockBuilder(\OxidEsales\Eshop\Application\Model\Payment::class)->disableOriginalConstructor()->getMock();
        $oPayment->method('load')->willReturn(true);
        $oPayment->method('__get')->willReturn(new Field('test'));

        UtilsObject::setClassInstance(\OxidEsales\Eshop\Application\Model\Payment::class, $oPayment);

        $oOrder = oxNew($this->getProxyClassName(\Mollie\Payment\extend\Application\Model\Order::class));
        $oOrder->oxorder__oxpaymenttype = new Field('molliecreditcard');
        $oOrder->oxorder__oxuserid = new Field('test');
        $result = $oOrder->_setPayment('molliecreditcard');

        $this->assertInstanceOf(\OxidEsales\Eshop\Application\Model\UserPayment::class, $result);

        $oOrder->setNonPublicVar('blMollieFinalizeReturnMode', true);
        $result = $oOrder->_setPayment('molliecreditcard');

        $this->assertInstanceOf(\OxidEsales\Eshop\Application\Model\UserPayment::class, $result);
    }

    public function testExecutePayment()
    {
        $oBasket = Registry::getSession()->getBasket();

        $oUserpayment = $this->getMockBuilder(UserPayment::class)->disableOriginalConstructor()->getMock();

        $oPaymentGateway = $this->getMockBuilder(\OxidEsales\Eshop\Application\Model\PaymentGateway::class)->disableOriginalConstructor()->getMock();
        $oPaymentGateway->method('executePayment')->willReturn(true);

        UtilsObject::setClassInstance(\OxidEsales\Eshop\Application\Model\PaymentGateway::class, $oPaymentGateway);

        $oOrder = oxNew($this->getProxyClassName(\Mollie\Payment\extend\Application\Model\Order::class));
        $result = $oOrder->_executePayment($oBasket, $oUserpayment);

        $this->assertTrue($result);

        $oOrder->setNonPublicVar('blMollieFinalizeReturnMode', true);
        $oOrder->setNonPublicVar('blMollieReinitializePaymentMode', true);
        $oOrder->oxorder__oxordernr = new Field('test');

        $result = $oOrder->_executePayment($oBasket, $oUserpayment);

        $this->assertTrue($result);
        $this->assertEmpty($oOrder->oxorder__oxordernr->value);

        $result = $oOrder->_setNumber();

        $this->assertTrue($result);
        $this->assertEquals('test', $oOrder->oxorder__oxordernr->value);
    }

    public function testSetFolder()
    {
        $expected = "MolliePending";

        $oBasket = Registry::getSession()->getBasket();
        $oBasket->setPayment('OxidCorePayment');
        Registry::getSession()->setBasket($oBasket);

        $oOrder = oxNew($this->getProxyClassName(\Mollie\Payment\extend\Application\Model\Order::class));
        $oOrder->_setFolder();

        $this->assertNotEmpty($oOrder->oxorder__oxfolder->value);
        $this->assertNotEquals($expected, $oOrder->oxorder__oxfolder->value);

        $oBasket = Registry::getSession()->getBasket();
        $oBasket->setPayment('molliecreditcard');
        Registry::getSession()->setBasket($oBasket);

        $oConfig = $this->getMockBuilder(Config::class)->disableOriginalConstructor()->getMock();
        $oConfig->method('getShopConfVar')->willReturn($expected);

        Registry::set(Config::class, $oConfig);

        $oOrder->_setFolder();

        $this->assertEquals($expected, $oOrder->oxorder__oxfolder->value);
    }

    public function testSetOrderStatus()
    {
        $expected = "testStatus";

        $oOrder = oxNew($this->getProxyClassName(\Mollie\Payment\extend\Application\Model\Order::class));
        $oOrder->oxorder__oxpaymenttype = new Field('molliecreditcard');
        $oOrder->oxorder__oxtransstatus = new Field('NOT_FINISHED');
        $oOrder->save();
        $oOrder->setNonPublicVar('mollieRecalculateOrder', true);
        $oOrder->_setOrderStatus($expected);

        $this->assertNotEquals($expected, $oOrder->oxorder__oxtransstatus->value);

        $oOrder->setNonPublicVar('mollieRecalculateOrder', false);
        $oOrder->_setOrderStatus($expected);

        $this->assertEquals($expected, $oOrder->oxorder__oxtransstatus->value);
    }

    public function testValidateStock()
    {
        $oBasket = Registry::getSession()->getBasket();

        $oOrder = oxNew($this->getProxyClassName(\Mollie\Payment\extend\Application\Model\Order::class));
        $result = $oOrder->validateStock($oBasket);

        $this->assertNull($result);

        $oOrder->setNonPublicVar('blMollieFinalizeReturnMode', true);

        $result = $oOrder->validateStock($oBasket);

        $this->assertNull($result);
    }

    public function testValidateOrder()
    {
        $oBasket = Registry::getSession()->getBasket();

        $oUser = $oBasket->getUser();

        $oOrder = oxNew($this->getProxyClassName(\Mollie\Payment\extend\Application\Model\Order::class));
        $oOrder->oxorder__oxuserid = new Field('test');
        $result = $oOrder->validateOrder($oBasket, $oUser);

        $this->assertEquals(\Mollie\Payment\extend\Application\Model\Order::ORDER_STATE_INVALIDPAYMENT, $result);

        $oOrder->setNonPublicVar('blMollieFinishOrderReturnMode', true);

        $result = $oOrder->validateOrder($oBasket, $oUser);

        $this->assertNull($result);
    }

    public function testValidatePayment()
    {
        $oBasket = Registry::getSession()->getBasket();

        $oOrder = oxNew($this->getProxyClassName(\Mollie\Payment\extend\Application\Model\Order::class));
        $result = $oOrder->validatePayment($oBasket);

        $this->assertEquals(\Mollie\Payment\extend\Application\Model\Order::ORDER_STATE_INVALIDPAYMENT, $result);

        $oOrder->setNonPublicVar('blMollieReinitializePaymentMode', true);

        $result = $oOrder->validatePayment($oBasket);

        $this->assertNull($result);
    }

    public function testFinalizeOrder()
    {
        $oRequest = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock();
        $oRequest->method('getRequestEscapedParameter')->willReturn('handleMollieReturn');

        Registry::set(Request::class, $oRequest);

        $oBasket = Registry::getSession()->getBasket();
        $oBasket->setPayment('molliecreditcard');

        $oUser = $this->getMockBuilder(User::class)->disableOriginalConstructor()->getMock();
        $oUser->method('__get')->willReturn('test');

        $oOrder = oxNew($this->getProxyClassName(\Mollie\Payment\extend\Application\Model\Order::class));
        $oOrder->oxorder__oxpaymenttype = new Field("molliecreditcard");
        $return = $oOrder->finalizeOrder($oBasket, $oUser);

        $this->assertEquals($return, Order::ORDER_STATE_INVALIDPAYMENT);
    }

    public function testSetUser()
    {
        $expected = "test";

        $aContactInfo = [
            'emailAddress' => $expected,
            'givenName' => $expected,
            'familyName' => $expected,
            'locality' => $expected,
            'countryCode' => $expected,
            'postalCode' => $expected,
            'administrativeArea' => $expected,
            'addressLines' => ['Teststr. 14'],
        ];

        $oRequest = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock();
        $oRequest->method('getRequestEscapedParameter')->willReturn($aContactInfo);

        Registry::set(Request::class, $oRequest);

        $oUser = $this->getMockBuilder(User::class)->disableOriginalConstructor()->getMock();
        $oUser->method('__get')->willReturn(new Field('test'));

        $oOrder = oxNew($this->getProxyClassName(\Mollie\Payment\extend\Application\Model\Order::class));
        $oOrder->setNonPublicVar('blMollieIsApplePayButtonMode', true);
        $oOrder->_setUser($oUser);

        $this->assertEquals($expected, $oOrder->oxorder__oxdelzip->value);
    }

    public function testSetUserException()
    {
        $oUser = $this->getMockBuilder(User::class)->disableOriginalConstructor()->getMock();
        $oUser->method('__get')->willReturn('test');

        $oOrder = oxNew($this->getProxyClassName(\Mollie\Payment\extend\Application\Model\Order::class));
        $oOrder->setNonPublicVar('blMollieIsApplePayButtonMode', true);

        $this->expectException(\Exception::class);
        $oOrder->_setUser($oUser);
    }

    public function testValidateDeliveryAddress()
    {
        $oUser = $this->getMockBuilder(User::class)->disableOriginalConstructor()->getMock();
        $oUser->method('getEncodedDeliveryAddress')->willReturn('test');

        $oOrder = oxNew($this->getProxyClassName(\Mollie\Payment\extend\Application\Model\Order::class));
        $result = $oOrder->validateDeliveryAddress($oUser);

        $this->assertEquals(\Mollie\Payment\extend\Application\Model\Order::ORDER_STATE_INVALIDDELADDRESSCHANGED, $result);

        $oOrder->setNonPublicVar('blMollieIsApplePayButtonMode', true);
        $oOrder->setNonPublicVar('blMollieReinitializePaymentMode', true);

        $result = $oOrder->validateDeliveryAddress($oUser);

        $this->assertEquals(0, $result);
    }

    public function testCancelOrder()
    {
        Payment::destroyInstance();

        $expected = 'mollieCancelFolder';

        $oConfig = $this->getMockBuilder(Config::class)->disableOriginalConstructor()->getMock();
        $oConfig->method('getShopConfVar')->willReturn($expected);

        Registry::set(Config::class, $oConfig);

        $oApiOrder = $this->getMockBuilder(\Mollie\Api\Resources\Order::class)->disableOriginalConstructor()->getMock();
        $oApiOrder->method('cancel')->willReturn(true);
        $oApiOrder->isCancelable = true;

        $oApiEndpoint = $this->getMockBuilder(\Mollie\Api\Endpoints\OrderEndpoint::class)->disableOriginalConstructor()->getMock();
        $oApiEndpoint->method('get')->willReturn($oApiOrder);

        $oPaymentModel = $this->getMockBuilder(Creditcard::class)->disableOriginalConstructor()->getMock();
        $oPaymentModel->method('getApiEndpoint')->willReturn($oApiEndpoint);

        $oPaymentHelper = $this->getMockBuilder(Payment::class)->disableOriginalConstructor()->getMock();
        $oPaymentHelper->method('getMolliePaymentModel')->willReturn($oPaymentModel);
        $oPaymentHelper->method('isMolliePaymentMethod')->willReturn(true);

        UtilsObject::setClassInstance(Payment::class, $oPaymentHelper);

        $oOrder = new \Mollie\Payment\extend\Application\Model\Order();
        $oOrder->oxorder__oxpaymenttype = new Field('molliecreditcard');
        $oOrder->cancelOrder();

        $this->assertEquals($expected, $oOrder->oxorder__oxfolder->value);

        Payment::destroyInstance();
    }

    public function testMollieGetPaymentFinishUrl()
    {
        $oOrder = new \Mollie\Payment\extend\Application\Model\Order();
        $result = $oOrder->mollieGetPaymentFinishUrl();

        $this->assertContains("mollieFinishPayment", $result);
    }

    public function testMollieIsOrderInUnfinishedState()
    {
        $oOrder = new \Mollie\Payment\extend\Application\Model\Order();
        $result = $oOrder->mollieIsOrderInUnfinishedState();

        $this->assertFalse($result);

        $folder = "mollieTestFolder";

        $oConfig = $this->getMockBuilder(Config::class)->disableOriginalConstructor()->getMock();
        $oConfig->method('getShopConfVar')->willReturn($folder);

        Registry::set(Config::class, $oConfig);

        $oOrder->oxorder__oxtransstatus->value = "NOT_FINISHED";
        $oOrder->oxorder__oxfolder->value = $folder;

        $result = $oOrder->mollieIsOrderInUnfinishedState();

        $this->assertTrue($result);
    }

    public function testMollieRecreateBasket()
    {
        $oOrderarticle = $this->getMockBuilder(OrderArticle::class)->disableOriginalConstructor()->getMock();

        $oOrder = new \Mollie\Payment\extend\Application\Model\Order();
        $oOrder->save();
        $oOrder->setOrderArticleList([$oOrderarticle]);
        $result = $oOrder->mollieRecreateBasket();

        $this->assertInstanceOf(Basket::class, $result);
    }

    public function testMollieIsEligibleForPaymentFinish()
    {
        Payment::destroyInstance();

        $aResult = ['status' => 'open'];

        $oTransactionHandler = $this->getMockBuilder(\Mollie\Payment\Application\Model\TransactionHandler\Payment::class)->disableOriginalConstructor()->getMock();
        $oTransactionHandler->method('processTransaction')->willReturn($aResult);

        $oPaymentModel = $this->getMockBuilder(Creditcard::class)->disableOriginalConstructor()->getMock();
        $oPaymentModel->method('getTransactionHandler')->willReturn($oTransactionHandler);

        $oPaymentHelper = $this->getMockBuilder(Payment::class)->disableOriginalConstructor()->getMock();
        $oPaymentHelper->method('getMolliePaymentModel')->willReturn($oPaymentModel);
        $oPaymentHelper->method('isMolliePaymentMethod')->willReturn(true);

        UtilsObject::setClassInstance(Payment::class, $oPaymentHelper);

        $oOrder = new \Mollie\Payment\extend\Application\Model\Order();
        $oOrder->oxorder__oxpaymenttype = new Field('molliecreditcard');
        $oOrder->oxorder__oxpaid = new Field('0000-00-00 00:00:00');
        $oOrder->oxorder__oxtransstatus = new Field('NOT_FINISHED');
        $result = $oOrder->mollieIsEligibleForPaymentFinish(true);

        $this->assertTrue($result);

        UtilsObject::resetClassInstances();
        Payment::destroyInstance();
    }

    public function testMollieIsEligibleForPaymentFinishFalse()
    {
        Payment::destroyInstance();

        $aResult = ['status' => 'paid'];

        $oTransactionHandler = $this->getMockBuilder(\Mollie\Payment\Application\Model\TransactionHandler\Payment::class)->disableOriginalConstructor()->getMock();
        $oTransactionHandler->method('processTransaction')->willReturn($aResult);

        $oPaymentModel = $this->getMockBuilder(Creditcard::class)->disableOriginalConstructor()->getMock();
        $oPaymentModel->method('getTransactionHandler')->willReturn($oTransactionHandler);

        $oPaymentHelper = $this->getMockBuilder(Payment::class)->disableOriginalConstructor()->getMock();
        $oPaymentHelper->method('getMolliePaymentModel')->willReturn($oPaymentModel);
        $oPaymentHelper->method('isMolliePaymentMethod')->willReturn(true);

        UtilsObject::setClassInstance(Payment::class, $oPaymentHelper);

        $oOrder = new \Mollie\Payment\extend\Application\Model\Order();
        $oOrder->oxorder__oxpaymenttype = new Field('molliecreditcard');
        $oOrder->oxorder__oxpaid = new Field('0000-00-00 00:00:00');
        $oOrder->oxorder__oxtransstatus = new Field('NOT_FINISHED');
        $result = $oOrder->mollieIsEligibleForPaymentFinish(true);

        $this->assertFalse($result);

        UtilsObject::resetClassInstances();
        Payment::destroyInstance();
    }

    public function testMollieIsEligibleForPaymentFinishFalseNoMollie()
    {
        $oOrder = new \Mollie\Payment\extend\Application\Model\Order();
        $oOrder->oxorder__oxpaymenttype = new Field('oxempty');
        $result = $oOrder->mollieIsEligibleForPaymentFinish();

        $this->assertFalse($result);
    }

    public function testMollieSendSecondChanceEmail()
    {
        $oEmail = $this->getMockBuilder(\OxidEsales\Eshop\Core\Email::class)->disableOriginalConstructor()->getMock();

        UtilsObject::setClassInstance(\OxidEsales\Eshop\Core\Email::class, $oEmail);

        $oOrder = new \Mollie\Payment\extend\Application\Model\Order();
        $result = $oOrder->mollieSendSecondChanceEmail();

        $this->assertNull($result);
    }

    public function testMollieFinishOrder()
    {
        $oEmail = $this->getMockBuilder(Email::class)->disableOriginalConstructor()->getMock();
        $oEmail->method('sendOrderEMailToUser')->willReturn(true);

        UtilsObject::setClassInstance(Email::class, $oEmail);
        
        $oUser = $this->getMockBuilder(User::class)->disableOriginalConstructor()->getMock();
        $oUser->method('__get')->willReturn(new Field('Test'));
        
        $oOrderarticle = $this->getMockBuilder(OrderArticle::class)->disableOriginalConstructor()->getMock();

        $oOrder = oxNew($this->getProxyClassName(\Mollie\Payment\extend\Application\Model\Order::class));
        $oOrder->save();
        $oOrder->setNonPublicVar('_oUser', $oUser);
        $oOrder->setOrderArticleList([$oOrderarticle]);

        $result = $oOrder->mollieFinishOrder();

        $this->assertEquals(Order::ORDER_STATE_OK, $result);
    }

    public function testMollieReinitializePayment()
    {
        $oEmail = $this->getMockBuilder(Email::class)->disableOriginalConstructor()->getMock();
        $oEmail->method('sendOrderEMailToUser')->willReturn(true);

        UtilsObject::setClassInstance(Email::class, $oEmail);

        $oUser = $this->getMockBuilder(User::class)->disableOriginalConstructor()->getMock();
        $oUser->method('__get')->willReturn(new Field('Test'));

        UtilsObject::setClassInstance(User::class, $oUser);

        $oOrderarticle = $this->getMockBuilder(OrderArticle::class)->disableOriginalConstructor()->getMock();

        $oOrder = new \Mollie\Payment\extend\Application\Model\Order();
        $oOrder->oxorder__oxstorno = new Field(1);
        $oOrder->oxorder__oxuserid = new Field('userId');
        $oOrder->save();
        $oOrder->setOrderArticleList([$oOrderarticle]);
        $result = $oOrder->mollieReinitializePayment();

        $this->assertEquals(Order::ORDER_STATE_OK, $result);
    }

    public function testMollieLoadOrderByTransactionId()
    {
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute("REPLACE INTO oxorder (OXID, OXTRANSID) VALUE ('webhookTest', 'testTransId')");

        $oOrder = new \Mollie\Payment\extend\Application\Model\Order();
        $result = $oOrder->mollieLoadOrderByTransactionId('testTransId');

        $this->assertTrue($result);

        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute('DELETE FROM oxorder WHERE oxid = "webhookTest"');
    }

    public function testMollieGetPaymentTransactionIdPayment()
    {
        Payment::destroyInstance();

        $expected = 'tr_success';

        $oPaymentTransaction = new \stdClass();
        $oPaymentTransaction->id = $expected;

        $oApiOrder = $this->getMockBuilder(\Mollie\Api\Resources\Order::class)->disableOriginalConstructor()->getMock();
        $oApiOrder->_embedded = new \stdClass();
        $oApiOrder->_embedded->payments = [$oPaymentTransaction];

        $oApiEndpoint = $this->getMockBuilder(\Mollie\Api\Endpoints\OrderEndpoint::class)->disableOriginalConstructor()->getMock();
        $oApiEndpoint->method('get')->willReturn($oApiOrder);

        $oPaymentModel = $this->getMockBuilder(Creditcard::class)->disableOriginalConstructor()->getMock();
        $oPaymentModel->method('getApiEndpoint')->willReturn($oApiEndpoint);

        $oPaymentHelper = $this->getMockBuilder(Payment::class)->disableOriginalConstructor()->getMock();
        $oPaymentHelper->method('getMolliePaymentModel')->willReturn($oPaymentModel);

        UtilsObject::setClassInstance(Payment::class, $oPaymentHelper);

        $oOrder = new \Mollie\Payment\extend\Application\Model\Order();
        $oOrder->oxorder__oxtransid = new Field('ord_test');
        $result = $oOrder->mollieGetPaymentTransactionId();

        $this->assertEquals($expected, $result);

        UtilsObject::resetClassInstances();
        Payment::destroyInstance();
    }

    public function testMollieGetPaymentTransactionIdPaymentFalse()
    {
        Payment::destroyInstance();

        $oApiOrder = $this->getMockBuilder(\Mollie\Api\Resources\Payment::class)->disableOriginalConstructor()->getMock();

        $oApiEndpoint = $this->getMockBuilder(\Mollie\Api\Endpoints\OrderEndpoint::class)->disableOriginalConstructor()->getMock();
        $oApiEndpoint->method('get')->willReturn($oApiOrder);

        $oPaymentModel = $this->getMockBuilder(Creditcard::class)->disableOriginalConstructor()->getMock();
        $oPaymentModel->method('getApiEndpoint')->willReturn($oApiEndpoint);

        $oPaymentHelper = $this->getMockBuilder(Payment::class)->disableOriginalConstructor()->getMock();
        $oPaymentHelper->method('getMolliePaymentModel')->willReturn($oPaymentModel);

        UtilsObject::setClassInstance(Payment::class, $oPaymentHelper);

        $oOrder = new \Mollie\Payment\extend\Application\Model\Order();
        $oOrder->oxorder__oxtransid = new Field('ord_test');
        $result = $oOrder->mollieGetPaymentTransactionId();

        $this->assertFalse($result);

        UtilsObject::resetClassInstances();
        Payment::destroyInstance();
    }

    public function testMollieGetPaymentTransactionIdPaymentApi()
    {
        $expected = 'tr_test';

        $oOrder = new \Mollie\Payment\extend\Application\Model\Order();
        $oOrder->oxorder__oxtransid = new Field($expected);
        $result = $oOrder->mollieGetPaymentTransactionId();

        $this->assertEquals($expected, $result);
    }
}
