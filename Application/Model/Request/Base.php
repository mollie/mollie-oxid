<?php

namespace Mollie\Payment\Application\Model\Request;

use Mollie\Payment\Application\Model\RequestLog;
use OxidEsales\Eshop\Application\Model\Order as CoreOrder;
use OxidEsales\Eshop\Core\Registry;
use Mollie\Api\Exceptions\ApiException;

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
     * Return the Mollie webhook url
     *
     * @return string
     */
    protected function getWebhookUrl()
    {
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
     * @param string $sReturnUrl
     * @return void
     */
    protected function addRequestParameters(CoreOrder $oOrder, $dAmount, $sReturnUrl)
    {
        $oPaymentModel = $oOrder->mollieGetPaymentModel();

        $this->addParameter('method', $oPaymentModel->getMolliePaymentCode());
        $this->addParameter('amount', $this->getAmountParameters($oOrder, $dAmount));

        $this->addParameter('redirectUrl', $sReturnUrl);
        $this->addParameter('webhookUrl', $this->getWebhookUrl());

        $this->addParameter('metadata', $this->getMetadataParameters($oOrder));

        $this->addParameter('billingAddress', $this->getBillingAddressParameters($oOrder));
        if ($oOrder->oxorder__oxdellname->value != '') {
            $this->addParameter('shippingAddress', $this->getShippingAddressParameters($oOrder));
        }

        $this->addParameter('locale', $this->getLocale());

        $this->aParameters = array_merge($this->aParameters, $oPaymentModel->getPaymentSpecificParameters($oOrder));
    }

    /**
     * Execute Request to Mollie API and return Response
     *
     * @param CoreOrder $oOrder
     * @param double $dAmount
     * @param string $sReturnUrl
     * @return \Mollie\Api\Resources\Payment
     * @throws ApiException
     */
    public function sendRequest(CoreOrder $oOrder, $dAmount, $sReturnUrl)
    {
        $this->addRequestParameters($oOrder, $dAmount, $sReturnUrl);

        $oRequestLog = oxNew(RequestLog::class);
        try {
            $oResponse = $oOrder->mollieGetPaymentModel()->getApiEndpoint()->create($this->getParameters());

            $oRequestLog->logRequest($this->getParameters(), $oResponse);
        } catch (ApiException $exc) {
            $oRequestLog->logExceptionResponse($this->getParameters(), $exc->getCode(), $exc->getMessage(), $oOrder->mollieGetPaymentModel()->getApiMethod());
            throw $exc;
        }

        return $oResponse;
    }
}
