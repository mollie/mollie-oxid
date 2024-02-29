<?php

namespace Mollie\Payment\Tests\Unit\Application\Model\Payment;

use Mollie\Api\Exceptions\ApiException;
use OxidEsales\TestingLibrary\UnitTestCase;

class PayPalExpressTest extends UnitTestCase
{
    public function testHandlePaymentError()
    {
        $oExc = new ApiException("Error");

        $oPayment = new \Mollie\Payment\Application\Model\Payment\PayPalExpress();
        $result = $oPayment->handlePaymentError($oExc);

        $this->assertNull($result);
    }
}