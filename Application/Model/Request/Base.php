<?php

namespace Mollie\Payment\Application\Model\Request;

use Mollie\Payment\Application\Model\RequestLog;
use OxidEsales\Eshop\Application\Model\Order as CoreOrder;
use OxidEsales\Eshop\Core\Registry;

abstract class Base
{
    /**
     * Array or request parameters
     *
     * @var array
     */
    protected $aParameters = [];

    /**
     * Determines if the extended address is needed in the params
     *
     * @var bool
     */
    protected $blNeedsExtendedAddress = false;

    /**
     * Returns collected request parameters
     *
     * @return array
     */
    protected function getParameters()
    {
        return $this->aParameters;
    }

    /**
     * Add parameter to request
     *
     * @param string $sKey
     * @param string|array $mValue
     * @return void
     */
    public function addParameter($sKey, $mValue)
    {
        $this->aParameters[$sKey] = $mValue;
    }

    /**
     * Get amount array
     *
     * @param CoreOrder $oOrder
     * @param double $dAmount
     * @return array
     */
    protected function getAmountParameters(CoreOrder $oOrder, $dAmount)
    {
        return [
            'currency' => $oOrder->oxorder__oxcurrency->value,
            'value' => number_format($dAmount, 2, '.', ''),
        ];
    }

    /**
     * Loads country object and return country iso code
     *
     * @param string $sCountryId
     * @return string
     */
    protected function getCountryCode($sCountryId)
    {
        $oCountry = oxNew('oxcountry');
        $oCountry->load($sCountryId);
        return $oCountry->oxcountry__oxisoalpha2->value;
    }

    /**
     * Convert region id into region title
     *
     * @param string $sRegionId
     * @return string
     */
    protected function getRegionTitle($sRegionId)
    {
        $oState = oxNew('oxState');
        return $oState->getTitleById($sRegionId);
    }

    /**
     * Return billing address parameters
     *
     * @param CoreOrder $oOrder
     * @return array
     */
    protected function getBillingAddressParameters(CoreOrder $oOrder)
    {
        $aReturn = [
            'streetAndNumber' => trim($oOrder->oxorder__oxbillstreet->value.' '.$oOrder->oxorder__oxbillstreetnr->value),
            'postalCode' => $oOrder->oxorder__oxbillzip->value,
            'city' => $oOrder->oxorder__oxbillcity->value,
            'country' => $this->getCountryCode($oOrder->oxorder__oxbillcountryid->value),
        ];
        if (!empty((string)$oOrder->oxorder__oxbillstateid->value)) {
            $aReturn['region'] = $this->getRegionTitle($oOrder->oxorder__oxbillstateid->value);
        }
        if ($this->blNeedsExtendedAddress === true) {
            $aReturn['title'] = Registry::getLang()->translateString($oOrder->oxorder__oxbillsal->value);
            $aReturn['givenName'] = $oOrder->oxorder__oxbillfname->value;
            $aReturn['familyName'] = $oOrder->oxorder__oxbilllname->value;
            $aReturn['email'] = $oOrder->oxorder__oxbillemail->value;
        }
        return $aReturn;
    }

    /**
     * Return shipping address parameters
     *
     * @param CoreOrder $oOrder
     * @return array
     */
    protected function getShippingAddressParameters(CoreOrder $oOrder)
    {
        $aReturn = [
            'streetAndNumber' => trim($oOrder->oxorder__oxdelstreet->value.' '.$oOrder->oxorder__oxdelstreetnr->value),
            'postalCode' => $oOrder->oxorder__oxdelzip->value,
            'city' => $oOrder->oxorder__oxdelcity->value,
            'country' => $this->getCountryCode($oOrder->oxorder__oxdelcountryid->value),
        ];
        if (!empty((string)$oOrder->oxorder__oxbillstateid->value)) {
            $aReturn['region'] = $this->getRegionTitle($oOrder->oxorder__oxdelstateid->value);
        }
        if ($this->blNeedsExtendedAddress === true) {
            $aReturn['title'] = Registry::getLang()->translateString($oOrder->oxorder__oxdelsal->value);
            $aReturn['givenName'] = $oOrder->oxorder__oxdelfname->value;
            $aReturn['familyName'] = $oOrder->oxorder__oxdellname->value;
            $aReturn['email'] = $oOrder->oxorder__oxbillemail->value; // there is no explicit delivery email address
        }
        return $aReturn;
    }

    /**
     * Return metadata parameters
     *
     * @param CoreOrder $oOrder
     * @return array
     */
    protected function getMetadataParameters(CoreOrder $oOrder)
    {
        return [
            'order_id' => $oOrder->getId(),
            'store_id' => $oOrder->getShopId(),
            #'payment_token' => uniqid(), // which role does this field play?
        ];
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
     * Generates locale string
     * Oxid doesnt have a locale logic, so solving it with by using the language files
     *
     * @return string
     */
    protected function getLocale()
    {
        $sLocale = Registry::getLang()->translateString('MOLLIE_LOCALE');
        if (Registry::getLang()->isTranslated() === false) {
            $sLocale = 'en_US'; // default
        }
        return $sLocale;
    }

    /**
     * Add needed parameters to the API request
     *
     * @param CoreOrder $oOrder
     * @param double $dAmount
     * @return void
     */
    protected function addRequestParameters(CoreOrder $oOrder, $dAmount)
    {
        $this->addParameter('method', $oOrder->mollieGetPaymentModel()->getMolliePaymentCode());
        $this->addParameter('amount', $this->getAmountParameters($oOrder, $dAmount));

        $this->addParameter('redirectUrl', $this->getRedirectUrl());
        $this->addParameter('webhookUrl', $this->getWebhookUrl());

        $this->addParameter('metadata', $this->getMetadataParameters($oOrder));

        $this->addParameter('billingAddress', $this->getBillingAddressParameters($oOrder));
        if ($oOrder->oxorder__oxdellname->value != '') {
            $this->addParameter('shippingAddress', $this->getShippingAddressParameters($oOrder));
        }

        $this->addParameter('locale', $this->getLocale());
    }

    /**
     * Execute Request to Mollie API and return Response
     *
     * @param CoreOrder $oOrder
     * @param double $dAmount
     * @return \Mollie\Api\Resources\Payment
     */
    public function sendRequest(CoreOrder $oOrder, $dAmount)
    {
        $this->addRequestParameters($oOrder, $dAmount);
        $oResponse = $this->getApiEndpoint()->create($this->getParameters());

        $requestLogger = oxNew(RequestLog::class);
        $requestLogger->logRequest($this->getParameters(), $oResponse);

        return $oResponse;
    }

    /**
     * Logs an error response from a request, coming in form of an exception
     *
     * @param string $sCode
     * @param string $sMessage
     * @param string $method
     */
    public function logExceptionResponse($sCode, $sMessage, $method = '')
    {
        $requestLogger = oxNew(RequestLog::class);
        $aResponse = [
            'resource' => $method,
            'status' => 'ERROR',
            'code' => $sCode,
            'customMessage' => $sMessage
        ];

        $requestLogger->logRequest($this->getParameters(), (object) $aResponse);
    }
}
