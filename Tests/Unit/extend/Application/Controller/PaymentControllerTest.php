<?php


namespace Mollie\Payment\Tests\Unit\extend\Application\Controller;


use Mollie\Payment\Application\Model\Payment\Creditcard;
use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Application\Model\Payment;
use OxidEsales\Eshop\Application\Model\PaymentList;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Price;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Session;
use OxidEsales\EshopCommunity\Setup\Database;
use OxidEsales\TestingLibrary\UnitTestCase;
use OxidEsales\Eshop\Application\Model\DeliverySetList;

class PaymentControllerTest extends UnitTestCase
{
    public function testInit()
    {
        $oSession = $this->getMock(\OxidEsales\Eshop\Core\Utils::class, array('getVariable', 'deleteVariable', 'setVariable', 'processUrl'));
        $oSession->method('getVariable')->willReturn(true);
        $oSession->method('processUrl')->willReturn('test');
        Registry::set(\OxidEsales\Eshop\Core\Session::class, $oSession);

        $oPaymentController = new \Mollie\Payment\extend\Application\Controller\PaymentController();

        $return = $oPaymentController->init();

        $this->assertNull($return);
    }

    protected function getActiveOxidPayment()
    {
        return DatabaseProvider::getDb()->getOne("SELECT oxid FROM oxpayments WHERE oxactive = 1 AND oxid NOT LIKE '%mollie%' LIMIT 1");
    }

    protected function getPaymentListMock()
    {
        $oOxidPayment = oxNew(Payment::class);
        $oOxidPayment->load($this->getActiveOxidPayment());

        $oMolliePaymentModel = $this->getMockBuilder(Creditcard::class)->disableOriginalConstructor()->getMock();
        $oMolliePaymentModel->method('isMolliePaymentActive')->willReturn(false);

        $oMolliePayment = $this->getMockBuilder(Payment::class)->disableOriginalConstructor()->getMock();
        $oMolliePayment->method('getId')->willReturn('molliecreditcard');
        $oMolliePayment->method('isMolliePaymentMethod')->willReturn(true);
        $oMolliePayment->method('getMolliePaymentModel')->willReturn($oMolliePaymentModel);

        $aPaymentList = array();
        $aPaymentList[$oOxidPayment->getId()] = $oOxidPayment;
        $aPaymentList[$oMolliePayment->getId()] = $oMolliePayment;

        return $aPaymentList;
    }

    public function testGetPaymentList()
    {
        $oPrice = $this->getMockBuilder(Price::class)->disableOriginalConstructor()->getMock();
        $oPrice->method('getBruttoPrice')->willReturn(100);

        $oCurrency = new \stdClass();
        $oCurrency->name = "Test";

        $oBasket = $this->getMockBuilder(Basket::class)->disableOriginalConstructor()->getMock();
        $oBasket->method('getPrice')->willReturn($oPrice);
        $oBasket->method('getBasketCurrency')->willReturn($oCurrency);

        $oSession = $this->getMockBuilder(Session::class)->disableOriginalConstructor()->getMock();
        $oSession->method('getBasket')->willReturn($oBasket);
        Registry::set(\OxidEsales\Eshop\Core\Session::class, $oSession);

        $oPaymentController = new \Mollie\Payment\extend\Application\Controller\PaymentController();

        $oConfig = $this->getMockBuilder(\OxidEsales\Eshop\Core\Config::class)->disableOriginalConstructor()->getMock();
        $oConfig->method('getShopConfVar')->willReturn(true);
        Registry::set(\OxidEsales\Eshop\Core\Config::class, $oConfig);

        DatabaseProvider::getDb()->execute("UPDATE oxpayments SET oxactive = 1 WHERE oxid LIKE '%mollie%'");

        $aPaymentList = $this->getPaymentListMock();

        $oDeliverySetList = $this->getMockBuilder(DeliverySetList::class)->disableOriginalConstructor()->getMock();
        $oDeliverySetList->method('getDeliverySetData')->willReturn(array([], [], $aPaymentList));
        Registry::set(DeliverySetList::class, $oDeliverySetList);

        $return = $oPaymentController->getPaymentList();
        $this->assertCount(1, $return);
    }
}