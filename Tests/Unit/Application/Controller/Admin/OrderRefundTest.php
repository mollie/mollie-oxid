<?php

namespace Mollie\Payment\Tests\Unit\Application\Controller\Admin;

use Mollie\Api\Endpoints\PaymentEndpoint;
use Mollie\Payment\Application\Controller\Admin\OrderRefund;
use Mollie\Payment\Application\Model\Payment\Creditcard;
use Mollie\Payment\Application\Model\RequestLog;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Application\Model\OrderArticle;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\UtilsObject;
use OxidEsales\TestingLibrary\UnitTestCase;

class OrderRefundTest extends UnitTestCase
{
    public function testGetOrder()
    {
        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();

        UtilsObject::setClassInstance(Order::class, $oOrder);

        $expected = $oOrder;

        $oController = new OrderRefund();
        $oController->setEditObjectId('test');
        $result = $oController->getOrder();

        $this->assertEquals($expected, $result);
    }

    public function testWasRefundSuccessful()
    {
        $oController = new OrderRefund();
        $result = $oController->wasRefundSuccessful();

        $this->assertNull($result);
    }

    public function testGetErrorMessage()
    {
        $expected = 'test';

        $oController = new OrderRefund();
        $oController->setErrorMessage($expected);
        $result = $oController->getErrorMessage();

        $this->assertEquals($expected, $result);
    }

    public function testRender()
    {
        $expected = "mollie_order_refund.tpl";

        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();

        UtilsObject::setClassInstance(Order::class, $oOrder);

        $oController = new OrderRefund();
        $oController->setEditObjectId('test');
        $result = $oController->render();

        $this->assertEquals($expected, $result);
    }

    public function testFormatPrice()
    {
        $expected = "20.00";

        $oController = oxNew($this->getProxyClassName(OrderRefund::class));
        $result = $oController->formatPrice(20);

        $this->assertEquals($expected, $result);
    }

    public function testIsMollieOrderApi()
    {
        $oApiOrder = $this->getMockBuilder(\Mollie\Api\Resources\Order::class)->disableOriginalConstructor()->getMock();
        
        $oApiEndpoint = $this->getMockBuilder(PaymentEndpoint::class)->disableOriginalConstructor()->getMock();
        $oApiEndpoint->method('get')->willReturn($oApiOrder);
        
        $oPaymentModel = $this->getMockBuilder(Creditcard::class)->disableOriginalConstructor()->getMock();
        $oPaymentModel->method('getApiEndpoint')->willReturn($oApiEndpoint);
        
        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $oOrder->method('__get')->willReturn(null);
        $oOrder->method('mollieGetPaymentModel')->willReturn($oPaymentModel);

        UtilsObject::setClassInstance(Order::class, $oOrder);

        $oController = new OrderRefund();
        $oController->setEditObjectId('test');
        $result = $oController->isMollieOrderApi();

        $this->assertTrue($result);
    }

    public function testIsMollieOrderApiFalse()
    {
        $oApiEndpoint = $this->getMockBuilder(PaymentEndpoint::class)->disableOriginalConstructor()->getMock();
        $oApiEndpoint->method('get')->willReturn(null);

        $oPaymentModel = $this->getMockBuilder(Creditcard::class)->disableOriginalConstructor()->getMock();
        $oPaymentModel->method('getApiEndpoint')->willReturn($oApiEndpoint);

        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $oOrder->method('__get')->willReturn(null);
        $oOrder->method('mollieGetPaymentModel')->willReturn($oPaymentModel);

        UtilsObject::setClassInstance(Order::class, $oOrder);

        $oController = new OrderRefund();
        $oController->setEditObjectId('test');
        $result = $oController->isMollieOrderApi();

        $this->assertFalse($result);
    }

    public function testGetRefundType()
    {
        $expected = "quantity";

        $oApiOrder = $this->getMockBuilder(\Mollie\Api\Resources\Order::class)->disableOriginalConstructor()->getMock();

        $oApiEndpoint = $this->getMockBuilder(PaymentEndpoint::class)->disableOriginalConstructor()->getMock();
        $oApiEndpoint->method('get')->willReturn($oApiOrder);

        $oPaymentModel = $this->getMockBuilder(Creditcard::class)->disableOriginalConstructor()->getMock();
        $oPaymentModel->method('getApiEndpoint')->willReturn($oApiEndpoint);

        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $oOrder->method('__get')->willReturn(null);
        $oOrder->method('mollieGetPaymentModel')->willReturn($oPaymentModel);

        UtilsObject::setClassInstance(Order::class, $oOrder);

        $oController = oxNew($this->getProxyClassName(OrderRefund::class));
        $oController->setEditObjectId('test');
        $result = $oController->getRefundType(20);

        $this->assertEquals($expected, $result);
    }

    protected function getOrderArticleMock()
    {
        $oOrderArticle = $this->getMockBuilder(OrderArticle::class)->disableOriginalConstructor()->getMock();
        $oOrderArticle->method('getId')->willReturn('prod2');
        $oOrderArticle->method('mollieGetRefundableQuantity')->willReturn('0');
        $oOrderArticle->method('mollieGetRefundableAmount')->willReturn('0');
        $oOrderArticle->method('__get')->willReturnMap([
            ['oxorderarticles__molliequantityrefunded', new Field('')],
            ['oxorderarticles__mollieamountrefunded', new Field('10')],
            ['oxorderarticles__oxbrutprice', new Field('11')],
            ['oxorderarticles__oxamount', new Field('2')],
            ['oxorderarticles__oxartnum', new Field('test')],
            ['oxorderarticles__oxtitle', new Field('test')],
            ['oxorderarticles__oxbprice', new Field('5')],
            ['oxorderarticles__oxvat', new Field('19')],
            ['oxorderarticles__oxvat', new Field('19')],
            ['oxorderarticles__mollieamountrefunded', new Field(0)],
            ['oxorderarticles__mollieamountrefunded', new Field(0)],
            ['oxorder__molliediscountrefunded', new Field(0)],
        ]);

        return $oOrderArticle;
    }

