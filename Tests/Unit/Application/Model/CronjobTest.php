<?php


namespace Mollie\Payment\Tests\Unit\Application\Model;


use OxidEsales\TestingLibrary\UnitTestCase;
use Mollie\Payment\Application\Model\Cronjob;

class CronjobTest extends UnitTestCase
{
    public function tearDown()
    {
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute('TRUNCATE TABLE `'.Cronjob::$sTableName.'`');

        parent::tearDown();
    }

    public function testAddNewCronjob()
    {
        $sTestId = "UnitTestCronjob";

        $oCronjob = Cronjob::getInstance();
        $oCronjob->addNewCronjob($sTestId, 5);

        $result = \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->getOne("SELECT oxid FROM ".Cronjob::$sTableName." WHERE oxid = ?", array($sTestId));

        $this->assertEquals($sTestId, $result);
    }

    public function testIsCronjobAlreadyExisting()
    {
        $sCronjobId = "testCronjob";

        $oCronjob = Cronjob::getInstance();
        $result = $oCronjob->isCronjobAlreadyExisting($sCronjobId);

        $this->assertFalse($result);

        $oCronjob->addNewCronjob($sCronjobId, 5);

        $result = $oCronjob->isCronjobAlreadyExisting($sCronjobId);

        $this->assertTrue($result);
    }

    public function testMarkCronjobAsFinished()
    {
        $sCronjobId = "finishedCronjob";

        $oCronjob = Cronjob::getInstance();
        $oCronjob->addNewCronjob($sCronjobId, 5);
        $oCronjob->markCronjobAsFinished($sCronjobId);

        $result = \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->getOne("SELECT oxid FROM ".Cronjob::$sTableName." WHERE LAST_RUN != '0000-00-00 00:00:00'");

        $this->assertEquals($sCronjobId, $result);
    }

    public function testGetCronjobData()
    {
        $sCronjobId = "cronjobDataId";

        $oCronjob = Cronjob::getInstance();
        $oCronjob->addNewCronjob($sCronjobId, 5);
        $result = $oCronjob->getCronjobData($sCronjobId);

        $this->assertCount(3, $result);
    }
}