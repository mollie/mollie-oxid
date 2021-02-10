<?php


namespace Mollie\Payment\Tests\Unit\extend\Core;


use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Application\Model\Shop;
use OxidEsales\TestingLibrary\UnitTestCase;

class EmailTest extends UnitTestCase
{
    public function testMollieSendSecondChanceEmail()
    {
        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $oOrder->method('__get')->willReturnMap([
            ['oxorder__oxbillfname', new \OxidEsales\Eshop\Core\Field("Firstname")],
            ['oxorder__oxbilllname', new \OxidEsales\Eshop\Core\Field("Lastname")],
            ['oxorder__oxbillemail', new \OxidEsales\Eshop\Core\Field("testing@testdomain.net")],
        ]);

        $oShop = $this->getMockBuilder(Shop::class)->disableOriginalConstructor()->getMock();
        $oShop->method('__get')->willReturnMap([
            ['oxshops__oxorderemail', new \OxidEsales\Eshop\Core\Field("Firstname")],
            ['oxshops__oxname', new \OxidEsales\Eshop\Core\Field("Lastname")],
            ['oxshops__oxsmtp', new \OxidEsales\Eshop\Core\Field(false)],
            ['oxshops__oxsmtpuser', new \OxidEsales\Eshop\Core\Field(false)],
        ]);
        
        $oEmail = new \Mollie\Payment\extend\Core\Email();
        $oEmail->setShop($oShop);

        $result = $oEmail->mollieSendSecondChanceEmail($oOrder, "url");

        $this->assertTrue($result);
    }
}