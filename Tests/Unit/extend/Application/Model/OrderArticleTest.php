<?php


namespace Mollie\Payment\Tests\Unit\extend\Application\Model;


use OxidEsales\TestingLibrary\UnitTestCase;

class OrderArticleTest extends UnitTestCase
{
    protected function setUp()
    {
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute('TRUNCATE TABLE `oxarticles`');
    }

    protected function tearDown()
    {
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute('TRUNCATE TABLE `oxarticles`');
    }

    public function testMollieUncancelOrderArticle()
    {
        $sProdId = "testproduct";
        $iStock = 10;
        $iOrderedAmount = 2;

        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute('INSERT INTO oxarticles (OXID, OXSTOCK) VALUES (?, ?)', array($sProdId, $iStock));

        $oOrderArticle = new \Mollie\Payment\extend\Application\Model\OrderArticle();
        $oOrderArticle->setId("testOrderArticle");
        $oOrderArticle->setIsNewOrderItem(false);
        $oOrderArticle->oxorderarticles__oxstorno = new \OxidEsales\Eshop\Core\Field(1);
        $oOrderArticle->oxorderarticles__oxamount = new \OxidEsales\Eshop\Core\Field($iOrderedAmount);
        $oOrderArticle->oxorderarticles__oxartid = new \OxidEsales\Eshop\Core\Field($sProdId);

        $oOrderArticle->mollieUncancelOrderArticle();

        $result = \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->getOne("SELECT OXSTOCK FROM oxarticles WHERE oxid = ?", array($sProdId));

        $this->assertEquals(8, $result);
    }

    public function testMollieGetRefundableQuantity()
    {
        $oOrderArticle = new \Mollie\Payment\extend\Application\Model\OrderArticle();
        $oOrderArticle->oxorderarticles__mollieamountrefunded = new \OxidEsales\Eshop\Core\Field(50);
        $oOrderArticle->oxorderarticles__oxbrutprice = new \OxidEsales\Eshop\Core\Field(50);

        $result = $oOrderArticle->mollieGetRefundableQuantity();
        $expected = 0;

        $this->assertEquals($expected, $result);

        $oOrderArticle->oxorderarticles__oxbrutprice = new \OxidEsales\Eshop\Core\Field(60);
        $oOrderArticle->oxorderarticles__oxamount = new \OxidEsales\Eshop\Core\Field(5);
        $oOrderArticle->oxorderarticles__molliequantityrefunded = new \OxidEsales\Eshop\Core\Field(3);

        $result = $oOrderArticle->mollieGetRefundableQuantity();
        $expected = 2;

        $this->assertEquals($expected, $result);
    }

    public function testMollieGetRefundableAmount()
    {
        $oOrderArticle = new \Mollie\Payment\extend\Application\Model\OrderArticle();
        $oOrderArticle->oxorderarticles__molliequantityrefunded = new \OxidEsales\Eshop\Core\Field(5);
        $oOrderArticle->oxorderarticles__oxamount = new \OxidEsales\Eshop\Core\Field(5);

        $result = $oOrderArticle->mollieGetRefundableAmount();
        $expected = 0;

        $this->assertEquals($expected, $result);

        $oOrderArticle->oxorderarticles__oxamount = new \OxidEsales\Eshop\Core\Field(7);
        $oOrderArticle->oxorderarticles__oxbrutprice = new \OxidEsales\Eshop\Core\Field(100);
        $oOrderArticle->oxorderarticles__mollieamountrefunded = new \OxidEsales\Eshop\Core\Field(50);

        $result = $oOrderArticle->mollieGetRefundableAmount();
        $expected = 50;

        $this->assertEquals($expected, $result);
    }
}