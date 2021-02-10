<?php

namespace Mollie\Payment\Tests\Unit\Application\Model\Request;

use Mollie\Api\Endpoints\PaymentEndpoint;
use Mollie\Payment\Application\Model\Payment\Creditcard;
use Mollie\Payment\Application\Model\Request\Payment;
use OxidEsales\Eshop\Application\Model\Country;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Application\Model\State;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\UtilsObject;

class PaymentTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    public function testSendRequest()
    {
        $oCountry = $this->getMockBuilder(Country::class)->disableOriginalConstructor()->getMock();
        $oCountry->method('__get')->willReturn(new Field('NL'));

        UtilsObject::setClassInstance(Country::class, $oCountry);

        $oState = $this->getMockBuilder(State::class)->disableOriginalConstructor()->getMock();
        $oState->method('getTitleById')->willReturn('Bayern');

        UtilsObject::setClassInstance(State::class, $oState);

        $oApiPayment = $this->getMockBuilder(\Mollie\Api\Resources\Payment::class)->disableOriginalConstructor()->getMock();

        $oApiEndpoint = $this->getMockBuilder(PaymentEndpoint::class)->disableOriginalConstructor()->getMock();
        $oApiEndpoint->method('create')->willReturn($oApiPayment);

        $oPaymentModel = $this->getMockBuilder(Creditcard::class)->disableOriginalConstructor()->getMock();
        $oPaymentModel->method('getMolliePaymentCode')->willReturn('creditcard');
        $oPaymentModel->method('isRedirectUrlNeeded')->willReturn(true);
        $oPaymentModel->method('getApiMethod')->willReturn('order');
        $oPaymentModel->method('getPaymentSpecificParameters')->willReturn(['foo' => 'bar']);
        $oPaymentModel->method('getApiEndpoint')->willReturn($oApiEndpoint);

        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $oOrder->method('getId')->willReturn('testOrder');
        $oOrder->method('getShopId')->willReturn(1);
        $oOrder->method('mollieGetPaymentModel')->willReturn($oPaymentModel);
        $oOrder->method('__get')->willReturn(new Field('test'));

        $oRequest = new Payment();
        $result = $oRequest->sendRequest($oOrder, 50, "http://someurl.com");

        $this->assertInstanceOf(\Mollie\Api\Resources\Payment::class, $result);
    }

    public function testSendRequestApiException()
    {
        $oCountry = $this->getMockBuilder(Country::class)->disableOriginalConstructor()->getMock();
        $oCountry->method('__get')->willReturn(new Field('NL'));

        UtilsObject::setClassInstance(Country::class, $oCountry);

        $oState = $this->getMockBuilder(State::class)->disableOriginalConstructor()->getMock();
        $oState->method('getTitleById')->willReturn('Bayern');

        UtilsObject::setClassInstance(State::class, $oState);

        $oApiPayment = $this->getMockBuilder(\Mollie\Api\Resources\Payment::class)->disableOriginalConstructor()->getMock();

        $oApiEndpoint = $this->getMockBuilder(PaymentEndpoint::class)->disableOriginalConstructor()->getMock();
        $oApiEndpoint->method('create')->willThrowException(new \Mollie\Api\Exceptions\ApiException('Test-API-Exception'));

        $oPaymentModel = $this->getMockBuilder(Creditcard::class)->disableOriginalConstructor()->getMock();
        $oPaymentModel->method('getMolliePaymentCode')->willReturn('creditcard');
        $oPaymentModel->method('isRedirectUrlNeeded')->willReturn(true);
        $oPaymentModel->method('getApiMethod')->willReturn('order');
        $oPaymentModel->method('getPaymentSpecificParameters')->willReturn(['foo' => 'bar']);
        $oPaymentModel->method('getApiEndpoint')->willReturn($oApiEndpoint);

        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $oOrder->method('getId')->willReturn('testOrder');
        $oOrder->method('getShopId')->willReturn(1);
        $oOrder->method('mollieGetPaymentModel')->willReturn($oPaymentModel);
        $oOrder->method('__get')->willReturn(new Field('test'));

        $oRequest = new Payment();

        $this->expectException(\Mollie\Api\Exceptions\ApiException::class);
        $result = $oRequest->sendRequest($oOrder, 50, "http://someurl.com");
    }

    public function testSendRequestDetailsFailure()
    {
        $oCountry = $this->getMockBuilder(Country::class)->disableOriginalConstructor()->getMock();
        $oCountry->method('__get')->willReturn(new Field('NL'));

        UtilsObject::setClassInstance(Country::class, $oCountry);

        $oState = $this->getMockBuilder(State::class)->disableOriginalConstructor()->getMock();
        $oState->method('getTitleById')->willReturn('Bayern');

        UtilsObject::setClassInstance(State::class, $oState);

        $oDetails = new \stdClass();
        $oDetails->failureMessage = "Test-Exception";

        $oApiPayment = $this->getMockBuilder(\Mollie\Api\Resources\Payment::class)->disableOriginalConstructor()->getMock();
        $oApiPayment->details = $oDetails;

        $oApiEndpoint = $this->getMockBuilder(PaymentEndpoint::class)->disableOriginalConstructor()->getMock();
        $oApiEndpoint->method('create')->willReturn($oApiPayment);

        $oPaymentModel = $this->getMockBuilder(Creditcard::class)->disableOriginalConstructor()->getMock();
        $oPaymentModel->method('getMolliePaymentCode')->willReturn('creditcard');
        $oPaymentModel->method('isRedirectUrlNeeded')->willReturn(true);
        $oPaymentModel->method('getApiMethod')->willReturn('order');
        $oPaymentModel->method('getPaymentSpecificParameters')->willReturn(['foo' => 'bar']);
        $oPaymentModel->method('getApiEndpoint')->willReturn($oApiEndpoint);

        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $oOrder->method('getId')->willReturn('testOrder');
        $oOrder->method('getShopId')->willReturn(1);
        $oOrder->method('mollieGetPaymentModel')->willReturn($oPaymentModel);
        $oOrder->method('__get')->willReturn(new Field('test'));

        $oRequest = new Payment();

        $this->expectException(\Mollie\Api\Exceptions\ApiException::class);
        $result = $oRequest->sendRequest($oOrder, 50, "http://someurl.com");
    }

    public function testSendRequestExtraFailure()
    {
        $oCountry = $this->getMockBuilder(Country::class)->disableOriginalConstructor()->getMock();
        $oCountry->method('__get')->willReturn(new Field('NL'));

        UtilsObject::setClassInstance(Country::class, $oCountry);

        $oState = $this->getMockBuilder(State::class)->disableOriginalConstructor()->getMock();
        $oState->method('getTitleById')->willReturn('Bayern');

        UtilsObject::setClassInstance(State::class, $oState);

        $oExtra = new \stdClass();
        $oExtra->failureMessage = "Test-Exception";

        $oApiPayment = $this->getMockBuilder(\Mollie\Api\Resources\Payment::class)->disableOriginalConstructor()->getMock();
        $oApiPayment->extra = $oExtra;

        $oApiEndpoint = $this->getMockBuilder(PaymentEndpoint::class)->disableOriginalConstructor()->getMock();
        $oApiEndpoint->method('create')->willReturn($oApiPayment);

        $oPaymentModel = $this->getMockBuilder(Creditcard::class)->disableOriginalConstructor()->getMock();
        $oPaymentModel->method('getMolliePaymentCode')->willReturn('creditcard');
        $oPaymentModel->method('isRedirectUrlNeeded')->willReturn(true);
        $oPaymentModel->method('getApiMethod')->willReturn('order');
        $oPaymentModel->method('getPaymentSpecificParameters')->willReturn(['foo' => 'bar']);
        $oPaymentModel->method('getApiEndpoint')->willReturn($oApiEndpoint);

        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $oOrder->method('getId')->willReturn('testOrder');
        $oOrder->method('getShopId')->willReturn(1);
        $oOrder->method('mollieGetPaymentModel')->willReturn($oPaymentModel);
        $oOrder->method('__get')->willReturn(new Field('test'));

        $oRequest = new Payment();

        $this->expectException(\Mollie\Api\Exceptions\ApiException::class);
        $result = $oRequest->sendRequest($oOrder, 50, "http://someurl.com");
    }
}