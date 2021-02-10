<?php


namespace Mollie\Payment\Tests\Unit\extend\Application\Controller\Admin;


use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\UtilsObject;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\TestingLibrary\UnitTestCase;

class OrderMainTest extends UnitTestCase
{
    public function testSave()
    {
        $aEditval = ['oxorder__oxtrackcode' => 'new code'];

        $oRequest = $this->getMockBuilder(\OxidEsales\Eshop\Core\Request::class)->disableOriginalConstructor()->getMock();
        $oRequest->method("getRequestParameter")->willReturn($aEditval);

        Registry::set(\OxidEsales\Eshop\Core\Request::class, $oRequest);

        $order = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $order->method('load')->willReturn(true);
        $order->method('mollieIsMolliePaymentUsed')->willReturn(true);
        $order->method('__get')->willReturnMap([
            ['oxorder__oxtrackcode', new \OxidEsales\Eshop\Core\Field("not matching")],
            ['oxorder__oxsenddate', new \OxidEsales\Eshop\Core\Field("already sent")],
        ]);
        $order->method('mollieUpdateShippingTrackingCode')->willReturn(null);

        UtilsObject::setClassInstance(Order::class, $order);

        $oOrderMainController = new \Mollie\Payment\extend\Application\Controller\Admin\OrderMain();
        $result = $oOrderMainController->save();

        $this->assertNull($result);
    }

    public function testOnOrderSend()
    {
        $order = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $order->method('load')->willReturn(true);
        $order->method('save')->willReturn(true);
        $order->method('getOrderArticles')->willReturn([]);
        $order->method('mollieIsMolliePaymentUsed')->willReturn(true);
        $order->method('mollieMarkOrderAsShipped')->willReturn(null);

        UtilsObject::setClassInstance(Order::class, $order);

        $oOrderMainController = new \Mollie\Payment\extend\Application\Controller\Admin\OrderMain();
        $result = $oOrderMainController->sendOrder();

        $this->assertNull($result);
    }
}