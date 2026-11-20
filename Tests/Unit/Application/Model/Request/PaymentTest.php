<?php

namespace Mollie\Payment\Tests\Unit\Application\Model\Request;

use Mollie\Api\Endpoints\PaymentEndpoint;
use Mollie\Payment\Application\Model\Payment\Creditcard;
use Mollie\Payment\Application\Model\Request\Payment;
use OxidEsales\Eshop\Application\Model\Country;
use OxidEsales\Eshop\Application\Model\DeliverySet;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Application\Model\State;
use OxidEsales\Eshop\Application\Model\UserPayment;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Price;
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
        $oPaymentModel->method('getPaymentSpecificParameters')->willReturn(['foo' => 'bar']);
        $oPaymentModel->method('getApiEndpoint')->willReturn($oApiEndpoint);
        $oPaymentModel->method('getApiEndpointByOrder')->willReturn($oApiEndpoint);

        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $oOrder->method('getId')->willReturn('testOrder');
        $oOrder->method('getShopId')->willReturn(1);
        $oOrder->method('mollieGetPaymentModel')->willReturn($oPaymentModel);
        $oOrder->method('__get')->willReturnCallback([$this, 'getOrderFieldValue']);
        $oOrder->method('getOrderArticles')->willReturn($this->getEmptyOrderArticleList());

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
        $oPaymentModel->method('getPaymentSpecificParameters')->willReturn(['foo' => 'bar']);
        $oPaymentModel->method('getApiEndpoint')->willReturn($oApiEndpoint);
        $oPaymentModel->method('getApiEndpointByOrder')->willReturn($oApiEndpoint);

        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $oOrder->method('getId')->willReturn('testOrder');
        $oOrder->method('getShopId')->willReturn(1);
        $oOrder->method('mollieGetPaymentModel')->willReturn($oPaymentModel);
        $oOrder->method('__get')->willReturnCallback([$this, 'getOrderFieldValue']);
        $oOrder->method('getOrderArticles')->willReturn($this->getEmptyOrderArticleList());

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
        $oPaymentModel->method('getPaymentSpecificParameters')->willReturn(['foo' => 'bar']);
        $oPaymentModel->method('getApiEndpoint')->willReturn($oApiEndpoint);
        $oPaymentModel->method('getApiEndpointByOrder')->willReturn($oApiEndpoint);

        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $oOrder->method('getId')->willReturn('testOrder');
        $oOrder->method('getShopId')->willReturn(1);
        $oOrder->method('mollieGetPaymentModel')->willReturn($oPaymentModel);
        $oOrder->method('__get')->willReturnCallback([$this, 'getOrderFieldValue']);
        $oOrder->method('getOrderArticles')->willReturn($this->getEmptyOrderArticleList());

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
        $oPaymentModel->method('getPaymentSpecificParameters')->willReturn(['foo' => 'bar']);
        $oPaymentModel->method('getApiEndpoint')->willReturn($oApiEndpoint);
        $oPaymentModel->method('getApiEndpointByOrder')->willReturn($oApiEndpoint);

        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $oOrder->method('getId')->willReturn('testOrder');
        $oOrder->method('getShopId')->willReturn(1);
        $oOrder->method('mollieGetPaymentModel')->willReturn($oPaymentModel);
        $oOrder->method('__get')->willReturnCallback([$this, 'getOrderFieldValue']);
        $oOrder->method('getOrderArticles')->willReturn($this->getEmptyOrderArticleList());

        $oRequest = new Payment();

        $this->expectException(\Mollie\Api\Exceptions\ApiException::class);
        $result = $oRequest->sendRequest($oOrder, 50, "http://someurl.com");
    }

    public function testGetBasketItemsWithProportionalVatOfAdditionalCosts()
    {
        $oDelSet = oxNew(DeliverySet::class);
        $oDelSet->oxdeliveryset__oxtitle = new Field('Standard');

        $oUserPayment = oxNew(UserPayment::class);
        $oUserPayment->oxpayments__oxdesc = new Field('Creditcard');

        $oWrappingPrice = oxNew(Price::class);
        $oWrappingPrice->setBruttoPriceMode();
        $oWrappingPrice->setPrice(3.00, 18.951612903226);

        $aOrderFields = [
            'oxorder__oxcurrency' => 'EUR',
            'oxorder__oxdeltype' => 'oxidstandard',
            'oxorder__oxpaymenttype' => 'molliecreditcard',
            'oxorder__oxdelcost' => '5.95',
            'oxorder__oxdelvat' => '15.384615384615',
            'oxorder__oxpaycost' => '2.5',
            'oxorder__oxpayvat' => '15.384615384615',
            'oxorder__oxwrapcost' => '3',
            'oxorder__oxwrapvat' => '18.951612903226',
        ];

        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $oOrder->method('getOrderArticles')->willReturn($this->getEmptyOrderArticleList());
        $oOrder->method('getDelSet')->willReturn($oDelSet);
        $oOrder->method('getPaymentType')->willReturn($oUserPayment);
        $oOrder->method('getOrderWrappingPrice')->willReturn($oWrappingPrice);
        $oOrder->method('__get')->willReturnCallback(function ($sName) use ($aOrderFields) {
            if (isset($aOrderFields[$sName])) {
                return new Field($aOrderFields[$sName]);
            }
            return new Field('0');
        });

        $oRequest = new Payment();
        $aItems = $oRequest->getBasketItems($oOrder, true);

        $this->assertCount(3, $aItems);

        $this->assertSame('shipping_fee', $aItems[0]['type']);
        $this->assertSame(15.38, $aItems[0]['vatRate']);
        $this->assertSame('0.79', $aItems[0]['vatAmount']['value']);

        $this->assertSame('molliecreditcard', $aItems[1]['sku']);
        $this->assertSame(15.38, $aItems[1]['vatRate']);
        $this->assertSame('0.33', $aItems[1]['vatAmount']['value']);

        $this->assertSame('wrapping', $aItems[2]['sku']);
        $this->assertSame(18.95, $aItems[2]['vatRate']);
        $this->assertSame('0.48', $aItems[2]['vatAmount']['value']);
    }

    /**
     * Returns numeric values for price fields and text for all other order fields
     *
     * @param  string $sName
     * @return Field
     */
    public function getOrderFieldValue($sName)
    {
        if (preg_match('/(cost|sum|discount|vat|price)$/', $sName)) {
            return new Field('0');
        }
        return new Field('test');
    }

    /**
     * @return \OxidEsales\Eshop\Core\Model\ListModel
     */
    protected function getEmptyOrderArticleList()
    {
        $oOrderArticles = $this->getMockBuilder(\OxidEsales\Eshop\Core\Model\ListModel::class)->disableOriginalConstructor()->getMock();
        $oOrderArticles->method('getArray')->willReturn([]);
        return $oOrderArticles;
    }
}
