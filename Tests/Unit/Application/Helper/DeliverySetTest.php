<?php


namespace Mollie\Payment\Tests\Unit\Application\Helper;


use Mollie\Payment\Application\Helper\DeliverySet;
use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Application\Model\BasketItem;
use OxidEsales\Eshop\Application\Model\Delivery;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\UtilsObject;
use OxidEsales\TestingLibrary\UnitTestCase;

class DeliverySetTest extends UnitTestCase
{
    protected function setUp()
    {
        UtilsObject::resetClassInstances();
        DeliverySet::destroyInstance();
    }

    protected function getDeliveryListMock()
    {
        $oPrice = $this->getMockBuilder(\OxidEsales\Eshop\Core\Price::class)->disableOriginalConstructor()->getMock();
        $oPrice->method('getNettoPrice')->willReturn(10);
        $oPrice->method('getBruttoPrice')->willReturn(11);

        $oDelivery = $this->getMockBuilder(Delivery::class)->disableOriginalConstructor()->getMock();
        $oDelivery->method('getDeliveryPrice')->willReturn($oPrice);

        $oDelList = $this->getMockBuilder(\OxidEsales\Eshop\Application\Model\DeliveryList::class)->disableOriginalConstructor()->getMock();
        $oDelList->method('getDeliveryList')->willReturn([$oDelivery]);

        return $oDelList;
    }

    protected function getDeliverySetListMock()
    {
        $oDelSet = $this->getMockBuilder(\OxidEsales\EshopCommunity\Application\Model\DeliverySet::class)->disableOriginalConstructor()->getMock();
        $oDelSet->method('__get')->willReturn(new \OxidEsales\Eshop\Core\Field("delSetId"));

        $aAllSets = [$oDelSet];

        $oDelSetList = $this->getMockBuilder(\OxidEsales\Eshop\Application\Model\DeliverySetList::class)->disableOriginalConstructor()->getMock();
        $oDelSetList->method('getDeliverySetData')->willReturn([$aAllSets, "shipSet", []]);

        return $oDelSetList;
    }

    protected function getBasketMock()
    {
        $oBasketItem = $this->getMockBuilder(BasketItem::class)->disableOriginalConstructor()->getMock();
        $oBasketItem->method('getBasketItemKey')->willReturn("testBasketItemKey");

        $oBasket = $this->getMockBuilder(Basket::class)->disableOriginalConstructor()->getMock();
        $oBasket->method('getPriceForPayment')->willReturn(10);
        $oBasket->method('addToBasket')->willReturn($oBasketItem);
        $oBasket->method('getAdditionalServicesVatPercent')->willReturn(10);
        $oBasket->method('getShippingId')->willReturn('delSetId');

        return $oBasket;
    }

    protected function getRequestMock()
    {
        $oRequest = $this->getMockBuilder(\OxidEsales\Eshop\Core\Request::class)->disableOriginalConstructor()->getMock();
        $oRequest->method('getRequestEscapedParameter')->willReturn('12345');

        return $oRequest;
    }

    protected function getPaymentListMock()
    {
        $oPaymentList = $this->getMockBuilder(\OxidEsales\Eshop\Application\Model\PaymentList::class)->disableOriginalConstructor()->getMock();
        $oPaymentList->method('getPaymentList')->willReturn(['mollieapplepay' => true]);

        return $oPaymentList;
    }

    public function testIsDeliverySetAvailableWithPaymentType()
    {
        Registry::set(\OxidEsales\Eshop\Application\Model\PaymentList::class, $this->getPaymentListMock());

        $oBasket = $this->getMockBuilder(Basket::class)->disableOriginalConstructor()->getMock();
        $oBasket->method('getPriceForPayment')->willReturn(10);

        $oUser = $this->getMockBuilder(User::class)->disableOriginalConstructor()->getMock();

        $oDeliverySetHelper = DeliverySet::getInstance();
        $result = $oDeliverySetHelper->isDeliverySetAvailableWithPaymentType("shipSetId", $oBasket, $oUser);

        $this->assertTrue($result);
    }

    public function testIsDeliverySetAvailableWithPaymentTypeFalse()
    {
        $oPaymentList = $this->getMockBuilder(\OxidEsales\Eshop\Application\Model\PaymentList::class)->disableOriginalConstructor()->getMock();
        $oPaymentList->method('getPaymentList')->willReturn(['molliecreditcard' => true]);

        Registry::set(\OxidEsales\Eshop\Application\Model\PaymentList::class, $oPaymentList);

        $oBasket = $this->getMockBuilder(Basket::class)->disableOriginalConstructor()->getMock();
        $oBasket->method('getPriceForPayment')->willReturn(10);

        $oUser = $this->getMockBuilder(User::class)->disableOriginalConstructor()->getMock();

        $oDeliverySetHelper = DeliverySet::getInstance();
        $result = $oDeliverySetHelper->isDeliverySetAvailableWithPaymentType("shipSetId", $oBasket, $oUser);

        $this->assertFalse($result);
    }

    public function testGetDeliveryMethods()
    {
        $oConfig = $this->getMockBuilder(\OxidEsales\Eshop\Core\Config::class)->disableOriginalConstructor()->getMock();
        $oConfig->method('getConfigParam')->willReturn(true);

        Registry::set(\OxidEsales\Eshop\Core\Config::class, $oConfig);
        Registry::set(\OxidEsales\Eshop\Application\Model\PaymentList::class, $this->getPaymentListMock());
        Registry::set(\OxidEsales\Eshop\Core\Request::class, $this->getRequestMock());
        Registry::set(\OxidEsales\Eshop\Application\Model\DeliverySetList::class, $this->getDeliverySetListMock());

        UtilsObject::setClassInstance(\OxidEsales\Eshop\Application\Model\DeliveryList::class, $this->getDeliveryListMock());

        $oUser = $this->getMockBuilder(User::class)->disableOriginalConstructor()->getMock();

        $oDeliverySetHelper = DeliverySet::getInstance();
        $result = $oDeliverySetHelper->getDeliveryMethods($oUser, $this->getBasketMock());

        $this->assertCount(1, $result);
    }

    public function testGetDeliveryMethodsBrutto()
    {
        $oConfig = $this->getMockBuilder(\OxidEsales\Eshop\Core\Config::class)->disableOriginalConstructor()->getMock();
        $oConfig->method('getConfigParam')->willReturn(false);

        Registry::set(\OxidEsales\Eshop\Core\Config::class, $oConfig);
        Registry::set(\OxidEsales\Eshop\Application\Model\PaymentList::class, $this->getPaymentListMock());
        Registry::set(\OxidEsales\Eshop\Core\Request::class, $this->getRequestMock());
        Registry::set(\OxidEsales\Eshop\Application\Model\DeliverySetList::class, $this->getDeliverySetListMock());
        Registry::set(\OxidEsales\Eshop\Application\Model\DeliveryList::class, $this->getDeliveryListMock());

        $oUser = $this->getMockBuilder(User::class)->disableOriginalConstructor()->getMock();

        $oDeliverySetHelper = DeliverySet::getInstance();
        $result = $oDeliverySetHelper->getDeliveryMethods($oUser, $this->getBasketMock());

        $this->assertCount(1, $result);
    }
}