<?php


namespace Mollie\Payment\Tests\Unit\extend\Core;


use OxidEsales\TestingLibrary\UnitTestCase;

class SessionTest extends UnitTestCase
{
    public function testGetRequireSessionWithParams()
    {
        $oSession = oxNew($this->getProxyClassName(\Mollie\Payment\extend\Core\Session::class));

        $result = $oSession->_getRequireSessionWithParams();

        $this->assertTrue(is_array($result));
        $this->assertArrayHasKey('cl', $result);
        $this->assertArrayHasKey('mollieFinishPayment', $result['cl']);
    }
}