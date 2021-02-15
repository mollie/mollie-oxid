<?php


namespace Mollie\Payment\Tests\Unit\extend\Application\Controller\Admin;

use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\UtilsObject;
use OxidEsales\TestingLibrary\UnitTestCase;

class OrderOverviewTest extends UnitTestCase
{
    public function testSendorder()
    {
        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $oOrder->method('load')->willReturn(true);
        $oOrder->method('mollieIsMolliePaymentUsed')->willReturn(true);
        $oOrder->method('getOrderArticles')->willReturn([]);

        UtilsObject::setClassInstance(Order::class, $oOrder);

        $oOrderOverview = new \Mollie\Payment\extend\Application\Controller\Admin\OrderOverview();
        $oOrderOverview->setEditObjectId('not existant');

        $result = $oOrderOverview->sendorder();

        $this->assertNull($result);
    }
}
