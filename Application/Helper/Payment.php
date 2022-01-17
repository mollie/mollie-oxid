<?php

namespace Mollie\Payment\Application\Helper;

use Mollie\Payment\Application\Model\Payment\Base;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Core\Module\Module;

class Payment
{
    /**
     * @var Payment
     */
    protected static $oInstance = null;

    /**
     * Array with information about all enabled Mollie payment types
     *
     * @var array|null
     */
    protected $aPaymentInfo = null;

    /**
     * List of all available Mollie payment methods
     *
     * @var array
     */
    protected $aPaymentMethods = array(
        'molliebancontact'      => array('title' => 'Bancontact',       'model' => \Mollie\Payment\Application\Model\Payment\Bancontact::class),
        'molliebanktransfer'    => array('title' => 'Banktransfer',     'model' => \Mollie\Payment\Application\Model\Payment\Banktransfer::class),
        'molliebelfius'         => array('title' => 'Belfius',          'model' => \Mollie\Payment\Application\Model\Payment\Belfius::class),
        'molliecreditcard'      => array('title' => 'Credit Card',      'model' => \Mollie\Payment\Application\Model\Payment\Creditcard::class),
        'mollieeps'             => array('title' => 'EPS Österreich',   'model' => \Mollie\Payment\Application\Model\Payment\Eps::class),
        'molliegiftcard'        => array('title' => 'Giftcard',         'model' => \Mollie\Payment\Application\Model\Payment\Giftcard::class),
        'molliegiropay'         => array('title' => 'Giropay',          'model' => \Mollie\Payment\Application\Model\Payment\Giropay::class),
        'mollieideal'           => array('title' => 'iDeal',            'model' => \Mollie\Payment\Application\Model\Payment\Ideal::class),
        'molliekbc'             => array('title' => 'KBC',              'model' => \Mollie\Payment\Application\Model\Payment\Kbc::class),
        'mollieklarnapaylater'  => array('title' => 'Klarna Pay Later', 'model' => \Mollie\Payment\Application\Model\Payment\KlarnaPayLater::class),
        'mollieklarnapaynow'    => array('title' => 'Klarna Pay Now',   'model' => \Mollie\Payment\Application\Model\Payment\KlarnaPayNow::class),
        'mollieklarnasliceit'   => array('title' => 'Klarna Slice It',  'model' => \Mollie\Payment\Application\Model\Payment\KlarnaSliceIt::class),
        'molliepaypal'          => array('title' => 'Paypal',           'model' => \Mollie\Payment\Application\Model\Payment\PayPal::class),
        'molliepaysafecard'     => array('title' => 'Paysafecard',      'model' => \Mollie\Payment\Application\Model\Payment\Paysafecard::class),
        'molliesofort'          => array('title' => 'Sofort',           'model' => \Mollie\Payment\Application\Model\Payment\Sofort::class),
        'mollieapplepay'        => array('title' => 'Apple Pay',        'model' => \Mollie\Payment\Application\Model\Payment\ApplePay::class),
        'mollieprzelewy24'      => array('title' => 'Przelewy24',       'model' => \Mollie\Payment\Application\Model\Payment\Przelewy24::class),
        'molliemybank'          => array('title' => 'MyBank',           'model' => \Mollie\Payment\Application\Model\Payment\MyBank::class),
    );

    /**
     * Create singleton instance of payment helper
     *
     * @return Payment
     */
    static function getInstance()
    {
        if (self::$oInstance === null) {
            self::$oInstance = oxNew(self::class);
        }
        return self::$oInstance;
    }

    /**
     * Resets singleton class
     * Needed for unit testing
     *
     * @return void
     */
    public static function destroyInstance()
    {
        self::$oInstance = null;
    }

    /**
     * Return all available Mollie payment methods
     *
     * @return array
     */
    public function getMolliePaymentMethods()
    {
        $aPaymentMethods = array();
        foreach ($this->aPaymentMethods as $sPaymentId => $aPaymentMethodInfo) {
            $aPaymentMethods[$sPaymentId] = $aPaymentMethodInfo['title'];
        }
        return $aPaymentMethods;
    }

    /**
     * Determine if given paymentId is a Mollie payment method
     *
     * @param string $sPaymentId
     * @return bool
     */
    public function isMolliePaymentMethod($sPaymentId)
    {
        if (isset($this->aPaymentMethods[$sPaymentId])) {
            return true;
        }
        return false;
    }

    /**
     * Returns payment model for given paymentId
     *
     * @param string $sPaymentId
     * @return Base
     * @throws \Exception
     */
    public function getMolliePaymentModel($sPaymentId)
    {
        if ($this->isMolliePaymentMethod($sPaymentId) === false || !isset($this->aPaymentMethods[$sPaymentId]['model'])) {
            throw new \Exception('Mollie Payment method unknown - '.$sPaymentId);
        }

        $oPaymentModel = oxNew($this->aPaymentMethods[$sPaymentId]['model']);
        return $oPaymentModel;
    }

