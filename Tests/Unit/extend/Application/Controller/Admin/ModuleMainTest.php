<?php


namespace Mollie\Payment\Tests\Unit\extend\Application\Controller\Admin;


use Mollie\Payment\Application\Model\RequestLog;
use Mollie\Payment\extend\Application\Controller\Admin\ModuleMain;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Email;
use OxidEsales\Eshop\Core\UtilsObject;
use OxidEsales\TestingLibrary\UnitTestCase;

class ModuleMainTest extends UnitTestCase
{
    public function tearDown(): void
    {
        DatabaseProvider::getDb()->execute('TRUNCATE TABLE `'.RequestLog::$sTableName.'`');
        UtilsObject::resetClassInstances();

        parent::tearDown();
    }

    /**
     * @return array
     */
    protected function getLogTestRequest()
    {
        return [
            'method' => 'creditcard',
            'redirectUrl' => 'https://shop.test/index.php?cl=order&fnc=handleMollieReturn&force_sid=abc123def&stoken=ST0K3N&ord_agb=1&rtoken=RT0K3N',
            'billingAddress' => [
                'streetAndNumber' => 'Teststreet 1',
                'postalCode' => '12345',
                'city' => 'Testcity',
                'country' => 'DE',
                'organizationName' => '',
                'givenName' => 'Testfirst',
                'familyName' => 'Testlast',
                'email' => 'customer@example.com',
            ],
            'metadata' => [
                'order_id' => 'logTestOrder',
                'store_id' => '1',
            ],
        ];
    }

    /**
     * Inserts a log entry directly, like it was written by older module versions
     *
     * @return void
     */
    protected function insertLogTestEntry()
    {
        $sQuery = "INSERT INTO `".RequestLog::$sTableName."` (ORDERID, STOREID, REQUESTTYPE, RESPONSESTATUS, REQUEST, RESPONSE) VALUES (?, ?, ?, ?, ?, ?)";
        DatabaseProvider::getDb()->execute($sQuery, [
            'logTestOrder',
            '1',
            'payment',
            'open',
            json_encode($this->getLogTestRequest()),
            json_encode(['resource' => 'payment', 'status' => 'open', 'details' => ['cardHolder' => 'Testfirst Testlast', 'cardLabel' => 'Visa']]),
        ]);
    }

    public function testMollieMaskLogData()
    {
        $oModuleMain = $this->getProxyClassName(ModuleMain::class);
        $oModuleMain = new $oModuleMain();

        $aResult = $oModuleMain->mollieMaskLogData($this->getLogTestRequest());

        $this->assertEquals('***', $aResult['billingAddress']['givenName']);
        $this->assertEquals('***', $aResult['billingAddress']['familyName']);
        $this->assertEquals('***', $aResult['billingAddress']['email']);
        $this->assertEquals('***', $aResult['billingAddress']['streetAndNumber']);
        $this->assertEquals('', $aResult['billingAddress']['organizationName']);
        $this->assertEquals('DE', $aResult['billingAddress']['country']);
        $this->assertEquals('12345', $aResult['billingAddress']['postalCode']);
        $this->assertEquals('creditcard', $aResult['method']);
        $this->assertEquals('logTestOrder', $aResult['metadata']['order_id']);
        $this->assertEquals('text', $oModuleMain->mollieMaskLogData('text'));
    }

    public function testCreateLogAttachmentFile()
    {
        $this->insertLogTestEntry();

        $oModuleMain = $this->getProxyClassName(ModuleMain::class);
        $oModuleMain = new $oModuleMain();

        $sPath = $oModuleMain->createLogAttachmentFile();
        $this->assertNotFalse($sPath);
        $sContent = file_get_contents($sPath);
        $oModuleMain->mollieDeleteLogAttachmentFile($sPath);

        $this->assertEquals(realpath(sys_get_temp_dir()), realpath(dirname($sPath)));
        $this->assertFileNotExists($sPath);
        $this->assertStringContainsString('logTestOrder', $sContent);
        $this->assertStringContainsString('Visa', $sContent);
        $this->assertStringNotContainsString('customer@example.com', $sContent);
        $this->assertStringNotContainsString('Testlast', $sContent);
        $this->assertStringNotContainsString('abc123def', $sContent);
        $this->assertStringNotContainsString('RT0K3N', $sContent);
        $this->assertStringNotContainsString('ST0K3N', $sContent);
    }

    public function testCreateLogAttachmentFileNoEntries()
    {
        $oModuleMain = $this->getProxyClassName(ModuleMain::class);
        $oModuleMain = new $oModuleMain();

        $this->assertFalse($oModuleMain->createLogAttachmentFile());
    }

    public function testMollieSendSupportEnquiryDeletesFileOnError()
    {
        $this->insertLogTestEntry();
        $this->setRequestParameter('support', [
            'name' => 'Test',
            'email' => 'merchant@example.com',
            'subject' => 'Test',
            'enquiry' => 'Test',
        ]);

        $sAttachmentPath = null;
        $oEmail = $this->getMockBuilder(Email::class)->disableOriginalConstructor()->getMock();
        $oEmail->method('mollieSendSupportEmail')->willReturnCallback(function ($sName, $sEmail, $sSubject, $sEnquiryText, $sModuleVersion, $sPath) use (&$sAttachmentPath) {
            $sAttachmentPath = $sPath;
            throw new \Exception('Test-Exception');
        });
        UtilsObject::setClassInstance(Email::class, $oEmail);

        $oModuleMain = $this->getMockBuilder(ModuleMain::class)->setMethods(['mollieGetUsedVersionNumber'])->getMock();
        $oModuleMain->method('mollieGetUsedVersionNumber')->willReturn('1.0.0');

        try {
            $oModuleMain->mollieSendSupportEnquiry();
            $this->fail('Exception expected');
        } catch (\Exception $exc) {
            $this->assertEquals('Test-Exception', $exc->getMessage());
        }

        $this->assertNotEmpty($sAttachmentPath);
        $this->assertFileNotExists($sAttachmentPath);
        $this->assertFalse($oModuleMain->mollieMailHasBeenSent());
    }
}
