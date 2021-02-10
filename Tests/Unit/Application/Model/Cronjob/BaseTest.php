<?php

namespace Mollie\Payment\Tests\Unit\Application\Model\Cronjob;

use Mollie\Payment\Application\Model\Cronjob\Base;
use OxidEsales\TestingLibrary\UnitTestCase;

class BaseTest extends UnitTestCase
{
    public function testStartCronjob()
    {
        $oCronjob = new Base();
        $result = $oCronjob->startCronjob();

        $this->assertFalse($result);
    }
}
