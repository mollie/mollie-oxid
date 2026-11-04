<?php


namespace Mollie\Payment\Tests\Unit\Application\Model;


use Mollie\Payment\Application\Model\RequestLog;
use OxidEsales\TestingLibrary\UnitTestCase;

class RequestLogTest extends UnitTestCase
{
    public function tearDown(): void
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

    public function testLogRequestRemovesSessionParameters()
    {
        $sOrderId = "sessionParamId";
        $sReturnUrl = 'https://shop.test/index.php?cl=order&fnc=handleMollieReturn&paymentid=molliecreditcard&force_sid=abc123def&stoken=ST0K3N&ord_agb=1&rtoken=RT0K3N';

        $aRequest = [
            'redirectUrl' => $sReturnUrl,
            'metadata' => [
                'order_id' => $sOrderId,
                'store_id' => '1',
            ],
        ];
        $oResponse = new \stdClass();
        $oResponse->resource = 'payment';
        $oResponse->status = 'open';
        $oResponse->redirectUrl = $sReturnUrl;

        $oRequestLog = new RequestLog();
        $oRequestLog->logRequest($aRequest, $oResponse);

        $aRow = \OxidEsales\Eshop\Core\DatabaseProvider::getDb(\OxidEsales\Eshop\Core\DatabaseProvider::FETCH_MODE_ASSOC)->getRow("SELECT request, response FROM ".RequestLog::$sTableName." WHERE orderid = ?", array($sOrderId));
        $aRow = array_change_key_case($aRow, CASE_LOWER);

        $sExpectedUrl = 'https://shop.test/index.php?cl=order&fnc=handleMollieReturn&paymentid=molliecreditcard&force_sid=&stoken=&ord_agb=1&rtoken=';
        $aLoggedRequest = $oRequestLog->decodeData($aRow['request']);
        $aLoggedResponse = $oRequestLog->decodeData($aRow['response']);

        $this->assertEquals($sExpectedUrl, $aLoggedRequest['redirectUrl']);
        $this->assertEquals($sExpectedUrl, $aLoggedResponse['redirectUrl']);
        $this->assertEquals($sOrderId, $aLoggedRequest['metadata']['order_id']);
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