    protected function getOrderArticleMockNoRefund()
    {
        $oOrderArticle = $this->getMockBuilder(OrderArticle::class)->disableOriginalConstructor()->getMock();
        $oOrderArticle->method('getId')->willReturn('prod2');
        $oOrderArticle->method('mollieGetRefundableQuantity')->willReturn('2');
        $oOrderArticle->method('mollieGetRefundableAmount')->willReturn('10');
        $oOrderArticle->method('__get')->willReturnMap([

            ['oxorderarticles__oxbrutprice', new Field('11')],
            ['oxorderarticles__oxamount', new Field('2')],
            ['oxorderarticles__oxartnum', new Field('test')],
            ['oxorderarticles__oxtitle', new Field('test')],
            ['oxorderarticles__oxbprice', new Field('5')],
            ['oxorderarticles__oxvat', new Field('19')],
            ['oxorderarticles__oxvat', new Field('19')],
            ['oxorderarticles__mollieamountrefunded', new Field(0)],
            ['oxorderarticles__mollieamountrefunded', new Field(0)],
            ['oxorder__molliediscountrefunded', new Field(0)],
        ]);

        return $oOrderArticle;
    }

    protected function getOrderArticleMockFreeRefund()
    {
        $oOrderArticle = $this->getMockBuilder(OrderArticle::class)->disableOriginalConstructor()->getMock();
        $oOrderArticle->method('getId')->willReturn('prod2');
        $oOrderArticle->method('mollieGetRefundableQuantity')->willReturn('0');
        $oOrderArticle->method('mollieGetRefundableAmount')->willReturn('0');
        $oOrderArticle->method('__get')->willReturnMap([
            ['oxorderarticles__molliequantityrefunded', new Field('')],
            ['oxorderarticles__mollieamountrefunded', new Field('10')],
            ['oxorderarticles__oxbrutprice', new Field('20')],
            ['oxorderarticles__oxamount', new Field('2')],
            ['oxorderarticles__oxartnum', new Field('test')],
            ['oxorderarticles__oxtitle', new Field('test')],
            ['oxorderarticles__oxbprice', new Field('5')],
            ['oxorderarticles__oxvat', new Field('19')],
            ['oxorderarticles__oxvat', new Field('19')],
            ['oxorder__molliediscountrefunded', new Field(0)],
        ]);

        return $oOrderArticle;
    }

    protected function getOrderArticleDiscountMock()
    {
        $oOrderArticle = $this->getMockBuilder(OrderArticle::class)->disableOriginalConstructor()->getMock();
        $oOrderArticle->method('getId')->willReturn('prod1');
        $oOrderArticle->method('mollieGetRefundableQuantity')->willReturn('0');
        $oOrderArticle->method('mollieGetRefundableAmount')->willReturn('0');
        $oOrderArticle->method('__get')->willReturnMap([
            ['oxorderarticles__molliequantityrefunded', new Field('')],
            ['oxorderarticles__mollieamountrefunded', new Field('10')],
            ['oxorderarticles__oxbrutprice', new Field('10')],
            ['oxorderarticles__oxamount', new Field('2')],
            ['oxorderarticles__oxartnum', new Field('test')],
            ['oxorderarticles__oxtitle', new Field('test')],
            ['oxorderarticles__oxbprice', new Field('5')],
            ['oxorderarticles__oxvat', new Field('19')],
            ['oxorderarticles__oxvat', new Field('19')],
            ['oxorder__molliediscountrefunded', new Field(5)],
        ]);

        return $oOrderArticle;
    }

    protected function getOrderArticleQuantityAvailable()
    {
        $oOrderArticle = $this->getMockBuilder(OrderArticle::class)->disableOriginalConstructor()->getMock();
        $oOrderArticle->method('__get')->willReturnMap([
            ['oxorderarticles__mollieamountrefunded', new Field('10')],
            ['oxorderarticles__oxbprice', new Field('3')],
        ]);

        return $oOrderArticle;
    }

    public function testFreeRefund()
    {
        $oApiOrder = $this->getMockBuilder(\Mollie\Api\Resources\Order::class)->disableOriginalConstructor()->getMock();

        $oApiEndpoint = $this->getMockBuilder(PaymentEndpoint::class)->disableOriginalConstructor()->getMock();
        $oApiEndpoint->method('get')->willReturn($oApiOrder);

        $aBasketItems = [
            ['type' => 'physical'],
            [
                'type' => 'discount',
                'totalAmount' => ['value' => 5],
                'unitPrice' => ['value' => 5],
                'refund_amount' => ['value' => 5],
                'sku' => 'test',
                'name' => 'test',
                'vatRate' => 19,
            ],
            [
                'type' => 'product',
                'totalAmount' => ['value' => 10],
                'unitPrice' => ['value' => 10],
                'refund_amount' => ['value' => 5],
                'sku' => 'test2',
                'artnum' => 'test2',
                'name' => 'test',
                'vatRate' => 19,
            ],
        ];

        $oRequestModel = $this->getMockBuilder(\Mollie\Payment\Application\Model\Request\Payment::class)->disableOriginalConstructor()->getMock();
        $oRequestModel->method('getBasketItems')->willReturn($aBasketItems);

        $oPaymentModel = $this->getMockBuilder(Creditcard::class)->disableOriginalConstructor()->getMock();
        $oPaymentModel->method('getApiEndpoint')->willReturn($oApiEndpoint);
        $oPaymentModel->method('getApiRequestModel')->willReturn($oRequestModel);

        $aOrderArticles = [$this->getOrderArticleMock(), $this->getOrderArticleDiscountMock(), $this->getOrderArticleMockFreeRefund()];

        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $oOrder->method('__get')->willReturn(5);
        $oOrder->method('mollieGetPaymentModel')->willReturn($oPaymentModel);
        $oOrder->method('getOrderArticles')->willReturn($aOrderArticles);

        UtilsObject::setClassInstance(Order::class, $oOrder);

        $aFormData = [
            'prod1' => ['refund_quantity' => '5'],
            'prod2' => ['refund_quantity' => 5.67],
            'prod3' => ['refund_amount' => -3],
        ];

        $oRequest = $this->getMockBuilder(\OxidEsales\Eshop\Core\Request::class)->disableOriginalConstructor()->getMock();
        $oRequest->method('getRequestEscapedParameter')->willReturnMap([
            ['free_amount', null, 5],
            ['aOrderArticles', null, $aFormData],
            ['refund_description', null, 'desc'],
        ]);

        Registry::set(\OxidEsales\Eshop\Core\Request::class, $oRequest);

        $oController = new OrderRefund();
        $oController->setEditObjectId('test');
        $result = $oController->freeRefund();

        $this->assertNull($result);
    }

