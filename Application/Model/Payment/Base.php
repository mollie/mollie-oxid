<?php

namespace Mollie\Payment\Application\Model\Payment;

use Mollie\Api\Exceptions\ApiException;
use Mollie\Payment\Application\Model\PaymentConfig;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Application\Model\Order;
use Mollie\Payment\Application\Helper\Payment;

abstract class Base
{
    /**
     * Payment id in the oxid shop
     *
     * @var string
     */
    protected $sOxidPaymentId = null;

    /**
     * Method code used for API request
     *
     * @var string
     */
    protected $sMolliePaymentCode = null;

    /**
     * Loaded payment config
     *
     * @var array
     */
    protected $aPaymentConfig = null;

    /**
     * Determines if the payment methods only supports the order API
     *
     * @var bool
     */
    protected $blIsOnlyOrderApiSupported = false;

    /**
     * Determines if the payment methods supports the order expiry mechanism
     *
     * @var bool
     */
    protected $blIsOrderExpirySupported = true;

    /**
     * Determines if the payment methods has to add a redirect url to the request
     *
     * @var bool
     */
    protected $blIsRedirectUrlNeeded = true;

    /**
     * Determines the default API to be used when not configured differently
     *
     * @var string
     */
    protected $sDefaultApi = 'payment';

    /**
     * Determines custom config template if existing, otherwise false
     *
     * @var string|bool
     */
    protected $sCustomConfigTemplate = false;

    /**
     * Determines custom frontend template if existing, otherwise false
     *
     * @var string|bool
     */
    protected $sCustomFrontendTemplate = false;

    /**
     * Determines if the payment method is hidden at first when payment list is displayed
     *
     * @var bool
     */
    protected $blIsMethodHiddenInitially = false;

    /**
     * Determines if the payment method can be display in the payment list in checkout
     *
     * @var bool
     */
    protected $blShowInPaymentList = true;

    /**
     * Array with country-codes the payment method is restricted to
     * If property is set to false it is available to all countries
     *
     * @var array|false
     */
    protected $aBillingCountryRestrictedTo = false;

    /**
     * Determines if the payment method is only available for B2B orders
     * B2B mode is assumed when the company field in the billing address is filled
     *
     * @var bool
     */
    protected $blIsOnlyB2BSupported = false;

    /**
     * Return Oxid payment id
     *
     * @return string
     */
    public function getOxidPaymentId()
    {
        return $this->sOxidPaymentId;
    }

    /**
     * Return Mollie payment code
     *
     * @return string
     */
    public function getMolliePaymentCode()
    {
        return $this->sMolliePaymentCode;
    }

    /**
     * Returns if the payment methods only supports the order API
     *
     * @return bool
     */
    public function isOnlyOrderApiSupported()
    {
        return $this->blIsOnlyOrderApiSupported;
    }

    /**
     * Returns array of billing country restrictions
     *
     * @return bool
     */
    public function getBillingCountryRestrictedCountries()
    {
        return $this->aBillingCountryRestrictedTo;
    }

    /**
     * Returns if the payment methods supports the order expiry mechanism
     *
     * @return bool
     */
    public function isOrderExpirySupported()
    {
        return $this->blIsOrderExpirySupported;
    }

    /**
     * Returns if the payment method only supports B2B orders
     *
     * @return bool
     */
    public function isOnlyB2BSupported()
    {
        return $this->blIsOnlyB2BSupported;
    }

    /**
     * Returns if the payment methods needs to add the redirect url
     *
     * @param  Order $oOrder
     * @return bool
     */
    public function isRedirectUrlNeeded(Order $oOrder)
    {
        return $this->blIsRedirectUrlNeeded;
    }

    /**
     * Returns custom config template or false if not existing
     *
     * @return bool|string
     */
    public function getCustomConfigTemplate()
    {
        return $this->sCustomConfigTemplate;
    }

    /**
     * Returns custom frontend template or false if not existing
     *
     * @return bool|string
     */
    public function getCustomFrontendTemplate()
    {
        return $this->sCustomFrontendTemplate;
    }

    /**
     * Returns if the payment methods has to be hidden initially
     *
     * @return bool
     */
    public function isMollieMethodHiddenInitially()
    {
        return $this->blIsMethodHiddenInitially;
    }

    /**
     * Returns if the payment method can be display in the payment list in checkout
     *
     * @return bool
     */
    public function isMethodDisplayableInPaymentList()
    {
        return $this->blShowInPaymentList;
    }

    /**
     * Loads payment config if not loaded, otherwise returns preloaded config
     *
     * @return array
     */
    public function getPaymentConfig()
    {
        if ($this->aPaymentConfig === null) {
            $oPaymentConfig = oxNew(PaymentConfig::class);
            $this->aPaymentConfig = $oPaymentConfig->getPaymentConfig($this->getOxidPaymentId());
        }
        return $this->aPaymentConfig;
    }

