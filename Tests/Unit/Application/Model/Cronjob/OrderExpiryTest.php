<?php

namespace Mollie\Payment\Tests\Unit\Application\Model\Cronjob;

use Mollie\Payment\Application\Helper\Payment;
use Mollie\Payment\Application\Model\Cronjob\OrderExpiry;
use Mollie\Payment\Application\Model\Payment\Banktransfer;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\UtilsObject;
use OxidEsales\TestingLibrary\UnitTestCase;

class OrderExpiryTest extends UnitTestCase
{
    protected function setUp()
    {
        \OxidEsales\Eshop\Core\DatabaseProvider::getDB()->execute("DELETE FROM molliecronjob WHERE OXID = 'mollie_order_expiry'");

        parent::setUp();
    }

    public function tearDown()
    {
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute('DELETE FROM oxorder WHERE oxid = "orderExpiryTest"');

        parent::tearDown();
    }

    public function testStartCronjob()
    {
        Payment::destroyInstance();

        $iExpiryDays = 7;
        $sFolder = 'MollieBanktransferPending';

        $oPaymentModel = $this->getMockBuilder(Banktransfer::class)->disableOriginalConstructor()->getMock();
        $oPaymentModel->method('getExpiryDays')->willReturn($iExpiryDays);
        $oPaymentModel->method('isOrderExpirySupported')->willReturn(true);
        $oPaymentModel->method('getConfigParam')->willReturn($sFolder);
        $oPaymentModel->method('getOxidPaymentId')->willReturn('molliebanktransfer');

        $aPaymentMethods = ['molliebanktransfer' => 'Credit Card'];

        $oPaymentHelper = $this->getMockBuilder(Payment::class)->disableOriginalConstructor()->getMock();
        $oPaymentHelper->method('getMolliePaymentMethods')->willReturn($aPaymentMethods);
        $oPaymentHelper->method('getMolliePaymentModel')->willReturn($oPaymentModel);

        UtilsObject::setClassInstance(Payment::class, $oPaymentHelper);

        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $oOrder->method('getId')->willReturn('TestID');
        $oOrder->method('load')->willReturn(true);
        $oOrder->method('mollieIsOrderInUnfinishedState')->willReturn(true);

        UtilsObject::setClassInstance(Order::class, $oOrder);

        $oConfig = $this->getMockBuilder(Config::class)->disableOriginalConstructor()->getMock();
        $oConfig->method('getShopConfVar')->willReturn($sFolder);

        Registry::set(Config::class, $oConfig);

        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute("INSERT INTO oxorder (OXID, OXSTORNO, OXPAYMENTTYPE, OXORDERDATE, OXFOLDER) VALUE ('orderExpiryTest', 0, 'molliebanktransfer', ?, ?)", array(date('Y-m-d H:i:s', time() - (60 * 60 * 24 * $iExpiryDays) - 360), $sFolder));

        $oCronjob = new OrderExpiry(1);
        $result = $oCronjob->startCronjob();

        $this->assertTrue($result);

        UtilsObject::resetClassInstances();
        Payment::destroyInstance();
    }

    public function testIsCronjobActivated()
    {
        $oConfig = $this->getMockBuilder(Config::class)->disableOriginalConstructor()->getMock();
        $oConfig->method('getShopConfVar')->willReturn(true);

        Registry::set(Config::class, $oConfig);

        $oCronjob = new OrderExpiry();
        $result = $oCronjob->isCronjobActivated();

        $this->assertTrue($result);
    }

    public function testIsCronjobActivatedFalse()
    {
        $oConfig = $this->getMockBuilder(Config::class)->disableOriginalConstructor()->getMock();
        $oConfig->method('getShopConfVar')->willReturn(null);

        Registry::set(Config::class, $oConfig);

        $oCronjob = new OrderExpiry();
        $result = $oCronjob->isCronjobActivated();

        $this->assertFalse($result);
    }

    public function testGetMinuteInterval()
    {
        $expected = 10;

        $oCronjob = new OrderExpiry();
        $result = $oCronjob->getMinuteInterval();

        $this->assertEquals($expected, $result);
    }
}