    public function testFreeRefundException()
    {
        $expected = "Test-Exception";

        $oApiOrder = $this->getMockBuilder(\Mollie\Api\Resources\Order::class)->disableOriginalConstructor()->getMock();
        $oApiOrder->method('refund')->willThrowException(new \Exception($expected));

        $oApiEndpoint = $this->getMockBuilder(PaymentEndpoint::class)->disableOriginalConstructor()->getMock();
        $oApiEndpoint->method('get')->willReturn($oApiOrder);

        $oRequestModel = $this->getMockBuilder(\Mollie\Payment\Application\Model\Request\Payment::class)->disableOriginalConstructor()->getMock();
        $oRequestModel->method('getBasketItems')->willReturn([]);

        $oPaymentModel = $this->getMockBuilder(Creditcard::class)->disableOriginalConstructor()->getMock();
        $oPaymentModel->method('getApiEndpoint')->willReturn($oApiEndpoint);
        $oPaymentModel->method('getApiRequestModel')->willReturn($oRequestModel);

        $aOrderArticles = [$this->getOrderArticleMock(), $this->getOrderArticleDiscountMock(), $this->getOrderArticleMockFreeRefund()];

        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $oOrder->method('__get')->willReturn(5);
        $oOrder->method('mollieGetPaymentModel')->willReturn($oPaymentModel);
        $oOrder->method('getOrderArticles')->willReturn($aOrderArticles);

        UtilsObject::setClassInstance(Order::class, $oOrder);

        $oRequest = $this->getMockBuilder(\OxidEsales\Eshop\Core\Request::class)->disableOriginalConstructor()->getMock();
        $oRequest->method('getRequestEscapedParameter')->willReturnMap([
            ['free_amount', null, 5],
            ['aOrderArticles', null, []],
            ['refund_description', null, 'desc'],
        ]);

        Registry::set(\OxidEsales\Eshop\Core\Request::class, $oRequest);

        $oController = new OrderRefund();
        $oController->setEditObjectId('test');
        $oController->freeRefund();

        $this->assertEquals($expected, $oController->getErrorMessage());
    }

    public function testGetPartialRefundParameters()
    {
        $oApiOrder = $this->getMockBuilder(\Mollie\Api\Resources\Payment::class)->disableOriginalConstructor()->getMock();

        $oApiEndpoint = $this->getMockBuilder(PaymentEndpoint::class)->disableOriginalConstructor()->getMock();
        $oApiEndpoint->method('get')->willReturn($oApiOrder);

        $aBasketItems = [
            ['type' => 'physical'],
            [
                'type' => 'product',
                'totalAmount' => ['value' => 5],
                'unitPrice' => ['value' => 5],
                'refund_amount' => ['value' => 5],
                'sku' => 'prod1',
                'name' => 'test',
                'vatRate' => 19,
            ],
            [
                'type' => 'product',
                'totalAmount' => ['value' => 10],
                'unitPrice' => ['value' => 10],
                'refund_amount' => ['value' => 5],
                'sku' => 'prod2',
                'artnum' => 'test2',
                'name' => 'test',
                'vatRate' => 19,
            ],
            [
                'type' => 'discount',
                'totalAmount' => ['value' => 10],
                'unitPrice' => ['value' => 10],
                'refund_amount' => ['value' => 5],
                'sku' => 'discount',
                'artnum' => 'discount',
                'name' => 'test',
                'vatRate' => 19,
            ],
        ];

        $oRequestModel = $this->getMockBuilder(\Mollie\Payment\Application\Model\Request\Payment::class)->disableOriginalConstructor()->getMock();
        $oRequestModel->method('getBasketItems')->willReturn($aBasketItems);

        $oPaymentModel = $this->getMockBuilder(Creditcard::class)->disableOriginalConstructor()->getMock();
        $oPaymentModel->method('getApiEndpoint')->willReturn($oApiEndpoint);
        $oPaymentModel->method('getApiRequestModel')->willReturn($oRequestModel);

        $aOrderArticles = [$this->getOrderArticleMock(), $this->getOrderArticleDiscountMock()];

        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $oOrder->method('__get')->willReturn(5);
        $oOrder->method('mollieGetPaymentModel')->willReturn($oPaymentModel);
        $oOrder->method('getOrderArticles')->willReturn($aOrderArticles);

        UtilsObject::setClassInstance(Order::class, $oOrder);

        $aFormData = [
            'discount' => ['refund_amount' => '5'],
            'prod2' => ['refund_amount' => 5.67],
            'prod3' => ['refund_amount' => -3],
        ];

        $oRequest = $this->getMockBuilder(\OxidEsales\Eshop\Core\Request::class)->disableOriginalConstructor()->getMock();
        $oRequest->method('getRequestEscapedParameter')->willReturnMap([
            ['free_amount', null, null],
            ['aOrderArticles', null, $aFormData],
        ]);

        Registry::set(\OxidEsales\Eshop\Core\Request::class, $oRequest);

        $oController = oxNew($this->getProxyClassName(OrderRefund::class));
        $oController->setEditObjectId('test');
        $result = $oController->getPartialRefundParameters();

        $this->assertTrue(is_array($result));
    }

    public function testFullRefund()
    {
        $amount = new \stdClass();
        $amount->value = 5;

        $oApiOrder = $this->getMockBuilder(\Mollie\Api\Resources\Order::class)->disableOriginalConstructor()->getMock();
        $oApiOrder->method('refund')->willReturn(new \stdClass());
        $oApiOrder->method('refundAll')->willReturn(new \stdClass());
        $oApiOrder->amount = $amount;
        $oApiOrder->amountRefunded = $amount;

        $oApiEndpoint = $this->getMockBuilder(PaymentEndpoint::class)->disableOriginalConstructor()->getMock();
        $oApiEndpoint->method('get')->willReturn($oApiOrder);

        $aBasketItems = [
            ['type' => 'physical'],
            [
                'type' => 'discount',
                'totalAmount' => ['value' => 5],
                'unitPrice' => ['value' => 5],
                'refund_amount' => ['value' => 5],
                'sku' => 'test',
                'name' => 'test',
                'vatRate' => 19,
            ],
            [
                'type' => 'product',
                'totalAmount' => ['value' => 10],
                'unitPrice' => ['value' => 10],
                'refund_amount' => ['value' => 5],
                'sku' => 'test2',
                'artnum' => 'test2',
                'name' => 'test',
                'vatRate' => 19,
            ],
        ];

        $oRequestModel = $this->getMockBuilder(\Mollie\Payment\Application\Model\Request\Payment::class)->disableOriginalConstructor()->getMock();
        $oRequestModel->method('getBasketItems')->willReturn($aBasketItems);

        $oPaymentModel = $this->getMockBuilder(Creditcard::class)->disableOriginalConstructor()->getMock();
        $oPaymentModel->method('getApiEndpoint')->willReturn($oApiEndpoint);
        $oPaymentModel->method('getApiRequestModel')->willReturn($oRequestModel);

        $aOrderArticles = [$this->getOrderArticleMock(), $this->getOrderArticleDiscountMock()];

        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $oOrder->method('__get')->willReturn(5);
        $oOrder->method('getOrderArticles')->willReturn($aOrderArticles);
        $oOrder->method('mollieGetPaymentModel')->willReturn($oPaymentModel);

        UtilsObject::setClassInstance(Order::class, $oOrder);

        $aFormData = [
            'discount' => ['refund_amount' => '5'],
            'prod2' => ['refund_amount' => 5.67],
            'prod3' => ['refund_amount' => -3],
        ];

        $oRequest = $this->getMockBuilder(\OxidEsales\Eshop\Core\Request::class)->disableOriginalConstructor()->getMock();
        $oRequest->method('getRequestEscapedParameter')->willReturnMap([
            ['free_amount', null, null],
            ['aOrderArticles', null, $aFormData],
            ['refundRemaining', null, true],
        ]);

        Registry::set(\OxidEsales\Eshop\Core\Request::class, $oRequest);

        $oController = new OrderRefund();
        $oController->setEditObjectId('test');
        $result = $oController->fullRefund();

        $this->assertNull($result);
    }

