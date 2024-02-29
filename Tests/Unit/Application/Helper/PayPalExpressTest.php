<?php

namespace Mollie\Payment\Tests\Unit\Application\Helper;

use Mollie\Payment\Application\Helper\PayPalExpress;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\UtilsObject;
use OxidEsales\TestingLibrary\UnitTestCase;

class PayPalExpressTest extends UnitTestCase
{
    public function testIsMolliePayPalExpressCheckoutTrue()
    {
        $oSession = $this->getMockBuilder(\OxidEsales\Eshop\Core\Session::class)->disableOriginalConstructor()->getMock();
        $oSession->method('getVariable')->willReturn('sessionVar');

        Registry::set(\OxidEsales\Eshop\Core\Session::class, $oSession);

        $oPPEHelper = PayPalExpress::getInstance();
        $result = $oPPEHelper->isMolliePayPalExpressCheckout();

        $this->assertTrue($result);

        Registry::set(\OxidEsales\Eshop\Core\Session::class, null);
    }

    public function testIsMolliePayPalExpressCheckoutFalse()
    {
        $oSession = $this->getMockBuilder(\OxidEsales\Eshop\Core\Session::class)->disableOriginalConstructor()->getMock();
        $oSession->method('getVariable')->willReturn(null);

        Registry::set(\OxidEsales\Eshop\Core\Session::class, $oSession);

        $oPPEHelper = PayPalExpress::getInstance();
        $result = $oPPEHelper->isMolliePayPalExpressCheckout();

        $this->assertFalse($result);

        Registry::set(\OxidEsales\Eshop\Core\Session::class, null);
    }

    public function testMollieCancelPayPalExpress()
    {
        $oMollieSession = $this->getMockBuilder(\Mollie\Api\Resources\Session::class)->disableOriginalConstructor()->getMock();
        
        $oEndpoint = $this->getMockBuilder(\Mollie\Api\Endpoints\SessionEndpoint::class)->disableOriginalConstructor()->getMock();
        $oEndpoint->method('get')->willReturn($oMollieSession);        

        $oMollieApi = $this->getMockBuilder(\Mollie\Api\MollieApiClient::class)->disableOriginalConstructor()->getMock();
        $oMollieApi->session = $oEndpoint;

        UtilsObject::setClassInstance(\Mollie\Api\MollieApiClient::class, $oMollieApi);

        $oPPEHelper = PayPalExpress::getInstance();
        $result = $oPPEHelper->mollieCancelPayPalExpress(true);

        $this->assertNull($result);
    }

    public function testGetPayPalButtonUrl()
    {
        $oPPEHelper = PayPalExpress::getInstance();
        $result = $oPPEHelper->getPayPalButtonUrl();

        $result = strpos($result, "molliepayment") !== false ? true : false;
        $this->assertTrue($result);
    }

    public function testGetPayPalButtonUrlFallback()
    {
        $oPPEHelper = PayPalExpress::getInstance();
        $result = $oPPEHelper->getPayPalButtonUrl("pl", "not_existing", "not_existing", "not_existing");

        $result = strpos($result, "out/img/ppe/pl/rounded_pay_golden.png") !== false ? true : false;
        $this->assertTrue($result);
    }
}