    /**
     * Returns order API endpoint
     * Mode and API method can be given as parameter for working with orders already created, since config could be changed
     *
     * @param  string|bool $sMode
     * @param  string|bool $sApiMethod
     * @return \Mollie\Api\Endpoints\EndpointAbstract
     */
    public function getApiEndpoint($sMode = false, $sApiMethod = false)
    {
        if ($sApiMethod === false) {
            $sApiMethod = $this->getApiMethod();
        }
        if ($sApiMethod == 'order') {
            return Payment::getInstance()->loadMollieApi($sMode)->orders;
        }
        return Payment::getInstance()->loadMollieApi($sMode)->payments;
    }

    /**
     * Returns matching api endpoint the given order was created in
     *
     * @param  Order $oOrder
     * @return \Mollie\Api\Endpoints\EndpointAbstract
     */
    public function getApiEndpointByOrder($oOrder)
    {
        $sMode = $oOrder->oxorder__molliemode->value;
        if (empty($sMode)) {
            $sMode = false;
        }
        $sApi = $oOrder->oxorder__mollieapi->value;
        if (empty($sApi)) {
            $sApi = false;
        }
        return $this->getApiEndpoint($sMode, $sApi);
    }

    /**
     * Return request model based in the configured api method
     *
     * @param  Order $oOrder
     * @return \Mollie\Payment\Application\Model\Request\Base
     */
    public function getApiRequestModel($oOrder = false)
    {
        if ($this->getApiMethod($oOrder) == 'order') {
            return oxNew(\Mollie\Payment\Application\Model\Request\Order::class);
        }
        return oxNew(\Mollie\Payment\Application\Model\Request\Payment::class);
    }

    /**
     * Return request model based in the configured api method
     *
     * @param  Order $oOrder
     * @return \Mollie\Payment\Application\Model\TransactionHandler\Base
     */
    public function getTransactionHandler($oOrder = false)
    {
        if ($this->getApiMethod($oOrder) == 'order') {
            return oxNew(\Mollie\Payment\Application\Model\TransactionHandler\Order::class);
        }
        return oxNew(\Mollie\Payment\Application\Model\TransactionHandler\Payment::class);
    }

    /**
     * Return configured api method or default value if not yet configured
     *
     * @param  Order $oOrder
     * @return string
     */
    public function getApiMethod($oOrder = false)
    {
        if ($oOrder !== false && !empty($oOrder->oxorder__mollieapi->value)) {
            return $oOrder->oxorder__mollieapi->value;
        }

        $sApiMethod = $this->getConfigParam('api');
        if (empty($sApiMethod)) {
            $sApiMethod = $this->sDefaultApi;
            if ($this->blIsOnlyOrderApiSupported === true) {
                $sApiMethod = 'order';
            }
        }
        return $sApiMethod;
    }

    /**
     * Returns configured expiryDay count or default value if not saved yet
     *
     * @return int
     */
    public function getExpiryDays()
    {
        $iExpiryDays = $this->getConfigParam('expiryDays');
        if (!empty($iExpiryDays)) {
            return $iExpiryDays;
        }
        return 30; // default value
    }

    /**
     * Returns configured expiryDay count or default value if not saved yet
     *
     * @return int
     */
    public function getCaptureDays()
    {
        $iExpiryDays = $this->getConfigParam('captureDays');
        if (!empty($iExpiryDays)) {
            return $iExpiryDays;
        }
        return 7; // default value
    }

    /**
     * Gather issuer info from Mollie API
     *
     * @param array $aDynValue
     * @param string $sInputName
     * @return array
     */
    public function getIssuers($aDynValue, $sInputName)
    {
        $aReturn = [];

        if (!isset($aDynValue[$sInputName]) && $this->getConfigParam('issuer_list_type') == 'dropdown') {
            $aReturn[''] = ['title' => Registry::getLang()->translateString('MOLLIE_PLEASE_SELECT'), 'pic' => ''];
        }

        try {
            $aIssuersList = Payment::getInstance()->loadMollieApi()->methods->get($this->sMolliePaymentCode, ["include" => "issuers"])->issuers;
        } catch (\Exception $exc) { // Mollie API returned an exception like "The payment method is not active in your website profile."
            return [];
        }

        foreach ($aIssuersList as $oIssuer) {
            $aReturn[$oIssuer->id] = ['title' => $oIssuer->name, 'pic' => $oIssuer->image->size2x];
        }
        return $aReturn;
    }

