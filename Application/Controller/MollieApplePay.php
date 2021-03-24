<?php

namespace Mollie\Payment\Application\Controller;

use Mollie\Payment\Application\Helper\Order as OrderHelper;
use Mollie\Payment\Application\Helper\Payment;
use Mollie\Payment\Application\Helper\User as UserHelper;
use Mollie\Payment\Application\Model\TransactionHandler;
use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\OutOfStockException;
use OxidEsales\Eshop\Core\Registry;
use Mollie\Payment\Application\Helper\DeliverySet;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\EshopCommunity\Application\Model\Country;

class MollieApplePay extends FrontendController
{
    /**
     * Basket item id of temporarily added basket item. Needed to remove it from basket later
     *
     * @var string
     */
    protected $sDetailsProductBasketItemId = null;

    /**
     * Reads the servers domain from $_SERVER variable
     *
     * @return bool|string
     */
    protected function getDomainName()
    {
        if (!empty($_SERVER['HTTP_HOST'])) {
            return $_SERVER['HTTP_HOST'];
        } elseif (!empty($_SERVER['SERVER_NAME'])) {
            return $_SERVER['SERVER_NAME'];
        }
        return false;
    }

    /**
     * Adds product to basket by request parameters
     *
     * @param  \OxidEsales\Eshop\Application\Model\Basket $oBasket
     * @return \OxidEsales\Eshop\Application\Model\Basket
     */
    protected function addProductToBasket($oBasket)
    {
        $sDetailsProductId = Registry::getRequest()->getRequestEscapedParameter('detailsProductId');
        $iAmount = Registry::getRequest()->getRequestEscapedParameter('detailsProductAmount');
        if (!$iAmount) {
            $iAmount = 1;
        }
        if (!empty($sDetailsProductId)) { // applies when Apple Pay button on details page is pressed, since product is not in basket yet
            $oBasketItem = $oBasket->addToBasket($sDetailsProductId, $iAmount);
            $oBasket->calculateBasket(true);

            $this->sDetailsProductBasketItemId = $oBasketItem->getBasketItemKey();
            Registry::getSession()->deleteVariable("blAddedNewItem");
        }
        return $oBasket;
    }

    /**
     * Returns basket object
     *
     * @param  bool $blInit
     * @return \OxidEsales\Eshop\Application\Model\Basket
     */
    protected function getApplePayBasket($blInit = false)
    {
        $oBasket = Registry::getSession()->getBasket();
        if ($blInit === true) {
            $oBasket->deleteBasket();
        }
        $oBasket->setPayment('mollieapplepay');

        Registry::getSession()->setVariable('paymentid', 'mollieapplepay');

        return $oBasket;
    }

    /**
     * Order creation method
     *
     * @return \OxidEsales\Eshop\Application\Model\Order|false|array
     */
    protected function createOrder()
    {
        Registry::getSession()->deleteVariable('sess_challenge'); // Reset whatever order process was active before

        $oOrder = oxNew(\OxidEsales\Eshop\Application\Model\Order::class);
        $oOrder->mollieSetApplePayButtonMode(true);

        $oUser = UserHelper::getInstance()->getApplePayUser();

        $oBasket = $this->getApplePayBasket();
        $oBasket->calculateBasket(true);

        Registry::getConfig()->getActiveView()->setUser($oUser);
        $oBasket->setBasketUser($oUser);

        //finalizing ordering process (validating, storing order into DB, executing payment, setting status ...)
        $iSuccess = $oOrder->finalizeOrder($oBasket, $oUser);

        // performing special actions after user finishes order (assignment to special user groups)
        $oUser->onOrderExecute($oBasket, $iSuccess);

        if ($iSuccess ===\OxidEsales\Eshop\Application\Model\Order::ORDER_STATE_OK || $iSuccess === \OxidEsales\Eshop\Application\Model\Order::ORDER_STATE_MAILINGERROR) {
            \OxidEsales\Eshop\Core\Registry::getSession()->setVariable('sess_challenge', $oOrder->getId());
            return $oOrder;
        }

        OrderHelper::getInstance()->cancelCurrentOrder();

        $mReturn = false;
        if ($iSuccess == Order::ORDER_STATE_INVALIDPAYMENT && DeliverySet::getInstance()->isDeliverySetAvailableWithPaymentType($oBasket->getShippingId(), $oBasket, $oUser) === false) {
            $mReturn = array(
                'code' => 'billingContactInvalid',
                'contactField' => 'country',
                'message' => Registry::getLang()->translateString('MOLLIE_BILLING_APPLE_PAY_NOT_AVAILABLE'),
            );
        }

        return $mReturn;
    }

