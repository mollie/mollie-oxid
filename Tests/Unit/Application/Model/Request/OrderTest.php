<?php

namespace Mollie\Payment\Tests\Unit\Application\Model\Request;

use Mollie\Api\Endpoints\OrderEndpoint;
use Mollie\Api\Endpoints\PaymentEndpoint;
use Mollie\Payment\Application\Model\Payment\Creditcard;
use OxidEsales\Eshop\Application\Model\Article;
use OxidEsales\Eshop\Application\Model\Country;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Application\Model\OrderArticle;
use OxidEsales\Eshop\Application\Model\State;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Price;
use OxidEsales\Eshop\Core\UtilsObject;

class OrderTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    public function testSendRequest()
    {
        $oCountry = $this->getMockBuilder(Country::class)->disableOriginalConstructor()->getMock();
        $oCountry->method('__get')->willReturn(new Field('NL'));

        UtilsObject::setClassInstance(Country::class, $oCountry);

        $oState = $this->getMockBuilder(State::class)->disableOriginalConstructor()->getMock();
        $oState->method('getTitleById')->willReturn('Bayern');

        UtilsObject::setClassInstance(State::class, $oState);

        $oApiOrder = $this->getMockBuilder(\Mollie\Api\Resources\Order::class)->disableOriginalConstructor()->getMock();

        $oApiEndpoint = $this->getMockBuilder(OrderEndpoint::class)->disableOriginalConstructor()->getMock();
        $oApiEndpoint->method('create')->willReturn($oApiOrder);

        $oPaymentModel = $this->getMockBuilder(Creditcard::class)->disableOriginalConstructor()->getMock();
        $oPaymentModel->method('getMolliePaymentCode')->willReturn('creditcard');
        $oPaymentModel->method('isRedirectUrlNeeded')->willReturn(true);
        $oPaymentModel->method('getApiMethod')->willReturn('order');
        $oPaymentModel->method('getPaymentSpecificParameters')->willReturn(['foo' => 'bar']);
        $oPaymentModel->method('getApiEndpoint')->willReturn($oApiEndpoint);

        $oUser = $this->getMockBuilder(User::class)->disableOriginalConstructor()->getMock();
        $oUser->method('__get')->willReturn(new Field(''));

        $oArticle = $this->getMockBuilder(Article::class)->disableOriginalConstructor()->getMock();
        $oArticle->method('isDownloadable')->willReturn(true);
        $oArticle->method('getLink')->willReturn('http://somelink.com');

        UtilsObject::setClassInstance(Article::class, $oArticle);

        $oOrderarticle = $this->getMockBuilder(OrderArticle::class)->disableOriginalConstructor()->getMock();
        $oOrderarticle->method('getArticle')->willReturn($oOrderarticle);
        $oOrderarticle->method('__get')->willReturn(new Field('test'));

        $oOrderarticles = $this->getMockBuilder(\OxidEsales\Eshop\Core\Model\ListModel::class)->disableOriginalConstructor()->getMock();
        $oOrderarticles->method('getArray')->willReturn([$oOrderarticle]);

        $oPrice = $this->getMockBuilder(Price::class)->disableOriginalConstructor()->getMock();
        $oPrice->method('getVatValue')->willReturn(19);

        $oOrder = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $oOrder->method('getId')->willReturn('testOrder');
        $oOrder->method('getShopId')->willReturn(1);
        $oOrder->method('mollieGetPaymentModel')->willReturn($oPaymentModel);
        $oOrder->method('getUser')->willReturn($oUser);
        $oOrder->method('getOrderArticles')->willReturn($oOrderarticles);
        $oOrder->method('getOrderDeliveryPrice')->willReturn($oPrice);
        $oOrder->method('getOrderPaymentPrice')->willReturn($oPrice);
        $oOrder->method('getOrderWrappingPrice')->willReturn($oPrice);
        $oOrder->method('getOrderGiftCardPrice')->willReturn($oPrice);
        $oOrder->method('__get')->willReturn(new Field('5'));

        $oRequest = new \Mollie\Payment\Application\Model\Request\Order();
        $result = $oRequest->sendRequest($oOrder, 50, "http://someurl.com");

        $this->assertInstanceOf(\Mollie\Api\Resources\Order::class, $result);
    }
}