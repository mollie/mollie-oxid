<?php

namespace Mollie\Payment\Tests\Unit\Application\Model\TransactionHandler;

use Mollie\Api\Endpoints\OrderEndpoint;
use Mollie\Api\Endpoints\PaymentEndpoint;
use Mollie\Payment\Application\Model\Payment\Creditcard;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;

class OrderTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    public function testHandleTransactionStatus()
    {
        $amount = new \stdClass();
        $amount->currency = 'EUR';
        $amount->value = 50;

        $oTransaction = $this->getMockBuilder(\Mollie\Api\Resources\Order::class)->disableOriginalConstructor()->getMock();
        $oTransaction->method('isPaid')->willReturn(true);
        $oTransaction->method('isAuthorized')->willReturn(true);
        $oTransaction->method('isRefunded')->willReturn(true);
        $oTransaction->method('isCreated')->willReturn(true);
        $oTransaction->method('isCanceled')->willReturn(true);
        $oTransaction->method('isCompleted')->willReturn(true);
        $oTransaction->amount = $amount;
        $oTransaction->method = 'banktransfer';
        $oTransaction->_embedded = null;

        $oApiEndpoint = $this->getMockBuilder(OrderEndpoint::class)->disableOriginalConstructor()->getMock();
        $oApiEndpoint->method('get')->willReturn($oTransaction);

        $oPaymentModel = $this->getMockBuilder(Creditcard::class)->disableOriginalConstructor()->getMock();
        $oPaymentModel->method('getApiEndpoint')->willReturn($oApiEndpoint);
        $oPaymentModel->method('getConfigParam')->willReturn('status');

        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $oOrder->method('getId')->willReturn("testId");
        $oOrder->method('mollieGetPaymentModel')->willReturn($oPaymentModel);
        $oOrder->method('mollieIsPaid')->willReturn(false);
        $oOrder->method('__get')->willReturnMap([
            ['oxorder__oxcurrency', new Field('EUR')],
            ['oxorder__oxstorno', new Field(1)],
            ['oxorder__oxtotalordersum', new Field(50)],
        ]);

        $oPayment = new \Mollie\Payment\Application\Model\TransactionHandler\Order();
        $result = $oPayment->processTransaction($oOrder);

        $this->assertTrue($result['success']);
    }

    public function testHandleTransactionStatusCurrencyLastPayment()
    {
        $payment = new \stdClass();
        $payment->status = "canceled";

        $embedded = new \stdClass();
        $embedded->payments = [$payment];

        $oTransaction = $this->getMockBuilder(\Mollie\Api\Resources\Order::class)->disableOriginalConstructor()->getMock();
        $oTransaction->_embedded = $embedded;

        $oApiEndpoint = $this->getMockBuilder(OrderEndpoint::class)->disableOriginalConstructor()->getMock();
        $oApiEndpoint->method('get')->willReturn($oTransaction);

        $oPaymentModel = $this->getMockBuilder(Creditcard::class)->disableOriginalConstructor()->getMock();
        $oPaymentModel->method('getApiEndpoint')->willReturn($oApiEndpoint);
        $oPaymentModel->method('getConfigParam')->willReturn('status');

        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $oOrder->method('getId')->willReturn("testId");
        $oOrder->method('mollieGetPaymentModel')->willReturn($oPaymentModel);
        $oOrder->method('mollieIsPaid')->willReturn(false);
        $oOrder->method('__get')->willReturnMap([
            ['oxorder__oxcurrency', new Field('mismatch')],
        ]);

        $oPayment = new \Mollie\Payment\Application\Model\TransactionHandler\Order();
        $result = $oPayment->processTransaction($oOrder);

        $this->assertFalse($result['success']);
    }

    public function testHandleTransactionStatusCurrency()
    {
        $amount = new \stdClass();
        $amount->currency = 'EUR';

        $oTransaction = $this->getMockBuilder(\Mollie\Api\Resources\Order::class)->disableOriginalConstructor()->getMock();
        $oTransaction->method('isPaid')->willReturn(true);
        $oTransaction->_embedded = null;

        $oApiEndpoint = $this->getMockBuilder(OrderEndpoint::class)->disableOriginalConstructor()->getMock();
        $oApiEndpoint->method('get')->willReturn($oTransaction);

        $oPaymentModel = $this->getMockBuilder(Creditcard::class)->disableOriginalConstructor()->getMock();
        $oPaymentModel->method('getApiEndpoint')->willReturn($oApiEndpoint);
        $oPaymentModel->method('getConfigParam')->willReturn('status');

        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $oOrder->method('getId')->willReturn("testId");
        $oOrder->method('mollieGetPaymentModel')->willReturn($oPaymentModel);
        $oOrder->method('mollieIsPaid')->willReturn(false);
        $oOrder->method('__get')->willReturnMap([
            ['oxorder__oxcurrency', new Field('mismatch')],
        ]);

        $oPayment = new \Mollie\Payment\Application\Model\TransactionHandler\Order();
        $result = $oPayment->processTransaction($oOrder);

        $this->assertFalse($result['success']);
    }
}