    public function testFullRefundPayment()
    {
        $expected = "Test-Exception";

        $oApiOrder = $this->getMockBuilder(\Mollie\Api\Resources\Payment::class)->disableOriginalConstructor()->getMock();
        $oApiOrder->method('refund')->willThrowException(new \Exception($expected));

        $oApiEndpoint = $this->getMockBuilder(PaymentEndpoint::class)->disableOriginalConstructor()->getMock();
        $oApiEndpoint->method('get')->willReturn($oApiOrder);

        $aBasketItems = [
            ['type' => 'physical'],
            [
                'type' => 'discount',
                'totalAmount' => ['value' => 5],
                'unitPrice' => ['value' => 5],
                'refund_amount' => ['value' => 5],
                'sku' => 'test',
                'name' => 'test',
                'vatRate' => 19,
            ],
            [
                'type' => 'product',
                'totalAmount' => ['value' => 10],
                'unitPrice' => ['value' => 10],
                'refund_amount' => ['value' => 5],
                'sku' => 'test2',
                'artnum' => 'test2',
                'name' => 'test',
                'vatRate' => 19,
            ],
        ];

        $oRequestModel = $this->getMockBuilder(\Mollie\Payment\Application\Model\Request\Payment::class)->disableOriginalConstructor()->getMock();
        $oRequestModel->method('getBasketItems')->willReturn($aBasketItems);

        $oPaymentModel = $this->getMockBuilder(Creditcard::class)->disableOriginalConstructor()->getMock();
        $oPaymentModel->method('getApiEndpoint')->willReturn($oApiEndpoint);
        $oPaymentModel->method('getApiRequestModel')->willReturn($oRequestModel);

        $aOrderArticles = [$this->getOrderArticleMock(), $this->getOrderArticleDiscountMock()];

        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $oOrder->method('__get')->willReturn(5);
        $oOrder->method('getOrderArticles')->willReturn($aOrderArticles);
        $oOrder->method('mollieGetPaymentModel')->willReturn($oPaymentModel);

        UtilsObject::setClassInstance(Order::class, $oOrder);

        $aFormData = [
            'discount' => ['refund_amount' => '5'],
            'prod2' => ['refund_amount' => 5.67],
            'prod3' => ['refund_amount' => -3],
        ];

        $oRequest = $this->getMockBuilder(\OxidEsales\Eshop\Core\Request::class)->disableOriginalConstructor()->getMock();
        $oRequest->method('getRequestEscapedParameter')->willReturnMap([
            ['free_amount', null, null],
            ['aOrderArticles', null, $aFormData],
            ['refundRemaining', null, true],
        ]);

        Registry::set(\OxidEsales\Eshop\Core\Request::class, $oRequest);

        $oController = new OrderRefund();
        $oController->setEditObjectId('test');
        $oController->fullRefund();

        $this->assertEquals($expected, $oController->getErrorMessage());
    }

    public function testPartialRefund()
    {
        $amount = new \stdClass();
        $amount->value = 5;

        $line = new \stdClass();
        $line->sku = "test";
        $line->id = "test";

        $oApiOrder = $this->getMockBuilder(\Mollie\Api\Resources\Order::class)->disableOriginalConstructor()->getMock();
        $oApiOrder->method('refund')->willReturn(new \stdClass());
        $oApiOrder->method('refundAll')->willReturn(new \stdClass());
        $oApiOrder->method('lines')->willReturn([$line]);
        $oApiOrder->method('payments')->willReturn([$oApiOrder]);
        $oApiOrder->amount = $amount;
        $oApiOrder->amountRefunded = $amount;

        $oApiEndpoint = $this->getMockBuilder(PaymentEndpoint::class)->disableOriginalConstructor()->getMock();
        $oApiEndpoint->method('get')->willReturn($oApiOrder);

        $aBasketItems = [
            ['type' => 'physical'],
            [
                'type' => 'discount',
                'totalAmount' => ['value' => 5],
                'unitPrice' => ['value' => 5],
                'refund_amount' => ['value' => 5],
                'sku' => 'test',
                'name' => 'test',
                'vatRate' => 19,
            ],
            [
                'type' => 'product',
                'totalAmount' => ['value' => 10],
                'unitPrice' => ['value' => 10],
                'refund_amount' => ['value' => 5],
                'sku' => 'test2',
                'artnum' => 'test2',
                'name' => 'test',
                'vatRate' => 19,
            ],
        ];

        $oRequestModel = $this->getMockBuilder(\Mollie\Payment\Application\Model\Request\Payment::class)->disableOriginalConstructor()->getMock();
        $oRequestModel->method('getBasketItems')->willReturn($aBasketItems);

        $oPaymentModel = $this->getMockBuilder(Creditcard::class)->disableOriginalConstructor()->getMock();
        $oPaymentModel->method('getApiEndpoint')->willReturn($oApiEndpoint);
        $oPaymentModel->method('getApiRequestModel')->willReturn($oRequestModel);

        $aOrderArticles = [$this->getOrderArticleMock(), $this->getOrderArticleDiscountMock()];

        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $oOrder->method('__get')->willReturn(5);
        $oOrder->method('getOrderArticles')->willReturn($aOrderArticles);
        $oOrder->method('mollieGetPaymentModel')->willReturn($oPaymentModel);

        UtilsObject::setClassInstance(Order::class, $oOrder);

        $aFormData = [
            'discount' => ['refund_amount' => '5'],
            'prod1' => ['refund_amount' => 5.67],
            'prod2' => ['refund_quantity' => 5.67],
            'prod3' => ['refund_amount' => -3],
        ];

        $oRequest = $this->getMockBuilder(\OxidEsales\Eshop\Core\Request::class)->disableOriginalConstructor()->getMock();
        $oRequest->method('getRequestEscapedParameter')->willReturnMap([
            ['free_amount', null, null],
            ['aOrderArticles', null, $aFormData],
            ['refundRemaining', null, true],
        ]);

        Registry::set(\OxidEsales\Eshop\Core\Request::class, $oRequest);

        $oController = new OrderRefund();
        $oController->setEditObjectId('test');
        $result = $oController->partialRefund();

        $this->assertNull($result);
    }

