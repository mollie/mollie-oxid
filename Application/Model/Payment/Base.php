<?php

namespace Mollie\Payment\Application\Model\Payment;

use Mollie\Payment\Application\Model\PaymentConfig;

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
     * Returns custom config template or false if not existing
     *
     * @return bool|string
     */
    public function getCustomConfigTemplate()
    {
        return $this->sCustomConfigTemplate;
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
     * Return configured api method or default value if not yet configured
     *
     * @return string
     */
    public function getApiMethod()
    {
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
}
