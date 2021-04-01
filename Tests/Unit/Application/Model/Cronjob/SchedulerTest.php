<?php

namespace Mollie\Payment\Tests\Unit\Application\Model\Cronjob;

use Mollie\Payment\Application\Model\Cronjob\FinishOrders;
use Mollie\Payment\Application\Model\Cronjob\OrderExpiry;
use Mollie\Payment\Application\Model\Cronjob\SecondChance;
use OxidEsales\Eshop\Core\UtilsObject;
use OxidEsales\TestingLibrary\UnitTestCase;

class SchedulerTest extends UnitTestCase
{
    public function testStart()
    {
        $oCronOrderExpiry = $this->getMockBuilder(OrderExpiry::class)->disableOriginalConstructor()->getMock();
        $oCronOrderExpiry->method('isCronjobActivated')->willReturn(true);
        $oCronOrderExpiry->method('getLastRunDateTime')->willReturn(null);

        $oCronFinishOrders = $this->getMockBuilder(FinishOrders::class)->disableOriginalConstructor()->getMock();
        $oCronFinishOrders->method('isCronjobActivated')->willReturn(true);
        $oCronFinishOrders->method('getLastRunDateTime')->willReturn(date('Y-m-d H:i:s', (time() + (10 * 60))));
        $oCronFinishOrders->method('getMinuteInterval')->willReturn(5);

        $oCronSecondChance = $this->getMockBuilder(SecondChance::class)->disableOriginalConstructor()->getMock();
        $oCronSecondChance->method('isCronjobActivated')->willReturn(false);

        UtilsObject::setClassInstance(OrderExpiry::class, $oCronOrderExpiry);
        UtilsObject::setClassInstance(FinishOrders::class, $oCronFinishOrders);
        UtilsObject::setClassInstance(SecondChance::class, $oCronSecondChance);

        $oScheduler = new \Mollie\Payment\Application\Model\Cronjob\Scheduler();
        $result = $oScheduler->start(2);

        $this->assertNull($result);
    }
}
