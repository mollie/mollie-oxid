<?php

namespace Mollie\Payment\extend\Application\Model;

use Mollie\Api\Exceptions\ApiException;
use OxidEsales\Eshop\Application\Model\Order as CoreOrder;
use OxidEsales\Eshop\Core\Registry;
use Mollie\Payment\Application\Helper\Payment as PaymentHelper;

class PaymentGateway extends PaymentGateway_parent
{
    /**
     * OXID URL parameters to copy from initial order execute request
     *
     * @var array
     */
    protected $aMollieUrlCopyParameters = [
        'stoken',
        'sDeliveryAddressMD5',
        'oxdownloadableproductsagreement',
        'oxserviceproductsagreement',
    ];

    /**
     * Initiate Mollie payment functionality for Mollie payment types
     *
     * Executes payment, returns true on success.
     *
     * @param double $dAmount Goods amount
     * @param object $oOrder  User ordering object
     *
     * @extend executePayment
     * @return bool
     */
    public function executePayment($dAmount, &$oOrder)
    {
        if(!PaymentHelper::getInstance()->isMolliePaymentMethod($oOrder->oxorder__oxpaymenttype->value)) {
            return parent::executePayment($dAmount, $oOrder);
        }
        return $this->handleMolliePayment($oOrder, $dAmount);;
    }

    /**
     * Collect parameters from the current order execute call and add them to the return URL
     * Also add parameters needed for the return process
     *
     * @return string
     */
    protected function mollieGetAdditionalParameters()
    {
        $oRequest = Registry::getRequest();
        $oSession = Registry::getSession();

        $sAddParams = '';

        foreach ($this->aMollieUrlCopyParameters as $sParamName) {
            $sValue = $oRequest->getRequestEscapedParameter($sParamName);
            if (!empty($sValue)) {
                $sAddParams .= '&'.$sParamName.'='.$sValue;
            }
        }

        $sSid = $oSession->sid(true);
        if ($sSid != '') {
            $sAddParams .= '&'.$sSid;
        }

        if (!$oRequest->getRequestEscapedParameter('stoken')) {
            $sAddParams .= '&stoken='.$oSession->getSessionChallengeToken();
        }
        $sAddParams .= '&ord_agb=1';
        $sAddParams .= '&rtoken='.$oSession->getRemoteAccessToken();

        return $sAddParams;
    }

    /**
     * Generate a return url with all necessary return flags
     *
     * @return string
     */
    protected function getRedirectUrl()
    {
        $sBaseUrl = Registry::getConfig()->getCurrentShopUrl().'index.php?cl=order&fnc=handleMollieReturn';

        return $sBaseUrl.$this->mollieGetAdditionalParameters();
    }

    /**
     * Execute Mollie API request and redirect to Mollie for payment
     *
     * @param CoreOrder $oOrder
     * @param double $dAmount
     * @return bool
     */
    protected function handleMolliePayment(CoreOrder &$oOrder, $dAmount)
    {
        $oOrder->mollieSetOrderNumber();

        try {
            $oResponse = $oOrder->mollieGetPaymentModel()->getApiRequestModel()->sendRequest($oOrder, $dAmount, $this->getRedirectUrl());
            $oOrder->mollieSetTransactionId($oResponse->id);

            $sPaymentUrl = $oResponse->getCheckoutUrl();
            if (!empty($sPaymentUrl)) {
                Registry::getSession()->setVariable('mollieIsRedirected', true);
                Registry::getUtils()->redirect($sPaymentUrl);
            }
        } catch(ApiException $exc) {
            $this->_iLastErrorNo = $exc->getCode();
            $this->_sLastError = $exc->getMessage();
            return false;
        }
        return true;
    }
}
