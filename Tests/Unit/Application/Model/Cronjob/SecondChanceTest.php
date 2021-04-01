<?php


namespace Mollie\Payment\Tests\Unit\Application\Model\Cronjob;

use Mollie\Payment\Application\Model\Cronjob\SecondChance;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\UtilsObject;
use OxidEsales\TestingLibrary\UnitTestCase;

class SecondChanceTest extends UnitTestCase
{
    public function tearDown()
    {
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute('DELETE FROM oxorder WHERE oxid = "secondChanceTest"');

        parent::tearDown();
    }

    public function testStartCronjob()
    {
        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $oOrder->method('getId')->willReturn('TestID');
        $oOrder->method('load')->willReturn(true);
        $oOrder->method('mollieIsEligibleForPaymentFinish')->willReturn(true);

        UtilsObject::setClassInstance(Order::class, $oOrder);

        $iDayDiff = 1;

        $oConfig = $this->getMockBuilder(Config::class)->disableOriginalConstructor()->getMock();
        $oConfig->method('getShopConfVar')->willReturn($iDayDiff);

        Registry::set(Config::class, $oConfig);

        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute("INSERT INTO oxorder (OXID, OXPAYMENTTYPE, OXORDERDATE, OXTRANSSTATUS, OXPAID, MOLLIESECONDCHANCEMAILSENT) VALUE ('secondChanceTest', 'molliecreditcard', ?, 'NOT_FINISHED', '0000-00-00 00:00:00', '0000-00-00 00:00:00')", array(date('Y-m-d H:i:s', (time() - (60 * 60 * 24 * $iDayDiff) - 60))));

        $oCronjob = oxNew($this->getProxyClassName(SecondChance::class), 1);

        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute("UPDATE molliecronjob SET LAST_RUN = ? WHERE OXID = 'mollie_second_chance'", array(date('Y-m-d H:i:s', time() - 60 * 5)));

        $oCronjob->loadDbData();
        $result = $oCronjob->startCronjob();

        $this->assertTrue($result);
    }
}