    public function testPartialRefundFresh()
    {
        $amount = new \stdClass();
        $amount->value = 5;

        $line = new \stdClass();
        $line->sku = "test";
        $line->id = "test";

        $oApiOrder = $this->getMockBuilder(\Mollie\Api\Resources\Order::class)->disableOriginalConstructor()->getMock();
        $oApiOrder->method('refund')->willReturn(new \stdClass());
        $oApiOrder->method('refundAll')->willReturn(new \stdClass());
        $oApiOrder->method('lines')->willReturn([$line]);
        $oApiOrder->amount = $amount;
        $oApiOrder->amountRefunded = $amount;

        $oApiEndpoint = $this->getMockBuilder(PaymentEndpoint::class)->disableOriginalConstructor()->getMock();
        $oApiEndpoint->method('get')->willReturn($oApiOrder);

        $aBasketItems = [
            ['type' => 'physical'],
            [
                'type' => 'discount',
                'totalAmount' => ['value' => 5],
                'unitPrice' => ['value' => 5],
                'refund_amount' => ['value' => 5],
                'sku' => 'test',
                'name' => 'test',
                'vatRate' => 19,
            ],
        ];

        $oRequestModel = $this->getMockBuilder(\Mollie\Payment\Application\Model\Request\Payment::class)->disableOriginalConstructor()->getMock();
        $oRequestModel->method('getBasketItems')->willReturn($aBasketItems);

        $oPaymentModel = $this->getMockBuilder(Creditcard::class)->disableOriginalConstructor()->getMock();
        $oPaymentModel->method('getApiEndpoint')->willReturn($oApiEndpoint);
        $oPaymentModel->method('getApiRequestModel')->willReturn($oRequestModel);

        $aOrderArticles = [$this->getOrderArticleMockNoRefund()];

        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $oOrder->method('__get')->willReturn(5);
        $oOrder->method('getOrderArticles')->willReturn($aOrderArticles);
        $oOrder->method('mollieGetPaymentModel')->willReturn($oPaymentModel);

        UtilsObject::setClassInstance(Order::class, $oOrder);

        $aFormData = [
            'prod1' => ['refund_amount' => 5],
            'prod2' => ['refund_quantity' => 5.67],
        ];

        $oRequest = $this->getMockBuilder(\OxidEsales\Eshop\Core\Request::class)->disableOriginalConstructor()->getMock();
        $oRequest->method('getRequestEscapedParameter')->willReturnMap([
            ['free_amount', null, null],
            ['aOrderArticles', null, $aFormData],
            ['refundRemaining', null, true],
        ]);

        Registry::set(\OxidEsales\Eshop\Core\Request::class, $oRequest);

        $oController = new OrderRefund();
        $oController->setEditObjectId('test');
        $result = $oController->partialRefund();

        $this->assertNull($result);
    }

    public function testPartialRefundPayment()
    {
        $amount = new \stdClass();
        $amount->value = 5;

        $oApiOrder = $this->getMockBuilder(\Mollie\Api\Resources\Payment::class)->disableOriginalConstructor()->getMock();
        $oApiOrder->method('refund')->willReturn(new \stdClass());
        $oApiOrder->amount = $amount;
        $oApiOrder->amountRefunded = $amount;

        $oApiEndpoint = $this->getMockBuilder(PaymentEndpoint::class)->disableOriginalConstructor()->getMock();
        $oApiEndpoint->method('get')->willReturn($oApiOrder);

        $aBasketItems = [
            ['type' => 'physical'],
            [
                'type' => 'discount',
                'totalAmount' => ['value' => 5],
                'unitPrice' => ['value' => 5],
                'refund_amount' => ['value' => 5],
                'sku' => 'test',
                'name' => 'test',
                'vatRate' => 19,
            ],
            [
                'type' => 'product',
                'totalAmount' => ['value' => 10],
                'unitPrice' => ['value' => 10],
                'refund_amount' => ['value' => 5],
                'sku' => 'test2',
                'artnum' => 'test2',
                'name' => 'test',
                'vatRate' => 19,
            ],
        ];

        $oRequestModel = $this->getMockBuilder(\Mollie\Payment\Application\Model\Request\Payment::class)->disableOriginalConstructor()->getMock();
        $oRequestModel->method('getBasketItems')->willReturn($aBasketItems);

        $oPaymentModel = $this->getMockBuilder(Creditcard::class)->disableOriginalConstructor()->getMock();
        $oPaymentModel->method('getApiEndpoint')->willReturn($oApiEndpoint);
        $oPaymentModel->method('getApiRequestModel')->willReturn($oRequestModel);

        $aOrderArticles = [$this->getOrderArticleMock(), $this->getOrderArticleDiscountMock()];

        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $oOrder->method('__get')->willReturn(5);
        $oOrder->method('getOrderArticles')->willReturn($aOrderArticles);
        $oOrder->method('mollieGetPaymentModel')->willReturn($oPaymentModel);

        UtilsObject::setClassInstance(Order::class, $oOrder);

        $aFormData = [
            'discount' => ['refund_amount' => '500'],
            'prod1' => ['refund_amount' => 500.67],
            'prod2' => ['refund_quantity' => 500.67],
            'prod3' => ['refund_amount' => -3],
        ];

        $oRequest = $this->getMockBuilder(\OxidEsales\Eshop\Core\Request::class)->disableOriginalConstructor()->getMock();
        $oRequest->method('getRequestEscapedParameter')->willReturnMap([
            ['free_amount', null, null],
            ['aOrderArticles', null, $aFormData],
            ['refundRemaining', null, true],
        ]);

        Registry::set(\OxidEsales\Eshop\Core\Request::class, $oRequest);

        $oController = new OrderRefund();
        $oController->setEditObjectId('test');
        $result = $oController->partialRefund();

        $this->assertNull($result);
    }