    /**
     * Get dynvalue parameters from session or request
     *
     * @return mixed|null
     */
    protected function getDynValueParameters()
    {
        $aDynvalue = Registry::getSession()->getVariable('dynvalue');
        if (empty($aDynvalue)) {
            $aDynvalue = Registry::getRequest()->getRequestParameter('dynvalue');
        }
        return $aDynvalue;
    }

    /**
     * Return dynvalue parameter
     *
     * @param string $sParam
     * @return string|false
     */
    protected function getDynValueParameter($sParam)
    {
        $aDynValue = $this->getDynValueParameters();
        if (isset($aDynValue[$sParam])) {
            return $aDynValue[$sParam];
        }
        return false;
    }

    /**
     * Determines if payment method is activated for this Mollie account
     *
     * @param string|false $sBillingCountryCode
     * @return bool
     */
    public function isMolliePaymentActive($sBillingCountryCode = false)
    {
        $aInfo = Payment::getInstance()->getMolliePaymentInfo(false, false, $sBillingCountryCode);
        if (isset($aInfo[$this->sMolliePaymentCode])) {
            return true;
        }
        return false;
    }

    /**
     * Returnes minimum order sum for Mollie payment type to be usable
     *
     * @return object|false
     */
    public function getMollieFromAmount()
    {
        $aInfo = Payment::getInstance()->getMolliePaymentInfo();
        if (isset($aInfo[$this->sMolliePaymentCode]['minAmount'])) {
            return $aInfo[$this->sMolliePaymentCode]['minAmount'];
        }
        return false;
    }

    /**
     * Returnes maximum order sum for Mollie payment type to be usable
     *
     * @return object|false
     */
    public function getMollieToAmount()
    {
        $aInfo = Payment::getInstance()->getMolliePaymentInfo();
        if (!empty(isset($aInfo[$this->sMolliePaymentCode]['maxAmount']))) {
            return $aInfo[$this->sMolliePaymentCode]['maxAmount'];
        }
        return false;
    }

    /**
     * Checks if given basket brutto price is withing the payment sum limitations of the current Mollie payment type
     *
     * @param double $dBasketBruttoPrice
     * @return bool
     */
    public function mollieIsBasketSumInLimits($dBasketBruttoPrice)
    {
        $oFrom = $this->getMollieFromAmount();
        if ($oFrom && $dBasketBruttoPrice < $oFrom->value) {
            return false;
        }

        $oTo = $this->getMollieToAmount();
        if ($oTo && $dBasketBruttoPrice > $oTo->value) {
            return false;
        }
        return true;
    }

    /**
     * Checks if the payment method is available for the current billing country
     *
     * @param  string $sBillingCountryCode
     * @return bool
     */
    public function mollieIsMethodAvailableForCountry($sBillingCountryCode)
    {
        $aCountryRestrictions = $this->getBillingCountryRestrictedCountries();
        if ($aCountryRestrictions === false || in_array($sBillingCountryCode, $aCountryRestrictions) === true) {
            return true;
        }
        return false;
    }

    /**
     * Returns alternative logo url
     *
     * @return string
     */
    public function getAlternativeLogoUrl()
    {
        $sConfVar = "sMollie".$this->getOxidPaymentId().'AltLogo';
        $sAltLogo = Registry::getConfig()->getShopConfVar($sConfVar);
        if (!empty($sAltLogo)) {
            return Registry::getConfig()->getActiveView()->getViewConfig()->getModuleUrl('molliepayment', 'out/img/'.$sAltLogo);
        }
        return false;
    }

    /**
     * Returns URL of the payment method picture
     *
     * @return string|bool
     */
    public function getMolliePaymentMethodPic()
    {
        $sAltLogoUrl = $this->getAlternativeLogoUrl();
        if ($sAltLogoUrl !== false) {
            return $sAltLogoUrl;
        }

        $aInfo = Payment::getInstance()->getMolliePaymentInfo();
        if (isset($aInfo[$this->sMolliePaymentCode])) {
            return $aInfo[$this->sMolliePaymentCode]['pic'];
        }
        return false;
    }

    /**
     * Return parameters specific to the given payment type, if existing
     *
     * @param Order $oOrder
     * @return array
     */
    public function getPaymentSpecificParameters(Order $oOrder)
    {
        return [];
    }

    /**
     * Returns config value
     *
     * @param string $sParameterName
     * @return string
     */
    public function getConfigParam($sParameterName)
    {
        $aPaymentConfig = $this->getPaymentConfig();

        if (isset($aPaymentConfig[$sParameterName])) {
            return $aPaymentConfig[$sParameterName];
        }
        return false;
    }

    /**
     * Method to perform certain actions when the API call failed
     *
     * @param  ApiException $exc
     * @return void
     */
    public function handlePaymentError(ApiException $exc)
    {
        // do nothing here - method can be overloaded by child classes
    }
}
