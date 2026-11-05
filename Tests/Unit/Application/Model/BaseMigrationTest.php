<?php


namespace Mollie\Payment\Tests\Unit\Application\Model;


use Doctrine\DBAL\Schema\Schema;
use Mollie\Payment\Application\Model\BaseMigration;
use OxidEsales\TestingLibrary\UnitTestCase;

class BaseMigrationTest extends UnitTestCase
{
    /**
     * @param  array $aExpectedSql
     * @return BaseMigration
     */
    protected function getMigrationMock(array &$aExpectedSql)
    {
        $oMigration = $this->getMockBuilder(BaseMigration::class)
            ->disableOriginalConstructor()
            ->setMethods(['addSql'])
            ->getMockForAbstractClass();
        $oMigration->method('addSql')->willReturnCallback(function ($sSql) use (&$aExpectedSql) {
            $aExpectedSql[] = $sSql;
        });
        return $oMigration;
    }

    /**
     * @param  object $oObject
     * @param  string $sMethod
     * @param  array  $aArgs
     * @return mixed
     */
    protected function callProtected($oObject, $sMethod, array $aArgs)
    {
        $oMethod = new \ReflectionMethod(BaseMigration::class, $sMethod);
        $oMethod->setAccessible(true);
        return $oMethod->invokeArgs($oObject, $aArgs);
    }

    /**
     * @return Schema
     */
    protected function getSchema()
    {
        $oSchema = new Schema();
        $oTable = $oSchema->createTable('oxorder');
        $oTable->addColumn('OXID', 'string', ['length' => 32]);
        return $oSchema;
    }

    public function testAddColumnIfNotExists()
    {
        $aSql = [];
        $oMigration = $this->getMigrationMock($aSql);
        $oSchema = $this->getSchema();

        $result = $this->callProtected($oMigration, 'addColumnIfNotExists', [$oSchema, 'oxorder', 'MOLLIETEST', 'string', ['length' => 64], ['UPDATE oxorder SET MOLLIETEST = 1']]);

        $this->assertTrue($result);
        $this->assertTrue($oSchema->getTable('oxorder')->hasColumn('MOLLIETEST'));
        $this->assertEquals(['UPDATE oxorder SET MOLLIETEST = 1'], $aSql);
    }

    public function testAddColumnIfNotExistsExisting()
    {
        $aSql = [];
        $oMigration = $this->getMigrationMock($aSql);

        $result = $this->callProtected($oMigration, 'addColumnIfNotExists', [$this->getSchema(), 'oxorder', 'OXID', 'string', ['length' => 32], ['UPDATE oxorder SET OXID = 1']]);

        $this->assertFalse($result);
        $this->assertEmpty($aSql);
    }

    public function testAddColumnBySqlIfNotExists()
    {
        $aSql = [];
        $oMigration = $this->getMigrationMock($aSql);

        $result = $this->callProtected($oMigration, 'addColumnBySqlIfNotExists', [$this->getSchema(), 'oxorder', 'MOLLIETEST', 'ALTER TABLE oxorder ADD COLUMN MOLLIETEST INT']);
        $this->assertTrue($result);

        $resultExisting = $this->callProtected($oMigration, 'addColumnBySqlIfNotExists', [$this->getSchema(), 'oxorder', 'OXID', 'ALTER TABLE oxorder ADD COLUMN OXID INT']);
        $this->assertFalse($resultExisting);

        $this->assertEquals(['ALTER TABLE oxorder ADD COLUMN MOLLIETEST INT'], $aSql);
    }

    public function testCreateTableIfNotExists()
    {
        $aSql = [];
        $oMigration = $this->getMigrationMock($aSql);

        $this->assertTrue($this->callProtected($oMigration, 'createTableIfNotExists', [$this->getSchema(), 'mollietest', 'CREATE TABLE mollietest (OXID INT)']));
        $this->assertFalse($this->callProtected($oMigration, 'createTableIfNotExists', [$this->getSchema(), 'oxorder', 'CREATE TABLE oxorder (OXID INT)']));
        $this->assertEquals(['CREATE TABLE mollietest (OXID INT)'], $aSql);
    }
}
