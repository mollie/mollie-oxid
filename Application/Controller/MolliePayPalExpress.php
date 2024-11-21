<?php

namespace Mollie\Payment\Application\Controller;

use Mollie\Payment\Application\Helper\DeliverySet;
use Mollie\Payment\Application\Helper\Payment;
use Mollie\Payment\Application\Model\Payment\PayPalExpress;
use Mollie\Payment\Application\Helper\PayPalExpress as PayPalExpressHelper;
use Mollie\Payment\Application\Helper\User as UserHelper;
use Mollie\Payment\Application\Model\RequestLog;
use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Core\Registry;

class MolliePayPalExpress extends FrontendController
{
    /**
     * Defines PPE success return URL
     *
     * @return string
     */
    protected function getReturnUrl()
    {
        return Registry::getConfig()->getCurrentShopUrl().'index.php?cl=molliePayPalExpress&fnc=handlePayPalReturn';
    }

    /**
     * Defines PPE cancel/error return URL
     *
     * @return string
     */
    protected function getCancelUrl()
    {
        return Registry::getConfig()->getCurrentShopUrl().'index.php?cl=molliePayPalExpress&fnc=handlePayPalCancel';
    }

    /**
     * Starts Mollie PPE process
     *
     * @return void
     */
    public function initSession()
    {
        $oBasket = Registry::getSession()->getBasket();

        $sAid = Registry::getRequest()->getRequestEscapedParameter('aid');
        if (!empty($sAid)) {
            Registry::getSession()->setVariable('mollie_suppress_basket_modal', true);
            $iAmount = Registry::getRequest()->getRequestEscapedParameter('amount');
            if (empty($iAmount)) {
                $iAmount = 1;
            }
            $oBasket->addToBasket($sAid, $iAmount);
            $oBasket->calculateBasket(true);
        }

        $sDescription = Registry::getLang()->translateString('MOLLIE_PAYPAL_EXPRESS_DESCRIPTION').Registry::getConfig()->getActiveShop()->getFieldData('oxname');

        $aParams = [
            "amount" => [
                "value" => (string)number_format($oBasket->getBruttoSum(), 2, ".", ""), // request throws error when amount-value is NOT sent as string
                "currency" => $oBasket->getBasketCurrency()->name,
            ],
            "description" => $sDescription,
            "method" => "paypal",
            "methodDetails" => [
                "checkoutFlow" => "express",
            ],
            "redirectUrl" => $this->getReturnUrl(),
            "cancelUrl" => $this->getCancelUrl(),
        ];

        $aResponse = [
            'success' => false
        ];

        $oRequestLog = oxNew(RequestLog::class);
        try {
            $oMollieApi = Payment::getInstance()->loadMollieApi();
            $oSession = $oMollieApi->sessions->create($aParams);

            $oRequestLog->logRequest($aParams, $oSession, null, $this->getConfig()->getShopId());

            $aResponse['success'] = true;
            $aResponse['redirectUrl'] = $oSession->getRedirectUrl();

            Registry::getSession()->setVariable('mollie_ppe_sessionId', $oSession->id);
        } catch(\Exception $exc) {
            $oRequestLog->logExceptionResponse($aParams, $exc->getCode(), $exc->getMessage(), PayPalExpress::OXID, null, $this->getConfig()->getShopId());
            $aResponse['error'] = $exc->getMessage();
        }

        Registry::getUtils()->showMessageAndExit(json_encode($aResponse));
    }

    /**
     * Full session information is NOT available instantly
     * The billing/shipping-address information is collected asynchronously by mollie and therefore only available with a slight delay
     * It should be available after 2 seconds but this can take up to 5 seconds.
     * This method tries multiple times - up to 10 seconds
     *
     * @param  string $sSessionId
     * @return \Mollie\Api\Resources\Session
     * @throws \Mollie\Api\Exceptions\ApiException
     */
    protected function getSessionFromMollie($sSessionId)
    {
        $iTimeSpent = 0;
        $iMicroSecondSteps = 500000;
        $oMollieApi = Payment::getInstance()->loadMollieApi();
        while(($iTimeSpent / 1000000) < 10) { // while under 10 seconds
            usleep(500000); // 1000000 is 1 second - 500000 = 1/2 sec
            $iTimeSpent += $iMicroSecondSteps;

            $oSession = $oMollieApi->sessions->get($sSessionId);
            if (!empty($oSession->shippingAddress)) {
                return $oSession;
            }
        }

        throw new \Exception("Could not retrieve address information from PayPal");
    }