    /**
     * Ajax controller function for getting the Apple Pay merchant session from the Mollie API
     *
     * @return void
     */
    public function getMerchantSession()
    {
        $aResponse = array();
        $blSuccess = false;

        $sAmazonValidationUrl = Registry::getRequest()->getRequestEscapedParameter('validationUrl');
        if (!empty($sAmazonValidationUrl)) {
            try {
                $oMollieApi = Payment::getInstance()->loadMollieApi();
                $sJsonResponse = $oMollieApi->wallets->requestApplePayPaymentSession($this->getDomainName(), $sAmazonValidationUrl);
                $aResponse['merchantSession'] = $sJsonResponse;
                $blSuccess = true;
            } catch(\Exception $e) {
                error_log($e->getMessage());
            }
        }

        $aResponse['success'] = $blSuccess;
        Registry::getUtils()->showMessageAndExit(json_encode($aResponse));
    }

    /**
     * Ajax controller function for getting all deliverymethods
     *
     * @return void
     */
    public function getDeliveryMethods()
    {
        $aResponse = array();
        $blSuccess = false;

        $oUser = UserHelper::getInstance()->getApplePayUser(true);
        $oBasket = $this->getApplePayBasket();
        $oBasket->setBasketUser($oUser);

        $aDelMethods = DeliverySet::getInstance()->getDeliveryMethods($oUser, $oBasket);
        if (!empty($aDelMethods)) {
            // Apple Pay only sends a onshippingmethodselected event when the shipping method is changed, when only one is available its not sent, so we have to select the first one
            $oBasket->setShipping($aDelMethods[0]['identifier']);

            $blSuccess = true;
            $aResponse['shippingMethods'] = $aDelMethods;
        }

        $aResponse['success'] = $blSuccess;
        Registry::getUtils()->showMessageAndExit(json_encode($aResponse));
    }

    /**
     * Ajax controller function for updating the shipset
     *
     * @return void
     */
    public function updateShippingSet()
    {
        $sShipSet = Registry::getRequest()->getRequestEscapedParameter('shipSet');
        if (!empty($sShipSet)) {
            $oBasket = Registry::getSession()->getBasket();
            $oBasket->setShipping($sShipSet);
        }
        Registry::getUtils()->showMessageAndExit("");
    }

    /**
     * Ajax controller function for finalizing the order
     *
     * @return void
     */
    public function finalizeMollieOrder()
    {
        $aResponse = array();
        $blSuccess = false;

        try {
            $mReturn = $this->createOrder();
            if ($mReturn instanceof \OxidEsales\Eshop\Application\Model\Order) {
                $blSuccess = true;
                $aResponse['redirectUrl'] = Registry::getConfig()->getSslShopUrl()."?cl=thankyou";
            } elseif(is_array($mReturn)) {
                $aResponse['error'] = $mReturn;
            }
        } catch(\Exception $oExc) {
            $aResponse['errormessage'] = $oExc->getMessage();
        }
        $aResponse['success'] = $blSuccess;
        return Registry::getUtils()->showMessageAndExit(json_encode($aResponse));
    }

    /**
     * Ajax controller function for getting the basket price for the product
     * This is needed, because Oxid doesnt show the real basket price (i.e. with usergroup discounts) on details page
     * Starts Apple Pay checkout from product details page
     *
     * @return void
     */
    public function getProductBasketPrice()
    {
        $aResponse = array();
        $blSuccess = false;

        try {
            $oBasket = $this->getApplePayBasket(true);
            $oBasket = $this->addProductToBasket($oBasket);
            Registry::getSession()->setBasket($oBasket);

            $blSuccess = true;
            $aResponse['productBasketPrice'] = $oBasket->getDiscountedProductsBruttoPrice();
        } catch(OutOfStockException $oExc) {
            $aResponse['errormessage'] = Registry::getLang()->translateString($oExc->getMessage());
            $aResponse['showexception'] = true;
        } catch(\Exception $oExc) {
            $aResponse['errormessage'] = $oExc->getMessage();
        }
        $aResponse['success'] = $blSuccess;
        return Registry::getUtils()->showMessageAndExit(json_encode($aResponse));
    }
}
