<?php


namespace Mollie\Payment\Tests\Unit\Application\Controller;


use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\UtilsObject;
use OxidEsales\TestingLibrary\UnitTestCase;

class MollieFinishPaymentTest extends UnitTestCase
{
    public function testRenderNoOrder()
    {
        $oUtils = $this->getMockBuilder(\OxidEsales\Eshop\Core\Utils::class)->disableOriginalConstructor()->getMock();
        $oUtils->method('redirect')->willReturn(null);

        Registry::set(\OxidEsales\Eshop\Core\Utils::class, $oUtils);

        $oController = new \Mollie\Payment\Application\Controller\MollieFinishPayment();
        $result = $oController->render();

        $this->assertNull($result);
    }

    public function testRender()
    {
        $oRequest = $this->getMockBuilder(\OxidEsales\Eshop\Core\Request::class)->disableOriginalConstructor()->getMock();
        $oRequest->method('getRequestParameter')->willReturn(true);

        Registry::set(\OxidEsales\Eshop\Core\Request::class, $oRequest);

        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $oOrder->method('load')->willReturn(true);
        $oOrder->method('mollieIsEligibleForPaymentFinish')->willReturn(true);

        UtilsObject::setClassInstance(Order::class, $oOrder);

        $oUtils = $this->getMockBuilder(\OxidEsales\Eshop\Core\Utils::class)->disableOriginalConstructor()->getMock();
        $oUtils->method('redirect')->willReturn(null);

        Registry::set(\OxidEsales\Eshop\Core\Utils::class, $oUtils);

        $oController = new \Mollie\Payment\Application\Controller\MollieFinishPayment();
        $result = $oController->render();

        $this->assertNull($result);
    }
}