<?php


namespace Mollie\Payment\Tests\Unit\extend\Application\Model;


use OxidEsales\TestingLibrary\UnitTestCase;

class UserTest extends UnitTestCase
{
    protected function setUp()
    {
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute('TRUNCATE TABLE `oxobject2group`');
    }

    protected function tearDown()
    {
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute('TRUNCATE TABLE `oxobject2group`');
    }

    public function testMollieSetAutoGroups()
    {
        $sUserId = "testUserId";

        $oUser = new \Mollie\Payment\extend\Application\Model\User();
        $oUser->setId($sUserId);
        $oUser->oxuser__oxcountryid = new \OxidEsales\Eshop\Core\Field("testcountry");

        $oUser->mollieSetAutoGroups();

        $iCount = \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->getOne("SELECT COUNT(OXID) FROM oxobject2group WHERE oxobjectid = ?", array($sUserId));

        $this->assertGreaterThan(0, $iCount);
    }
}