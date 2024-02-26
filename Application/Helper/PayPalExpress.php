<?php

namespace Mollie\Payment\Application\Helper;

use OxidEsales\Eshop\Core\Registry;

class PayPalExpress
{
    /**
     * @var PayPalExpress
     */
    protected static $oInstance = null;

    protected $aPayPalExpressFallBackButtons = [
        "en" => "out/img/ppe/en/rounded_pay_golden.png",
        "de" => "out/img/ppe/de/rounded_pay_golden.png",
        "nl" => "out/img/ppe/nl/rounded_pay_golden.png",
        "fr" => "out/img/ppe/fr/rounded_pay_golden.png",
        "pl" => "out/img/ppe/pl/rounded_pay_golden.png",
    ];

    /**
     * Create singleton instance of order helper
     *
     * @return PayPalExpress
     */
    public static function getInstance()
    {
        if (self::$oInstance === null) {
            self::$oInstance = oxNew(self::class);
        }
        return self::$oInstance;
    }

    /**
     * Returns if a PayPal Express session is active
     *
     * @return bool
     */
    public function isMolliePayPalExpressCheckout()
    {
        if (!empty(Registry::getSession()->getVariable('mollie_ppe_sessionId')) && !empty(Registry::getSession()->getVariable('mollie_ppe_authenticationId'))) {
            return true;
        }
        return false;
    }

    /**
     * Cancels Mollie PayPal Express Session and reenables the payment list
     *
     * @return void
     */
    public function mollieCancelPayPalExpress($blCancelSession = false)
    {
        $blCancelSession = false; // Mollie API throws errors when trying to cancel, so this is disabled for now
        if ($blCancelSession === true) {
            $sSessionId = Registry::getSession()->getVariable('mollie_ppe_sessionId');

            $oMollieApi = Payment::getInstance()->loadMollieApi();
            $oSession = $oMollieApi->sessions->get($sSessionId);
            $oSession->cancel();
        }

        Registry::getSession()->deleteVariable('mollie_ppe_sessionId');
        Registry::getSession()->deleteVariable('mollie_ppe_authenticationId');
    }

    /**
     * Formats path to a PayPal Express button image
     *
     * @param  string $sLang
     * @param  string $sShape
     * @param  string $sType
     * @param  string $sColor
     * @return string
     */
    protected function getPayPalButtonPath($sLang, $sShape, $sType, $sColor)
    {
        return "out/img/ppe/".$sLang."/".$sShape."_".$sType."_".$sColor.".png";
    }

    /**
     * Returns a default fallback button
     *
     * @param  string $sLang
     * @return mixed|string
     */
    protected function getFallbackPayPalButtonUrl($sLang)
    {
        if (isset($this->aPayPalExpressFallBackButtons[$sLang])) {
            return $this->aPayPalExpressFallBackButtons[$sLang];
        }
        return $this->getPayPalButtonPath("en", "rounded", "pay", "golden");
    }

    /**
     * Returns url to a PayPal Express button image
     *
     * @param  string $sLang
     * @param  string $sShape
     * @param  string $sType
     * @param  string $sColor
     * @return string
     */
    public function getPayPalButtonUrl($sLang = null, $sShape = null, $sType = null, $sColor = null)
    {
        if (!$sLang) {
            $aLangArray = Registry::getLang()->getLanguageArray();
            $sLang = $aLangArray[Registry::getLang()->getTplLanguage()]->abbr;
            error_log("Lang-Abbr: ".$sLang.PHP_EOL, 3, __DIR__."/lang.log");
            if (!in_array($sLang, array_keys($this->aPayPalExpressFallBackButtons))) {
                $sLang = "en";
            }
        }
        if (!$sShape) {
            $sShape = Registry::getConfig()->getShopConfVar('sMolliePPEButtonShape');
        }
        if (!$sType) {
            $sType = Registry::getConfig()->getShopConfVar('sMolliePPEButtonType');
        }
        if (!$sColor) {
            $sColor = Registry::getConfig()->getShopConfVar('sMolliePPEButtonColor');
        }

        $sImagePath = $this->getPayPalButtonPath($sLang, $sShape, $sType, $sColor);
        if (!file_exists(getShopBasePath()."/modules/mollie/molliepayment/".$sImagePath)) {
            $sImagePath = $this->getFallbackPayPalButtonUrl($sLang);
        }
        return Registry::getConfig()->getActiveView()->getViewConfig()->getModuleUrl('molliepayment', $sImagePath);
    }
}