    public function testPartialRefundPaymentException()
    {
        $expected = "Test-Exception";

        $oApiOrder = $this->getMockBuilder(\Mollie\Api\Resources\Payment::class)->disableOriginalConstructor()->getMock();
        $oApiOrder->method('refund')->willThrowException(new \Exception($expected));

        $oApiEndpoint = $this->getMockBuilder(PaymentEndpoint::class)->disableOriginalConstructor()->getMock();
        $oApiEndpoint->method('get')->willReturn($oApiOrder);

        $aBasketItems = [
            ['type' => 'physical'],
            [
                'type' => 'discount',
                'totalAmount' => ['value' => 5],
                'unitPrice' => ['value' => 5],
                'refund_amount' => ['value' => 5],
                'sku' => 'test',
                'name' => 'test',
                'vatRate' => 19,
            ],
            [
                'type' => 'product',
                'totalAmount' => ['value' => 10],
                'unitPrice' => ['value' => 10],
                'refund_amount' => ['value' => 5],
                'sku' => 'test2',
                'artnum' => 'test2',
                'name' => 'test',
                'vatRate' => 19,
            ],
        ];

        $oRequestModel = $this->getMockBuilder(\Mollie\Payment\Application\Model\Request\Payment::class)->disableOriginalConstructor()->getMock();
        $oRequestModel->method('getBasketItems')->willReturn($aBasketItems);

        $oPaymentModel = $this->getMockBuilder(Creditcard::class)->disableOriginalConstructor()->getMock();
        $oPaymentModel->method('getApiEndpoint')->willReturn($oApiEndpoint);
        $oPaymentModel->method('getApiRequestModel')->willReturn($oRequestModel);

        $aOrderArticles = [$this->getOrderArticleMock(), $this->getOrderArticleDiscountMock()];

        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $oOrder->method('__get')->willReturn(5);
        $oOrder->method('getOrderArticles')->willReturn($aOrderArticles);
        $oOrder->method('mollieGetPaymentModel')->willReturn($oPaymentModel);

        UtilsObject::setClassInstance(Order::class, $oOrder);

        $aFormData = [
            'discount' => ['refund_amount' => '500'],
            'prod1' => ['refund_amount' => 500.67],
            'prod2' => ['refund_quantity' => 500.67],
            'prod3' => ['refund_amount' => -3],
        ];

        $oRequest = $this->getMockBuilder(\OxidEsales\Eshop\Core\Request::class)->disableOriginalConstructor()->getMock();
        $oRequest->method('getRequestEscapedParameter')->willReturnMap([
            ['free_amount', null, null],
            ['aOrderArticles', null, $aFormData],
            ['refundRemaining', null, true],
        ]);

        Registry::set(\OxidEsales\Eshop\Core\Request::class, $oRequest);

        $oController = new OrderRefund();
        $oController->setEditObjectId('test');
        $oController->partialRefund();

        $this->assertEquals($expected, $oController->getErrorMessage());
    }

    public function testPartialRefundEmpty()
    {
        $oApiOrder = $this->getMockBuilder(\Mollie\Api\Resources\Payment::class)->disableOriginalConstructor()->getMock();
        $oApiOrder->method('refund')->willReturn(new \stdClass());

        $oApiEndpoint = $this->getMockBuilder(PaymentEndpoint::class)->disableOriginalConstructor()->getMock();
        $oApiEndpoint->method('get')->willReturn($oApiOrder);

        $oRequestModel = $this->getMockBuilder(\Mollie\Payment\Application\Model\Request\Payment::class)->disableOriginalConstructor()->getMock();
        $oRequestModel->method('getBasketItems')->willReturn([]);

        $oPaymentModel = $this->getMockBuilder(Creditcard::class)->disableOriginalConstructor()->getMock();
        $oPaymentModel->method('getApiEndpoint')->willReturn($oApiEndpoint);
        $oPaymentModel->method('getApiRequestModel')->willReturn($oRequestModel);

        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $oOrder->method('__get')->willReturn(5);
        $oOrder->method('getOrderArticles')->willReturn([]);
        $oOrder->method('mollieGetPaymentModel')->willReturn($oPaymentModel);

        UtilsObject::setClassInstance(Order::class, $oOrder);

        $oRequest = $this->getMockBuilder(\OxidEsales\Eshop\Core\Request::class)->disableOriginalConstructor()->getMock();
        $oRequest->method('getRequestEscapedParameter')->willReturnMap([
            ['free_amount', null, null],
            ['aOrderArticles', null, []],
            ['refundRemaining', null, true],
        ]);

        Registry::set(\OxidEsales\Eshop\Core\Request::class, $oRequest);

        $oController = new OrderRefund();
        $oController->setEditObjectId('test');
        $result = $oController->partialRefund();

        $this->assertNull($result);
    }

    public function testIsQuantityAvailable()
    {
        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $oOrder->method('getOrderArticles')->willReturn([$this->getOrderArticleQuantityAvailable(), $this->getOrderArticleMock(), $this->getOrderArticleDiscountMock()]);

        UtilsObject::setClassInstance(Order::class, $oOrder);

        $oController = new OrderRefund();
        $oController->setEditObjectId('test');
        $result = $oController->isQuantityAvailable();

        $this->assertFalse($result);
    }

    public function testIsQuantityAvailableTrue()
    {
        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $oOrder->method('__get')->willReturn(5);
        $oOrder->method('getOrderArticles')->willReturn([]);

        UtilsObject::setClassInstance(Order::class, $oOrder);

        $oController = new OrderRefund();
        $oController->setEditObjectId('test');
        $result = $oController->isQuantityAvailable();

        $this->assertTrue($result);
    }

