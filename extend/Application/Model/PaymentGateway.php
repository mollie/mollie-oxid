<?php

namespace Mollie\Payment\extend\Application\Model;

use Mollie\Payment\Application\Model\PaymentConfig;
use Mollie\Api\Exceptions\ApiException;
use OxidEsales\Eshop\Application\Model\Order as CoreOrder;
use OxidEsales\Eshop\Core\Registry;
use Mollie\Payment\Application\Helper\Payment as PaymentHelper;

class PaymentGateway extends PaymentGateway_parent
{

    /**
     * Overrides standard oxid finalizeOrder method if the used payment method belongs to PAYONE.
     * Return parent's return if payment method is no PAYONE method
     *
     * Executes payment, returns true on success.
     *
     * @param double $dAmount Goods amount
     * @param object &$oOrder User ordering object
     *
     * @extend executePayment
     * @return bool
     */
    public function executePayment( $dAmount, &$oOrder )
    {
        if(!PaymentHelper::getInstance()->isMolliePaymentMethod($oOrder->oxorder__oxpaymenttype->value)) {
            return parent::executePayment($dAmount, $oOrder);
        }

        $this->handleMolliePayment($dAmount, $oOrder);

        return false; // return false for for trying faster
    }

    /**
     * Generate a return url with all necessary return flags
     *
     * @return string
     */
    protected function getRedirectUrl()
    {
        $oConfig = Registry::getConfig();
        $oRequest = Registry::getRequest();
        $oSession = Registry::getSession();

        $sSid = $oSession->sid(true);
        if ($sSid != '') {
            $sSid = '&' . $sSid;
        }

        $sAddParams = '&fnc=execute&rtoken='.$oSession->getRemoteAccessToken();

        if ($oRequest->getRequestEscapedParameter('sDeliveryAddressMD5')) {
            $sAddParams .= '&sDeliveryAddressMD5='.$oRequest->getRequestEscapedParameter('sDeliveryAddressMD5');
        }

        /*
        $blDownloadableProductsAgreement = $oRequest->getRequestEscapedParameter('oxdownloadableproductsagreement');
        if ($blDownloadableProductsAgreement) {
            $sAddParams .= '&oxdownloadableproductsagreement=1';
        }

        $blServiceProductsAgreement = $oRequest->getRequestEscapedParameter('oxserviceproductsagreement');
        if ($blServiceProductsAgreement) {
            $sAddParams .= '&oxserviceproductsagreement=1'; // rewrite for oxserviceproductsagreement-param because of length-restriction
        }
*/
        $sSuccessUrl = $oConfig->getCurrentShopUrl().'index.php?cl=order&ord_agb=1&stoken='.$oRequest->getRequestEscapedParameter('stoken').$sSid.$sAddParams;

        return $sSuccessUrl;
    }

    /**
     * Return the Mollie webhook url
     *
     * @return string
     */
    protected function getWebhookUrl()
    {
        return 'https://robert.demoshop.fatchip.de/webhook.php';///@TODO Take this out
        return Registry::getConfig()->getCurrentShopUrl().'index.php?cl=mollieWebhook';
    }

    /**
     * Execute Mollie API request and redirect to Mollie for payment
     *
     * @param double $dAmount
     * @param CoreOrder $oOrder
     * @return bool
     */
    protected function handleMolliePayment($dAmount, CoreOrder &$oOrder)
    {
        $oCountry = oxNew('oxcountry');
        $oCountry->load($oOrder->oxorder__oxbillcountryid->value);

        $oOrder->mollieSetOrderNumber();

        $oMolliePaymentModel = $oOrder->mollieGetPaymentModel();

        $paymentData = [
            'amount' => [
                'currency' => $oOrder->oxorder__oxcurrency->value,
                'value' => number_format($dAmount, 2, '.', ''),
            ],
            'description' => 'OrderNr: '.$oOrder->oxorder__oxordernr->value,
            'redirectUrl' => $this->getRedirectUrl(),
            'webhookUrl' => $this->getWebhookUrl(),
            'method' => $oMolliePaymentModel->getMolliePaymentCode(),
            'issuer' => '',
            'metadata' => [
                'order_id' => $oOrder->getId(),
                'store_id' => $oOrder->getShopId(),
                'payment_token' => uniqid(),
            ],
            'locale' => '',
            'billingAddress' => [
                'streetAndNumber' => trim($oOrder->oxorder__oxbillstreet->value.' '.$oOrder->oxorder__oxbillstreetnr->value),
                'postalCode' => $oOrder->oxorder__oxbillzip->value,
                'city' => $oOrder->oxorder__oxbillcity->value,
                'country' => $oCountry->oxcountry__oxisoalpha2->value,
                #'region' => '',
            ],
        ];

        if ($oOrder->oxorder__oxdellname->value != '') {
            $paymentData['shippingAddress'] = [
                'streetAndNumber' => trim($oOrder->oxorder__oxdelstreet->value.' '.$oOrder->oxorder__oxdelstreetnr->value),
                'postalCode' => $oOrder->oxorder__oxdelzip->value,
                'city' => $oOrder->oxorder__oxdelcity->value,
                'country' => $oCountry->oxcountry__oxisoalpha2->value,
                #'region' => '',
            ];
        }

        ob_start();
        print_r($paymentData);
        error_log(ob_get_contents());
        ob_end_clean();
        
        try {
            $oApi = PaymentHelper::getInstance()->loadMollieApi();

            $payment = $oApi->payments->create($paymentData);
            $paymentUrl = $payment->getCheckoutUrl();

            $oOrder->mollieSetTransactionId($payment->id);

            if (!empty($paymentUrl)) {
                Registry::getUtils()->redirect($paymentUrl);
            }
        } catch(ApiException $exc) {
            $this->_iLastErrorNo = $exc->getCode();
            $this->_sLastError = $exc->getMessage();
        }

        return true;
    }
}
