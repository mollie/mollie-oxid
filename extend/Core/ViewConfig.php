<?php

namespace Mollie\Payment\extend\Core;

use OxidEsales\Eshop\Core\Registry;
use Mollie\Payment\Application\Helper\DeliverySet;
use Mollie\Payment\Application\Helper\Payment;

class ViewConfig extends ViewConfig_parent
{
    /**
     * Returns if the show icons option was enabled in admin
     *
     * @return bool
     */
    public function mollieShowIcons()
    {
        return (bool)Registry::getConfig()->getShopConfVar('blMollieShowIcons');
    }

    /**
     * Determines if Apple Pay button can be displayed
     *
     * @param  double $dPrice
     * @return bool
     */
    public function mollieCanShowApplePayButton($dPrice)
    {
        $oApplePay = oxNew(\OxidEsales\Eshop\Application\Model\Payment::class);
        if (Payment::getInstance()->getMollieMode() == 'live' && $oApplePay->load('mollieapplepay')) { // Apple Pay only available in live mode
            if ($oApplePay->oxpayments__oxactive->value == 1 && ($oApplePay->oxpayments__oxfromamount->value <= $dPrice && $oApplePay->oxpayments__oxtoamount->value >= $dPrice)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns the configured home country of the shop
     *
     * @return bool
     */
    protected function mollieGetHomeCountry()
    {
        $aHomeCountries = Registry::getConfig()->getConfigParam('aHomeCountry');
        if (!empty($aHomeCountries)) {
            $sCountryId = current($aHomeCountries); // get first element

            $oCountry = oxNew(\OxidEsales\Eshop\Application\Model\Country::class);
            if ($oCountry->load($sCountryId)) {
                return $oCountry;
            }
        }
        return false;
    }

    /**
     * Returns iso2 country code of the homecountry of the shop
     *
     * @return string|bool
     */
    public function mollieGetHomeCountryCode()
    {
        $oCountry = DeliverySet::getInstance()->getHomeCountry();
        if ($oCountry !== false) {
            return $oCountry->oxcountry__oxisoalpha2->value;
        }
        return false;
    }

    /**
     * Returns current currency
     *
     * @return string
     */
    public function mollieGetCurrentCurrency()
    {
        return Registry::getConfig()->getActShopCurrencyObject()->name;
    }

    /**
     * Returns shop url
     *
     * @return string
     */
    public function mollieGetShopUrl()
    {
        return rtrim(Registry::getConfig()->getSslShopUrl(), '/').'/';
    }
}