    public function testHasOrderVoucher()
    {
        $oApiOrder = $this->getMockBuilder(\Mollie\Api\Resources\Order::class)->disableOriginalConstructor()->getMock();

        $oApiEndpoint = $this->getMockBuilder(PaymentEndpoint::class)->disableOriginalConstructor()->getMock();
        $oApiEndpoint->method('get')->willReturn($oApiOrder);

        $aBasketItems = [
            ['type' => 'physical'],
            [
                'type' => 'discount',
                'totalAmount' => ['value' => 5],
                'unitPrice' => ['value' => 5],
                'refund_amount' => ['value' => 5],
                'sku' => 'test',
                'name' => 'test',
                'vatRate' => 19,
            ],
            [
                'type' => 'product',
                'totalAmount' => ['value' => 10],
                'unitPrice' => ['value' => 10],
                'refund_amount' => ['value' => 5],
                'sku' => 'prod2',
                'artnum' => 'test2',
                'name' => 'test',
                'vatRate' => 19,
            ],
        ];

        $oRequestModel = $this->getMockBuilder(\Mollie\Payment\Application\Model\Request\Payment::class)->disableOriginalConstructor()->getMock();
        $oRequestModel->method('getBasketItems')->willReturn($aBasketItems);

        $oPaymentModel = $this->getMockBuilder(Creditcard::class)->disableOriginalConstructor()->getMock();
        $oPaymentModel->method('getApiEndpoint')->willReturn($oApiEndpoint);
        $oPaymentModel->method('getApiRequestModel')->willReturn($oRequestModel);

        $aOrderArticles = [$this->getOrderArticleMock(), $this->getOrderArticleDiscountMock()];

        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $oOrder->method('__get')->willReturn(5);
        $oOrder->method('mollieGetPaymentModel')->willReturn($oPaymentModel);
        $oOrder->method('getOrderArticles')->willReturn($aOrderArticles);

        UtilsObject::setClassInstance(Order::class, $oOrder);

        $oController = new OrderRefund();
        $oController->setEditObjectId('test');
        $result = $oController->hasOrderVoucher();

        $this->assertTrue($result);
    }

    public function testHasOrderVoucherFalse()
    {
        $oApiOrder = $this->getMockBuilder(\Mollie\Api\Resources\Order::class)->disableOriginalConstructor()->getMock();

        $oApiEndpoint = $this->getMockBuilder(PaymentEndpoint::class)->disableOriginalConstructor()->getMock();
        $oApiEndpoint->method('get')->willReturn($oApiOrder);

        $aBasketItems = [];

        $oRequestModel = $this->getMockBuilder(\Mollie\Payment\Application\Model\Request\Payment::class)->disableOriginalConstructor()->getMock();
        $oRequestModel->method('getBasketItems')->willReturn($aBasketItems);

        $oPaymentModel = $this->getMockBuilder(Creditcard::class)->disableOriginalConstructor()->getMock();
        $oPaymentModel->method('getApiEndpoint')->willReturn($oApiEndpoint);
        $oPaymentModel->method('getApiRequestModel')->willReturn($oRequestModel);

        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $oOrder->method('__get')->willReturn(5);
        $oOrder->method('mollieGetPaymentModel')->willReturn($oPaymentModel);
        $oOrder->method('getOrderArticles')->willReturn([]);

        UtilsObject::setClassInstance(Order::class, $oOrder);

        $oController = new OrderRefund();
        $oController->setEditObjectId('test');
        $result = $oController->hasOrderVoucher();

        $this->assertFalse($result);
    }

    public function testSendSecondChanceEmail()
    {
        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $oOrder->method('mollieIsMolliePaymentUsed')->willReturn(true);

        UtilsObject::setClassInstance(Order::class, $oOrder);

        $oController = new OrderRefund();
        $oController->setEditObjectId('test');
        $result = $oController->sendSecondChanceEmail();

        $this->assertNull($result);
    }

    public function testGetRefundableAmountByType()
    {
        $expected = 5;

        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $oOrder->method('__get')->willReturnMap([
            ['oxorder__oxdelcost', new Field(10)],
            ['oxorder__oxwrapcost', new Field(10)],
            ['oxorder__oxgiftcardcost', new Field(10)],
            ['oxorder__oxvoucherdiscount', new Field(10)],
            ['oxorder__oxdiscount', new Field(10)],
            ['oxorder__molliedelcostrefunded', new Field(5)],
            ['oxorder__molliewrapcostrefunded', new Field(5)],
            ['oxorder__molliegiftcardrefunded', new Field(5)],
            ['oxorder__mollievoucherdiscountrefunded', new Field(5)],
            ['oxorder__molliediscountrefunded', new Field(5)],
        ]);

        UtilsObject::setClassInstance(Order::class, $oOrder);

        $oController = oxNew($this->getProxyClassName(OrderRefund::class));
        $oController->setEditObjectId('test');
        $result = $oController->getRefundableAmountByType('shipping_fee');
        $this->assertEquals($expected, $result);

        $result = $oController->getRefundableAmountByType('wrapping');
        $this->assertEquals($expected, $result);

        $result = $oController->getRefundableAmountByType('giftcard');
        $this->assertEquals($expected, $result);

        $result = $oController->getRefundableAmountByType('voucher');
        $this->assertEquals($expected, $result);

        $result = $oController->getRefundableAmountByType('discount');
        $this->assertEquals($expected, $result);

        $result = $oController->getRefundableAmountByType('foobar');
        $this->assertEquals(0, $result);
    }

    public function testGetAmountRefundedByType()
    {
        $expected = 5;

        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $oOrder->method('__get')->willReturnMap([
            ['oxorder__molliedelcostrefunded', new Field(5)],
            ['oxorder__molliewrapcostrefunded', new Field(5)],
            ['oxorder__molliegiftcardrefunded', new Field(5)],
            ['oxorder__mollievoucherdiscountrefunded', new Field(5)],
            ['oxorder__molliediscountrefunded', new Field(5)],
        ]);

        UtilsObject::setClassInstance(Order::class, $oOrder);

        $oController = oxNew($this->getProxyClassName(OrderRefund::class));
        $oController->setEditObjectId('test');
        $result = $oController->getAmountRefundedByType('shipping_fee');
        $this->assertEquals($expected, $result);

        $result = $oController->getAmountRefundedByType('wrapping');
        $this->assertEquals($expected, $result);

        $result = $oController->getAmountRefundedByType('giftcard');
        $this->assertEquals($expected, $result);

        $result = $oController->getAmountRefundedByType('voucher');
        $this->assertEquals($expected, $result);

        $result = $oController->getAmountRefundedByType('discount');
        $this->assertEquals($expected, $result);

        $result = $oController->getAmountRefundedByType('foobar');
        $this->assertEquals(0, $result);
    }

    public function testGetTypeFromBasketItem()
    {
        $expected = 'wrapping';

        $oController = oxNew($this->getProxyClassName(OrderRefund::class));
        $oController->setEditObjectId('test');

        $result = $oController->getTypeFromBasketItem(['type' => 'test', 'sku' => $expected]);
        $this->assertEquals($expected, $result);
    }

    public function testGetFormatedPrice()
    {
        $expected = "50,00";

        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $oOrder->method('__get')->willReturn(new Field('EUR'));

        UtilsObject::setClassInstance(Order::class, $oOrder);

        $oController = new OrderRefund();
        $oController->setEditObjectId('test');
        $result = $oController->getFormatedPrice(50);

        $this->assertEquals($expected, $result);
    }

