<?php


namespace Mollie\Payment\Tests\Unit\Application\Helper;


use Mollie\Payment\Application\Helper\PaymentFinishLink;
use OxidEsales\TestingLibrary\UnitTestCase;

class PaymentFinishLinkTest extends UnitTestCase
{
    public function tearDown(): void
    {
        PaymentFinishLink::destroyInstance();

        parent::tearDown();
    }

    public function testGenerateToken()
    {
        $oHelper = PaymentFinishLink::getInstance();

        $sToken = $oHelper->generateToken();

        $this->assertSame(1, preg_match('/^[0-9a-f]{64}$/', $sToken));
        $this->assertNotEquals($sToken, $oHelper->generateToken());
    }

    public function testIsWellFormedToken()
    {
        $oHelper = PaymentFinishLink::getInstance();

        $this->assertTrue($oHelper->isWellFormedToken($oHelper->generateToken()));
        $this->assertTrue($oHelper->isWellFormedToken(str_repeat('a', 64)));

        $this->assertFalse($oHelper->isWellFormedToken(null));
        $this->assertFalse($oHelper->isWellFormedToken(''));
        $this->assertFalse($oHelper->isWellFormedToken(str_repeat('a', 63))); // truncated link
        $this->assertFalse($oHelper->isWellFormedToken(str_repeat('a', 65)));
        $this->assertFalse($oHelper->isWellFormedToken(str_repeat('A', 64)));
        $this->assertFalse($oHelper->isWellFormedToken(str_repeat('a', 60).'&amp')); // broken by email client
        $this->assertFalse($oHelper->isWellFormedToken(PaymentFinishLink::TOKEN_INVALIDATED));
        $this->assertFalse($oHelper->isWellFormedToken([str_repeat('a', 64)]));
    }
}
