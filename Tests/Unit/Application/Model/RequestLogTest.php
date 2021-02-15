<?php


namespace Mollie\Payment\Tests\Unit\Application\Model;


use Mollie\Payment\Application\Model\RequestLog;
use OxidEsales\TestingLibrary\UnitTestCase;

class RequestLogTest extends UnitTestCase
{
    public function tearDown()
    {
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute('TRUNCATE TABLE `'.RequestLog::$sTableName.'`');

        parent::tearDown();
    }

    public function testLogRequest()
    {
        $sOrderId = "testId";

        $aRequest = ['metadata' => [
            'order_id' => $sOrderId,
            'store_id' => '1',
        ]];
        $oResponse = $this->getMockBuilder(\Mollie\Api\Endpoints\PaymentEndpoint::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCheckoutUrl'])
            ->getMock();
        $oResponse->_links = ['Links'];
        $oResponse->resource = 'order';
        $oResponse->status = 'created';
        $oResponse->method('getCheckoutUrl')->willReturn('http://www.mollie.com');

        $oRequestLog = new RequestLog();
        $oRequestLog->logRequest($aRequest, $oResponse);

        $result = $oRequestLog->getLogEntryForOrder($sOrderId);
        $this->assertArrayHasKey('ORDERID', $result);
        $this->assertEquals($result['ORDERID'], $sOrderId);

    }

    public function testGetLogEntryForOrder()
    {
        $oRequestLog = new RequestLog();
        $result = $oRequestLog->getLogEntryForOrder("notExistant");

        $this->assertFalse($result);
    }

    public function testLogExceptionResponse()
    {
        $sOrderId = "exceptionId";

        $oRequestLog = new RequestLog();
        $oRequestLog->logExceptionResponse(['foo' => 'bar'], 'test', 'test', 'mollietest', $sOrderId);

        $result = \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->getOne("SELECT orderid FROM ".RequestLog::$sTableName." WHERE orderid = ?", array($sOrderId));

        $this->assertEquals($sOrderId, $result);
    }

    public function testDecodeData()
    {
        $expected = 'bar';

        $oSession = oxNew($this->getProxyClassName(RequestLog::class));
        $result = $oSession->decodeData(json_encode(['foo' => $expected]));

        $this->assertArrayHasKey('foo', $result);
        $this->assertEquals($expected, $result['foo']);
    }
}