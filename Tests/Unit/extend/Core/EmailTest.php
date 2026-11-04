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

    public function testMollieSendSupportEmail()
    {
        $oShop = $this->getMockBuilder(Shop::class)->disableOriginalConstructor()->getMock();
        $oShop->method('__get')->willReturnMap([
            ['oxshops__oxorderemail', new \OxidEsales\Eshop\Core\Field("shop@testdomain.net")],
            ['oxshops__oxname', new \OxidEsales\Eshop\Core\Field("Shopname")],
            ['oxshops__oxsmtp', new \OxidEsales\Eshop\Core\Field(false)],
            ['oxshops__oxsmtpuser', new \OxidEsales\Eshop\Core\Field(false)],
        ]);

        $sAttachmentPath = tempnam(sys_get_temp_dir(), 'mollie_test_');
        file_put_contents($sAttachmentPath, 'Test');

        // template rendering is not part of this test
        $oEmail = $this->getMockBuilder(\Mollie\Payment\extend\Core\Email::class)->setMethods(['mollieRenderTemplate'])->getMock();
        $oEmail->method('mollieRenderTemplate')->willReturn('Body');
        $oEmail->setShop($oShop);

        $result = $oEmail->mollieSendSupportEmail("Name", "testing@testdomain.net", "Subject", "Enquiry", "1.0.0", $sAttachmentPath);
        unlink($sAttachmentPath);

        $this->assertTrue($result);
        $this->assertStringContainsString("Subject", $oEmail->getSubject());
        $this->assertCount(1, $oEmail->getAttachments());
    }
}