    public function testIsFullRefundAvailable()
    {
        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $oOrder->method('getOrderArticles')->willReturn([]);
        $oOrder->method('__get')->willReturnMap([
            ['oxorder__molliedelcostrefunded', new Field(0)],
            ['oxorder__molliepaycostrefunded', new Field(0)],
            ['oxorder__molliewrapcostrefunded', new Field(0)],
            ['oxorder__molliegiftcardrefunded', new Field(0)],
            ['oxorder__mollievoucherdiscountrefunded', new Field(0)],
            ['oxorder__molliediscountrefunded', new Field(0)],
        ]);

        UtilsObject::setClassInstance(Order::class, $oOrder);

        $oController = new OrderRefund();
        $oController->setEditObjectId('test');
        $result = $oController->isFullRefundAvailable();

        $this->assertTrue($result);
    }

    public function testIsFullRefundAvailableFalse()
    {
        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $oOrder->method('getOrderArticles')->willReturn([]);
        $oOrder->method('__get')->willReturnMap([
            ['oxorder__molliedelcostrefunded', new Field(5)],
        ]);

        UtilsObject::setClassInstance(Order::class, $oOrder);

        $oController = new OrderRefund();
        $oController->setEditObjectId('test');
        $result = $oController->isFullRefundAvailable();

        $this->assertFalse($result);
    }

    public function testIsFullRefundAvailableOrderArticle()
    {
        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $oOrder->method('getOrderArticles')->willReturn([$this->getOrderArticleQuantityAvailable()]);

        UtilsObject::setClassInstance(Order::class, $oOrder);

        $oController = new OrderRefund();
        $oController->setEditObjectId('test');
        $result = $oController->isFullRefundAvailable();

        $this->assertFalse($result);
    }

    public function testIsMollieOrder()
    {
        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $oOrder->method('__get')->willReturnMap([
            ['oxorder__oxpaymenttype', new Field('molliecreditcard')],
        ]);

        UtilsObject::setClassInstance(Order::class, $oOrder);

        $oController = new OrderRefund();
        $oController->setEditObjectId('test');
        $result = $oController->isMollieOrder();

        $this->assertTrue($result);
    }

    public function testGetAmountRemainingRefunded()
    {
        $expected = "50,00";

        $amount = new \stdClass();
        $amount->value = 50;

        $amountDifferent = new \stdClass();
        $amountDifferent->value = 60;

        $oApiOrder = $this->getMockBuilder(\Mollie\Api\Resources\Order::class)->disableOriginalConstructor()->getMock();
        $oApiOrder->amountRemaining = $amount;
        $oApiOrder->amountRefunded = $amount;
        $oApiOrder->amount = $amountDifferent;

        $oApiEndpoint = $this->getMockBuilder(PaymentEndpoint::class)->disableOriginalConstructor()->getMock();
        $oApiEndpoint->method('get')->willReturn($oApiOrder);

        $oPaymentModel = $this->getMockBuilder(Creditcard::class)->disableOriginalConstructor()->getMock();
        $oPaymentModel->method('getApiEndpoint')->willReturn($oApiEndpoint);

        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $oOrder->method('mollieGetPaymentModel')->willReturn($oPaymentModel);
        $oOrder->method('__get')->willReturnMap([
            ['oxorder__oxpaymenttype', new Field('molliecreditcard')],
        ]);

        UtilsObject::setClassInstance(Order::class, $oOrder);

        $oController = new OrderRefund();
        $oController->setEditObjectId('test');

        $result = $oController->getAmountRemaining();
        $this->assertEquals($expected, $result);

        $result = $oController->getAmountRefunded();
        $this->assertEquals($expected, $result);

        $result = $oController->isOrderRefundable();
        $this->assertTrue($result);
    }

    public function testIsOrderRefundable()
    {
        $oRequest = $this->getMockBuilder(\OxidEsales\Eshop\Core\Request::class)->disableOriginalConstructor()->getMock();
        $oRequest->method('getRequestEscapedParameter')->willReturn('fullRefund');

        Registry::set(\OxidEsales\Eshop\Core\Request::class, $oRequest);

        $oController = oxNew($this->getProxyClassName(OrderRefund::class));
        $oController->setNonPublicVar('_blSuccessfulRefund', true);
        $result = $oController->isOrderRefundable();

        $this->assertFalse($result);
    }

    public function testIsOrderRefundableFalse()
    {
        $amount = new \stdClass();
        $amount->value = 50;

        $oApiOrder = $this->getMockBuilder(\Mollie\Api\Resources\Order::class)->disableOriginalConstructor()->getMock();
        $oApiOrder->amountRefunded = $amount;
        $oApiOrder->amount = $amount;

        $oApiEndpoint = $this->getMockBuilder(PaymentEndpoint::class)->disableOriginalConstructor()->getMock();
        $oApiEndpoint->method('get')->willReturn($oApiOrder);

        $oPaymentModel = $this->getMockBuilder(Creditcard::class)->disableOriginalConstructor()->getMock();
        $oPaymentModel->method('getApiEndpoint')->willReturn($oApiEndpoint);

        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $oOrder->method('mollieGetPaymentModel')->willReturn($oPaymentModel);

        UtilsObject::setClassInstance(Order::class, $oOrder);

        $oController = new OrderRefund();
        $oController->setEditObjectId('test');
        $result = $oController->isOrderRefundable();

        $this->assertFalse($result);
    }

    public function testUpdateRefundedAmounts()
    {
        $expected = 5;

        $oOrder = new Order();

        $oController = oxNew($this->getProxyClassName(OrderRefund::class));

        $result = $oController->updateRefundedAmounts($oOrder, 'shipping_fee', $expected);
        $this->assertEquals($expected, $result->oxorder__molliedelcostrefunded->value);

        $result = $oController->updateRefundedAmounts($oOrder, 'payment_fee', $expected);
        $this->assertEquals($expected, $result->oxorder__molliepaycostrefunded->value);

        $result = $oController->updateRefundedAmounts($oOrder, 'wrapping', $expected);
        $this->assertEquals($expected, $result->oxorder__molliewrapcostrefunded->value);

        $result = $oController->updateRefundedAmounts($oOrder, 'giftcard', $expected);
        $this->assertEquals($expected, $result->oxorder__molliegiftcardrefunded->value);

        $result = $oController->updateRefundedAmounts($oOrder, 'voucher', $expected);
        $this->assertEquals($expected, $result->oxorder__mollievoucherdiscountrefunded->value);

        $result = $oController->updateRefundedAmounts($oOrder, 'discount', $expected);
        $this->assertEquals($expected, $result->oxorder__molliediscountrefunded->value);
    }
}