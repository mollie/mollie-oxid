<?php


namespace Mollie\Payment\Tests\Unit\extend\Application\Model;


use Mollie\Payment\Application\Model\Payment\Creditcard;
use Mollie\Payment\Application\Model\Request\Payment;
use Mollie\Api\Resources\Payment as ApiPayment;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use OxidEsales\TestingLibrary\UnitTestCase;

class PaymentGatewayTest extends UnitTestCase
{
    public function testExecutePayment()
    {
        $oRequest = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock();
        $oRequest->method('getRequestEscapedParameter')->willReturn("value");

        Registry::set(Request::class, $oRequest);

        $oConfig = $this->getMockBuilder(Config::class)->disableOriginalConstructor()->getMock();
        $oConfig->method('getCurrentShopUrl')->willReturn('http://someurl.com');

        Registry::set(Config::class, $oConfig);

        $oUtils = $this->getMock(\OxidEsales\Eshop\Core\Utils::class, array('redirect'));
        $oUtils->method('redirect')->willReturn(null);
        Registry::set(\OxidEsales\Eshop\Core\Utils::class, $oUtils);

        $oSession = $this->getMock(\OxidEsales\Eshop\Core\Utils::class, array('sid', 'getSessionChallengeToken', 'getRemoteAccessToken', 'setVariable'));
        $oSession->method('sid')->willReturn('test');
        $oSession->method('getSessionChallengeToken')->willReturn('test');
        $oSession->method('getRemoteAccessToken')->willReturn('test');
        $oSession->method('setVariable')->willReturn(null);
        Registry::set(\OxidEsales\Eshop\Core\Session::class, $oSession);

        $oResponse = $this->getMock(ApiPayment::class, array('getCheckoutUrl'), array(), '', false);
        $oResponse->method('getCheckoutUrl')->willReturn("http://www.mollie.com");

        $oRequestModel = $this->getMock(Payment::class, array('sendRequest'), array(), '', false);
        $oRequestModel->method('sendRequest')->willReturn($oResponse);

        $oCreditcard = $this->getMock(Creditcard::class, array('getApiRequestModel'), array(), '', false);
        $oCreditcard->method('getApiRequestModel')->willReturn($oRequestModel);

        $oOrder = $this->getMock(Order::class, array('mollieGetPaymentModel'), array(), '', false);
        $oOrder->oxorder__oxpaymenttype = new \OxidEsales\Eshop\Core\Field("molliecreditcard");
        $oOrder->method('mollieGetPaymentModel')->willReturn($oCreditcard);

        $oPaymentGateway = new \Mollie\Payment\extend\Application\Model\PaymentGateway();
        $result = $oPaymentGateway->executePayment(50, $oOrder);

        $this->assertTrue($result);
    }

    public function testExecutePaymentException()
    {
        $oExc = new \Mollie\Api\Exceptions\ApiException("TestError", 404);

        $oRequestModel = $this->getMock(Payment::class, array('sendRequest'), array(), '', false);
        $oRequestModel->method('sendRequest')->willThrowException($oExc);

        $oCreditcard = $this->getMock(Creditcard::class, array('getApiRequestModel'), array(), '', false);
        $oCreditcard->method('getApiRequestModel')->willReturn($oRequestModel);

        $oOrder = $this->getMock(Order::class, array('mollieGetPaymentModel'), array(), '', false);
        $oOrder->oxorder__oxpaymenttype = new \OxidEsales\Eshop\Core\Field("molliecreditcard");
        $oOrder->method('mollieGetPaymentModel')->willReturn($oCreditcard);

        $oPaymentGateway = new \Mollie\Payment\extend\Application\Model\PaymentGateway();
        $result = $oPaymentGateway->executePayment(50, $oOrder);

        $this->assertFalse($result);
    }

    public function testExecutePaymentNotMollie()
    {
        $oOrder = oxNew(Order::class);
        $oOrder->oxorder__oxpaymenttype = new \OxidEsales\Eshop\Core\Field("oxidpaypal");

        $oPaymentGateway = new \Mollie\Payment\extend\Application\Model\PaymentGateway();
        $result = $oPaymentGateway->executePayment(50, $oOrder);

        $this->assertTrue($result);
    }
}