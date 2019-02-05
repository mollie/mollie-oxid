<?php

namespace Mollie\Payment\Application\Helper;

use Mollie\Payment\Application\Model\Payment\Base;
use OxidEsales\Eshop\Core\Registry;

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
        'molliebitcoin'         => array('title' => 'Bitcoin',          'model' => \Mollie\Payment\Application\Model\Payment\Bitcoin::class),
        'molliecreditcard'      => array('title' => 'Credit Card',      'model' => \Mollie\Payment\Application\Model\Payment\Creditcard::class),
        'mollieeps'             => array('title' => 'EPS Österreich',   'model' => \Mollie\Payment\Application\Model\Payment\Eps::class),
        'molliegiftcard'        => array('title' => 'Giftcard',         'model' => \Mollie\Payment\Application\Model\Payment\Giftcard::class),
        'molliegiropay'         => array('title' => 'Giropay',          'model' => \Mollie\Payment\Application\Model\Payment\Giropay::class),
        'mollieideal'           => array('title' => 'iDeal',            'model' => \Mollie\Payment\Application\Model\Payment\Ideal::class),
        'mollieinghomepay'      => array('title' => 'ING Homepay',      'model' => \Mollie\Payment\Application\Model\Payment\IngHomepay::class),
        'molliekbc'             => array('title' => 'KBC',              'model' => \Mollie\Payment\Application\Model\Payment\Kbc::class),
        'mollieklarnapaylater'  => array('title' => 'Klarna Pay Later', 'model' => \Mollie\Payment\Application\Model\Payment\KlarnaPayLater::class),
        'mollieklarnasliceit'   => array('title' => 'Klarna Slice It',  'model' => \Mollie\Payment\Application\Model\Payment\KlarnaSliceIt::class),
        'molliepaypal'          => array('title' => 'Paypal',           'model' => \Mollie\Payment\Application\Model\Payment\PayPal::class),
        'molliepaysafecard'     => array('title' => 'Paysafecard',      'model' => \Mollie\Payment\Application\Model\Payment\Paysafecard::class),
        'molliesofort'          => array('title' => 'Sofort',           'model' => \Mollie\Payment\Application\Model\Payment\Sofort::class),
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
     * Return Mollie token depending on configured mode
     *
     * @return string
     */
    public function getMollieToken()
    {
        if (Registry::getConfig()->getShopConfVar('sMollieMode') == 'live') {
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
            $aParams = ['resource' => 'orders'];
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
     * Instantiate MollieApiClient
     *
     * @return \Mollie\Api\MollieApiClient
     * @throws \Exception
     */
    public function loadMollieApi()
    {
        try {
            if (!$this->getMollieToken()) {
                throw new \Exception('Mollie API token is not configured');
            }
            if (class_exists('Mollie\Api\MollieApiClient')) {
                $mollieApi = new \Mollie\Api\MollieApiClient();
                $mollieApi->setApiKey($this->getMollieToken());
                return $mollieApi;
            } else {
                throw new \Exception('Class Mollie\Api\MollieApiClient does not exist');
            }
        } catch(\Exception $e) {
            error_log($e->getMessage());
            throw $e;
        }
    }
}
