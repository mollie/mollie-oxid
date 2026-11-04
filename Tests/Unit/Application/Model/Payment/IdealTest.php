<?php


namespace Mollie\Payment\Tests\Unit\Application\Model\Payment;


use Mollie\Api\Endpoints\MethodEndpoint;
use Mollie\Payment\Application\Helper\Payment;
use Mollie\Payment\Application\Model\PaymentConfig;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use OxidEsales\Eshop\Core\UtilsObject;
use OxidEsales\TestingLibrary\UnitTestCase;

class IdealTest extends UnitTestCase
{
    public function testGetPaymentSpecificParametersWithoutIssuer()
    {
        // The issuer selection was removed for iDEAL, a transmitted issuer must not be used anymore
        $aDynValue = ['mollie_ideal_issuer' => 'issuer'];

        $oRequest = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock();
        $oRequest->method('getRequestParameter')->willReturn($aDynValue);

        Registry::set(Request::class, $oRequest);

        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();

        $oPayment = new \Mollie\Payment\Application\Model\Payment\Ideal();
        $result = $oPayment->getPaymentSpecificParameters($oOrder);

        $this->assertArrayNotHasKey('issuer', $result);
    }

    public function testGetPaymentSpecificParametersParent()
    {
        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();

        $oPayment = new \Mollie\Payment\Application\Model\Payment\Ideal();
        $result = $oPayment->getPaymentSpecificParameters($oOrder);

        $this->assertCount(0, $result);
    }

    public function testGetMolliePaymentCode()
    {
        $oPayment = new \Mollie\Payment\Application\Model\Payment\Ideal();
        $result = $oPayment->getMolliePaymentCode();

        $this->assertEquals("ideal", $result);
    }

    public function testIsOrderExpirySupported()
    {
        $oPayment = new \Mollie\Payment\Application\Model\Payment\Ideal();
        $result = $oPayment->isOrderExpirySupported();

        $this->assertTrue($result);
    }

    public function testIsMollieMethodHiddenInitially()
    {
        $oPayment = new \Mollie\Payment\Application\Model\Payment\Ideal();
        $result = $oPayment->isMollieMethodHiddenInitially();

        $this->assertFalse($result);
    }

    public function testGetCustomConfigTemplateNotExisting()
    {
        $oPayment = new \Mollie\Payment\Application\Model\Payment\Ideal();
        $result = $oPayment->getCustomConfigTemplate();

        $this->assertFalse($result);
    }

    public function testGetCustomFrontendTemplateNotExisting()
    {
        $oPayment = new \Mollie\Payment\Application\Model\Payment\Ideal();
        $result = $oPayment->getCustomFrontendTemplate();

        $this->assertFalse($result);
    }
}
