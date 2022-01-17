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
                $oMolliePayment = $oApplePay->getMolliePaymentModel();
                if ($oMolliePayment && $oMolliePayment->isMolliePaymentActive() && $oMolliePayment->mollieIsBasketSumInLimits($dPrice) === true) {
                    return true;
                }
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

    /**
     * Returns if Apple Pay button should be shown on basket page
     *
     * @return bool
     */
    public function mollieShowApplePayButtonOnBasket()
    {
        return (bool)Registry::getConfig()->getShopConfVar('blMollieApplePayButtonOnBasket');
    }

    /**
     * Returns if Apple Pay button should be shown on product details page
     *
     * @return bool
     */
    public function mollieShowApplePayButtonOnDetails()
    {
        return (bool)Registry::getConfig()->getShopConfVar('blMollieApplePayButtonOnDetails');
    }

    /**
     * Functionality from Basket->getPriceForPayment() but without the delivery costs since they are added later
     *
     * @return double
     */
    public function mollieGetApplePayBasketSum()
    {
        $oBasket = Registry::getSession()->getBasket();

        $dPrice = $oBasket->getDiscountedProductsBruttoPrice();
        //#1905 not discounted products should be included in payment amount calculation
        if ($oPriceList = $oBasket->getNotDiscountProductsPrice()) {
            $dPrice += $oPriceList->getBruttoSum();
        }

        return $dPrice;
    }
}
