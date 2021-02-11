<?php


namespace Mollie\Payment\Tests\Unit\Application\Helper;


use Mollie\Payment\Application\Helper\Order;
use OxidEsales\Eshop\Core\UtilsObject;
use OxidEsales\TestingLibrary\UnitTestCase;

class OrderTest extends UnitTestCase
{
    public function testCancelCurrentOrder()
    {
        $oOrder = $this->getMockBuilder(\OxidEsales\Eshop\Application\Model\Order::class)->disableOriginalConstructor()->getMock();
        $oOrder->method('load')->willReturn(true);
        $oOrder->method('__get')->willReturn(new \OxidEsales\Eshop\Core\Field("NOT_FINISHED"));

        UtilsObject::setClassInstance(\OxidEsales\Eshop\Application\Model\Order::class, $oOrder);

        $oOrderHelper = Order::getInstance();
        $result = $oOrderHelper->cancelCurrentOrder();

        $this->assertNull($result);
    }
}