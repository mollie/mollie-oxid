<?php


namespace Mollie\Payment\Tests\Unit\Application\Controller;


use Mollie\Payment\Application\Helper\PaymentFinishLink;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\UtilsObject;
use OxidEsales\TestingLibrary\UnitTestCase;

class MollieFinishPaymentTest extends UnitTestCase
{
    /**
     * Mocks request with the given parameters
     *
     * @param  array $aParams
     * @return void
     */
    protected function setRequestParams($aParams)
    {
        $oRequest = $this->getMockBuilder(\OxidEsales\Eshop\Core\Request::class)->disableOriginalConstructor()->getMock();
        $oRequest->method('getRequestParameter')->willReturnCallback(function ($sName) use ($aParams) {
            return isset($aParams[$sName]) ? $aParams[$sName] : null;
        });
        $oRequest->method('getRequestEscapedParameter')->willReturnCallback(function ($sName) use ($aParams) {
            return isset($aParams[$sName]) ? $aParams[$sName] : null;
        });

        Registry::set(\OxidEsales\Eshop\Core\Request::class, $oRequest);
    }

    protected function setUtilsMock()
    {
        $oUtils = $this->getMockBuilder(\OxidEsales\Eshop\Core\Utils::class)->disableOriginalConstructor()->getMock();
        $oUtils->method('redirect')->willReturn(null);

        Registry::set(\OxidEsales\Eshop\Core\Utils::class, $oUtils);
    }

    /**
     * @param  bool $blErrorExpected
     * @return void
     */
    protected function setUtilsViewMock($blErrorExpected)
    {
        $oUtilsView = $this->getMockBuilder(\OxidEsales\Eshop\Core\UtilsView::class)->disableOriginalConstructor()->getMock();
        $oUtilsView->expects($blErrorExpected ? $this->once() : $this->never())->method('addErrorToDisplay')->with('MOLLIE_ERROR_PAYMENT_LINK_INVALID');

        Registry::set(\OxidEsales\Eshop\Core\UtilsView::class, $oUtilsView);
    }

    public function testRenderNoOrder()
    {
        $this->setUtilsMock();

        $oController = new \Mollie\Payment\Application\Controller\MollieFinishPayment();
        $result = $oController->render();

        $this->assertNull($result);
    }

    public function testRender()
    {
        $sToken = str_repeat('a', 64);
        $this->setRequestParams(['id' => 'unitTestOrder', PaymentFinishLink::PARAM_TOKEN => $sToken]);

        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $oOrder->method('load')->willReturn(true);
        $oOrder->expects($this->once())->method('mollieIsPaymentFinishTokenValid')->with($sToken)->willReturn(true);
        $oOrder->method('mollieIsEligibleForPaymentFinish')->willReturn(true);
        $oOrder->expects($this->once())->method('mollieReinitializePayment');

        UtilsObject::setClassInstance(Order::class, $oOrder);

        $this->setUtilsMock();
        $this->setUtilsViewMock(false);

        $oController = new \Mollie\Payment\Application\Controller\MollieFinishPayment();
        $result = $oController->render();

        $this->assertNull($result);
    }

    public function testRenderInvalidToken()
    {
        $this->setRequestParams(['id' => 'unitTestOrder', PaymentFinishLink::PARAM_TOKEN => str_repeat('b', 64)]);

        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $oOrder->method('load')->willReturn(true);
        $oOrder->method('mollieIsPaymentFinishTokenValid')->willReturn(false);
        $oOrder->expects($this->never())->method('mollieIsEligibleForPaymentFinish');
        $oOrder->expects($this->never())->method('mollieReinitializePayment');

        UtilsObject::setClassInstance(Order::class, $oOrder);

        $this->setUtilsMock();
        $this->setUtilsViewMock(true);

        $oController = new \Mollie\Payment\Application\Controller\MollieFinishPayment();
        $result = $oController->render();

        $this->assertNull($result);
    }

    public function testRenderUnknownOrder()
    {
        $this->setRequestParams(['id' => 'unknownOrder', PaymentFinishLink::PARAM_TOKEN => str_repeat('a', 64)]);

        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $oOrder->method('load')->willReturn(false);
        $oOrder->expects($this->never())->method('mollieReinitializePayment');

        UtilsObject::setClassInstance(Order::class, $oOrder);

        $this->setUtilsMock();
        $this->setUtilsViewMock(true);

        $oController = new \Mollie\Payment\Application\Controller\MollieFinishPayment();
        $result = $oController->render();

        $this->assertNull($result);
    }
}