    /**
     * Returns basket object
     *
     * @param  bool $blInit
     * @return \OxidEsales\Eshop\Application\Model\Basket
     */
    protected function getPayPalExpressBasket($oUser)
    {
        $oBasket = Registry::getSession()->getBasket();
        $oBasket->setPayment(PayPalExpress::OXID);

        Registry::getSession()->setVariable('paymentid', PayPalExpress::OXID);

        if (empty(Registry::getSession()->getVariable('sShipSet'))) {
            $aDelMethods = DeliverySet::getInstance()->getDeliveryMethods($oUser, $oBasket);
            if (!empty($aDelMethods)) {
                $oBasket->setShipping($aDelMethods[0]['identifier']);
            }
        }

        return $oBasket;
    }

    /**
     * Method handles PayPal express errors
     *
     * @param  string $sErrorMessage
     * @param  \Exception $oException
     * @return void
     */
    protected function handlePayPalExpressError($sErrorMessage = null, $oException = null)
    {
        PayPalExpressHelper::getInstance()->mollieCancelPayPalExpress(false);
        Registry::getSession()->setVariable('mollieErrorMessage', $sErrorMessage);
        Registry::getSession()->setVariable('mollieModalTimeout', time() + 2); // add 2 sec to basket modal timeout
        Registry::getUtils()->redirect(Registry::getConfig()->getSslShopUrl()."?cl=basket");
        exit;
    }

    /**
     * Checks if the given delivery country is active in Oxid
     *
     * @param  \stdClass $oMollieSessionAddress
     * @return bool
     */
    protected function isDeliveryCountryAvailable($oMollieSessionAddress)
    {
        $country = oxNew(\OxidEsales\Eshop\Application\Model\Country::class);
        $countryId = $country->getIdByCode($oMollieSessionAddress->country);

        $country->load($countryId);
        if ($country->oxcountry__oxactive->value == 1) {
            return true;
        }
        return false;
    }

    /**
     * Handles the return of the customer from PayPal to the shop
     *
     * @return void
     */
    public function handlePayPalReturn()
    {
        $sSessionId = Registry::getSession()->getVariable('mollie_ppe_sessionId');
        if (empty($sSessionId)) {
            $this->handlePayPalExpressError(Registry::getLang()->translateString("MOLLIE_PAYPAL_EXPRESS_SESSIONID_MISSING")); // redirects to basket with error message, so execution ends here
        }

        $oRequestLog = oxNew(RequestLog::class);
        try {
            $oSession = $this->getSessionFromMollie($sSessionId);

            $oRequestLog->logRequest(['sessionId' => $sSessionId], $oSession, null, $this->getConfig()->getShopId());
        } catch (\Exception $exc) {
            $oRequestLog->logExceptionResponse(['sessionId' => $sSessionId], $exc->getCode(), $exc->getMessage(), PayPalExpress::OXID, null, $this->getConfig()->getShopId());
            $this->handlePayPalExpressError(Registry::getLang()->translateString("MOLLIE_PAYPAL_EXPRESS_NO_SESSION_INFO")); // redirects to basket with error message, so execution ends here
        }

        if ($this->isDeliveryCountryAvailable($oSession->shippingAddress) === false) {
            $this->handlePayPalExpressError(Registry::getLang()->translateString("MOLLIE_PAYPAL_DELIVERY_COUNTRY_INACTIVE")); // redirects to basket with error message, so execution ends here
        }

        Registry::getSession()->setVariable('mollie_ppe_authenticationId', $oSession->authenticationId);

        $oUser = UserHelper::getInstance()->getMollieSessionUser($oSession->shippingAddress);

        $oBasket = $this->getPayPalExpressBasket($oUser);
        $oBasket->calculateBasket(true);

        Registry::getConfig()->getActiveView()->setUser($oUser);
        $oBasket->setBasketUser($oUser);

        Registry::getSession()->setVariable('usr', $oUser->getId());
        Registry::getSession()->setBasket($oBasket);

        $sRedirectUrl = Registry::getConfig()->getSslShopUrl()."?cl=order";
        Registry::getUtils()->redirect($sRedirectUrl);
    }

    /**
     * Handles a cancellation of the PayPal Express process by the user
     *
     * @return void
     */
    public function handlePayPalCancel()
    {
        $sRedirectUrl = Registry::getConfig()->getSslShopUrl()."?cl=basket";

        PayPalExpressHelper::getInstance()->mollieCancelPayPalExpress();

        Registry::getUtils()->redirect($sRedirectUrl);
    }
}