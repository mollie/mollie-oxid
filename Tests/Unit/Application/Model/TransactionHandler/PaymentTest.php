<?php

namespace Mollie\Payment\Tests\Unit\Application\Model\TransactionHandler;

use Mollie\Api\Endpoints\PaymentEndpoint;
use Mollie\Payment\Application\Model\Payment\Creditcard;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;

class PaymentTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    public function testHandleTransactionStatus()
    {
        $amount = new \stdClass();
        $amount->currency = 'EUR';
        $amount->value = 50;

        $oTransaction = $this->getMockBuilder(\Mollie\Api\Resources\Payment::class)->disableOriginalConstructor()->getMock();
        $oTransaction->method('isPaid')->willReturn(true);
        $oTransaction->method('hasRefunds')->willReturn(false);
        $oTransaction->method('isOpen')->willReturn(true);
        $oTransaction->method('isCanceled')->willReturn(true);
        $oTransaction->method('isPending')->willReturn(true);
        $oTransaction->amount = $amount;
        $oTransaction->method = 'banktransfer';
        $oTransaction->status = 'test';

        $oApiEndpoint = $this->getMockBuilder(PaymentEndpoint::class)->disableOriginalConstructor()->getMock();
        $oApiEndpoint->method('get')->willReturn($oTransaction);

        $oPaymentModel = $this->getMockBuilder(Creditcard::class)->disableOriginalConstructor()->getMock();
        $oPaymentModel->method('getApiEndpoint')->willReturn($oApiEndpoint);
        $oPaymentModel->method('getApiEndpointByOrder')->willReturn($oApiEndpoint);
        $oPaymentModel->method('getConfigParam')->willReturn('status');

        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $oOrder->method('getId')->willReturn("testId");
        $oOrder->method('mollieGetPaymentModel')->willReturn($oPaymentModel);
        $oOrder->method('mollieGetTransaction')->willReturn($oTransaction);
        $oOrder->method('mollieIsPaid')->willReturn(false);
        $oOrder->method('__get')->willReturnMap([
            ['oxorder__oxcurrency', new Field('EUR')],
            ['oxorder__oxstorno', new Field(1)],
            ['oxorder__oxtotalordersum', new Field(50)],
        ]);

        $oPayment = new \Mollie\Payment\Application\Model\TransactionHandler\Payment();
        $result = $oPayment->processTransaction($oOrder, 'webhook');

        $this->assertTrue($result['success']);
    }

    public function testHandleTransactionStatusCurrency()
    {
        $amount = new \stdClass();
        $amount->currency = 'mismatch';

        $oTransaction = $this->getMockBuilder(\Mollie\Api\Resources\Payment::class)->disableOriginalConstructor()->getMock();
        $oTransaction->method('isPaid')->willReturn(true);
        $oTransaction->method('hasRefunds')->willReturn(false);
        $oTransaction->amount = $amount;
        $oTransaction->status = 'test';

        $oApiEndpoint = $this->getMockBuilder(PaymentEndpoint::class)->disableOriginalConstructor()->getMock();
        $oApiEndpoint->method('get')->willReturn($oTransaction);

        $oPaymentModel = $this->getMockBuilder(Creditcard::class)->disableOriginalConstructor()->getMock();
        $oPaymentModel->method('getApiEndpoint')->willReturn($oApiEndpoint);
        $oPaymentModel->method('getApiEndpointByOrder')->willReturn($oApiEndpoint);

        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $oOrder->method('getId')->willReturn("testId");
        $oOrder->method('mollieGetPaymentModel')->willReturn($oPaymentModel);
        $oOrder->method('mollieGetTransaction')->willReturn($oTransaction);
        $oOrder->method('__get')->willReturnMap([
            ['oxorder__oxcurrency', new Field('EUR')],
            ['oxorder__oxstorno', new Field(1)],
            ['oxorder__oxtotalordersum', new Field(50)],
        ]);

        $oPayment = new \Mollie\Payment\Application\Model\TransactionHandler\Payment();
        $result = $oPayment->processTransaction($oOrder, 'webhook');

        $this->assertFalse($result['success']);
    }

    public function testHandleTransactionStatusRefund()
    {
        $amount = new \stdClass();
        $amount->currency = 'mismatch';

        $oTransaction = $this->getMockBuilder(\Mollie\Api\Resources\Payment::class)->disableOriginalConstructor()->getMock();
        $oTransaction->method('isPaid')->willReturn(false);
        $oTransaction->method('hasRefunds')->willReturn(true);
        $oTransaction->method('isOpen')->willReturn(false);
        $oTransaction->method('isCanceled')->willReturn(false);
        $oTransaction->method('isExpired')->willReturn(false);
        $oTransaction->method('isPending')->willReturn(false);
        $oTransaction->amount = $amount;
        $oTransaction->status = 'test';

        $oApiEndpoint = $this->getMockBuilder(PaymentEndpoint::class)->disableOriginalConstructor()->getMock();
        $oApiEndpoint->method('get')->willReturn($oTransaction);

        $oPaymentModel = $this->getMockBuilder(Creditcard::class)->disableOriginalConstructor()->getMock();
        $oPaymentModel->method('getApiEndpoint')->willReturn($oApiEndpoint);
        $oPaymentModel->method('getApiEndpointByOrder')->willReturn($oApiEndpoint);

        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $oOrder->method('getId')->willReturn("testId");
        $oOrder->method('mollieGetPaymentModel')->willReturn($oPaymentModel);
        $oOrder->method('mollieGetTransaction')->willReturn($oTransaction);
        $oOrder->method('__get')->willReturnMap([
            ['oxorder__oxcurrency', new Field('EUR')],
            ['oxorder__oxstorno', new Field(1)],
            ['oxorder__oxtotalordersum', new Field(50)],
        ]);

        $oPayment = new \Mollie\Payment\Application\Model\TransactionHandler\Payment();
        $result = $oPayment->processTransaction($oOrder, 'webhook');

        $this->assertTrue($result['success']);
    }

    public function testHandleTransactionStatusException()
    {
        $oApiEndpoint = $this->getMockBuilder(PaymentEndpoint::class)->disableOriginalConstructor()->getMock();
        $oApiEndpoint->method('get')->willThrowException(new \Exception('Test-Exception'));

        $oPaymentModel = $this->getMockBuilder(Creditcard::class)->disableOriginalConstructor()->getMock();
        $oPaymentModel->method('getApiEndpoint')->willReturn($oApiEndpoint);
        $oPaymentModel->method('getApiEndpointByOrder')->willReturn($oApiEndpoint);

        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $oOrder->method('getId')->willReturn("testId");
        $oOrder->method('mollieGetPaymentModel')->willReturn($oPaymentModel);

        $oConfig = $this->getMockBuilder(Config::class)->disableOriginalConstructor()->getMock();
        $oConfig->method('getShopConfVar')->willReturn(true);

        Registry::set(Config::class, $oConfig);

        $oPayment = new \Mollie\Payment\Application\Model\TransactionHandler\Payment();
        $result = $oPayment->processTransaction($oOrder, 'webhook');

        $this->assertFalse($result['success']);
    }

    /**
     * @param  bool $blOpenStatusAllowed
     * @return Order
     */
    protected function getOpenTransactionOrderMock($blOpenStatusAllowed)
    {
        $amount = new \stdClass();
        $amount->currency = 'EUR';
        $amount->value = 50;

        $oTransaction = $this->getMockBuilder(\Mollie\Api\Resources\Payment::class)->disableOriginalConstructor()->getMock();
        $oTransaction->method('isPaid')->willReturn(false);
        $oTransaction->method('isAuthorized')->willReturn(false);
        $oTransaction->method('hasRefunds')->willReturn(false);
        $oTransaction->method('isOpen')->willReturn(true);
        $oTransaction->method('isCanceled')->willReturn(false);
        $oTransaction->method('isExpired')->willReturn(false);
        $oTransaction->method('isPending')->willReturn(false);
        $oTransaction->amount = $amount;
        $oTransaction->method = 'creditcard';
        $oTransaction->status = 'open';

        $oPaymentModel = $this->getMockBuilder(Creditcard::class)->disableOriginalConstructor()->getMock();
        $oPaymentModel->method('isOpenStatusOnReturnAllowed')->willReturn($blOpenStatusAllowed);

        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $oOrder->method('getId')->willReturn("testId");
        $oOrder->method('mollieGetPaymentModel')->willReturn($oPaymentModel);
        $oOrder->method('mollieGetTransaction')->willReturn($oTransaction);
        $oOrder->method('__get')->willReturnMap([
            ['oxorder__oxcurrency', new Field('EUR')],
            ['oxorder__oxstorno', new Field(0)],
            ['oxorder__oxtotalordersum', new Field(50)],
            ['oxorder__oxtransid', new Field('tr_test')],
        ]);

        return $oOrder;
    }

    public function testHandleTransactionStatusOpenNotCompleted()
    {
        $oPayment = new \Mollie\Payment\Application\Model\TransactionHandler\Payment();
        $result = $oPayment->processTransaction($this->getOpenTransactionOrderMock(false), 'success');

        $this->assertFalse($result['success']);
        $this->assertEquals('open', $result['status']);
    }

    public function testHandleTransactionStatusOpenCompletedLater()
    {
        $oPayment = new \Mollie\Payment\Application\Model\TransactionHandler\Payment();
        $result = $oPayment->processTransaction($this->getOpenTransactionOrderMock(true), 'success');

        $this->assertTrue($result['success']);
        $this->assertEquals('open', $result['status']);
    }

    public function testIsOpenStatusOnReturnAllowed()
    {
        $this->assertTrue(oxNew(\Mollie\Payment\Application\Model\Payment\Banktransfer::class)->isOpenStatusOnReturnAllowed());
        $this->assertTrue(oxNew(\Mollie\Payment\Application\Model\Payment\Wero::class)->isOpenStatusOnReturnAllowed());
        $this->assertTrue(oxNew(\Mollie\Payment\Application\Model\Payment\PayByBank::class)->isOpenStatusOnReturnAllowed());
        $this->assertFalse(oxNew(\Mollie\Payment\Application\Model\Payment\Creditcard::class)->isOpenStatusOnReturnAllowed());
        $this->assertFalse(oxNew(\Mollie\Payment\Application\Model\Payment\PayPal::class)->isOpenStatusOnReturnAllowed());
    }
}