    /**
     * Returns configured mode of mollie
     *
     * @return string
     */
    public function getMollieMode()
    {
        return Registry::getConfig()->getShopConfVar('sMollieMode');
    }

    /**
     * Return Mollie token depending on configured mode
     *
     * @param  string|bool $sMode
     * @return string
     */
    public function getMollieToken($sMode = false)
    {
        if ($sMode === false) {
            $sMode = $this->getMollieMode();
        }
        if ($sMode == 'live') {
            return Registry::getConfig()->getShopConfVar('sMollieLiveToken');
        }
        return Registry::getConfig()->getShopConfVar('sMollieTestToken');
    }

    /**
     * Collect information about all activated Mollie payment types
     *
     * @param double|bool $dAmount
     * @param string|bool $sCurrency
     * @return array
     */
    public function getMolliePaymentInfo($dAmount = false, $sCurrency = false)
    {
        if ($this->aPaymentInfo === null || ($dAmount !== false && $sCurrency !== false)) {
            $aParams = ['resource' => 'orders', 'includeWallets' => 'applepay'];
            if ($dAmount !== false && $sCurrency !== false) {
                $aParams['amount[value]'] = number_format($dAmount, 2, '.', '');
                $aParams['amount[currency]'] = $sCurrency;
            }
            $aPaymentInfo = [];
            try {
                $aMollieInfo = $this->loadMollieApi()->methods->all($aParams);
                foreach ($aMollieInfo as $oItem) {
                    $aPaymentInfo[$oItem->id] = [
                        'title' => $oItem->description,
                        'pic' => $oItem->image->size2x,
                        'minAmount' => $oItem->minimumAmount,
                        'maxAmount' => $oItem->maximumAmount,
                    ];
                }
            } catch (\Exception $exc) {
                error_log($exc->getMessage());
            }
            $this->aPaymentInfo = $aPaymentInfo;
        }
        return $this->aPaymentInfo;
    }

    /**
     * Check if connection with token can be established
     *
     * @param  string $sTokenConfVar
     * @return bool
     */
    public function isConnectionWithTokenSuccessful($sTokenConfVar)
    {
        try {
            $sMode = stripos($sTokenConfVar, 'live') !== false ? 'live' : 'test';
            $aMollieInfo = $this->loadMollieApi($sMode)->methods->all(['resource' => 'orders', 'includeWallets' => 'applepay']);
            if (empty($aMollieInfo)) {
                return false;
            }
        } catch (\Exception $exc) {
            return false;
        }
        return true;
    }

    /**
     * Return Mollie module version
     *
     * @return string
     */
    protected function getModuleVersion()
    {
        $module = oxNew(Module::class);
        $module->load('molliepayment');
        return $module->getInfo('version');
    }

    /**
     * Returns oxid shop version
     *
     * @return string
     */
    protected function getShopVersion()
    {
        return Registry::getConfig()->getActiveShop()->oxshops__oxversion->value;
    }

    /**
     * Instantiate MollieApiClient
     *
     * @param  string|bool $sMode
     * @return \Mollie\Api\MollieApiClient
     * @throws \Exception
     */
    public function loadMollieApi($sMode = false)
    {
        try {
            $sMollieToken = $this->getMollieToken($sMode);
            if (!$sMollieToken) {
                throw new \Exception('Mollie API token is not configured');
            }
            if (class_exists('Mollie\Api\MollieApiClient')) {
                $mollieApi = oxNew(\Mollie\Api\MollieApiClient::class);
                $mollieApi->setApiKey($sMollieToken);

                $mollieApi->addVersionString("MollieOxid/".$this->getModuleVersion());
                $mollieApi->addVersionString("Oxid/".$this->getShopVersion());
                return $mollieApi;
            } else {
                throw new \Exception('Class Mollie\Api\MollieApiClient does not exist');
            }
        } catch(\Exception $e) {
            error_log($e->getMessage());
            throw $e;
        }
    }

    /**
     * Returns current Mollie profileId
     *
     * @return string
     * @throws \Mollie\Api\Exceptions\ApiException
     */
    public function getProfileId()
    {
        $mollieApi = $this->loadMollieApi();
        return $mollieApi->profiles->getCurrent()->id;
    }

    /**
     * Generates locale string
     * Oxid doesnt have a locale logic, so solving it with by using the language files
     *
     * @return string
     */
    public function getLocale()
    {
        $sLocale = Registry::getLang()->translateString('MOLLIE_LOCALE');
        if (Registry::getLang()->isTranslated() === false) {
            $sLocale = 'en_US'; // default
        }
        return $sLocale;
    }
}
