<?php

namespace Mollie\Payment\Application\Controller;

use Mollie\Payment\Application\Helper\Payment;
use Mollie\Payment\Application\Helper\User as UserHelper;
use Mollie\Payment\Application\Model\TransactionHandler;
use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\DatabaseProvider;
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
     * Returns basket object
     *
     * @return \OxidEsales\Eshop\Application\Model\Basket
     */
    protected function getApplePayBasket()
    {
        $oBasket = Registry::getSession()->getBasket();
        $oBasket->setPayment('mollieapplepay');

        $sDetailsProductId = Registry::getRequest()->getRequestEscapedParameter('detailsProductId');
        if (!empty($sDetailsProductId)) { // applies when Apple Pay button on details page is pressed, since product is not in basket yet
            $oBasketItem = $oBasket->addToBasket($sDetailsProductId, 1);
            $oBasket->calculateBasket();

            $this->sDetailsProductBasketItemId = $oBasketItem->getBasketItemKey();
            \OxidEsales\Eshop\Core\Registry::getSession()->deleteVariable("blAddedNewItem");
        }
        return $oBasket;
    }

    /**
     * Order creation method
     *
     * @return \OxidEsales\Eshop\Application\Model\Order|false|array
     */
    protected function createOrder()
    {
        $oOrder = oxNew(\OxidEsales\Eshop\Application\Model\Order::class);
        $oOrder->mollieSetApplePayButtonMode(true);

        $sApplePayShipSet = \OxidEsales\Eshop\Core\Registry::getSession()->getVariable('sApplePayShipSet');
        \OxidEsales\Eshop\Core\Registry::getSession()->deleteVariable('sApplePayShipSet');

        $oUser = UserHelper::getInstance()->getApplePayUser();

        Registry::getConfig()->getActiveView()->setUser($oUser);

        $oBasket = $this->getApplePayBasket();
        $oBasket->setBasketUser($oUser);
        $oBasket->setShipping($sApplePayShipSet);
        $oBasket->calculateBasket(true);

        //finalizing ordering process (validating, storing order into DB, executing payment, setting status ...)
        $iSuccess = $oOrder->finalizeOrder($oBasket, $oUser);

        // performing special actions after user finishes order (assignment to special user groups)
        $oUser->onOrderExecute($oBasket, $iSuccess);

        if ($iSuccess ===\OxidEsales\Eshop\Application\Model\Order::ORDER_STATE_OK || $iSuccess === \OxidEsales\Eshop\Application\Model\Order::ORDER_STATE_MAILINGERROR) {
            \OxidEsales\Eshop\Core\Registry::getSession()->setVariable('sess_challenge', $oOrder->getId());
            return $oOrder;
        }

        $mReturn = false;
        if ($iSuccess == Order::ORDER_STATE_INVALIDPAYMENT && DeliverySet::getInstance()->isDeliverySetAvailableWithPaymentType($sApplePayShipSet, $oBasket, $oUser) === false) {
            $mReturn = array(
                'code' => 'billingContactInvalid',
                'contactField' => 'country',
                'message' => Registry::getLang()->translateString('MOLLIE_BILLING_APPLE_PAY_NOT_AVAILABLE'),
            );
        }

        if ($this->sDetailsProductBasketItemId !== null) {
            $oBasket->removeItem($this->sDetailsProductBasketItemId);
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
        return Registry::getUtils()->showMessageAndExit(json_encode($aResponse));
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

        $oUser = UserHelper::getInstance()->getApplePayUser();
        $oBasket = $this->getApplePayBasket();

        $aDelMethods = DeliverySet::getInstance()->getDeliveryMethods($oUser, $oBasket);
        if (!empty($aDelMethods)) {
            // Apple Pay only sends a onshippingmethodselected event when the shipping method is changed, when only one is available its not sent, so we have to select the first one
            \OxidEsales\Eshop\Core\Registry::getSession()->setVariable('sApplePayShipSet', $aDelMethods[0]['identifier']);

            $blSuccess = true;
            $aResponse['shippingMethods'] = $aDelMethods;
        }

        $aResponse['success'] = $blSuccess;
        return Registry::getUtils()->showMessageAndExit(json_encode($aResponse));
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
            \OxidEsales\Eshop\Core\Registry::getSession()->setVariable('sApplePayShipSet', $sShipSet);
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
}
