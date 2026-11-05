<?php


namespace Mollie\Payment\Tests\Unit\Application\Controller;


use Mollie\Payment\Application\Model\Payment\Creditcard;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\UtilsObject;
use OxidEsales\TestingLibrary\UnitTestCase;

class MollieWebhookTest extends UnitTestCase
{
    public function testRender()
    {
        $oRequest = $this->getMockBuilder(\OxidEsales\Eshop\Core\Request::class)->disableOriginalConstructor()->getMock();
        $oRequest->method('getRequestParameter')->willReturnMap([
            ['testByMollie', null, null],
            ['id', null, 'testTransId'],
        ]);

        Registry::set(\OxidEsales\Eshop\Core\Request::class, $oRequest);

        // Utils mock prevents a real exit in case the order is not treated as ready
        $oUtils = $this->getMockBuilder(\OxidEsales\Eshop\Core\Utils::class)->disableOriginalConstructor()->getMock();
        $oUtils->expects($this->never())->method('showMessageAndExit');
        Registry::set(\OxidEsales\Eshop\Core\Utils::class, $oUtils);

        $oTransactionHandler = $this->getMockBuilder(\Mollie\Payment\Application\Model\TransactionHandler\Payment::class)->disableOriginalConstructor()->getMock();
        $oTransactionHandler->expects($this->once())->method('processTransaction')->with($this->anything(), 'webhook');

        $oPaymentModel = $this->getMockBuilder(Creditcard::class)->disableOriginalConstructor()->getMock();
        $oPaymentModel->method('getTransactionHandler')->willReturn($oTransactionHandler);

        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $oOrder->method('isLoaded')->willReturn(true);
        $oOrder->method('mollieGetPaymentModel')->willReturn($oPaymentModel);
        $oOrder->method('mollieLoadOrderByTransactionId')->willReturn(true);
        $oOrder->method('mollieOrderIsWebhookReady')->willReturn(true);

        UtilsObject::setClassInstance(Order::class, $oOrder);

        $oController = new \Mollie\Payment\Application\Controller\MollieWebhook();
        $result = $oController->render();

        $expected = 'molliewebhook.tpl';

        $this->assertEquals($expected, $result);
    }

    public function testRenderNoOrder()
    {
        $oRequest = $this->getMockBuilder(\OxidEsales\Eshop\Core\Request::class)->disableOriginalConstructor()->getMock();
        $oRequest->method('getRequestParameter')->willReturnMap([
            ['testByMollie', null, null],
            ['id', null, 'notExistant'],
        ]);

        Registry::set(\OxidEsales\Eshop\Core\Request::class, $oRequest);

        $oUtils = $this->getMockBuilder(\OxidEsales\Eshop\Core\Utils::class)->disableOriginalConstructor()->getMock();
        $oUtils->expects($this->once())->method('setHeader')->with('HTTP/1.1 409 Conflict');
        $oUtils->expects($this->once())->method('showMessageAndExit')->willReturn(null);

        Registry::set(\OxidEsales\Eshop\Core\Utils::class, $oUtils);

        $oController = new \Mollie\Payment\Application\Controller\MollieWebhook();
        $result = $oController->render();

        $expected = 'molliewebhook.tpl';

        $this->assertEquals($expected, $result);
    }

    public function testRenderDirectReturn()
    {
        $expected = "molliewebhook.tpl";

        $oRequest = $this->getMockBuilder(\OxidEsales\Eshop\Core\Request::class)->disableOriginalConstructor()->getMock();
        $oRequest->method('getRequestParameter')->willReturn(true);

        Registry::set(\OxidEsales\Eshop\Core\Request::class, $oRequest);

        $oController = new \Mollie\Payment\Application\Controller\MollieWebhook();
        $result = $oController->render();

        $this->assertEquals($expected, $result);
    }
}